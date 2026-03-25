.PHONY: help install build up down restart logs test lint clean deploy-staging deploy-production backup health-check dev-up dev-down dev-logs dev-fresh dev-shell

# Default target
.DEFAULT_GOAL := help

help: ## Show this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	@echo "Installing PHP dependencies..."
	docker compose exec app composer install
	@echo "Installing NPM dependencies..."
	npm install
	@echo "✅ Dependencies installed"

build: ## Build Docker images
	@echo "Building Docker images..."
	docker compose build
	@echo "✅ Build complete"

up: ## Start containers
	@echo "Starting containers..."
	docker compose up -d
	@echo "✅ Containers started"
	@make health-check

down: ## Stop containers
	@echo "Stopping containers..."
	docker compose down
	@echo "✅ Containers stopped"

restart: down up ## Restart containers

logs: ## View container logs
	docker compose logs -f app

dev-up: ## Start local development stack with Vite HMR
	@echo "Starting development stack..."
	docker compose -f docker-compose.dev.yml up -d
	@echo "✅ Dev stack started: app=http://localhost:8000 vite=http://localhost:5173"

dev-down: ## Stop local development stack
	@echo "Stopping development stack..."
	docker compose -f docker-compose.dev.yml down
	@echo "✅ Dev stack stopped"

dev-logs: ## View development logs for app and Vite
	docker compose -f docker-compose.dev.yml logs -f app vite

dev-fresh: ## Fresh install with migrations and seeds in dev stack
	@echo "Running fresh development installation..."
	docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed
	@echo "✅ Dev database refreshed"

dev-shell: ## Open shell in development app container
	docker compose -f docker-compose.dev.yml exec app sh

test: ## Run tests
	@echo "Running tests..."
	docker compose exec app php artisan test
	@echo "✅ Tests complete"

lint: ## Run code linters
	@echo "Running Laravel Pint..."
	docker compose exec app ./vendor/bin/pint
	@echo "✅ Linting complete"

clean: ## Clean up containers, volumes, and caches
	@echo "Cleaning up..."
	docker compose down -v
	rm -rf vendor node_modules
	@echo "✅ Cleanup complete"

deploy-staging: ## Deploy to staging environment
	@echo "Deploying to staging..."
	@chmod +x scripts/*.sh
	@./scripts/pre-deploy-check.sh
	@./scripts/backup-db.sh staging
	docker compose pull
	docker compose up -d
	@sleep 10
	docker compose exec -T app php artisan migrate --force
	docker compose exec -T app php artisan optimize
	@./scripts/health-check.sh staging
	@echo "✅ Staging deployment complete"

deploy-production: ## Deploy to production environment
	@echo "Deploying to production..."
	@chmod +x scripts/*.sh
	@./scripts/pre-deploy-check.sh
	@./scripts/backup-db.sh production
	docker compose exec -T app php artisan down --retry=60
	docker compose pull
	docker compose up -d
	@sleep 15
	docker compose exec -T app php artisan migrate --force
	docker compose exec -T app php artisan optimize
	docker compose exec -T app php artisan up
	@./scripts/health-check.sh production
	@echo "✅ Production deployment complete"

backup: ## Create database backup
	@echo "Creating database backup..."
	@chmod +x scripts/backup-db.sh
	@./scripts/backup-db.sh production
	@echo "✅ Backup complete"

health-check: ## Run health check
	@echo "Running health check..."
	@chmod +x scripts/health-check.sh
	@./scripts/health-check.sh staging || true

rollback: ## Rollback to previous version
	@echo "Rolling back deployment..."
	@chmod +x scripts/rollback.sh
	@./scripts/rollback.sh production
	@echo "✅ Rollback complete"

optimize: ## Optimize Laravel application
	@echo "Optimizing application..."
	docker compose exec app php artisan optimize
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache
	@echo "✅ Optimization complete"

migrate: ## Run database migrations
	@echo "Running migrations..."
	docker compose exec app php artisan migrate
	@echo "✅ Migrations complete"

fresh: ## Fresh install with migrations and seeds
	@echo "Running fresh installation..."
	docker compose exec app php artisan migrate:fresh --seed
	@echo "✅ Fresh install complete"

shell: ## Open shell in app container
	docker compose exec app sh

db-shell: ## Open MySQL shell
	docker compose exec db mysql -uroot -proot laravel

composer-update: ## Update Composer dependencies
	@echo "Updating Composer dependencies..."
	docker compose exec app composer update
	@echo "✅ Composer update complete"

npm-update: ## Update NPM dependencies
	@echo "Updating NPM dependencies..."
	npm update
	@echo "✅ NPM update complete"

security-check: ## Run security audit
	@echo "Running security audit..."
	docker compose exec app composer audit
	npm audit
	@echo "✅ Security check complete"
