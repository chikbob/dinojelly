#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

function print_status() {
    echo -e "${GREEN}==>${NC} $1"
}

function print_error() {
    echo -e "${RED}Error:${NC} $1"
}

function print_warning() {
    echo -e "${YELLOW}Warning:${NC} $1"
}

# Check if environment argument is provided
if [ -z "$1" ]; then
    print_error "Environment not specified. Usage: ./deploy.sh [local|staging|production]"
    exit 1
fi

ENVIRONMENT=$1

case $ENVIRONMENT in
    local)
        print_status "Deploying to local environment..."
        docker compose -f docker-compose.dev.yml down
        docker compose -f docker-compose.dev.yml build
        docker compose -f docker-compose.dev.yml up -d
        
        print_status "Waiting for services to be ready..."
        sleep 5
        
        print_status "Running migrations..."
        docker compose -f docker-compose.dev.yml exec -T app php artisan migrate
        
        print_status "Application is running at http://localhost:8000"
        ;;
        
    staging|production)
        print_status "Deploying to $ENVIRONMENT environment..."
        
        # Pull latest images
        print_status "Pulling latest images..."
        docker compose pull
        
        # Backup database
        print_status "Creating database backup..."
        docker compose exec -T db mysqldump -uroot -proot laravel > backup-$(date +%Y%m%d-%H%M%S).sql
        
        # Deploy
        print_status "Starting containers..."
        docker compose up -d
        
        # Wait for app to be ready
        print_status "Waiting for application to be ready..."
        sleep 10
        
        # Run migrations
        print_status "Running migrations..."
        docker compose exec -T app php artisan migrate --force
        
        # Clear and cache
        print_status "Optimizing application..."
        docker compose exec -T app php artisan config:cache
        docker compose exec -T app php artisan route:cache
        docker compose exec -T app php artisan view:cache
        
        print_status "Deployment completed successfully!"
        ;;
        
    *)
        print_error "Invalid environment: $ENVIRONMENT"
        echo "Valid environments: local, staging, production"
        exit 1
        ;;
esac
