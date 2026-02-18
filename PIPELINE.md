# Deployment Pipeline Documentation

## Overview

This application includes a comprehensive CI/CD pipeline with automated testing, security scanning, and deployment to staging and production environments.

## Pipeline Components

### 1. Continuous Integration Workflow (`.github/workflows/ci-cd.yml`)

The main CI/CD pipeline includes:

- **Code Quality**: Runs Laravel Pint for code style checks
- **Testing**: PHPUnit tests with MySQL and Redis services
- **Security Scanning**: Composer audit, NPM audit, and Trivy container scanning
- **Docker Build**: Multi-stage builds with layer caching
- **Deployment**: Automated deployment to staging and production

### 2. Automated Backups (`.github/workflows/backup.yml`)

- Daily scheduled backups at 2 AM UTC
- Manual backup trigger via GitHub Actions
- Supports both staging and production environments

### 3. Deployment Scripts

#### Health Check (`scripts/health-check.sh`)
Validates deployment success by checking:
- Application HTTP response
- Database connectivity
- Redis connectivity
- Critical routes accessibility
- Storage permissions
- Error logs
- Queue workers status

#### Database Backup (`scripts/backup-db.sh`)
- Creates compressed SQL backups
- Keeps last 7 backups automatically
- Optional S3 upload support
- Generates backup with timestamp

#### Rollback (`scripts/rollback.sh`)
- Rolls back to previous Docker image
- Optional database restoration from backup
- Automatic health check after rollback
- Maintenance mode management

#### Pre-Deployment Check (`scripts/pre-deploy-check.sh`)
Validates environment before deployment:
- Docker availability
- Configuration files presence
- Environment variables
- Disk space
- Directory permissions
- Docker Compose configuration

### 4. Health Check Endpoints (`routes/health.php`)

- `/health` - Simple health check
- `/health/detailed` - Comprehensive check (database, cache, storage)
- `/health/ready` - Readiness probe
- `/health/live` - Liveness probe

### 5. Makefile

Convenient commands for development and deployment:
```bash
make help              # Show all available commands
make install          # Install dependencies
make up               # Start containers
make test             # Run tests
make deploy-staging   # Deploy to staging
make deploy-production # Deploy to production
make backup           # Create database backup
make rollback         # Rollback deployment
```

## Setup Instructions

### 1. Configure GitHub Secrets

Go to repository Settings → Secrets and variables → Actions, and add:

**For Staging:**
- `STAGING_HOST` - Staging server hostname/IP
- `STAGING_USERNAME` - SSH username
- `STAGING_SSH_KEY` - Private SSH key

**For Production:**
- `PRODUCTION_HOST` - Production server hostname/IP
- `PRODUCTION_USERNAME` - SSH username
- `PRODUCTION_SSH_KEY` - Private SSH key

**Optional (for S3 backups):**
- `AWS_S3_BACKUP_BUCKET` - S3 bucket name

### 2. Server Setup

On each deployment server:

```bash
# Create app directory
mkdir -p /opt/app
cd /opt/app

# Clone repository
git clone https://github.com/your-username/your-repo.git .

# Create environment file
cp .env.example .env
# Edit .env with production values

# Create required directories
mkdir -p backups scripts

# Make scripts executable
chmod +x scripts/*.sh

# Login to GitHub Container Registry
echo $GITHUB_TOKEN | docker login ghcr.io -u your-username --password-stdin
```

### 3. Configure Environment Variables

Update `.env` file on the server with production values:

```env
APP_NAME="Your App"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secure-random-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### 4. Initial Deployment

```bash
# On the server
cd /opt/app

# Build and start containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Generate app key (if not set)
docker compose exec app php artisan key:generate

# Optimize
docker compose exec app php artisan optimize

# Run health check
./scripts/health-check.sh production
```

## Deployment Workflows

### Automatic Deployment

**Staging Deployment:**
1. Push to `staging` branch
2. Pipeline runs tests and security scans
3. Builds Docker image
4. Deploys to staging server
5. Runs migrations and optimizations
6. Performs health check

**Production Deployment:**
1. Push to `main` branch
2. Pipeline runs tests and security scans
3. Builds Docker image
4. Deploys to production server with maintenance mode
5. Runs migrations and optimizations
6. Disables maintenance mode
7. Performs health check
8. Automatic rollback on failure

### Manual Deployment

Using Makefile:
```bash
# Staging
make deploy-staging

# Production
make deploy-production
```

Using scripts directly:
```bash
# Pre-deployment check
./scripts/pre-deploy-check.sh

# Create backup
./scripts/backup-db.sh production

# Deploy
docker compose pull
docker compose up -d

# Run migrations
docker compose exec -T app php artisan migrate --force

# Optimize
docker compose exec -T app php artisan optimize

# Health check
./scripts/health-check.sh production
```

## Rollback Procedure

### Automatic Rollback
- Production deployments automatically rollback on failure

### Manual Rollback
```bash
# Using Makefile
make rollback

# Using script
./scripts/rollback.sh production

# Specific backup restoration
# 1. List backups
ls -lh backups/

# 2. Restore specific backup
gunzip -c backups/production_backup_20260209_120000.sql.gz | \
  docker compose exec -T db mysql -uroot -proot laravel
```

## Monitoring

### View Logs
```bash
# Application logs
make logs

# Specific service
docker compose logs -f app

# Last 100 lines
docker compose logs --tail=100 app
```

### Health Check
```bash
# Simple check
curl https://yourdomain.com/health

# Detailed check
curl https://yourdomain.com/health/detailed

# Using script
./scripts/health-check.sh production
```

## Backups

### Automated Backups
- Daily backups run automatically at 2 AM UTC via GitHub Actions
- Backups are stored on the deployment server in `/opt/app/backups/`
- Last 7 backups are kept automatically

### Manual Backups
```bash
# Using Makefile
make backup

# Using script
./scripts/backup-db.sh production

# Manual backup
docker compose exec -T db mysqldump -uroot -proot laravel | gzip > backup.sql.gz
```

### S3 Backup Integration
To enable S3 backups, set environment variable on the server:
```bash
export AWS_S3_BACKUP_BUCKET=your-bucket-name
```

## Security Features

1. **Composer Audit**: Checks PHP dependencies for vulnerabilities
2. **NPM Audit**: Checks JavaScript dependencies for vulnerabilities
3. **Trivy Scanner**: Scans Docker images for security issues
4. **GitHub Security**: Results uploaded to Security tab
5. **Secrets Management**: All sensitive data in GitHub Secrets

## Troubleshooting

### Deployment Fails
1. Check GitHub Actions logs
2. Review server logs: `make logs`
3. Run health check: `./scripts/health-check.sh production`
4. Rollback if needed: `make rollback`

### Database Connection Issues
```bash
# Check database container
docker compose ps db

# Test connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Issues
```bash
# Fix storage permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Health Check Fails
```bash
# Check all services
docker compose ps

# Restart services
docker compose restart

# Check application logs
docker compose logs app
```

## Performance Optimization

The pipeline includes several optimizations:

1. **Docker Layer Caching**: GitHub Actions cache for faster builds
2. **Composer Cache**: Cached dependencies between builds
3. **NPM Cache**: Cached node_modules
4. **Laravel Optimization**: Config, route, and view caching
5. **OPcache**: Enabled in production Docker image

## Best Practices

1. **Always test in staging first**: Push to `staging` branch before `main`
2. **Review test results**: Check all tests pass before deploying
3. **Monitor health checks**: Review health check results after deployment
4. **Keep backups**: Ensure daily backups are running
5. **Update dependencies**: Regularly run `make security-check`
6. **Review logs**: Monitor application logs for errors
7. **Plan rollbacks**: Always have a rollback strategy ready

## Support

For issues or questions:
1. Check deployment logs in GitHub Actions
2. Review server logs with `make logs`
3. Run health checks with `make health-check`
4. Review this documentation
5. Check existing backups before major changes

## Useful Commands Reference

```bash
# Development
make install          # Install dependencies
make up              # Start containers
make down            # Stop containers
make test            # Run tests
make lint            # Run code linter

# Deployment
make deploy-staging   # Deploy to staging
make deploy-production # Deploy to production
make backup          # Create backup
make rollback        # Rollback deployment
make health-check    # Run health check

# Maintenance
make optimize        # Optimize Laravel
make migrate         # Run migrations
make fresh           # Fresh install with seeds
make shell           # Open app shell
make db-shell        # Open database shell

# Updates
make composer-update  # Update PHP dependencies
make npm-update      # Update JS dependencies
make security-check  # Run security audit
```
