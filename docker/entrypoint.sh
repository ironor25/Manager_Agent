#!/bin/sh
set -e

# Environment setup for Render
PORT=${PORT:-8080}
APP_ENV=${APP_ENV:-production}

# Log startup
echo "Starting Laravel application on port $PORT in $APP_ENV environment..."

# Wait for PostgreSQL to be ready (with timeout)
if [ ! -z "$DB_HOST" ]; then
    echo "Waiting for PostgreSQL at $DB_HOST:${DB_PORT:-5432}..."
    counter=0
    until pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" 2>/dev/null || [ $counter -ge 30 ]; do
        echo "PostgreSQL is unavailable - sleeping..."
        sleep 1
        counter=$((counter + 1))
    done
    
    if [ $counter -ge 30 ]; then
        echo "Warning: Could not connect to PostgreSQL after 30 attempts"
    else
        echo "PostgreSQL is up and running"
    fi
fi

# Clear and regenerate configuration cache
echo "Clearing configuration cache..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true

echo "Generating configuration cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders if specified
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force
fi

# Create a symbolic link to storage if needed
if [ ! -L "public/storage" ]; then
    php artisan storage:link || true
fi

# Update nginx configuration with correct port
sed -i "s/listen 8080/listen $PORT/g" /etc/nginx/conf.d/default.conf

# Ensure proper permissions after artisan commands
chown -R www-data:www-data storage bootstrap/cache

# Start services using supervisor
echo "Starting web services..."
exec supervisord -c /etc/supervisord.conf
