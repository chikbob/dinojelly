#!/bin/bash

# Pre-deployment validation script
# Usage: ./pre-deploy-check.sh

set -e

echo "🔍 Running pre-deployment checks..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running"
    exit 1
fi
echo "✅ Docker is running"

# Check if docker-compose.yml exists
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ docker-compose.yml not found"
    exit 1
fi
echo "✅ docker-compose.yml found"

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "❌ .env file not found"
    exit 1
fi
echo "✅ .env file found"

# Validate .env file has required variables
REQUIRED_VARS=("APP_KEY" "DB_HOST" "DB_DATABASE" "DB_USERNAME" "DB_PASSWORD")
for var in "${REQUIRED_VARS[@]}"; do
    if ! grep -q "^${var}=" .env; then
        echo "❌ Missing required environment variable: $var"
        exit 1
    fi
done
echo "✅ Required environment variables present"

# Check disk space
DISK_AVAILABLE=$(df -BG . | tail -1 | awk '{print $4}' | sed 's/G//')
MIN_DISK_SPACE=5

if [ "$DISK_AVAILABLE" -lt "$MIN_DISK_SPACE" ]; then
    echo "⚠️  Warning: Low disk space (${DISK_AVAILABLE}GB available, ${MIN_DISK_SPACE}GB minimum recommended)"
else
    echo "✅ Sufficient disk space (${DISK_AVAILABLE}GB available)"
fi

# Check if backup directory exists and is writable
BACKUP_DIR="./backups"
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    echo "✅ Created backup directory"
else
    echo "✅ Backup directory exists"
fi

if [ ! -w "$BACKUP_DIR" ]; then
    echo "❌ Backup directory is not writable"
    exit 1
fi
echo "✅ Backup directory is writable"

# Check if scripts are executable
SCRIPTS=("./scripts/health-check.sh" "./scripts/backup-db.sh" "./scripts/rollback.sh")
for script in "${SCRIPTS[@]}"; do
    if [ -f "$script" ]; then
        if [ ! -x "$script" ]; then
            chmod +x "$script"
            echo "✅ Made $script executable"
        fi
    fi
done

# Validate docker-compose configuration
if docker compose config > /dev/null 2>&1; then
    echo "✅ docker-compose.yml is valid"
else
    echo "❌ docker-compose.yml has errors"
    exit 1
fi

# Check if required services are defined
REQUIRED_SERVICES=("app" "db")
for service in "${REQUIRED_SERVICES[@]}"; do
    if docker compose config --services | grep -q "^${service}$"; then
        echo "✅ Service '$service' is defined"
    else
        echo "❌ Service '$service' is not defined in docker-compose.yml"
        exit 1
    fi
done

# Check current containers status
if docker compose ps app | grep -q "Up"; then
    echo "✅ Application container is running"
    
    # Check if app is responding
    if docker compose exec -T app php artisan --version > /dev/null 2>&1; then
        echo "✅ Application is responsive"
    else
        echo "⚠️  Warning: Application container is running but not responsive"
    fi
else
    echo "ℹ️  Application container is not currently running"
fi

echo ""
echo "✅ All pre-deployment checks passed!"
echo "Ready to proceed with deployment"
exit 0
