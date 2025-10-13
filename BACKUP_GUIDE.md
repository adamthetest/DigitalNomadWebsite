# Data Backup & Restore System

This document explains how to backup and restore data from your Digital Nomad Website.

## 📋 Available Commands

### Backup Commands

```bash
# Backup all data (default)
php artisan backup:data

# Backup specific data types
php artisan backup:data --type=users
php artisan backup:data --type=cities
php artisan backup:data --type=articles
php artisan backup:data --type=deals
php artisan backup:data --type=newsletter
php artisan backup:data --type=favorites

# Backup in different formats
php artisan backup:data --format=json    # JSON format (default)
php artisan backup:data --format=csv     # CSV format
php artisan backup:data --format=sql     # SQL INSERT statements
```

### Restore Commands

```bash
# Restore all data from a backup
php artisan restore:data backups/2025-10-13_22-58-00

# Restore specific table
php artisan restore:data backups/2025-10-13_22-58-00 --table=users

# Restore without confirmation prompt
php artisan restore:data backups/2025-10-13_22-58-00 --confirm
```

## 📁 Backup Structure

Backups are stored in `storage/app/private/backups/` with timestamped directories:

```
storage/app/private/backups/
├── 2025-10-13_22-58-00/
│   ├── users.json
│   ├── cities.json
│   ├── articles.json
│   ├── deals.json
│   ├── newsletter_subscribers.json
│   ├── favorites.json
│   ├── coworking_spaces.json
│   ├── cost_items.json
│   ├── visa_rules.json
│   ├── affiliate_links.json
│   └── backup_summary.json
└── 2025-10-13_22-57-56/
    └── users.json
```

## 📊 What Gets Backed Up

### Users Table
- User ID, name, email
- Email verification status
- Created/updated timestamps
- **Note**: Passwords are NOT backed up for security

### Cities Table
- All city information
- Country relationships
- Coordinates, descriptions, stats

### Articles Table
- Article content and metadata
- City relationships
- Author information

### Deals Table
- Deal information and pricing
- Validity dates and categories

### Newsletter Subscribers
- Subscriber information
- Interests and preferences
- Subscription status

### Favorites
- User favorites with relationships
- Personal notes

### Coworking Spaces
- Space details and amenities
- Pricing and contact information

### Cost Items
- Cost of living data
- Categories and pricing

### Visa Rules
- Visa information by country
- Requirements and restrictions

### Affiliate Links
- Affiliate link data
- Tracking information

## 🔧 Backup Formats

### JSON Format (Default)
- Human-readable
- Easy to parse programmatically
- Includes relationships

### CSV Format
- Spreadsheet compatible
- Good for data analysis
- Easy to import into other systems

### SQL Format
- Direct database import
- Includes INSERT statements
- Database agnostic

## ⚠️ Important Notes

### Security
- **Passwords are NOT backed up** for security reasons
- Admin access is required for restore operations
- Backups are stored in private directory

### Data Integrity
- Restore operations **truncate existing data**
- Always backup before restoring
- Test restores in development environment first

### File Locations
- Backups: `storage/app/private/backups/`
- Logs: `storage/logs/laravel.log`
- Database: `database/database.sqlite`

## 🚀 Automated Backups

### Cron Job Setup
Add to your crontab for daily backups:

```bash
# Daily backup at 2 AM
0 2 * * * cd /path/to/your/project && php artisan backup:data --type=all --format=json
```

### Laravel Scheduler
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:data --type=all')
             ->daily()
             ->at('02:00');
}
```

## 📱 Admin Panel Integration

The backup system can be integrated into the admin panel for easy management:

- View all backups
- Create new backups
- Download backup files
- Restore from backups
- Delete old backups

## 🔍 Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   chmod -R 755 storage/
   ```

2. **Storage Directory Missing**
   ```bash
   php artisan storage:link
   ```

3. **Backup Fails**
   - Check disk space
   - Verify database connection
   - Check Laravel logs

### Logs
Check backup/restore logs in:
- `storage/logs/laravel.log`
- Command output

## 📞 Support

For backup-related issues:
1. Check Laravel logs
2. Verify file permissions
3. Ensure sufficient disk space
4. Test with small data sets first
