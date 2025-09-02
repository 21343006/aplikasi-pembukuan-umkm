# 🔧 Dokumentasi Teknis Developer - Aplikasi UMKM

## 🏗️ Arsitektur Aplikasi

### **Technology Stack:**
- **Backend:** Laravel 11.x (PHP 8.2+)
- **Frontend:** Livewire 3.x + Alpine.js
- **Database:** SQLite (Development) / MySQL (Production)
- **Styling:** Bootstrap 5.x + Custom CSS
- **Icons:** Bootstrap Icons
- **Charts:** Chart.js (untuk grafik dan visualisasi)

### **Struktur Aplikasi:**
```
aplikasi-umkm/
├── app/
│   ├── Livewire/           # Livewire Components
│   ├── Models/             # Eloquent Models
│   ├── Http/Controllers/   # Traditional Controllers
│   └── Providers/          # Service Providers
├── resources/
│   ├── views/
│   │   ├── livewire/       # Livewire Blade Views
│   │   └── layouts/        # Layout Templates
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript Files
├── database/
│   ├── migrations/        # Database Migrations
│   ├── seeders/          # Database Seeders
│   └── factories/        # Model Factories
└── routes/
    └── web.php           # Web Routes
```

---

## 📁 Struktur Livewire Components

### **Authentication:**
- `Auth/Login.php` - Login component
- `Auth/Register.php` - Registration component

### **Dashboard & Profile:**
- `Dashboard.php` - Main dashboard
- `UserProfile.php` - User profile management

### **Financial Management:**
- `Capitals/ModalPage.php` - Modal management
- `Capitals/FixedCostPage.php` - Fixed cost management
- `Capitals/CapitalearlyForm.php` - Initial capital form

### **Transactions:**
- `Incomes/IncomePage.php` - Income management
- `Incomes/ProductAnalysis.php` - Product analysis
- `Expenditures/ExpenditurePage.php` - Expenditure management

### **Business Analysis:**
- `Beps/BepForm.php` - Break Even Point analysis
- `Simulations/WhatIfAnalysis.php` - What-if scenario analysis
- `Reports/IrrPage.php` - IRR analysis

### **Reports:**
- `Reports/ProfitLoss.php` - Profit & Loss report
- `Reports/ReportbulananList.php` - Monthly reports
- `Reports/Reporttahunan.php` - Annual reports

### **Inventory:**
- `Stock/ProductStockPage.php` - Stock management
- `StockHistoryList.php` - Stock history

### **Debt & Receivables:**
- `Debts.php` - Debt management
- `Receivables.php` - Receivable management
- `DebtReceivable.php` - Combined debt/receivable view

---

## 🗄️ Database Schema

### **Core Tables:**

#### **users**
```sql
- id (primary key)
- name
- email
- password
- business_name
- business_type
- address
- phone
- nib
- initial_balance
- is_active
- created_at
- updated_at
```

#### **products**
```sql
- id (primary key)
- user_id (foreign key)
- name
- quantity
- low_stock_threshold
- created_at
- updated_at
```

#### **incomes**
```sql
- id (primary key)
- user_id (foreign key)
- product_id (foreign key)
- tanggal
- jumlah_terjual
- harga_satuan
- total_pendapatan
- biaya_per_unit
- laba
- created_at
- updated_at
```

#### **expenditures**
```sql
- id (primary key)
- user_id (foreign key)
- tanggal
- keterangan
- jumlah
- created_at
- updated_at
```

#### **capitals**
```sql
- id (primary key)
- user_id (foreign key)
- tanggal
- keperluan
- keterangan
- nominal
- jenis (masuk/keluar)
- created_at
- updated_at
```

#### **fixed_costs**
```sql
- id (primary key)
- user_id (foreign key)
- tanggal
- keperluan
- nominal
- created_at
- updated_at
```

#### **debts**
```sql
- id (primary key)
- user_id (foreign key)
- creditor_name
- description
- amount
- due_date
- status
- paid_amount
- paid_date
- notes
- created_at
- updated_at
```

#### **receivables**
```sql
- id (primary key)
- user_id (foreign key)
- debtor_name
- description
- amount
- due_date
- status
- paid_amount
- paid_date
- notes
- created_at
- updated_at
```

#### **stock_histories**
```sql
- id (primary key)
- user_id (foreign key)
- product_id (foreign key)
- type (in/out)
- quantity
- notes
- created_at
- updated_at
```

---

## 🔧 Konfigurasi & Setup

### **Environment Variables (.env):**
```env
APP_NAME="Aplikasi UMKM"
APP_ENV=local
APP_KEY=base64:your-key-here
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

LIVEWIRE_ASSET_URL=
```

### **Dependencies (composer.json):**
```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "livewire/livewire": "^3.0",
        "alpinejs": "^3.0"
    }
}
```

### **Frontend Dependencies (package.json):**
```json
{
    "devDependencies": {
        "autoprefixer": "^10.4.0",
        "postcss": "^8.4.0",
        "tailwindcss": "^3.0.0"
    }
}
```

---

## 🚀 Development Workflow

### **1. Setup Development Environment:**
```bash
# Clone repository
git clone <repository-url>
cd aplikasi-umkm

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with dummy data
php artisan db:seed --class=DummyDataSeeder

# Build assets
npm run build

# Start development server
php artisan serve
```

### **2. Development Commands:**
```bash
# Create new Livewire component
php artisan make:livewire ComponentName

# Create new model with migration
php artisan make:model ModelName -m

# Create new seeder
php artisan make:seeder SeederName

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize for production
php artisan optimize
```

### **3. Code Standards:**
- **PHP:** PSR-12 coding standards
- **JavaScript:** ESLint configuration
- **CSS:** Prettier formatting
- **Git:** Conventional commits

---

## 🔐 Security Implementation

### **Authentication:**
- Laravel's built-in authentication
- CSRF protection on all forms
- Session management
- Password hashing with bcrypt

### **Authorization:**
- Middleware-based route protection
- User-specific data access
- Input validation and sanitization

### **Data Protection:**
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- CSRF token validation
- Input sanitization

---

## 📊 Performance Optimization

### **Database Optimization:**
- Proper indexing on foreign keys
- Eager loading for relationships
- Query optimization with Laravel Debugbar
- Database connection pooling

### **Frontend Optimization:**
- Asset minification
- Image optimization
- Lazy loading for components
- CDN integration for static assets

### **Caching Strategy:**
- Route caching
- Config caching
- View caching
- Query result caching

---

## 🧪 Testing Strategy

### **Unit Tests:**
- Model tests
- Service class tests
- Helper function tests

### **Feature Tests:**
- Livewire component tests
- Route tests
- Authentication tests

### **Browser Tests:**
- User workflow tests
- UI interaction tests
- Cross-browser compatibility

### **Test Commands:**
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter TestClassName

# Run tests with coverage
php artisan test --coverage
```

---

## 📦 Deployment

### **Production Environment:**
- **Server:** Ubuntu 20.04+ / CentOS 8+
- **Web Server:** Nginx / Apache
- **PHP:** 8.2+
- **Database:** MySQL 8.0+ / PostgreSQL 13+
- **Cache:** Redis / Memcached

### **Deployment Steps:**
```bash
# 1. Server preparation
sudo apt update
sudo apt install nginx php8.2-fpm mysql-server

# 2. Application deployment
git clone <repository>
cd aplikasi-umkm
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Environment setup
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# 4. Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 5. Web server configuration
sudo nano /etc/nginx/sites-available/umkm-app
sudo ln -s /etc/nginx/sites-available/umkm-app /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

### **Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/aplikasi-umkm/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔄 Maintenance & Monitoring

### **Log Management:**
- Laravel logging to files
- Error tracking with Sentry (optional)
- Performance monitoring
- Database query logging

### **Backup Strategy:**
- Database backup (daily)
- File backup (weekly)
- Configuration backup
- Disaster recovery plan

### **Monitoring:**
- Server health monitoring
- Application performance monitoring
- Error rate monitoring
- User activity monitoring

---

## 🐛 Troubleshooting

### **Common Issues:**

#### **1. Livewire Component Not Loading:**
```bash
# Clear Livewire cache
php artisan livewire:discover
php artisan view:clear
```

#### **2. Database Connection Issues:**
```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo();

# Reset database
php artisan migrate:fresh --seed
```

#### **3. Asset Loading Issues:**
```bash
# Rebuild assets
npm run build
php artisan view:clear
```

#### **4. Permission Issues:**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

---

## 📚 Resources & References

### **Documentation:**
- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://laravel-livewire.com/docs)
- [Bootstrap Documentation](https://getbootstrap.com/docs)

### **Development Tools:**
- Laravel Debugbar
- Laravel Telescope
- Laravel IDE Helper
- PHP CS Fixer

### **Best Practices:**
- Laravel Best Practices
- PHP PSR Standards
- Security Best Practices
- Performance Optimization

---

## 🤝 Contributing

### **Development Workflow:**
1. Fork the repository
2. Create feature branch
3. Make changes
4. Write tests
5. Submit pull request

### **Code Review Process:**
- Automated testing
- Code style checking
- Security review
- Performance review

### **Release Process:**
- Version tagging
- Changelog generation
- Deployment automation
- Post-deployment testing

---

*Dokumentasi teknis ini dibuat untuk membantu developer memahami struktur aplikasi dan proses pengembangan.*
