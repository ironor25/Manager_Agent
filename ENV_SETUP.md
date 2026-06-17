# Environment Setup Guide

This document explains all environment variables and how to configure them for different environments.

## 🌍 Environment Files

- `.env` - Local development (not committed to git)
- `.env.example` - Template for local development (committed)
- `.env.docker` - Docker Compose specific settings
- `.env.production` - Production template for Render deployment
- `.env.testing` - Testing environment (for phpunit)

## 📝 Required Environment Variables

### Application

```env
APP_NAME=Manager Agent              # Application name
APP_ENV=production                 # Environment: production, local, testing
APP_DEBUG=false                    # Debug mode (always false in production)
APP_KEY=base64:xxxxx               # Encryption key (generate with: php artisan key:generate)
APP_URL=https://yourdomain.com    # Application URL
PORT=8080                          # Port (for Docker/Render)
```

### Database (PostgreSQL)

```env
DB_CONNECTION=pgsql               # Database driver (pgsql for PostgreSQL)
DB_HOST=postgres                  # Database host (localhost, container name, or cloud host)
DB_PORT=5432                      # PostgreSQL port (default: 5432)
DB_DATABASE=manager_agent         # Database name
DB_USERNAME=postgres              # Database user
DB_PASSWORD=your_password         # Database password
```

### Session & Cache

```env
SESSION_DRIVER=database           # Session storage: database, cookie, redis
CACHE_STORE=database              # Cache store: database, redis, array, file
CACHE_PREFIX=                     # Cache key prefix (optional)
```

### Queue

```env
QUEUE_CONNECTION=database         # Queue driver: database, redis, sync
```

### Mail

```env
MAIL_MAILER=log                   # Mail driver: log, smtp, sendmail, mailgun, etc.
MAIL_HOST=localhost               # SMTP host
MAIL_PORT=587                     # SMTP port
MAIL_USERNAME=                    # SMTP username
MAIL_PASSWORD=                    # SMTP password
MAIL_ENCRYPTION=tls               # Encryption: tls, ssl
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Logging

```env
LOG_CHANNEL=stack                 # Log channel: stack, single, daily, slack, etc.
LOG_STACK=single                  # Stack channels (for stack driver)
LOG_LEVEL=debug                   # Log level: debug, info, notice, warning, error, critical, alert, emergency
```

### API Keys & Third-party Services

```env
GEMINI_API_KEY=your_api_key       # Google Gemini API key for AI features
```

### Optional: Redis

```env
REDIS_HOST=redis                  # Redis host
REDIS_PASSWORD=null               # Redis password
REDIS_PORT=6379                   # Redis port
REDIS_CLIENT=phpredis             # Redis client: phpredis, predis
```

### Optional: Memcached

```env
MEMCACHED_HOST=127.0.0.1         # Memcached host
MEMCACHED_PORT=11211             # Memcached port
```

## 🏠 Local Development

### Setup

```bash
# Copy template
cp .env.example .env

# Generate app key
php artisan key:generate

# Update .env with your local database credentials
# Then run migrations
php artisan migrate --seed
```

### Configuration

```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_USERNAME=postgres
DB_PASSWORD=yourpassword
LOG_LEVEL=debug
```

## 🐳 Docker Compose Development

### Configuration

The `docker-compose.yml` automatically sets environment variables. Override in `.env.docker`:

```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=postgres                  # Service name from docker-compose.yml
DB_USERNAME=postgres
DB_PASSWORD=postgres
RUN_SEEDERS=true                 # Automatically seed database
```

### Usage

```bash
# Copy Docker environment file (optional, auto-configured)
cp .env.docker .env

# Start services
docker-compose up -d

# Migrations run automatically
# Check status
docker-compose logs app
```

## 🚀 Production (Render)

### Configuration Methods

#### Method 1: Using render.yaml (Recommended)

Environment variables are defined in `render.yaml`. The file automatically configures:

- Database connection via Render PostgreSQL service
- Application settings
- Secrets (auto-generated for APP_KEY)

#### Method 2: Manual Dashboard Setup

Set these in Render dashboard under "Environment":

```env
APP_NAME=Manager Agent
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx              # Generate locally or Render auto-generates
APP_URL=https://yourdomain.onrender.com

DB_CONNECTION=pgsql
DB_HOST=${DATABASE_URL_HOST}      # From Render PostgreSQL
DB_PORT=5432
DB_DATABASE=${DATABASE_NAME}      # From Render PostgreSQL
DB_USERNAME=${DATABASE_USER}      # From Render PostgreSQL
DB_PASSWORD=${DATABASE_PASSWORD}  # From Render PostgreSQL

LOG_LEVEL=error
LOG_CHANNEL=stack

GEMINI_API_KEY=your_actual_key    # Set your real API key
```

### Getting Render Database Credentials

1. Go to Render Dashboard → PostgreSQL service
2. Copy connection string or individual values
3. Set environment variables in your Web Service

## 🧪 Testing

```env
# phpunit.xml automatically sets
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## 🔐 Security Best Practices

1. **Never commit sensitive files**: `.env`, `.env.*.local`
2. **Use strong passwords**: At least 16 characters for production
3. **Rotate keys regularly**: Regenerate APP_KEY periodically
4. **Use environment variables**: Store secrets as env vars, not in code
5. **HTTPS in production**: Always use HTTPS (Render provides free SSL)
6. **Restrict database access**: Use VPC or IP whitelist in production
7. **Use .gitignore**: Ensure `.env` is in `.gitignore`

## 🧪 Verifying Configuration

```bash
# Check current configuration
php artisan config:show
php artisan config:show database

# Test database connection
php artisan tinker
DB::connection()->getPdo();  # Should succeed

# Clear and rebuild cache
php artisan config:cache
php artisan cache:clear

# Run migrations
php artisan migrate:status
```

## 🐛 Common Issues

### "No database selected"

- Ensure DB_DATABASE is set correctly
- Check database user permissions
- Verify PostgreSQL server is running

### "SQLSTATE[HY000]: General error"

- Check database credentials
- Verify PostgreSQL connection
- Review application logs

### "No application encryption key"

```bash
php artisan key:generate
```

### "Migrations failed"

```bash
# Check migration status
php artisan migrate:status

# Refresh migrations
php artisan migrate:refresh --seed
```

## 📋 Environment Variables Checklist

### Required

- [ ] APP_NAME
- [ ] APP_ENV
- [ ] APP_KEY
- [ ] APP_URL
- [ ] DB_CONNECTION=pgsql
- [ ] DB_HOST
- [ ] DB_PORT=5432
- [ ] DB_DATABASE
- [ ] DB_USERNAME
- [ ] DB_PASSWORD

### Optional but Recommended

- [ ] GEMINI_API_KEY (for AI features)
- [ ] MAIL_MAILER
- [ ] LOG_LEVEL
- [ ] SESSION_DRIVER
- [ ] CACHE_STORE

### For Production

- [ ] APP_DEBUG=false
- [ ] LOG_LEVEL=error
- [ ] Backup credentials stored securely
- [ ] SSL/HTTPS enabled
- [ ] Database backups configured

## 📞 Reference

- [Laravel Configuration](https://laravel.com/docs/configuration)
- [PostgreSQL Connection](https://laravel.com/docs/database)
- [Render Environment Variables](https://render.com/docs/environment-variables)

---

**Last Updated**: 2026-06-16
