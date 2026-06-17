# Quick Start Guide - Manager Agent

## 🚀 Get Started in 5 Minutes

### Prerequisites

- Docker Desktop
- Git

### Steps

1. **Clone the repository**

```bash
git clone <your-repo>
cd manager_agent
```

2. **Start the application**

```bash
docker-compose up -d
```

3. **Wait for services to initialize** (30-60 seconds)

```bash
docker-compose ps  # Should show all services as "running"
```

4. **Access the app**

- Open http://localhost:8080 in your browser
- Health check: http://localhost:8080/health

### Common Commands

```bash
# View logs
docker-compose logs -f

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Laravel Tinker (interactive shell)
docker-compose exec app php artisan tinker

# Run tests
docker-compose exec app php artisan test

# Access PostgreSQL shell
docker-compose exec postgres psql -U postgres -d manager_agent

# Stop all services
docker-compose down

# Reset database (delete all data)
docker-compose down -v
docker-compose up -d
```

### Access Points

- **Web Application**: http://localhost:8080
- **PostgreSQL**: localhost:5432 (user: postgres, password: postgres)
- **Database**: manager_agent

### Troubleshooting

**App not starting?**

```bash
docker-compose logs app
```

**Port already in use?**

```bash
# Change PORT in docker-compose.yml or use a different port
docker-compose down
```

**Database migration error?**

```bash
docker-compose exec app php artisan migrate:refresh --seed
```

### For Production Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for Render deployment instructions.

---

**Database**: PostgreSQL 15 | **PHP**: 8.2 | **Laravel**: 11.x
