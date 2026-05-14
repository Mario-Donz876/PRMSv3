# Linux Deployment Guide — PRMS v3

This guide covers everything needed to deploy PRMS v3 on a fresh **Ubuntu 22.04 LTS** or **Debian 12** VPS.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Provisioning](#server-provisioning)
3. [Database Setup](#database-setup)
4. [Application Setup](#application-setup)
5. [Web Server Configuration](#web-server-configuration)
6. [SSL / HTTPS](#ssl--https)
7. [Cron Jobs](#cron-jobs)
8. [Deployment Updates](#deployment-updates)
9. [Backup & Recovery](#backup--recovery)
10. [Security Checklist](#security-checklist)
11. [Troubleshooting](#troubleshooting)

---

## Prerequisites

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| OS          | Ubuntu 20.04 | Ubuntu 22.04 LTS |
| RAM         | 1 GB    | 2 GB        |
| Disk        | 10 GB   | 50 GB       |
| PHP         | 8.1     | 8.2         |
| MySQL / MariaDB | 10.5 | MariaDB 10.11 |
| Web server  | Apache 2.4 **or** Nginx 1.22+ | Apache 2.4 |
| Composer    | 2.x     | latest      |

---

## Server Provisioning

The `deploy/install.sh` script installs all required packages in one step.

```bash
# Clone the repository
git clone https://github.com/dsitservicesja-lab/PRMSv3.git /var/www/prms/public

# Run the installer (as root)
cd /var/www/prms/public
sudo bash deploy/install.sh
```

You can customise behaviour with environment variables before running:

```bash
# Use Nginx instead of Apache
sudo WEB_SERVER=nginx PHP_VER=8.2 bash deploy/install.sh
```

---

## Database Setup

### Create the database and user

```sql
-- Connect as root
CREATE DATABASE prms_ims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'prms_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON prms_ims.* TO 'prms_user'@'localhost';
FLUSH PRIVILEGES;
```

Or use the helper flag in `deploy.sh`:

```bash
sudo bash deploy/deploy.sh --init-db
```

### Import the base schema

```bash
mysql -u prms_user -p prms_ims < prmsv2.sql
```

### Run migrations (in order)

```bash
sudo bash deploy/deploy.sh --run-migrations
```

> **Note:** The deploy script tracks applied migrations in `.applied_migrations` so it is safe to run multiple times.

---

## Application Setup

### 1. Install PHP dependencies

```bash
cd /var/www/prms/public
composer install --no-dev --optimize-autoloader
```

### 2. Create the environment file

```bash
cp .env.example .env
nano .env          # fill in DB credentials, mail settings, APP_URL
chmod 640 .env
chown www-data:www-data .env
```

Key values to set:

| Variable | Example |
|----------|---------|
| `APP_URL` | `https://prms.yourdomain.com` |
| `APP_ENV` | `prod` |
| `DB_HOST` | `127.0.0.1` |
| `DB_NAME` | `prms_ims` |
| `DB_USER` | `prms_user` |
| `DB_PASS` | *(strong password)* |
| `MAIL_HOST` | `smtp.gmail.com` |
| `MAIL_PORT` | `587` |
| `MAIL_USER` | your Gmail address |
| `MAIL_PASS` | Gmail App Password |

### 3. Set permissions

```bash
chown -R www-data:www-data /var/www/prms/public
find /var/www/prms/public -type f -exec chmod 644 {} \;
find /var/www/prms/public -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/prms/public/uploads
```

---

## Web Server Configuration

### Apache

```bash
sudo cp deploy/apache.conf /etc/apache2/sites-available/prms.conf

# Edit the ServerName
sudo nano /etc/apache2/sites-available/prms.conf

sudo a2ensite prms
sudo a2enmod rewrite headers deflate expires ssl proxy_fcgi setenvif
sudo a2enconf php8.2-fpm
sudo systemctl reload apache2
```

### Nginx

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/prms

# Edit the server_name
sudo nano /etc/nginx/sites-available/prms

sudo ln -s /etc/nginx/sites-available/prms /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## SSL / HTTPS

Use [Certbot](https://certbot.eff.org/) with Let's Encrypt (free):

```bash
# Apache
sudo certbot --apache -d prms.yourdomain.com

# Nginx
sudo certbot --nginx -d prms.yourdomain.com
```

Certbot will automatically update your virtual host with the certificate paths.

Auto-renewal is set up by the Certbot package; verify with:

```bash
sudo systemctl status certbot.timer
```

---

## Cron Jobs

Edit the cron table for `www-data`:

```bash
sudo crontab -u www-data -e
```

Add:

```cron
# Send pending email notifications (every 5 minutes)
*/5 * * * *  php /var/www/prms/public/cron/send_notifications.php >> /var/log/prms_cron.log 2>&1
```

---

## Deployment Updates

To pull the latest code on the server and apply new migrations:

```bash
cd /var/www/prms/public
sudo bash deploy/update.sh --run-migrations
```

The update script will:

- fetch the latest changes from `origin`
- fail fast if a merge, rebase, cherry-pick, or revert is already in progress
- clear leftover unmerged index entries from an earlier conflicted stash restore
- stash tracked changes and untracked files before switching branches or pulling
- restore the stash after the pull and stop immediately if conflicts remain
- run `composer install`, set permissions, optionally run migrations, and reload PHP-FPM

To update a specific branch:

```bash
cd /var/www/prms/public
sudo bash deploy/update.sh --branch application --run-migrations
```

---

## Backup & Recovery

### Database backup (daily via cron)

```bash
sudo crontab -e
# Add:
0 2 * * *  mysqldump -u prms_user -p'YOUR_PASS' prms_ims | gzip > /var/backups/prms/prms_$(date +\%F).sql.gz
```

### Restore

```bash
gunzip -c /var/backups/prms/prms_2026-05-01.sql.gz | mysql -u prms_user -p prms_ims
```

---

## Security Checklist

- [ ] `.env` file is **not** committed to git (`.gitignore` already covers this)
- [ ] `.env` permissions are `640` owned by `www-data`
- [ ] `APP_ENV=prod` — disables PHP error output to browser
- [ ] SSL certificate installed and HTTP redirects to HTTPS
- [ ] Database user has only `GRANT ALL` on the `prms_ims` database (not `*.* `)
- [ ] `uploads/` directory is not web-accessible for PHP execution:
  ```apache
  # Apache: already in the virtual host
  <Directory /var/www/prms/public/uploads>
      php_flag engine off
  </Directory>
  ```
- [ ] Regular backups tested and stored off-server
- [ ] MariaDB `bind-address = 127.0.0.1` (no external DB access)
- [ ] Firewall: only ports 22, 80, 443 open

---

## Troubleshooting

### "Database connection failed"
- Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` in `.env`
- Test connectivity: `mysql -u prms_user -p prms_ims -e "SELECT 1"`

### Blank white page / HTTP 500
- Set `APP_ENV=dev` temporarily to see PHP errors
- Check `/var/log/apache2/prms_error.log` or `/var/log/nginx/prms_error.log`
- Check `/var/log/php8.2-fpm.log`

### File uploads fail
- Verify `uploads/` is writable by `www-data`
- Check `upload_max_filesize` / `post_max_size` in `/etc/php/8.2/fpm/conf.d/99-prms.ini`

### Emails not sending
- Confirm `MAIL_*` values in `.env`
- For Gmail, an **App Password** is required (not your regular password)
- Enable less-secure app access **or** use a dedicated SMTP relay
