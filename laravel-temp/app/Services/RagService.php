<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\GuzzleException;

class RagService
{
    private OllamaClient $ollama;
    private string $qadrantHost;
    private int $qdrantPort;

    public function __construct(OllamaClient $ollama)
    {
        $this->ollama = $ollama;
        $this->qadrantHost = config('services.qdrant.host', 'localhost');
        $this->qdrantPort = config('services.qdrant.port', 6333);
    }

    /**
     * Generate embeddings for text using Ollama
     *
     * @param string $text
     * @return array Vector embedding
     * @throws GuzzleException
     */
    public function generateEmbedding(string $text): array
    {
        $model = config('ollama.embedding_model', 'nomic-embed-text');

        $response = Http::baseUrl(config('ollama.base_url'))
            ->timeout(config('ollama.timeout', 60))
            ->post('/api/embeddings', [
                'model' => $model,
                'prompt' => $text,
            ]);

        $data = $response->json();

        return $data['embedding'] ?? [];
    }

    /**
     * Generate text using Ollama chat endpoint
     *
     * @param string $prompt
     * @param array $context Optional context for RAG
     * @param array $options Optional generation options
     * @return string Generated response
     * @throws GuzzleException
     */
    public function generateResponse(
        string $prompt,
        array $context = [],
        array $options = []
    ): string {
        $messages = [];

        // Add system context if provided
        if (!empty($context)) {
            $contextText = implode("\n\n", $context);
            $messages[] = [
                'role' => 'system',
                'content' => "You are a helpful AI assistant. Use the following context to answer questions:\n\n{$contextText}",
            ];
        }

        // Add user prompt
        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $chatOptions = array_merge([
            'model' => config('ollama.default_model', 'llama3.2'),
        ], $options);

        $response = $this->ollama->chat($messages, $chatOptions);

        return $response['message']['content'] ?? '';
    }

    /**
     * Search Qdrant vector database
     *
     * @param array $vector Embedding vector
     * @param string $collection Collection name
     * @param int $limit Number of results
     * @return array Search results
     */
    public function searchVectors(
        array $vector,
        string $collection = 'documents',
        int $limit = 5
    ): array {
        try {
            $response = Http::baseUrl("http://{$this->qadrantHost}:{$this->qdrantPort}")
                ->post("/collections/{$collection}/points/search", [
                    'vector' => $vector,
                    'limit' => $limit,
                    'with_payload' => true,
                ])
                ->throw();

            return $response->json()['result'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Qdrant search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Store embedding in Qdrant
     *
     * @param int $pointId Unique point ID
     * @param array $vector Embedding vector
     * @param array $payload Associated metadata
     * @param string $collection Collection name
     * @return bool Success status
     */
    public function storeEmbedding(
        int $pointId,
        array $vector,
        array $payload = [],
        string $collection = 'documents'
    ): bool {
        try {
            Http::baseUrl("http://{$this->qadrantHost}:{$this->qdrantPort}")
                ->put("/collections/{$collection}/points", [
                    'points' => [
                        [
                            'id' => $pointId,
                            'vector' => $vector,
                            'payload' => $payload,
                        ],
                    ],
                ])
                ->throw();

            return true;
        } catch (\Exception $e) {
            \Log::error('Qdrant store error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete RAG pipeline: embed query, search, and generate response
     *
     * @param string $query User query
     * @param string $collection Qdrant collection
     * @param int $contextLimit Number of context results
     * @return array{response: string, sources: array}
     */
    public function ragPipeline(
        string $query,
        string $collection = 'documents',
        int $contextLimit = 5
    ): array {
        // Step 1: Generate embedding for query
        $queryEmbedding = $this->generateEmbedding($query);

        if (empty($queryEmbedding)) {
            return [
                'response' => 'Failed to generate query embedding',
                'sources' => [],
            ];
        }

        // Step 2: Search for relevant documents
        $searchResults = $this->searchVectors($queryEmbedding, $collection, $contextLimit);

        // Step 3: Extract context from search results
        $context = array_map(function ($result) {
            return $result['payload']['text'] ?? '';
        }, $searchResults);

        // Filter out empty contexts
        $context = array_filter($context);

        // Step 4: Generate response with context
        $response = $this->generateResponse($query, $context);

        return [
            'response' => $response,
            'sources' => $searchResults,
        ];
    }
}
