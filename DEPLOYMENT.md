# Muster Deployment Guide

This document outlines the steps to deploy Muster to a production environment.

## 1. Server Requirements
- PHP 8.4+
- PostgreSQL or MySQL (PostgreSQL recommended and pre-configured)
- Redis (Required for Horizon, Cache, and Sessions)
- Node.js 22+ (for building assets)
- Supervisor (for Horizon and queue workers)

## 2. Environment Configuration
Copy `.env.example` to `.env` and set the following production-critical variables:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://muster.yourdomain.com

# Database
DB_CONNECTION=pgsql
# ... set database credentials

# Redis (Crucial for Horizon and App state)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue and Broadcasting
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb

# Storage (MUST be cloud storage for production to persist avatars/uploads)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...

# Email (Required for invitations and password resets)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
```

## 3. Deployment Steps

```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# 2. Build frontend assets
npm run build

# 3. Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Run migrations
php artisan migrate --force

# 5. Restart queue workers (Horizon)
php artisan horizon:terminate

# 6. Restart Reverb (if running via supervisor)
# supervisorctl restart reverb:*
```

## 4. Supervisor Configuration

You must configure Supervisor to keep Horizon running. Create `/etc/supervisor/conf.d/horizon.conf`:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /path/to/muster/artisan horizon
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/path/to/muster/storage/logs/horizon.log
stopwaitsecs=3600
```

## 5. Cron Configuration

The task scheduler must run every minute to calculate streaks, issue badges, and record Horizon metrics. Add to crontab (`crontab -e`):

```bash
* * * * * cd /path/to/muster && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Initial Setup

For a brand new environment, after migrating, an initial user must register. Then promote them to platform admin via Tinker:

```bash
php artisan tinker
> User::first()->update(['is_platform_admin' => true]);
```
They can then access `/system/setup` to configure the first organization and unit.
