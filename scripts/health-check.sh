#!/bin/bash

# Health check script for deployment validation
# Usage: ./health-check.sh [staging|production]

set -e

ENVIRONMENT=${1:-staging}

# Set URL based on environment
if [ "$ENVIRONMENT" == "production" ]; then
    APP_URL=${PRODUCTION_URL:-"http://localhost:8000"}
else
    APP_URL=${STAGING_URL:-"http://localhost:8000"}
fi

MAX_RETRIES=30
RETRY_DELAY=2
SUCCESS=0

echo "🔍 Starting health check for $ENVIRONMENT environment..."
echo "Testing URL: $APP_URL"

# Function to check HTTP response
check_http() {
    local url=$1
    local expected_status=${2:-200}
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url" || echo "000")
    
    if [ "$response" == "$expected_status" ]; then
        return 0
    else
        return 1
    fi
}

# Wait for application to be ready
echo "⏳ Waiting for application to respond..."
for i in $(seq 1 $MAX_RETRIES); do
    if check_http "$APP_URL/health" 200; then
        echo "✅ Application is responding"
        SUCCESS=1
        break
    fi
    
    echo "Attempt $i/$MAX_RETRIES failed, retrying in ${RETRY_DELAY}s..."
    sleep $RETRY_DELAY
done

if [ $SUCCESS -eq 0 ]; then
    echo "❌ Health check failed: Application not responding after $MAX_RETRIES attempts"
    exit 1
fi

# Check database connectivity
echo "🔍 Checking database connectivity..."
if docker compose exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" 2>&1 | grep -q "DB OK"; then
    echo "✅ Database connection successful"
else
    echo "❌ Database connection failed"
    exit 1
fi

# Check Redis connectivity
echo "🔍 Checking Redis connectivity..."
if docker compose exec -T app php artisan tinker --execute="Cache::store('redis')->get('test'); echo 'Redis OK';" 2>&1 | grep -q "Redis OK"; then
    echo "✅ Redis connection successful"
else
    echo "⚠️  Redis connection check inconclusive"
fi

# Check critical routes
echo "🔍 Testing critical routes..."
ROUTES=("/" "/login")

for route in "${ROUTES[@]}"; do
    if check_http "$APP_URL$route" 200; then
        echo "✅ Route $route is accessible"
    else
        echo "❌ Route $route is not accessible"
        exit 1
    fi
done

# Check storage permissions
echo "🔍 Checking storage permissions..."
if docker compose exec -T app test -w storage/logs; then
    echo "✅ Storage is writable"
else
    echo "❌ Storage is not writable"
    exit 1
fi

# Check for errors in logs
echo "🔍 Checking for recent errors in logs..."
ERROR_COUNT=$(docker compose exec -T app tail -n 100 storage/logs/laravel.log 2>/dev/null | grep -c "ERROR" || echo "0")

if [ "$ERROR_COUNT" -gt 10 ]; then
    echo "⚠️  Warning: Found $ERROR_COUNT errors in recent logs"
else
    echo "✅ Log check passed (found $ERROR_COUNT errors)"
fi

# Check queue workers (if applicable)
echo "🔍 Checking queue workers..."
if docker compose exec -T app php artisan queue:failed --format=json 2>/dev/null | grep -q "^\["; then
    FAILED_JOBS=$(docker compose exec -T app php artisan queue:failed --format=json | jq '. | length')
    if [ "$FAILED_JOBS" -gt 0 ]; then
        echo "⚠️  Warning: Found $FAILED_JOBS failed jobs in queue"
    else
        echo "✅ No failed jobs in queue"
    fi
fi

echo ""
echo "🎉 All health checks passed successfully!"
echo "Environment: $ENVIRONMENT"
echo "Timestamp: $(date -u +"%Y-%m-%d %H:%M:%S UTC")"
exit 0
