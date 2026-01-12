# Ollama Setup Guide - Essencience.com Local AI Pilot

## Overview
This guide sets up Ollama as your local LLM (Large Language Model) service for the Essencience.com Laravel application. Ollama provides fast, efficient AI capabilities without relying on cloud APIs.

## Prerequisites

### System Requirements
- **macOS/Linux/Windows with WSL2**: Docker support
- **Disk Space**: ~15GB for models (Llama 3.2 + Nomic Embed Text)
- **RAM**: Minimum 8GB (16GB+ recommended)
- **GPU** (optional): NVIDIA GPU for faster inference

### Software Requirements
- Docker & Docker Compose installed
- Terminal/Command line access
- Git (for repository management)

## Quick Start (Docker Compose)

### 1. Start Ollama with Docker

```bash
# Navigate to project root
cd /Volumes/EXTERNAL/Essencience.com

# Make setup script executable
chmod +x rag-setup.sh

# Run the automated setup
./rag-setup.sh
```

**Or manually:**

```bash
# Start all RAG containers (Ollama, Qdrant, Redis, PostgreSQL, Meilisearch)
docker-compose -f docker-compose.rag.yml up -d

# Verify containers are running
docker-compose -f docker-compose.rag.yml ps
```

### 2. Pull LLM Models

Once containers are running, pull the models:

```bash
# Pull embedding model (required for RAG)
docker exec essencience-ollama ollama pull nomic-embed-text

# Pull main language model
docker exec essencience-ollama ollama pull llama3.2

# Alternative models (choose one or more):
docker exec essencience-ollama ollama pull llama2
docker exec essencience-ollama ollama pull mistral
docker exec essencience-ollama ollama pull neural-chat
```

### 3. Configure Laravel

Update your Laravel environment:

```bash
# Edit laravel-temp/.env
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.2
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_TIMEOUT=300
```

### 4. Install PHP Dependencies

```bash
cd laravel-temp

# Install HTTP client if not already present
composer require guzzlehttp/guzzle

# Optional: For PostgreSQL + pgvector support
composer require pgvector/pgvector
```

## Service Architecture

### Available Endpoints

| Service | URL | Purpose | Port |
|---------|-----|---------|------|
| **Ollama** | `http://localhost:11434` | LLM Inference & Embeddings | 11434 |
| **Qdrant** | `http://localhost:6333` | Vector Database (RAG) | 6333 |
| **Redis** | `localhost:6379` | Caching Layer | 6379 |
| **PostgreSQL** | `localhost:5432` | Relational Database | 5432 |
| **Meilisearch** | `http://localhost:7700` | Full-Text Search | 7700 |

## Usage Examples

### Basic Text Generation

```php
use App\Services\OllamaClient;

$ollama = new OllamaClient();

// Generate text
$response = $ollama->generate(
    "Write a short poem about Laravel",
    ['model' => 'llama3.2']
);

echo $response['response'];
```

### Chat Interface

```php
use App\Services\OllamaClient;

$ollama = new OllamaClient();

$messages = [
    [
        'role' => 'user',
        'content' => 'What is the capital of France?'
    ]
];

$response = $ollama->chat($messages);
echo $response['message']['content'];
```

### RAG Pipeline (Retrieval-Augmented Generation)

```php
use App\Services\RagService;

$rag = app(RagService::class);

// Complete RAG pipeline: embed → search → generate
$result = $rag->ragPipeline(
    "How do I use Livewire with Laravel?",
    collection: 'documentation'
);

echo $result['response'];
// Returns relevant documentation context with AI-generated answer
```

### Generate Embeddings

```php
use App\Services\RagService;

$rag = app(RagService::class);

// Generate embedding for semantic search
$embedding = $rag->generateEmbedding("Laravel Livewire tutorial");

// Search similar documents
$results = $rag->searchVectors($embedding, 'documents', limit: 5);
```

## Docker Management

### Monitor Logs

```bash
# Ollama logs
docker logs -f essencience-ollama

# Qdrant logs
docker logs -f essencience-qdrant

# All RAG containers
docker-compose -f docker-compose.rag.yml logs -f
```

### Stop Services

```bash
# Stop all containers
docker-compose -f docker-compose.rag.yml down

# Stop and remove volumes (WARNING: deletes data)
docker-compose -f docker-compose.rag.yml down -v
```

### Check Container Status

```bash
# List running containers
docker-compose -f docker-compose.rag.yml ps

# Check if Ollama is responsive
curl http://localhost:11434/api/tags

# Check Qdrant health
curl http://localhost:6333/health
```

## Available Ollama Models

### Recommended for Essencience.com

#### Llama 3.2 (Recommended)
- **Size**: ~8.5GB (7B params) or ~46GB (70B params)
- **Speed**: Fast, good quality
- **Use**: General purpose chat and generation
- **Pull**: `ollama pull llama3.2`

#### Nomic Embed Text (Required for RAG)
- **Size**: ~275MB
- **Purpose**: Vector embeddings for semantic search
- **Must Have**: Yes, for RAG functionality
- **Pull**: `ollama pull nomic-embed-text`

#### Mistral (Fast Alternative)
- **Size**: ~14GB
- **Speed**: Very fast, lower quality
- **Use**: When speed matters more than quality
- **Pull**: `ollama pull mistral`

#### Neural Chat (Optimized)
- **Size**: ~4GB
- **Speed**: Fast, optimized for conversations
- **Use**: Customer support, chatbots
- **Pull**: `ollama pull neural-chat`

### Other Available Models

```bash
ollama pull dolphin-mixtral   # Uncensored, powerful
ollama pull llama2            # Original Llama 2
ollama pull neural-chat       # Conversation optimized
ollama pull orca-mini         # Compact, good quality
ollama pull phi              # Small, efficient
```

## Laravel Service Integration

### OllamaClient Service

The `OllamaClient` service provides low-level access to Ollama endpoints:

```php
public function generate(string $prompt, array $options = []): array
public function chat(array $messages, array $options = []): array
```

### RagService

The `RagService` provides high-level RAG functionality:

```php
public function generateEmbedding(string $text): array
public function generateResponse(string $prompt, array $context = [], array $options = []): array
public function searchVectors(array $vector, string $collection = 'documents', int $limit = 5): array
public function storeEmbedding(int $pointId, array $vector, array $payload = [], string $collection = 'documents'): bool
public function ragPipeline(string $query, string $collection = 'documents', int $contextLimit = 5): array
```

## Creating a Livewire Component with Ollama

```php
<?php

namespace App\Livewire;

use App\Services\OllamaClient;
use Livewire\Component;

class AiAssistant extends Component
{
    #[Reactive]
    public string $message = '';
    
    public string $response = '';
    public bool $loading = false;
    
    #[Computed]
    public function history()
    {
        return cache()->get('chat_history', []);
    }
    
    public function askAi(): void
    {
        $this->loading = true;
        
        $ollama = app(OllamaClient::class);
        $result = $ollama->chat([
            ['role' => 'user', 'content' => $this->message]
        ]);
        
        $this->response = $result['message']['content'] ?? 'No response';
        $this->message = '';
        $this->loading = false;
    }
    
    public function render()
    {
        return view('livewire.ai-assistant');
    }
}
```

## Troubleshooting

### Ollama Won't Start

```bash
# Check Docker is running
docker ps

# Check Ollama logs
docker logs essencience-ollama

# Restart container
docker-compose -f docker-compose.rag.yml restart ollama
```

### Models Not Loading

```bash
# List available models
docker exec essencience-ollama ollama list

# Remove model and retry
docker exec essencience-ollama ollama rm llama3.2
docker exec essencience-ollama ollama pull llama3.2
```

### Connection Refused Errors

```bash
# Verify port is accessible
curl http://localhost:11434/api/tags

# Check container is actually running
docker ps | grep ollama

# Ensure Docker network is correct
docker-compose -f docker-compose.rag.yml ps
```

### Out of Memory Errors

```bash
# Check available memory
free -h  # Linux
vm_stat # macOS

# Use smaller model
docker exec essencience-ollama ollama pull orca-mini

# Increase Docker memory limit (Docker Desktop Settings)
```

### Slow Responses

- **Check CPU Usage**: Use smaller model (Mistral, Phi)
- **Enable GPU**: Ensure NVIDIA driver installed (see docker-compose.rag.yml)
- **Check System Resources**: Monitor with `top` or Activity Monitor

## Configuration Reference

### Environment Variables (.env)

```env
# Ollama Configuration
OLLAMA_BASE_URL=http://127.0.0.1:11434    # Ollama endpoint
OLLAMA_MODEL=llama3.2                      # Default model
OLLAMA_EMBEDDING_MODEL=nomic-embed-text    # Embedding model
OLLAMA_TIMEOUT=300                         # Request timeout (seconds)

# Qdrant Configuration
QDRANT_HOST=127.0.0.1
QDRANT_PORT=6333

# Redis (Caching)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Database (Optional PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=essencience_rag
DB_USERNAME=essencience
DB_PASSWORD=EssencienceRAG2026!
```

## Next Steps

1. ✅ Start containers with `./rag-setup.sh`
2. ✅ Pull models with `ollama pull`
3. ✅ Test with `curl http://localhost:11434/api/tags`
4. ✅ Create Livewire components using OllamaClient
5. ✅ Implement RAG pipeline for your features
6. 📚 Read [README-RAG.md](./README-RAG.md) for advanced setup
7. 🚀 Deploy to production with container orchestration

## Additional Resources

- [Ollama Documentation](https://github.com/ollama/ollama)
- [Qdrant Vector DB](https://qdrant.tech)
- [Laravel Livewire](https://laravel-livewire.com)
- [Vector Embeddings Guide](https://platform.openai.com/docs/guides/embeddings)

---

**Last Updated**: January 2026  
**Project**: Essencience.com  
**Framework**: Laravel + Livewire  
**AI Stack**: Ollama + Qdrant + Redis
