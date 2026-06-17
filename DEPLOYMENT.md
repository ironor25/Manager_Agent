# Manager Agent - Deployment Guide

This guide covers the complete migration from MySQL to PostgreSQL and deployment on Render using Docker.

## 📋 Table of Contents

1. [PostgreSQL Migration](#postgresql-migration)
2. [Local Development with Docker](#local-development-with-docker)
3. [Production Deployment on Render](#production-deployment-on-render)
4. [Environment Configuration](#environment-configuration)
5. [Troubleshooting](#troubleshooting)

---

## 🗃️ PostgreSQL Migration

### What Changed

The application has been migrated from MySQL to PostgreSQL:

- **Database Configuration**: Updated `bootstrap/config/database.php` to use PostgreSQL as default
- **.env Files**: Changed `DB_CONNECTION=mysql` to `DB_CONNECTION=pgsql`
- **Port**: Changed from 3306 (MySQL) to 5432 (PostgreSQL)
- **Docker Setup**: Updated to include PostgreSQL instead of MySQL

### SQL Compatibility

The application uses:

- **Laravel Query Builder & Eloquent ORM**: Database-agnostic, works with both MySQL and PostgreSQL
- **Migrations**: Use `Schema` class for database-agnostic table creation
- **Raw Queries**: Minimal raw SQL used; standard ANSI SQL compatible with both databases

### Migration Format

PostgreSQL uses the same date/time format as MySQL for ISO 8601 timestamps:

- Format: `YYYY-MM-DD HH:MM:SS`
- Laravel's `timestamps()` helper automatically handles this

---

## 🐳 Local Development with Docker

### Prerequisites

- Docker Desktop installed
- Docker Compose installed
- Git installed

### Setup Steps

1. **Clone and navigate to project**

```bash
cd manager_agent
```

2. **Build and start services**

```bash
docker-compose up -d
```

This will:

- Create PostgreSQL database container
- Build and start PHP-FPM + Nginx container
- Run migrations automatically
- Optionally seed database (controlled by `RUN_SEEDERS` env var)

3. **Access the application**

- Web: `http://localhost:8080`
- Health check: `http://localhost:8080/health`

4. **View logs**

```bash
docker-compose logs -f app
docker-compose logs -f postgres
```

5. **Run artisan commands**

```bash
docker-compose exec app php artisan tinker
docker-compose exec app php artisan migrate:fresh --seed
```

6. **Access PostgreSQL**

```bash
docker-compose exec postgres psql -U postgres -d manager_agent
```

### Useful Commands

```bash
# Stop containers
docker-compose down

# Remove volumes (reset database)
docker-compose down -v

# Rebuild without cache
docker-compose build --no-cache

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

---

## 🚀 Production Deployment on Render

### Option 1: Infrastructure as Code (Recommended)

1. **Commit render.yaml to repository**

```bash
git add render.yaml docker/
git commit -m "Add Render deployment configuration"
git push origin main
```

2. **Deploy via Render Dashboard**
    - Go to https://dashboard.render.com/
    - Click "New +" → "Infrastructure as Code"
    - Select your GitHub repository
    - Review the blueprint
    - Click "Deploy"

3. **Monitor deployment**
    - Watch logs in the Render dashboard
    - First deployment runs migrations automatically

### Option 2: Manual Setup

1. **Create PostgreSQL Database**
    - In Render dashboard: New → PostgreSQL
    - Choose region and plan
    - Note the connection string

2. **Create Web Service**
    - New → Web Service
    - Connect GitHub repository
    - Select Docker runtime
    - Set environment variables (see below)
    - Deploy

### Environment Variables for Production

Set these in your Render service:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
PORT=8080

# Database (from Render PostgreSQL service)
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host.c.aivencloud.com  # Auto-filled if using render.yaml
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

# Application Settings
BCRYPT_ROUNDS=12
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_LEVEL=error

# APIs (set your actual keys)
GEMINI_API_KEY=your_api_key_here

# Other settings
RUN_SEEDERS=false  # Set to true only for initial deployment
```

### Database Connection String (from Render)

Render provides a connection string like:

```
postgresql://username:password@host:port/database
```

Parse this into individual environment variables above.

---

## ⚙️ Environment Configuration

### .env Files Explained

**Development** (`.env`):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=postgres
DB_PASSWORD=
```

**Production** (`.env.production`):

```env
DB_CONNECTION=pgsql
DB_HOST=${DATABASE_HOST}  # From Render
DB_DATABASE=${DATABASE_NAME}
DB_USERNAME=${DATABASE_USER}
DB_PASSWORD=${DATABASE_PASSWORD}
```

**Docker Compose** (via environment in `docker-compose.yml`):

```yaml
environment:
    DB_CONNECTION: pgsql
    DB_HOST: postgres
    DB_DATABASE: manager_agent
```

---

## 🔍 Database Details

### PostgreSQL Connection

- **Default Port**: 5432
- **Default User**: postgres
- **Connection String**: `pgsql://user:password@host:5432/dbname`

### Migration Path in Container

1. Container starts → runs `entrypoint.sh`
2. Waits for PostgreSQL to be ready (30 second timeout)
3. Clears configuration cache
4. **Runs migrations**: `php artisan migrate --force`
5. **Optionally seeds**: `php artisan db:seed --force` (if `RUN_SEEDERS=true`)
6. Starts PHP-FPM and Nginx

### Database Health

Check database health from app container:

```bash
docker-compose exec postgres pg_isready -U postgres
```

Connect to database:

```bash
docker-compose exec postgres psql -U postgres -d manager_agent
```

---

## 🚦 Health Checks

The application includes health check endpoints:

1. **HTTP Health Check** (for load balancers)
    - Endpoint: `GET /health`
    - Returns: `200 OK` with "healthy" message

2. **Docker Health Check**
    - Interval: 30 seconds
    - Timeout: 10 seconds
    - Retries: 3

3. **PostgreSQL Readiness Check**
    - Checked on startup via `pg_isready`
    - 30 second timeout with 1 second intervals

---

## 📊 Performance Optimization

### Nginx Configuration

- GZIP compression enabled
- Static asset caching (1 year for versioned assets)
- Security headers configured
- Request timeout: 60 seconds

### PHP-FPM Configuration

- Dynamic process management
- Max 100 child processes
- Memory limit: 256MB
- Execution timeout: 60 seconds
- Max upload: 100MB

### Database Connection Pooling

- PostgreSQL native connection pooling
- Persistent connections recommended for production

---

## 🐛 Troubleshooting

### Database Connection Issues

**Error: "could not connect to server"**

```bash
# Check if PostgreSQL is running
docker-compose ps postgres

# Check logs
docker-compose logs postgres

# Restart PostgreSQL
docker-compose restart postgres
```

**Error: "SQLSTATE[HY000]: General error"**

- Ensure migrations ran successfully
- Check database name matches configuration
- Verify permissions on PostgreSQL user

### Application Issues

**Error: "No application encryption key has been specified"**

```bash
docker-compose exec app php artisan key:generate
```

**Error: "Migrations failed"**

```bash
# View migration status
docker-compose exec app php artisan migrate:status

# Rollback and re-run
docker-compose exec app php artisan migrate:refresh
```

**Port Already in Use**

```bash
# Change port in docker-compose.yml
# Or stop the conflicting service
docker kill $(docker ps -q)
```

### Render Deployment Issues

**Service not starting**

1. Check logs in Render dashboard
2. Verify all environment variables are set
3. Ensure database is ready before app service

**Migrations timeout on first deploy**

- Render services might take time to initialize
- Check database logs
- Increase migration timeout if needed

**Out of memory**

- Increase service plan resources
- Optimize queries
- Use Laravel Telescope for profiling

---

## 📝 Deployment Checklist

Before deploying to production:

- [ ] Update `.env.production` with production values
- [ ] Set APP_KEY in environment variables
- [ ] Configure GEMINI_API_KEY or other APIs
- [ ] Set custom APP_URL if using custom domain
- [ ] Review security settings in nginx.conf
- [ ] Enable HTTPS (automatic with Render)
- [ ] Set LOG_LEVEL=error for production
- [ ] Configure backup for PostgreSQL database
- [ ] Set up monitoring and alerting
- [ ] Test migrations in staging environment
- [ ] Review database connection limits

---

## 🔐 Security Considerations

1. **Environment Variables**: Never commit `.env` files
2. **Database**: Use strong passwords, restrict access
3. **HTTPS**: Render provides free SSL/TLS certificates
4. **Headers**: Security headers are configured in nginx
5. **API Keys**: Store in environment variables, not in code
6. **File Permissions**: Docker runs with restricted permissions

---

## 📞 Support

For issues with:

- **Render**: https://render.com/docs
- **Laravel**: https://laravel.com/docs
- **PostgreSQL**: https://www.postgresql.org/docs/
- **Docker**: https://docs.docker.com/

---

## 🎯 Next Steps

1. **Local Testing**: Run `docker-compose up` and test thoroughly
2. **Git Setup**: Ensure render.yaml is committed
3. **Deploy**: Follow "Option 1: Infrastructure as Code" above
4. **Monitor**: Watch logs and performance metrics
5. **Scale**: Adjust resources as needed

---

**Last Updated**: 2026-06-16
**PostgreSQL Version**: 15
**PHP Version**: 8.2
**Laravel Version**: 11.x
