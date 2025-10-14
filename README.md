# Digital Nomad Website

A comprehensive Laravel application for digital nomads to discover cities, calculate costs, find coworking spaces, and access exclusive deals.

**🌐 GitHub Repository**: [https://github.com/adamthetest/DigitalNomadWebsite](https://github.com/adamthetest/DigitalNomadWebsite)

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
- **Complete backup and restore system**

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

## Backup & Restore System

The application includes a comprehensive backup and restore system that covers all database tables and provides both command-line and admin panel interfaces.

### Backup System

#### Complete Website Backup
Backup all data on the website (20 database tables):

```bash
php artisan backup:data --type=all
```

#### Individual Table Backups
Backup specific data types:

```bash
# User data
php artisan backup:data --type=users

# Geographic data
php artisan backup:data --type=countries
php artisan backup:data --type=cities
php artisan backup:data --type=neighborhoods

# Content data
php artisan backup:data --type=articles
php artisan backup:data --type=deals

# Business data
php artisan backup:data --type=companies
php artisan backup:data --type=jobs
php artisan backup:data --type=job_interactions

# Security data
php artisan backup:data --type=security_logs
php artisan backup:data --type=banned_ips

# Service data
php artisan backup:data --type=coworking_spaces
php artisan backup:data --type=cost_items
php artisan backup:data --type=visa_rules

# Other data
php artisan backup:data --type=newsletter
php artisan backup:data --type=favorites
php artisan backup:data --type=affiliate_links
```

#### Backup Formats
Choose your preferred backup format:

```bash
# JSON format (default)
php artisan backup:data --type=all --format=json

# CSV format
php artisan backup:data --type=all --format=csv

# SQL format
php artisan backup:data --type=all --format=sql
```

### Restore System

#### Complete Restore
Restore all data from a backup:

```bash
php artisan restore:data backups/2025-10-14_16-08-28 --confirm
```

#### Individual Table Restore
Restore specific tables:

```bash
php artisan restore:data backups/2025-10-14_16-08-28 --table=users --confirm
php artisan restore:data backups/2025-10-14_16-08-28 --table=articles --confirm
```

### Admin Panel Backup Management

#### Access Backup Management
1. Log into the admin panel at `/admin`
2. Navigate to "Backup Management" in the sidebar
3. View backup statistics and recent backups

#### Create Backups via Admin Panel
- **Create Full Backup**: Backs up all 20 database tables
- **Backup Users Only**: Backs up user accounts and profiles
- **Backup Job Board**: Backs up companies, jobs, and job interactions
- **Backup Security Logs**: Backs up security events and banned IPs

#### Restore from Admin Panel
1. Go to Backup Management page
2. Find the backup you want to restore from
3. Click the orange "Restore" button
4. Confirm the warning dialog
5. Wait for restore to complete

### Backup Data Coverage

The complete backup includes **20 database tables**:

#### Geographic Data
- **Countries**: Country information and metadata
- **Cities**: City details with country relationships
- **Neighborhoods**: City subdivisions with city names

#### User & Authentication
- **Users**: User accounts with social profiles and preferences
- **Sessions**: Active user sessions (temporary)
- **Password Reset Tokens**: Password reset functionality (temporary)
- **Security Logs**: Security events with user context

#### Business Data
- **Companies**: Company profiles and information
- **Jobs**: Job listings with company relationships
- **Job User Interactions**: Job applications and interactions

#### Content
- **Articles**: Blog posts and guides
- **Deals**: Exclusive deals and offers
- **Newsletter Subscribers**: Email subscription data

#### User Preferences
- **Favorites**: User favorite items
- **Affiliate Links**: Affiliate marketing links

#### Location Services
- **Coworking Spaces**: Workspace listings with amenities
- **Cost Items**: Cost of living data
- **Visa Rules**: Visa requirements and information

#### System Data
- **Cache**: Application cache (temporary)
- **Banned IPs**: Security/IP blocking with admin info

### Backup File Structure

Backups are stored in `storage/app/private/backups/` with timestamp-based directories:

```
storage/app/private/backups/
├── 2025-10-14_16-08-28/
│   ├── backup_summary.json
│   ├── users.json
│   ├── cities.json
│   ├── countries.json
│   ├── neighborhoods.json
│   ├── articles.json
│   ├── deals.json
│   ├── companies.json
│   ├── jobs.json
│   ├── security_logs.json
│   └── ... (all 20 tables)
```

### Backup Summary

Each backup includes a `backup_summary.json` file with metadata:

```json
{
    "backup_date": "2025-10-14T16:08:28.931735Z",
    "backup_type": "all",
    "tables_backed_up": [
        "users", "cities", "countries", "neighborhoods", 
        "articles", "deals", "companies", "jobs", 
        "security_logs", "banned_ips", "..."
    ],
    "total_records": 48,
    "backup_location": "backups/2025-10-14_16-08-28"
}
```

### Safety Features

#### Restore Safety
- **Double Confirmation**: Requires confirmation before restore
- **Schema Validation**: Only restores columns that exist in current schema
- **Foreign Key Handling**: Restores tables in proper dependency order
- **Required Fields**: Handles missing required fields (e.g., sets default passwords)
- **Temporary Data**: Skips temporary tables (sessions, cache, tokens)

#### Backup Safety
- **Complete Coverage**: Backs up all database tables
- **Relationship Data**: Includes foreign key relationships for context
- **Multiple Formats**: Supports JSON, CSV, and SQL formats
- **Timestamped**: Each backup has a unique timestamp identifier

### Best Practices

#### Regular Backups
```bash
# Create daily backups
php artisan backup:data --type=all

# Create weekly security logs backup
php artisan backup:data --type=security_logs
```

#### Before Major Changes
```bash
# Always backup before updates
php artisan backup:data --type=all
```

#### Testing Restores
```bash
# Test restore on development environment first
php artisan restore:data backups/latest-backup --confirm
```

### Troubleshooting

#### Common Issues
- **Foreign Key Errors**: Restore command handles dependencies automatically
- **Missing Columns**: Only restores columns that exist in current schema
- **Password Issues**: Users will need to reset passwords after restore
- **Temporary Data**: Sessions and cache are not restored (by design)

#### Backup Location
- **Development**: `storage/app/private/backups/`
- **Production**: Configure appropriate storage disk in `.env`

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
