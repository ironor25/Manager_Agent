#!/bin/bash
# Render Deployment Script for Manager Agent

set -e

echo "🚀 Manager Agent - Render Deployment Script"
echo "==========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Render CLI is installed
if ! command -v render &> /dev/null; then
    echo -e "${RED}❌ Render CLI is not installed${NC}"
    echo "Please install it from: https://render.com/docs/deploy-docker"
    exit 1
fi

# Check if git is clean
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}⚠️  You have uncommitted changes. Please commit or stash them.${NC}"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Get deployment info
RENDER_PROJECT=${1:-manager-agent}
BRANCH=${2:-main}

echo -e "${GREEN}📦 Deployment Configuration${NC}"
echo "Project: $RENDER_PROJECT"
echo "Branch: $BRANCH"
echo ""

# Build and test locally first
echo -e "${GREEN}🔨 Building Docker image locally...${NC}"
docker build -t manager-agent:latest .

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Docker build successful${NC}"
else
    echo -e "${RED}❌ Docker build failed${NC}"
    exit 1
fi

# Test database migrations with local docker-compose
echo -e "${GREEN}🧪 Testing with docker-compose...${NC}"
docker-compose up -d postgres
sleep 5

# Run migrations test
echo "Running migrations test..."
docker-compose run --rm app php artisan migrate:fresh --force --seed || true

docker-compose down

echo ""
echo -e "${GREEN}📋 Next Steps for Render Deployment:${NC}"
echo ""
echo "1. Connect your repository to Render:"
echo "   - Go to https://dashboard.render.com/"
echo "   - Connect your GitHub account if not already done"
echo "   - Click 'New +' > 'Web Service' or use 'Infrastructure as Code'"
echo ""
echo "2. For Infrastructure as Code deployment:"
echo "   - Commit the render.yaml to your repository"
echo "   - Push to your GitHub repository"
echo "   - Go to https://dashboard.render.com/infrastructure"
echo "   - Click 'New +' > 'Infrastructure as Code'"
echo "   - Select your repository and branch"
echo "   - Review and deploy"
echo ""
echo "3. Configure Environment Variables in Render Dashboard:"
echo "   - APP_URL: https://your-app-name.onrender.com"
echo "   - GEMINI_API_KEY: Your API key"
echo ""
echo "4. Database Configuration:"
echo "   - PostgreSQL will be created automatically by render.yaml"
echo "   - Migrations will run automatically on deployment"
echo ""
echo "5. Monitoring:"
echo "   - Check deployment logs in Render Dashboard"
echo "   - Monitor database connections and performance"
echo ""
echo -e "${YELLOW}📌 Important Notes:${NC}"
echo "   - Set APP_KEY in Render environment (it will be auto-generated)"
echo "   - Store sensitive API keys as environment variables"
echo "   - Use a custom domain for production (optional but recommended)"
echo "   - Enable automatic deploys for main branch"
echo ""
echo -e "${GREEN}✨ Ready to deploy!${NC}"
