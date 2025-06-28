# PHKM - Pharmacy Management System

A modern pharmacy management system built with Laravel 12 and Vue.js 3, featuring role-based access control for administrators, doctors, and pharmacists.

## Features

- **Multi-role Authentication**: Admin, Doctor, and Pharmacist roles with specific permissions
- **Patient Management**: Complete patient records and information tracking
- **Prescription Management**: Digital prescription creation and processing
- **Medicine Inventory**: Comprehensive medicine database and stock management
- **Invoice System**: Automated billing and payment processing
- **Admin Panel**: Filament-powered admin interface for complete system management
- **Modern UI**: Vue.js 3 with TailwindCSS and shadcn/vue components

## Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **PHP 8.2+** - Programming language
- **SQLite/MySQL/PostgreSQL** - Database (SQLite by default)
- **Filament 3** - Admin panel
- **Spatie Laravel Permission** - Role and permission management

### Frontend
- **Vue.js 3** - Frontend framework
- **Inertia.js** - SPA experience without API
- **TailwindCSS** - Utility-first CSS framework
- **shadcn/vue** - Re-usable UI components
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP 8.2 or higher**
- **Composer** (PHP dependency manager)
- **Node.js 18+** and **npm** (for frontend dependencies)
- **Git** (for version control)

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url> phkm
cd phkm
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Create your environment file:

```bash
cp .env.example .env
```

**Note**: If `.env.example` doesn't exist, create a `.env` file with the following content:

```env
APP_NAME="PHKM - Pharmacy Management"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=phkm
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### Option A: SQLite (Default - Recommended for Development)

Create the SQLite database file:

```bash
touch database/database.sqlite
```

#### Option B: MySQL/PostgreSQL

If you prefer MySQL or PostgreSQL, update your `.env` file:

**For MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phkm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**For PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=phkm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

### 8. Seed the Database

This will create sample data including default users, patients, medicines, prescriptions, and invoices:

```bash
php artisan db:seed
```

### 9. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 10. Start the Development Server

#### Option A: Using Laravel's Built-in Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

#### Option B: Using the Composer Dev Script (Recommended)

This runs the server, queue worker, logs, and Vite dev server concurrently:

```bash
composer run dev
```

This will start:
- Laravel development server on `http://localhost:8000`
- Queue worker for background jobs
- Log monitoring with Pail
- Vite development server for hot module reloading

## Default User Accounts

After seeding, you can log in with these default accounts:

### Administrator
- **Username**: `admin`
- **Password**: `password`
- **Access**: Full admin panel access via Filament

### Doctor
- **Username**: `doctor`
- **Password**: `password`
- **Access**: Patient search, prescription creation

### Pharmacist
- **Username**: `apoteker`
- **Password**: `password`
- **Access**: Prescription processing, payment handling, invoice generation

## Application Structure

### Main Routes

- `/` - Redirects to login
- `/login` - Authentication page
- `/dashboard` - Role-based dashboard redirect
- `/dashboard/doctor` - Doctor interface
- `/dashboard/pharmacist` - Pharmacist interface
- `/admin` - Admin panel (Filament)

### Key Features by Role

#### Admin
- Complete system management via Filament admin panel
- User management (create/edit doctors, pharmacists)
- Patient records management
- Medicine inventory management
- Prescription oversight
- Invoice and payment tracking

#### Doctor
- Search and select patients
- Search available medicines
- Create digital prescriptions
- View prescription history

#### Pharmacist
- View pending prescriptions
- Process prescription fulfillment
- Update prescription status (pending → ready → completed)
- Handle payment processing
- Generate invoices

## Development Commands

### Frontend Development
```bash
# Start Vite dev server with hot reloading
npm run dev

# Build for production
npm run build

# Build with SSR support
npm run build:ssr

# Format code
npm run format

# Lint code
npm run lint
```

### Backend Development
```bash
# Start development environment (all services)
composer run dev

# Run tests
composer run test
php artisan test

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models

# Database operations
php artisan migrate:fresh --seed  # Reset and reseed database
php artisan migrate:rollback      # Rollback last migration
php artisan db:wipe               # Drop all tables
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
