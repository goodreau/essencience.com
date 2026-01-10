#!/bin/bash

# Ollama Setup & Test Script for Essencience.com
# This script starts Ollama containers and verifies the setup

set -e

echo "🚀 Essencience.com - Ollama Local AI Pilot Setup"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if Docker is running
echo -e "${BLUE}📦 Checking Docker...${NC}"
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker is not running. Please start Docker first.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Docker is running${NC}"
echo ""

# Navigate to project root
PROJECT_ROOT="/Volumes/EXTERNAL/Essencience.com"
if [ ! -d "$PROJECT_ROOT" ]; then
    echo -e "${RED}❌ Project root not found: $PROJECT_ROOT${NC}"
    exit 1
fi
cd "$PROJECT_ROOT"

# Start containers
echo -e "${BLUE}🐳 Starting RAG containers...${NC}"
docker-compose -f docker-compose.rag.yml up -d

echo -e "${YELLOW}⏳ Waiting for services to initialize (15 seconds)...${NC}"
sleep 15

# Check Ollama
echo ""
echo -e "${BLUE}🤖 Checking Ollama...${NC}"
if curl -s http://localhost:11434/api/tags > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Ollama is running on http://localhost:11434${NC}"
else
    echo -e "${YELLOW}⚠️  Ollama not responding yet, it may still be starting${NC}"
fi

# Check Qdrant
echo -e "${BLUE}📊 Checking Qdrant Vector Database...${NC}"
if curl -s http://localhost:6333/health > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Qdrant is running on http://localhost:6333${NC}"
else
    echo -e "${YELLOW}⚠️  Qdrant not responding yet${NC}"
fi

# Check Redis
echo -e "${BLUE}📍 Checking Redis...${NC}"
if redis-cli -h localhost -p 6379 ping > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Redis is running on localhost:6379${NC}"
else
    echo -e "${YELLOW}⚠️  Redis not responding yet${NC}"
fi

echo ""
echo -e "${BLUE}📥 Pulling LLM models...${NC}"
echo "This may take several minutes..."
echo ""

# Pull embedding model
echo -e "${YELLOW}⏳ Pulling nomic-embed-text (embeddings)...${NC}"
docker exec essencience-ollama ollama pull nomic-embed-text
echo -e "${GREEN}✅ Embedding model downloaded${NC}"

# Pull main LLM
echo -e "${YELLOW}⏳ Pulling llama3.2 (main LLM)...${NC}"
docker exec essencience-ollama ollama pull llama3.2
echo -e "${GREEN}✅ Main LLM model downloaded${NC}"

echo ""
echo -e "${BLUE}📋 Available models:${NC}"
docker exec essencience-ollama ollama list

echo ""
echo "=================================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=================================================="
echo ""
echo -e "${BLUE}🔗 Service URLs:${NC}"
echo "  • Ollama:    http://localhost:11434"
echo "  • Qdrant:    http://localhost:6333"
echo "  • Redis:     localhost:6379"
echo ""
echo -e "${BLUE}📝 Next Steps:${NC}"
echo "  1. Test Ollama: curl http://localhost:11434/api/tags"
echo "  2. Read OLLAMA_SETUP.md for detailed documentation"
echo "  3. Update laravel-temp/.env with OLLAMA settings"
echo "  4. Run: cd laravel-temp && php artisan serve"
echo "  5. Visit: http://localhost:8000 in your browser"
echo ""
echo -e "${BLUE}💻 Test the AI:${NC}"
echo "  1. Create a route: Route::get('/ai-chat', \\App\\Livewire\\AiChatbox::class)"
echo "  2. Visit: http://localhost:8000/ai-chat"
echo "  3. Start chatting with the AI!"
echo ""
echo -e "${YELLOW}⚠️  Troubleshooting:${NC}"
echo "  • If Ollama doesn't respond: docker logs essencience-ollama"
echo "  • To stop containers: docker-compose -f docker-compose.rag.yml down"
echo "  • To remove all data: docker-compose -f docker-compose.rag.yml down -v"
echo ""
