<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\OllamaClient;
use App\Services\RagService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class AiChatbox extends Component
{
    #[Reactive]
    public string $userMessage = '';

    public string $aiResponse = '';
    public bool $isLoading = false;
    public array $messages = [];
    public string $mode = 'chat'; // 'chat' or 'rag'

    public function mount(): void
    {
        $this->messages = [];
    }

    /**
     * Send message and get AI response
     */
    public function sendMessage(): void
    {
        if (empty(trim($this->userMessage))) {
            return;
        }

        $this->isLoading = true;

        try {
            if ($this->mode === 'rag') {
                $this->handleRagQuery();
            } else {
                $this->handleChatQuery();
            }

            // Add user message to history
            $this->messages[] = [
                'role' => 'user',
                'content' => $this->userMessage,
            ];

            // Add AI response to history
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $this->aiResponse,
            ];

            $this->userMessage = '';
        } catch (\Exception $e) {
            $this->aiResponse = 'Error: ' . $e->getMessage();
            \Log::error('AI Chat Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Handle regular chat query
     */
    private function handleChatQuery(): void
    {
        $ollama = app(OllamaClient::class);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI assistant for the Essencience.com Laravel application. Provide concise, accurate answers.',
            ],
            ...$this->messages,
            [
                'role' => 'user',
                'content' => $this->userMessage,
            ],
        ];

        $response = $ollama->chat($messages, [
            'model' => config('ollama.default_model'),
        ]);

        $this->aiResponse = $response['message']['content'] ?? 'No response generated';
    }

    /**
     * Handle RAG query (with vector search context)
     */
    private function handleRagQuery(): void
    {
        $rag = app(RagService::class);

        // Use RAG pipeline to search and generate response
        $result = $rag->ragPipeline(
            $this->userMessage,
            collection: 'documents',
            contextLimit: 3
        );

        $this->aiResponse = $result['response'] ?? 'No response generated';

        // Log sources for debugging
        if (!empty($result['sources'])) {
            \Log::info('RAG Sources: ' . json_encode($result['sources']));
        }
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(): void
    {
        $this->messages = [];
        $this->aiResponse = '';
        $this->userMessage = '';
    }

    /**
     * Toggle between chat and RAG modes
     */
    public function toggleMode(): void
    {
        $this->mode = $this->mode === 'chat' ? 'rag' : 'chat';
        $this->clearHistory();
    }

    public function render()
    {
        return view('livewire.ai-chatbox');
    }
}
