# 🎯 Manager Agent - Employee Performance Management System

A comprehensive Laravel-based employee performance management and analytics system built with PostgreSQL and Docker, optimized for deployment on Render.

## ✨ Features

- **Employee Management**: Track employee information, teams, and roles
- **Task Management**: Assign, track, and manage employee tasks
- **Performance Analytics**: Comprehensive employee performance scoring and analytics
- **Attendance Tracking**: Monitor employee attendance and patterns
- **Meeting Notes**: Record and track meeting discussions
- **GitHub Integration**: Track git commits and contributions
- **Team Management**: Create and manage team structures
- **Performance Reports**: Generate detailed performance and team reports
- **Dashboard**: Interactive dashboards for analytics and insights

## 🛠️ Technology Stack

- **Backend**: Laravel 11.x with PHP 8.2
- **Database**: PostgreSQL 15
- **Frontend**: Blade templates with Vite
- **Containerization**: Docker & Docker Compose
- **Deployment**: Render platform
- **API**: RESTful API with JSON responses
- **Task Queuing**: Database-driven job queue

## 📋 Prerequisites

- Docker Desktop (for local development)
- Git
- PHP 8.2+ (for local development without Docker)
- PostgreSQL 15+ (if not using Docker)

## 🚀 Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd manager_agent

# Start the application
docker-compose up -d

# Application will be available at http://localhost:8080
```

See [QUICKSTART.md](./QUICKSTART.md) for more details.

### Option 2: Local Development

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup PostgreSQL database
# Update .env with your database credentials

# Run migrations and seed
php artisan migrate --seed

# Start development server
php artisan serve
npm run dev
```

## 📚 Documentation

- **[QUICKSTART.md](./QUICKSTART.md)** - Get started in 5 minutes
- **[DEPLOYMENT.md](./DEPLOYMENT.md)** - Complete deployment guide for Render
- **[ARCHITECTURE.md](./docs/ARCHITECTURE.md)** - System architecture and design

## 🐳 Docker Setup

### Services

The `docker-compose.yml` includes:

- **PostgreSQL 15**: Database server
- **PHP-FPM 8.2**: Application runtime
- **Nginx**: Web server with reverse proxy
- **Redis** (optional): Cache and session store

### Environment Files

- `.env` - Local development
- `.env.docker` - Docker Compose specific settings
- `.env.production` - Production settings (Render)

## 📊 Database

### Migration from MySQL to PostgreSQL

This project has been migrated from MySQL to PostgreSQL:

- ✅ All configurations updated to use PostgreSQL
- ✅ Database connection set to port 5432
- ✅ All queries are database-agnostic (Eloquent ORM)
- ✅ Migrations use standard SQL compatible with PostgreSQL

### Running Migrations

```bash
# Development
docker-compose exec app php artisan migrate

# Production (automatic on Render deployment)
# Migrations run automatically via entrypoint.sh
```

## 🌐 Deployment

### Deploy to Render

1. **Push to GitHub**

```bash
git add .
git commit -m "Prepare for production deployment"
git push origin main
```

2. **Deploy via Render Dashboard**
    - Go to https://dashboard.render.com/
    - Click "New +" → "Infrastructure as Code"
    - Select your repository
    - Deploy

3. **Set Environment Variables**
    - `APP_URL`: Your production domain
    - `GEMINI_API_KEY`: Your API key
    - Database credentials (auto-configured with render.yaml)

For detailed instructions, see [DEPLOYMENT.md](./DEPLOYMENT.md).

## 📁 Project Structure

```
manager_agent/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Application controllers
│   │   └── Middleware/         # Custom middleware
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic services
├── database/
│   ├── migrations/             # Database migrations
│   ├── seeders/                # Database seeders
│   └── factories/              # Model factories
├── docker/                     # Docker configurations
│   ├── Dockerfile             # Production Dockerfile
│   ├── entrypoint.sh          # Container startup script
│   ├── nginx.conf             # Nginx configuration
│   ├── php-fpm.conf           # PHP-FPM configuration
│   └── supervisord.conf       # Supervisor configuration
├── routes/                     # Application routes
├── resources/                  # Views and assets
├── storage/                    # Application storage
├── bootstrap/
│   └── config/                # Configuration files
├── tests/                      # Tests
├── docker-compose.yml          # Local development compose file
├── render.yaml                # Render deployment blueprint
└── DEPLOYMENT.md              # Deployment guide
```

## 🔧 Configuration

### Environment Variables

Key environment variables (see `.env.example`):

```env
APP_ENV=production|local
APP_DEBUG=false|true
DB_CONNECTION=pgsql
DB_HOST=localhost|container
DB_PORT=5432
DB_DATABASE=manager_agent
DB_USERNAME=postgres
DB_PASSWORD=your_password
GEMINI_API_KEY=your_api_key
```

### Database Configuration

PostgreSQL connection settings are configured in:

- `bootstrap/config/database.php` - Laravel database config
- Environment variables - Connection details

## 🧪 Testing

```bash
# Run tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=TestName

# Coverage report
docker-compose exec app php artisan test --coverage
```

## 📊 APIs

The application provides RESTful APIs for:

- Employee management
- Task tracking
- Performance analytics
- Attendance records
- Team management
- GitHub commits
- Meeting notes

### API Endpoints

See routes in `routes/api.php` for complete API documentation.

## 🔐 Security

- ✅ HTTPS enabled (automatic with Render)
- ✅ Security headers configured in Nginx
- ✅ Input validation and sanitization
- ✅ CSRF protection with Laravel's middleware
- ✅ Environment variables for sensitive data
- ✅ Database credentials never committed

## 📈 Performance

- **Caching**: Database-backed caching with optimization
- **Database**: PostgreSQL with efficient queries
- **Compression**: Gzip compression enabled in Nginx
- **Asset Optimization**: Vite for fast asset compilation

## 🐛 Troubleshooting

### Database Connection Issues

```bash
# Check PostgreSQL status
docker-compose ps postgres

# View logs
docker-compose logs postgres
```

### Application Not Starting

```bash
# View application logs
docker-compose logs app

# Check migrations
docker-compose exec app php artisan migrate:status
```

### Port Already in Use

```bash
# Change PORT in docker-compose.yml
# Or stop conflicting services
docker-compose down
```

See [DEPLOYMENT.md](./DEPLOYMENT.md#-troubleshooting) for more troubleshooting steps.

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -am 'Add new feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 📞 Support

For help with:

- **Laravel**: https://laravel.com/docs
- **PostgreSQL**: https://www.postgresql.org/docs/
- **Docker**: https://docs.docker.com/
- **Render**: https://render.com/docs

## 🎯 Roadmap

- [ ] Advanced analytics dashboard
- [ ] Real-time notifications
- [ ] Mobile app
- [ ] Performance forecasting with ML
- [ ] Integration with other HR systems
- [ ] Advanced reporting capabilities

---

**Last Updated**: 2026-06-16  
**Database**: PostgreSQL 15  
**PHP**: 8.2  
**Laravel**: 11.x  
**Docker**: Ready for Render deployment ✅
