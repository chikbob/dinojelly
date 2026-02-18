#!/bin/bash

# Database backup script
# Usage: ./backup-db.sh [staging|production]

set -e

ENVIRONMENT=${1:-staging}
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/${ENVIRONMENT}_backup_$TIMESTAMP.sql"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "🗄️  Starting database backup for $ENVIRONMENT environment..."

# Get database credentials from docker-compose
DB_NAME=$(docker compose exec -T app printenv DB_DATABASE)
DB_USER=$(docker compose exec -T app printenv DB_USERNAME)
DB_PASS=$(docker compose exec -T app printenv DB_PASSWORD)
DB_HOST=$(docker compose exec -T app printenv DB_HOST)

# Create backup
echo "📦 Creating backup: $BACKUP_FILE"
docker compose exec -T db mysqldump \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    -u"$DB_USER" \
    -p"$DB_PASS" \
    "$DB_NAME" > "$BACKUP_FILE"

# Compress backup
echo "🗜️  Compressing backup..."
gzip "$BACKUP_FILE"
BACKUP_FILE="${BACKUP_FILE}.gz"

# Calculate backup size
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

echo "✅ Backup created successfully!"
echo "File: $BACKUP_FILE"
echo "Size: $BACKUP_SIZE"

# Keep only last 7 backups
echo "🧹 Cleaning up old backups (keeping last 7)..."
ls -t "$BACKUP_DIR"/${ENVIRONMENT}_backup_*.sql.gz | tail -n +8 | xargs -r rm

BACKUP_COUNT=$(ls -1 "$BACKUP_DIR"/${ENVIRONMENT}_backup_*.sql.gz 2>/dev/null | wc -l)
echo "📊 Total backups: $BACKUP_COUNT"

# Optional: Upload to S3 or other cloud storage
if [ -n "$AWS_S3_BACKUP_BUCKET" ]; then
    echo "☁️  Uploading backup to S3..."
    aws s3 cp "$BACKUP_FILE" "s3://$AWS_S3_BACKUP_BUCKET/$ENVIRONMENT/" --storage-class STANDARD_IA
    echo "✅ Backup uploaded to S3"
fi

echo "✅ Backup process completed"
exit 0
