# Digital Nomad Website

A comprehensive Laravel application for digital nomads to discover cities, calculate costs, find coworking spaces, and access exclusive deals.

## Features

### Phase 0 - Foundation ✅
- **Authentication System**: Complete user registration, login, logout, and password reset functionality
- **Admin Panel**: Filament-powered admin interface for content management
- **Database Structure**: Comprehensive data models for cities, countries, deals, articles, and more
- **Responsive Design**: Modern, mobile-friendly UI with Tailwind CSS

### Authentication
- User registration and login
- Password reset functionality
- Protected dashboard for authenticated users
- Admin authentication via Filament

### Admin Features
- Manage cities and countries
- Create and edit articles
- Manage deals and affiliate links
- Handle newsletter subscribers
- Configure visa rules and coworking spaces

## Installation

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Copy environment file: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Configure your database in `.env`
6. Run migrations and seeders: `php artisan migrate:fresh --seed`
7. Start the development server: `php artisan serve`

## Default Credentials

- **Admin Panel**: `/admin`
- **Email**: `admin@digitalnomad.com`
- **Password**: `password`

## Testing

Run the test suite:
```bash
php artisan test
```

## Technology Stack

- **Backend**: Laravel 11
- **Frontend**: Blade templates with Tailwind CSS
- **Admin Panel**: Filament
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Testing**: PHPUnit

## Contributing

This project is in active development. Phase 0 (Foundation) is complete with authentication and admin functionality.
