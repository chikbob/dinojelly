# Deployment Guide

This document describes the deployment pipeline and how to deploy the application.

## Architecture

The application uses:
- **PHP 8.2** with FPM
- **Nginx** as the web server
- **MySQL 8.0** for the database
- **Redis** for caching and sessions
- **Supervisor** to manage processes
- Multi-stage Docker builds for optimized images

## Quick Start

### Local Development

```bash
# Using Docker Compose
docker-compose -f docker-compose.dev.yml up -d

# Or using the deploy script
chmod +x deploy.sh
./deploy.sh local
```

Application will be available at http://localhost:8000

### Production Deployment

```bash
# Build and run
docker-compose up -d

# Or using the deploy script
./deploy.sh production
```

## Deployment Options

### 1. Docker Compose (Recommended for VPS/Single Server)

**Files:**
- `docker-compose.yml` - Production configuration
- `docker-compose.dev.yml` - Development configuration

**Deploy:**
```bash
# Production
docker-compose up -d

# View logs
docker-compose logs -f app

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize
docker-compose exec app php artisan optimize
```

### 2. Kubernetes (Recommended for Scale)

**Files in `k8s/` directory:**
- `deployment.yml` - Application deployment and service
- `mysql.yml` - Database deployment
- `redis.yml` - Redis deployment
- `ingress.yml` - Ingress configuration
- `secrets.example.yml` - Template for secrets

**Setup:**
```bash
# Create namespace
kubectl create namespace laravel-app

# Create secrets (update values first)
cp k8s/secrets.example.yml k8s/secrets.yml
# Edit k8s/secrets.yml with your values
kubectl apply -f k8s/secrets.yml -n laravel-app

# Deploy services
kubectl apply -f k8s/redis.yml -n laravel-app
kubectl apply -f k8s/mysql.yml -n laravel-app
kubectl apply -f k8s/deployment.yml -n laravel-app
kubectl apply -f k8s/ingress.yml -n laravel-app

# Check status
kubectl get pods -n laravel-app

# Run migrations
kubectl exec -it deployment/laravel-app -n laravel-app -- php artisan migrate --force
```

### 3. CI/CD with GitHub Actions

**File:** `.github/workflows/deploy.yml`

**Setup:**

1. **Configure Repository Secrets** (GitHub Settings → Secrets and variables → Actions):
   ```
   STAGING_HOST - Staging server hostname
   STAGING_USERNAME - SSH username for staging
   STAGING_SSH_KEY - Private SSH key for staging
   
   PRODUCTION_HOST - Production server hostname
   PRODUCTION_USERNAME - SSH username for production
   PRODUCTION_SSH_KEY - Private SSH key for production
   ```

2. **Server Setup:**
   ```bash
   # On your server
   mkdir -p /opt/app
   cd /opt/app
   
   # Clone your repo
   git clone https://github.com/your-username/your-repo.git .
   
   # Create .env file
   cp .env.example .env
   # Edit .env with production values
   
   # Login to GitHub Container Registry
   echo $GITHUB_TOKEN | docker login ghcr.io -u your-username --password-stdin
   ```

3. **Deploy:**
   - Push to `staging` branch → Deploys to staging
   - Push to `main` branch → Deploys to production
   - Pull requests → Runs tests only

**Pipeline stages:**
1. Run tests (PHPUnit + frontend build)
2. Build Docker image
3. Push to GitHub Container Registry
4. Deploy to environment
5. Run migrations and cache optimization

## Environment Variables

Required environment variables for production:

```env
APP_NAME=YourApp
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secure-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## Performance Optimization

The Docker image includes:
- OPcache enabled for PHP
- Static asset caching (1 year)
- Gzip compression
- Config/route/view caching
- Optimized Composer autoloader
- Multi-stage builds for smaller images

## Monitoring and Logs

### Docker Compose
```bash
# View all logs
docker-compose logs -f

# View specific service
docker-compose logs -f app

# View last 100 lines
docker-compose logs --tail=100 app
```

### Kubernetes
```bash
# View logs
kubectl logs -f deployment/laravel-app -n laravel-app

# View logs from specific pod
kubectl logs -f pod-name -n laravel-app

# View events
kubectl get events -n laravel-app
```

## Rollback

### Docker Compose
```bash
# Pull previous image version
docker-compose pull

# Restore database backup
docker-compose exec -T db mysql -uroot -proot laravel < backup-YYYYMMDD-HHMMSS.sql

# Restart
docker-compose up -d
```

### Kubernetes
```bash
# Rollback to previous revision
kubectl rollout undo deployment/laravel-app -n laravel-app

# Rollback to specific revision
kubectl rollout undo deployment/laravel-app --to-revision=2 -n laravel-app

# Check rollout history
kubectl rollout history deployment/laravel-app -n laravel-app
```

## Scaling

### Docker Compose
Edit `docker-compose.yml`:
```yaml
services:
  app:
    deploy:
      replicas: 3
```

### Kubernetes
```bash
# Scale manually
kubectl scale deployment/laravel-app --replicas=5 -n laravel-app

# Auto-scaling (HPA)
kubectl autoscale deployment/laravel-app --cpu-percent=80 --min=3 --max=10 -n laravel-app
```

## Troubleshooting

### Application not starting
```bash
# Check logs
docker-compose logs app

# Check if services are running
docker-compose ps

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Permission issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database connection issues
```bash
# Check if MySQL is running
docker-compose ps db

# Test connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

## Security Checklist

- [ ] Update all secrets in `.env`
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Enable HTTPS (configure in ingress/load balancer)
- [ ] Keep Docker images updated
- [ ] Enable firewall on servers
- [ ] Regularly backup database
- [ ] Monitor logs for suspicious activity
- [ ] Use secrets management (Kubernetes Secrets, AWS Secrets Manager, etc.)

## Maintenance

### Update dependencies
```bash
# Update Composer dependencies
docker-compose exec app composer update

# Update NPM dependencies
npm update

# Rebuild image
docker-compose build --no-cache
docker-compose up -d
```

### Database backups
```bash
# Manual backup
docker-compose exec -T db mysqldump -uroot -proot laravel > backup.sql

# Automated backups (add to crontab)
0 2 * * * cd /opt/app && docker-compose exec -T db mysqldump -uroot -proot laravel > backups/backup-$(date +\%Y\%m\%d).sql
```

## Support

For issues or questions:
1. Check logs first
2. Review this documentation
3. Check Laravel documentation: https://laravel.com/docs
4. Check Docker documentation: https://docs.docker.com
