# php-laravels

Laravel application with complete Docker development environment.

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Make (optional, for convenience commands)

### Installation & Setup

**Project was created with Laravel Docker Creator v2.0**

```bash
# Start Docker environment
make up

# Complete setup (Generate key, run migrations, setup storage)
make setup

# Install dependencies (if needed)
make install
```

### Access Points

- 🌐 **Application**: http://localhost:8080
- 🗄️ **PHPMyAdmin**: http://localhost:8081
- 📧 **Mailhog**: http://localhost:8025

## 🛠 Available Commands

| Command | Description |
|---------|-------------|
| `make up` | Start all services |
| `make down` | Stop all services |
| `make restart` | Restart all services |
| `make shell` | Access app container |
| `make logs` | Show container logs |
| `make setup` | Complete Laravel setup |
| `make install` | Install dependencies |
| `make fresh` | Fresh install with migrations |
| `make migrate` | Run migrations |
| `make test` | Run tests |
| `make cache` | Clear all cache |
| `make optimize` | Optimize for production |

## 🐳 Services

- **app**: PHP 8.2-FPM with Laravel (UID/GID: 1000/1000)
- **nginx**: Web server (Port 8080)
- **mysql**: MySQL 8.0 database (Port 3306)
- **redis**: Redis cache (Port 6379)
- **phpmyadmin**: Database management (Port 8081)
- **mailhog**: Email testing (Port 8025)
- **composer**: Dependency management
- **node**: Frontend asset compilation

## 📁 Project Structure

```
php-laravels/
├── docker/                 # Docker configurations
│   ├── nginx/             # Nginx configs
│   ├── php/               # PHP Dockerfile & entrypoint
│   └── mysql/             # MySQL init scripts
├── docker-compose.yml     # Docker services
├── Makefile              # Development commands
└── ... (Laravel files)
```

## 🔧 Development

### Running Artisan Commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:controller ExampleController
```

### Installing PHP Packages
```bash
docker-compose run --rm composer require package/name
```

### Installing NPM Packages
```bash
docker-compose run --rm node npm install package-name
```

### Database Access
- **Host**: localhost
- **Port**: 3306
- **Database**: php_laravels
- **Username**: php_laravels
- **Password**: secret
- **Root Password**: rootsecret

## 📧 Email Testing

Mailhog captures all outgoing emails in development:
- Web interface: http://localhost:8025
- SMTP: localhost:1025

## 🔐 Security Features

- **User Mapping**: Container user matches host UID/GID (1000:1000)
- **Permission Management**: Automatic Laravel directory permissions
- **Secure Defaults**: 755/644 file permissions with 775 for writable directories

## 🚀 Production Deployment

1. Update environment variables in `.env`
2. Build production images
3. Use docker-compose.prod.yml for production deployment

## 📝 Notes

- **Created with**: Laravel Docker Creator v2.0
- **Host UID/GID**: 1000/1000
- **Laravel Version**: 12.x
- **PHP Version**: 8.2
- **MySQL Version**: 8.0

---

Happy coding! 🎉

**Created with Laravel Docker Creator v2.0**
