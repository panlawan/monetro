# Monetro.io - Personal Finance Management System

Modern personal finance management system built with Laravel 11, Bootstrap 5.3.0, Font Awesome 6.4.0, and Alpine.js for tracking income, expenses, and investments in stocks, crypto, and forex with real-time updates.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-purple?style=flat-square&logo=bootstrap)
![Font Awesome](https://img.shields.io/badge/Font%20Awesome-6.4.0-blue?style=flat-square&logo=fontawesome)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.4.2-green?style=flat-square&logo=alpinedotjs)
![Vite](https://img.shields.io/badge/Vite-6.2.4-yellow?style=flat-square&logo=vite)

## 🌟 Features

- **💰 Financial Tracking**: Income, expenses, and investment management
- **📊 Real-time Dashboard**: Live market data and portfolio updates
- **🔒 Authentication**: Laravel Breeze with Bootstrap 5 styling
- **📱 Responsive Design**: Mobile-first responsive interface
- **⚡ Real-time Updates**: Market data simulation and live balance updates
- **🎨 Modern UI**: Bootstrap 5.3.0 with Font Awesome 6.4.0 icons
- **🚀 Interactive**: Alpine.js for dynamic user interactions
- **🐳 Docker Ready**: Complete Docker development environment
- **🔄 CI/CD Pipeline**: GitHub Actions for automated deployment

## 🛠️ Tech Stack

### Backend
- **Laravel 11.x**: PHP framework
- **MySQL 8.0**: Database
- **Redis**: Cache and session storage
- **PHP 8.2**: Programming language

### Frontend
- **Bootstrap 5.3.0**: CSS framework
- **Font Awesome 6.4.0**: Icon library
- **Alpine.js 3.4.2**: JavaScript framework
- **Sass 1.89.2**: CSS preprocessor
- **Vite 6.2.4**: Build tool

### DevOps
- **Docker & Docker Compose**: Containerization
- **GitHub Actions**: CI/CD pipeline
- **Nginx**: Web server

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### 1. Clone Repository
```bash
git clone https://github.com/your-username/monetro.io.git
cd monetro.io
```

### 2. Start Development Environment
```bash
# Start containers
docker-compose up -d

# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose run --rm node npm install

# Setup environment
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Build frontend assets
docker-compose run --rm node npm run build
```

### 3. Access Application
- **Web Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## 📦 Installation Guide

### 1. Laravel Breeze Authentication Setup

```bash
# Enter app container
docker-compose exec app bash

# Install Laravel Breeze
composer require laravel/breeze --dev
php artisan breeze:install blade

# Run migrations
php artisan migrate
exit
```

### 2. Frontend Dependencies Setup

```bash
# Install Bootstrap, Font Awesome, and related packages
docker-compose run --rm node npm install bootstrap@5.3.0 @popperjs/core
docker-compose run --rm node npm install @fortawesome/fontawesome-free@6.4.0
docker-compose run --rm node npm install sass@1.89.2 --save-dev
docker-compose run --rm node npm install terser --save-dev
```

### 3. Project Structure

```
monetro.io/
├── resources/
│   ├── sass/
│   │   └── app.scss                 # Bootstrap + Font Awesome + Custom styles
│   ├── js/
│   │   └── app.js                   # Alpine.js + Bootstrap + Custom JS
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php        # Main layout with Bootstrap navbar
│       ├── dashboard.blade.php      # Financial dashboard
│       └── auth/                    # Authentication views
├── vite.config.js                   # Vite configuration
├── package.json                     # Node.js dependencies
├── docker-compose.yml              # Docker services
└── .github/workflows/               # CI/CD pipeline
```

### 4. Current Dependencies

```json
{
  "devDependencies": {
    "@popperjs/core": "^2.11.8",
    "@tailwindcss/forms": "^0.5.2",
    "@tailwindcss/vite": "^4.0.0",
    "alpinejs": "^3.4.2",
    "autoprefixer": "^10.4.2",
    "axios": "^1.8.2",
    "bootstrap": "^5.3.0",
    "concurrently": "^9.0.1",
    "laravel-vite-plugin": "^1.2.0",
    "postcss": "^8.4.31",
    "sass": "^1.89.2",
    "tailwindcss": "^3.1.0",
    "terser": "^5.24.0",
    "vite": "^6.2.4"
  },
  "dependencies": {
    "@fortawesome/fontawesome-free": "^6.4.0"
  }
}
```

## 🎨 Frontend Development

### Bootstrap 5.3.0 Configuration

**SCSS Structure (`resources/sass/app.scss`):**
```scss
// Import Bootstrap
@import '~bootstrap/scss/bootstrap';

// Import Font Awesome
@import '~@fortawesome/fontawesome-free/css/all.min.css';

// Custom Monetro.io variables
:root {
    --monetro-primary: #4e73df;
    --monetro-success: #1cc88a;
    --monetro-danger: #e74a3b;
    --monetro-warning: #f6c23e;
    --monetro-info: #36b9cc;
}

// Custom components
.monetro-card-stat { /* financial stat cards */ }
.market-item { /* market data styling */ }
.financial-table { /* transaction tables */ }
```

### Alpine.js Integration

**JavaScript Setup (`resources/js/app.js`):**
```javascript
import 'bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Financial dashboard components
Alpine.data('financialDashboard', () => ({
    balance: 1247850.25,
    income: 85420.00,
    expenses: 42180.50,
    investments: 485200.75
}));
```

### Font Awesome Icons

**Usage Examples:**
```html
<!-- Financial Icons -->
<i class="fas fa-wallet"></i>          <!-- Wallet -->
<i class="fas fa-chart-line"></i>      <!-- Investments -->
<i class="fab fa-bitcoin"></i>         <!-- Bitcoin -->
<i class="fas fa-dollar-sign"></i>     <!-- Income -->
<i class="fas fa-minus-circle"></i>    <!-- Expenses -->

<!-- Status Icons -->
<i class="fas fa-caret-up text-success"></i>   <!-- Profit -->
<i class="fas fa-caret-down text-danger"></i>  <!-- Loss -->
```

## 🔧 Development Commands

### Frontend Development
```bash
# Development mode (hot reload)
docker-compose run --rm node npm run dev

# Production build
docker-compose run --rm node npm run build

# Watch mode
docker-compose run --rm node npm run watch
```

### Laravel Commands
```bash
# Enter app container
docker-compose exec app bash

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Create new migration
php artisan make:migration create_transactions_table

# Create new controller
php artisan make:controller TransactionController
```

### Database Operations
```bash
# Fresh migration (⚠️ Drops all data)
docker-compose exec app php artisan migrate:fresh

# Seed database
docker-compose exec app php artisan db:seed

# Access MySQL
docker-compose exec mysql mysql -u monetro -p
```

## 🏗️ Project Architecture

### Dashboard Components

**Financial Statistics Cards:**
```html
<div class="col-xl-3 col-md-6 mb-4" data-stat-update>
    <div class="card border-start-primary border-4 h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Balance
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800" data-value>
                        ฿1,247,850.25
                    </div>
                </div>
                <div class="text-primary">
                    <i class="fas fa-wallet fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Market Data Updates:**
```html
<div class="market-item" data-market-update>
    <div class="d-flex justify-content-between">
        <div>
            <strong>SET Index</strong>
            <div class="text-muted small">Thai Stock Market</div>
        </div>
        <div class="text-end">
            <div class="market-price" data-price>1,458.75</div>
            <div class="market-change text-success" data-change>
                <i class="fas fa-caret-up"></i> +0.87%
            </div>
        </div>
    </div>
</div>
```

### Authentication Views

**Layout Component (`resources/views/layouts/app.blade.php`):**
- Bootstrap 5 navigation with Font Awesome icons
- Responsive sidebar for larger screens
- User dropdown with profile management
- Mobile-friendly design

**Features:**
- ✅ CSRF Protection
- ✅ Form Validation
- ✅ Error Handling
- ✅ Success Messages
- ✅ Loading States

## 🎯 Features Implementation

### Real-time Market Data
```javascript
// Simulated market updates every 30 seconds
function updateMarketData() {
    const marketItems = document.querySelectorAll('[data-market-update]');
    // Price fluctuation simulation
    // Update UI with new prices and change indicators
}
```

### Interactive Financial Dashboard
```javascript
// Alpine.js component for financial management
Alpine.data('financialDashboard', () => ({
    addTransaction(type, amount) {
        // Update balance based on transaction type
        // Trigger custom events for UI updates
    }
}));
```

### Keyboard Shortcuts
- `Ctrl + I`: Add new income
- `Ctrl + E`: Add new expense  
- `Ctrl + N`: Add new investment
- `Escape`: Close modals

## 🚀 Deployment

### GitHub Actions CI/CD Pipeline

**Pipeline Stages:**
1. **Build & Test**: Install dependencies, run tests, build assets
2. **Security Scan**: Security vulnerability checks
3. **Deploy Staging**: Automatic deployment to staging environment
4. **Deploy Production**: Zero-downtime deployment to production
5. **Rollback**: Automatic rollback on failure

**Required GitHub Secrets:**
```bash
# Staging Environment
STAGING_SSH_HOST=your-staging-server-ip
STAGING_SSH_USER=ubuntu
STAGING_SSH_PRIVATE_KEY=-----BEGIN OPENSSH PRIVATE KEY-----...

# Production Environment  
PROD_SSH_HOST=your-production-server-ip
PROD_SSH_USER=ubuntu
PROD_SSH_PRIVATE_KEY=-----BEGIN OPENSSH PRIVATE KEY-----...

# Notifications
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
```

### Server Requirements

**Minimum Specifications:**
- Ubuntu 22.04 LTS
- PHP 8.2 with extensions
- MySQL 8.0
- Redis 7.x
- Nginx
- Node.js 20.x
- SSL Certificate (Let's Encrypt)

### Production Build

```bash
# Build for production
docker-compose run --rm node npm run build

# Optimize Laravel
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## 🧪 Testing

### Frontend Testing
```bash
# Test icons and styling
curl -f http://localhost:8080/test-icons

# Test Font Awesome loading
# Check browser console for success messages
```

### Backend Testing
```bash
# Run Laravel tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=AuthenticationTest
```

## 🔍 Troubleshooting

### Common Issues

**1. Font Awesome Icons Not Showing:**
```bash
# Rebuild assets
docker-compose run --rm node npm run build

# Check for 404 errors in browser developer tools
# Verify Font Awesome files in public/build/fonts/
```

**2. Bootstrap Styles Not Applied:**
```bash
# Clear Laravel caches
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan config:clear

# Rebuild with verbose output
docker-compose run --rm node npm run build -- --verbose
```

**3. Sass Deprecation Warnings:**
```
⚠️ These warnings are from Bootstrap and don't affect functionality:
- Sass @import deprecation warnings
- Color function warnings  
- Mixed declaration warnings

The application works perfectly despite these warnings.
```

**4. Docker Permission Issues:**
```bash
# Fix file permissions
sudo chown -R $USER:$USER node_modules/
sudo chown -R $USER:$USER public/build/
```

### Performance Optimization

**Frontend:**
```bash
# Enable gzip compression in Nginx
# Preload critical fonts
# Optimize images with WebP format
# Use CSS/JS minification
```

**Backend:**
```bash
# Enable OPcache
# Configure Redis for sessions
# Database query optimization
# Laravel route/config caching
```

## 📚 Documentation

### API Endpoints (Future)
- `GET /api/dashboard` - Dashboard data
- `POST /api/transactions` - Create transaction
- `GET /api/market-data` - Real-time market data
- `GET /api/portfolio` - Investment portfolio

### Database Schema
```sql
-- Users table
users (id, name, email, phone, password, created_at, updated_at)

-- Transactions table (Future)
transactions (id, user_id, type, amount, description, category, created_at)

-- Investments table (Future)  
investments (id, user_id, symbol, quantity, purchase_price, current_price, created_at)
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Commit: `git commit -am 'Add new feature'`
5. Push: `git push origin feature/new-feature`
6. Create Pull Request

### Code Standards
- Follow PSR-12 for PHP
- Use ESLint for JavaScript
- Bootstrap classes over custom CSS when possible
- Font Awesome icons over custom SVGs
- Alpine.js for interactivity

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Development**: [Your Name](https://github.com/your-username)
- **Design**: Bootstrap 5 + Font Awesome
- **Infrastructure**: Docker + GitHub Actions

## 🙏 Acknowledgments

- Laravel Team for the excellent framework
- Bootstrap Team for the CSS framework
- Font Awesome for the icon library
- Alpine.js Team for the JavaScript framework
- Docker Community for containerization tools

---

## 🔗 Links

- **Live Demo**: https://monetro.io
- **Documentation**: https://docs.monetro.io
- **Bug Reports**: https://github.com/your-username/monetro.io/issues
- **Feature Requests**: https://github.com/your-username/monetro.io/discussions

---

**Built with ❤️ using Laravel, Bootstrap, and Font Awesome**

*Last Updated: $(date) - Version 1.0.0*

rm -f public/hot
rm -rf public/build/

php artisan view:clear
php artisan cache:clear
php artisan config:clear

npm install
npm run build