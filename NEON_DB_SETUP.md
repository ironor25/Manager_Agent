# Neon DB PostgreSQL Setup Guide

This guide explains how to configure your Manager Agent application to use Neon DB (PostgreSQL cloud database).

## 🚀 Neon DB Setup

### 1. Create Neon DB Database

1. Go to [Neon Console](https://console.neon.tech/)
2. Create a new project
3. Copy your database connection string (it will look like this):
    ```
    postgresql://user:password@host.neon.tech:5432/manager_agent?sslmode=require
    ```

### 2. Set Environment Variables

For **local development** (update `.env` file):

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://user:password@host.neon.tech:5432/manager_agent?sslmode=require
# OR individual settings:
DB_HOST=host.neon.tech
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=user
DB_PASSWORD=password
DB_SSLMODE=require
```

For **Docker local development** (use `.env.docker`):

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

For **Production (Render)** - Set environment variables in Render dashboard:

- `DB_CONNECTION=pgsql`
- `DB_HOST=<your-neon-host>`
- `DB_PORT=5432`
- `DB_DATABASE=manager_agent`
- `DB_USERNAME=<your-neon-user>`
- `DB_PASSWORD=<your-neon-password>`
- `DB_SSLMODE=require`

## 🔌 Connection Methods

### Option 1: Using Connection String

```php
// In .env
DB_URL=postgresql://user:password@host.neon.tech:5432/manager_agent?sslmode=require
```

### Option 2: Individual Parameters

```php
// In .env
DB_CONNECTION=pgsql
DB_HOST=host.neon.tech
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=user
DB_PASSWORD=password
DB_SSLMODE=require
```

## 🔒 SSL Configuration

Neon DB requires SSL connections. This is automatically handled by Laravel when you set:

```env
DB_SSLMODE=require
```

Available modes:

- `disable` - No SSL
- `allow` - SSL if available
- `prefer` - SSL preferred (default)
- `require` - SSL required
- `verify-ca` - Verify CA
- `verify-full` - Verify full chain

## 🐳 Docker Deployment with Neon DB

1. Update your `.env.docker` or docker-compose environment with Neon DB credentials
2. Run migrations:
    ```bash
    docker-compose exec app php artisan migrate
    ```

## 📊 PostgreSQL Features Used

The application uses:

- **Eloquent ORM** - Database-agnostic, fully compatible with PostgreSQL
- **Query Builder** - Works with all Laravel-supported databases
- **Migrations** - Use standard schema operations compatible with PostgreSQL
- **Collections** - Laravel collections for data manipulation

## ✅ Compatibility Notes

All Laravel components used in this application are compatible with PostgreSQL:

- ✅ Eloquent Models and Relations
- ✅ Query Builder
- ✅ Migrations
- ✅ Seeders
- ✅ Database transactions
- ✅ JSON columns (JSON, JSONB data types)

## 🔧 Useful Commands

### Run migrations

```bash
php artisan migrate
# Fresh migration (drops all tables first)
php artisan migrate:fresh --seed
```

### Connect to database via CLI

```bash
# Using connection string
psql postgresql://user:password@host.neon.tech:5432/manager_agent

# Or using individual parameters
psql -h host.neon.tech -U user -d manager_agent -W
```

### Laravel Tinker

```bash
php artisan tinker
# Then query data:
# >>> App\Models\Employee::all();
# >>> App\Models\Task::count();
```

## 🐛 Troubleshooting

### "SQLSTATE[08006]" - Connection failed

- Verify host, port, username, password in `.env`
- Check if Neon DB credentials are correct
- Ensure SSL is properly configured

### "SQLSTATE[42P01]" - Relation does not exist

- Run migrations first: `php artisan migrate`
- Check table names match model definitions

### "Peer certificate cannot be authenticated" SSL error

- Ensure `DB_SSLMODE=require` is set in `.env`
- Try using the full connection string from Neon console

## 📚 Additional Resources

- [Neon Documentation](https://neon.tech/docs)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Laravel Database Documentation](https://laravel.com/docs/database)
