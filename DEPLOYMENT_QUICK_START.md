# Quick Start Deployment Guide

## First-Time Setup

### 1. Configure GitHub Repository

Add these secrets in GitHub repository settings (Settings → Secrets and variables → Actions):

**Staging:**
```
STAGING_HOST=staging.yourdomain.com
STAGING_USERNAME=deploy
STAGING_SSH_KEY=<your-private-ssh-key>
```

**Production:**
```
PRODUCTION_HOST=yourdomain.com
PRODUCTION_USERNAME=deploy
PRODUCTION_SSH_KEY=<your-private-ssh-key>
```

### 2. Server Preparation

On each server (staging and production):

```bash
# Install Docker and Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Create deployment directory
mkdir -p /opt/app
cd /opt/app

# Clone repository
git clone https://github.com/your-username/your-repo.git .

# Setup environment
cp .env.example .env
nano .env  # Edit with production values

# Create directories
mkdir -p backups scripts

# Make scripts executable
chmod +x scripts/*.sh

# Login to GitHub Container Registry
echo $GITHUB_TOKEN | docker login ghcr.io -u your-username --password-stdin
```

### 3. Initial Deployment

```bash
# Start services
docker compose up -d

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate --force

# Create admin user (if needed)
docker compose exec app php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'is_admin' => true]);

# Optimize
docker compose exec app php artisan optimize

# Test health
./scripts/health-check.sh production
```

## Daily Usage

### Deploy to Staging
```bash
git checkout staging
git merge develop
git push origin staging
# GitHub Actions handles the rest
```

### Deploy to Production
```bash
git checkout main
git merge staging
git push origin main
# GitHub Actions handles the rest
```

### Quick Commands
```bash
make help              # Show all commands
make up                # Start containers
make logs              # View logs
make test              # Run tests
make backup            # Create backup
make health-check      # Check health
make deploy-staging    # Deploy to staging
make deploy-production # Deploy to production
```

### Manual Deployment
```bash
cd /opt/app
./scripts/pre-deploy-check.sh
./scripts/backup-db.sh production
docker compose pull
docker compose up -d
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize
./scripts/health-check.sh production
```

### Rollback
```bash
make rollback
# or
./scripts/rollback.sh production
```

## Monitoring

### Health Checks
```bash
# Via script
./scripts/health-check.sh production

# Via HTTP
curl https://yourdomain.com/health
curl https://yourdomain.com/health/detailed
```

### View Logs
```bash
make logs
# or
docker compose logs -f app
```

### Database Backup
```bash
make backup
# or
./scripts/backup-db.sh production
```

## Troubleshooting

### Application Not Starting
```bash
docker compose logs app
docker compose ps
docker compose restart
```

### Database Issues
```bash
docker compose logs db
docker compose exec db mysql -uroot -proot laravel
```

### Permission Issues
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Rollback Deployment
```bash
make rollback
```

## Pipeline Features

✅ Automated testing with PHPUnit
✅ Code quality checks with Laravel Pint
✅ Security scanning (Composer audit, NPM audit, Trivy)
✅ Docker image building with caching
✅ Zero-downtime deployments
✅ Automatic database backups
✅ Health checks after deployment
✅ Automatic rollback on failure
✅ Daily scheduled backups

## Environment Configuration

Required `.env` variables:
```env
APP_NAME="Your App"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secure-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
```

For detailed documentation, see [PIPELINE.md](PIPELINE.md)
