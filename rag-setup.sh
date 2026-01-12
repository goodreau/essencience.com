#!/bin/bash

# RAG Container Setup Script for Essencience.com
# This script initializes the RAG infrastructure

set -e

echo "🚀 Setting up RAG Container Mode for Essencience..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker first."
    exit 1
fi

# Load environment variables
if [ -f .env.rag ]; then
    export $(cat .env.rag | grep -v '^#' | xargs)
    echo "✅ Loaded RAG environment variables"
fi

# Start containers
echo "📦 Starting RAG containers..."
docker-compose -f docker-compose.rag.yml up -d

# Wait for services to be ready
echo "⏳ Waiting for services to initialize..."
sleep 10

# Check Qdrant
echo "🔍 Checking Qdrant vector database..."
if curl -s http://localhost:6333/health > /dev/null; then
    echo "✅ Qdrant is running"
else
    echo "⚠️  Qdrant is not responding yet"
fi

# Check Ollama
echo "🔍 Checking Ollama..."
if curl -s http://localhost:11434/api/tags > /dev/null; then
    echo "✅ Ollama is running"
    echo "📥 Pulling embedding model..."
    docker exec essencience-ollama ollama pull nomic-embed-text
    echo "📥 Pulling LLM model..."
    docker exec essencience-ollama ollama pull llama3.2
else
    echo "⚠️  Ollama is not responding yet"
fi

# Check Redis
echo "🔍 Checking Redis..."
if docker exec essencience-redis redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis is running"
else
    echo "⚠️  Redis is not responding yet"
fi

# Check PostgreSQL
echo "🔍 Checking PostgreSQL..."
if docker exec essencience-postgres pg_isready -U essencience > /dev/null 2>&1; then
    echo "✅ PostgreSQL is running"
    echo "🔧 Enabling pgvector extension..."
    docker exec essencience-postgres psql -U essencience -d essencience_rag -c "CREATE EXTENSION IF NOT EXISTS vector;"
else
    echo "⚠️  PostgreSQL is not responding yet"
fi

# Check Meilisearch
echo "🔍 Checking Meilisearch..."
if curl -s http://localhost:7700/health > /dev/null; then
    echo "✅ Meilisearch is running"
else
    echo "⚠️  Meilisearch is not responding yet"
fi

echo ""
echo "🎉 RAG Container Mode Setup Complete!"
echo ""
echo "📊 Service Endpoints:"
echo "  - Qdrant Vector DB: http://localhost:6333"
echo "  - Ollama LLM: http://localhost:11434"
echo "  - Redis Cache: localhost:6379"
echo "  - PostgreSQL: localhost:5432"
echo "  - Meilisearch: http://localhost:7700"
echo ""
echo "📝 Next Steps:"
echo "  1. Update laravel-temp/.env with database credentials"
echo "  2. Install Laravel packages: composer require pgvector/pgvector"
echo "  3. Create RAG service classes in app/Services/"
echo "  4. Index your content: php artisan rag:index"
echo ""
echo "🛑 To stop containers: docker-compose -f docker-compose.rag.yml down"
echo "🗑️  To remove volumes: docker-compose -f docker-compose.rag.yml down -v"
