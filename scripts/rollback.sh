#!/bin/bash

# Rollback script for deployment failures
# Usage: ./rollback.sh [staging|production]

set -e

ENVIRONMENT=${1:-staging}
BACKUP_DIR="./backups"

echo "⚠️  Starting rollback for $ENVIRONMENT environment..."

# Get previous image tag
PREVIOUS_IMAGE=$(docker compose images app -q | head -n 2 | tail -n 1)

if [ -z "$PREVIOUS_IMAGE" ]; then
    echo "❌ Error: No previous image found for rollback"
    exit 1
fi

echo "🔄 Rolling back to previous image: $PREVIOUS_IMAGE"

# Enable maintenance mode
echo "🚧 Enabling maintenance mode..."
docker compose exec -T app php artisan down --retry=60 || true

# Stop current containers
echo "🛑 Stopping current containers..."
docker compose down

# Restore previous image
echo "🔄 Restoring previous image..."
docker tag "$PREVIOUS_IMAGE" "$(docker compose config --images app | head -n1)"

# Start containers with previous image
echo "🚀 Starting containers with previous image..."
docker compose up -d

# Wait for application to be ready
echo "⏳ Waiting for application to start..."
sleep 10

# Find most recent backup
LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/${ENVIRONMENT}_backup_*.sql.gz 2>/dev/null | head -n 1)

if [ -n "$LATEST_BACKUP" ]; then
    echo "🗄️  Found backup: $LATEST_BACKUP"
    read -p "Do you want to restore the database from backup? (y/N): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "🔄 Restoring database from backup..."
        
        # Decompress and restore
        gunzip -c "$LATEST_BACKUP" | docker compose exec -T db mysql \
            -u"$(docker compose exec -T app printenv DB_USERNAME)" \
            -p"$(docker compose exec -T app printenv DB_PASSWORD)" \
            "$(docker compose exec -T app printenv DB_DATABASE)"
        
        echo "✅ Database restored from backup"
    fi
else
    echo "⚠️  No backup found, skipping database restore"
fi

# Clear caches
echo "🧹 Clearing caches..."
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear

# Disable maintenance mode
echo "✅ Disabling maintenance mode..."
docker compose exec -T app php artisan up

# Run health check
echo "🔍 Running health check..."
if ./scripts/health-check.sh "$ENVIRONMENT"; then
    echo "✅ Rollback completed successfully!"
    echo "Application is running on previous version"
else
    echo "❌ Rollback health check failed"
    echo "Manual intervention may be required"
    exit 1
fi

exit 0
