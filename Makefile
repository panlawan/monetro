# Laravel Docker Development Commands

.PHONY: help build up down restart shell logs install fresh migrate seed test cache optimize

# Default command
help:
	@echo "Laravel Docker Development Commands"
	@echo "=================================="
	@echo "build      - Build Docker containers"
	@echo "up         - Start all services"
	@echo "down       - Stop all services"
	@echo "restart    - Restart all services"
	@echo "shell      - Access app container shell"
	@echo "logs       - Show logs"
	@echo "install    - Install dependencies"
	@echo "fresh      - Fresh install with migrations and seeds"
	@echo "migrate    - Run database migrations"
	@echo "seed       - Run database seeders"
	@echo "test       - Run tests"
	@echo "cache      - Clear and rebuild cache"
	@echo "optimize   - Optimize application"
	@echo "setup      - Complete setup (key, migrate, storage)"

# Build containers
build:
	docker-compose build --no-cache

# Start services
up:
	docker-compose up -d
	@echo "🌐 Application: http://localhost:8080"
	@echo "🗄️ PHPMyAdmin: http://localhost:8081"
	@echo "📧 Mailhog: http://localhost:8025"

# Stop services
down:
	docker-compose down

# Restart services
restart: down up

# Access app container
shell:
	docker-compose exec app bash

# Show logs
logs:
	docker-compose logs -f

# Install dependencies
install:
	docker-compose run --rm composer install
	docker-compose run --rm node npm install
	docker-compose run --rm node npm run build

# Fresh installation
fresh:
	docker-compose run --rm composer install
	docker-compose exec app php artisan key:generate --force
	docker-compose exec app php artisan migrate:fresh --seed
	docker-compose exec app php artisan storage:link

# Complete setup
setup:
	docker-compose exec app php artisan key:generate --force
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan storage:link
	docker-compose exec app php artisan config:cache

# Run migrations
migrate:
	docker-compose exec app php artisan migrate

# Run seeders
seed:
	docker-compose exec app php artisan db:seed

# Run tests
test:
	docker-compose exec app php artisan test

# Clear cache
cache:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Optimize application
optimize:
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

# Generate APP_KEY
key:
	docker-compose exec app php artisan key:generate --force
