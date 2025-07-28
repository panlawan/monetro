#!/bin/bash
set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}🚀 Deploying to Production...${NC}"

# Load environment
if [[ -f ".env.production" ]]; then
    source .env.production
fi

# Enable maintenance mode
echo -e "${YELLOW}🔧 Enabling maintenance mode...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan down --retry=60 || true

# Create backup
echo -e "${YELLOW}💾 Creating backup...${NC}"
BACKUP_DIR="./backups/$(date +'%Y%m%d_%H%M%S')"
mkdir -p "$BACKUP_DIR"
docker-compose -f docker-compose.prod.yml exec -T mysql mysqldump \
    -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
    > "${BACKUP_DIR}/database.sql" || true

# Pull latest images and recreate
echo -e "${YELLOW}🔄 Updating containers...${NC}"
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d --force-recreate

# Wait for application
echo -e "${YELLOW}⏳ Waiting for application...${NC}"
sleep 30

# Run Laravel commands
echo -e "${YELLOW}⚙️ Running Laravel commands...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec -T app php artisan optimize
docker-compose -f docker-compose.prod.yml exec -T app php artisan queue:restart

# Health check
echo -e "${YELLOW}🏥 Running health check...${NC}"
if curl -f -s "http://localhost/health" > /dev/null; then
    echo -e "${GREEN}✅ Health check passed${NC}"
else
    echo -e "${RED}❌ Health check failed - check logs${NC}"
fi

# Disable maintenance mode
echo -e "${YELLOW}🔓 Disabling maintenance mode...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan up

echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
