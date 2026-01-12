# RAG Container Mode - Essencience.com

## Overview
This setup provides a complete RAG (Retrieval-Augmented Generation) infrastructure for Essencience.com using containerized services.

## Architecture

### Components
1. **Qdrant** - Vector database for semantic search
2. **Ollama** - Local LLM for embeddings and text generation
3. **PostgreSQL + pgvector** - Relational database with vector support
4. **Redis** - Caching layer for performance
5. **Meilisearch** - Fast full-text search engine

## Quick Start

### 1. Start RAG Containers
```bash
chmod +x rag-setup.sh
./rag-setup.sh
```

### 2. Manual Start (Alternative)
```bash
docker-compose -f docker-compose.rag.yml up -d
```

### 3. Pull LLM Models
```bash
docker exec essencience-ollama ollama pull llama3.2
docker exec essencience-ollama ollama pull nomic-embed-text
```

## Service URLs

| Service | URL | Purpose |
|---------|-----|---------|
| Qdrant | http://localhost:6333 | Vector similarity search |
| Ollama | http://localhost:11434 | LLM embeddings & generation |
| Redis | localhost:6379 | Query caching |
| PostgreSQL | localhost:5432 | Structured data + vectors |
| Meilisearch | http://localhost:7700 | Full-text search |

## Laravel Integration

### 1. Update .env
```env
# Add to laravel-temp/.env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=essencience_rag
DB_USERNAME=essencience
DB_PASSWORD=EssencienceRAG2026!

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QDRANT_HOST=localhost
QDRANT_PORT=6333

OLLAMA_HOST=http://localhost:11434
```

### 2. Install PHP Packages
```bash
cd laravel-temp
composer require pgvector/pgvector
composer require qdrant/php-client
composer require guzzlehttp/guzzle
```

### 3. Create RAG Service
Create `app/Services/RagService.php`:
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Qdrant\Client as QdrantClient;

class RagService
{
    private $qdrant;
    private $ollama;
    
    public function __construct()
    {
        $this->qdrant = new QdrantClient(
            config('services.qdrant.host'),
            config('services.qdrant.port')
        );
        $this->ollama = config('services.ollama.host');
    }
    
    public function embed(string $text): array
    {
        $response = Http::post("{$this->ollama}/api/embeddings", [
            'model' => 'nomic-embed-text',
            'prompt' => $text
        ]);
        
        return $response->json()['embedding'];
    }
    
    public function search(string $query, int $limit = 5): array
    {
        $embedding = $this->embed($query);
        
        return $this->qdrant->search(
            'essencience_content',
            $embedding,
            $limit
        );
    }
}
```

## Usage Examples

### Index Content
```php
use App\Services\RagService;

$rag = new RagService();

// Generate embedding
$embedding = $rag->embed("Philosophy of Essencience");

// Store in Qdrant
$rag->qdrant->upsert('essencience_content', [
    'id' => 1,
    'vector' => $embedding,
    'payload' => [
        'title' => 'Essencience Philosophy',
        'content' => '...',
        'category' => 'philosophy'
    ]
]);
```

### Search Content
```php
$results = $rag->search("What is consciousness?", limit: 5);

foreach ($results as $result) {
    echo $result['payload']['title'];
}
```

## Management Commands

### Check Status
```bash
docker-compose -f docker-compose.rag.yml ps
```

### View Logs
```bash
docker-compose -f docker-compose.rag.yml logs -f
```

### Stop Services
```bash
docker-compose -f docker-compose.rag.yml stop
```

### Remove Everything
```bash
docker-compose -f docker-compose.rag.yml down -v
```

### Access Services Directly

#### Qdrant Dashboard
```bash
open http://localhost:6333/dashboard
```

#### Ollama CLI
```bash
docker exec -it essencience-ollama ollama run llama3.2
```

#### Redis CLI
```bash
docker exec -it essencience-redis redis-cli
```

#### PostgreSQL CLI
```bash
docker exec -it essencience-postgres psql -U essencience -d essencience_rag
```

## Advanced Configuration

### GPU Support (Mac/Linux)
Ollama automatically uses GPU if available. For NVIDIA GPUs, ensure `nvidia-docker` is installed.

### Custom Models
```bash
# List available models
docker exec essencience-ollama ollama list

# Pull specific model
docker exec essencience-ollama ollama pull mistral

# Use custom model
docker exec essencience-ollama ollama run codellama
```

### Qdrant Collections
```bash
# Create collection via API
curl -X PUT http://localhost:6333/collections/essencience_content \
  -H 'Content-Type: application/json' \
  -d '{
    "vectors": {
      "size": 768,
      "distance": "Cosine"
    }
  }'
```

## Troubleshooting

### Containers Won't Start
```bash
# Check Docker
docker info

# View specific container logs
docker logs essencience-qdrant
docker logs essencience-ollama
```

### Port Conflicts
Edit `docker-compose.rag.yml` to change ports:
```yaml
ports:
  - "6333:6333"  # Change left side only
```

### Reset Everything
```bash
docker-compose -f docker-compose.rag.yml down -v
./rag-setup.sh
```

## Performance Tuning

### Redis Memory
```bash
docker exec essencience-redis redis-cli CONFIG SET maxmemory 256mb
docker exec essencience-redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

### PostgreSQL
Edit `docker-compose.rag.yml`:
```yaml
command: postgres -c shared_buffers=256MB -c max_connections=200
```

## Security Notes

- Change default passwords in `.env.rag`
- Use API keys for production (Qdrant, Meilisearch)
- Enable SSL/TLS for external access
- Restrict ports with firewall rules

## Resources

- [Qdrant Documentation](https://qdrant.tech/documentation/)
- [Ollama Models](https://ollama.ai/library)
- [pgvector Guide](https://github.com/pgvector/pgvector)
- [Meilisearch Docs](https://docs.meilisearch.com/)
