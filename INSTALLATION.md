# Smart Resume Analyzer - Installation Guide

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Quick Start](#quick-start)
3. [Manual Installation](#manual-installation)
4. [Configuration](#configuration)
5. [Troubleshooting](#troubleshooting)
6. [Post-Installation](#post-installation)

---

## System Requirements

### Minimum Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Memory**: 512MB RAM minimum (1GB recommended)
- **Disk Space**: 500MB free space
- **Composer**: Latest version

### Recommended Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0.32 or higher
- **RAM**: 2GB or more
- **SSD**: Recommended for better performance
- **Modern Browser**: Chrome, Firefox, Safari, or Edge

### Optional Dependencies
- pdftotext (for enhanced PDF processing): `sudo apt-get install poppler-utils`
- Redis (for caching): For improved performance in production
- ImageMagick: For image processing

---

## Quick Start

### Option 1: Automated Installation (Recommended)

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/smart-resume-analyzer.git
cd smart-resume-analyzer

# 2. Make installation script executable
chmod +x install.sh

# 3. Run installation script
./install.sh

# 4. Start the development server
php -S localhost:8000

# 5. Open browser
# Visit http://localhost:8000
```

### Option 2: Docker Installation

```bash
# Build and run with Docker
docker-compose up -d

# Access the application
# http://localhost:8000
```

---

## Manual Installation

### Step 1: Prerequisites Check

```bash
# Check PHP version
php -v

# Check MySQL version
mysql --version

# Check Composer
composer --version
```

### Step 2: Clone Repository

```bash
git clone https://github.com/yourusername/smart-resume-analyzer.git
cd smart-resume-analyzer
```

### Step 3: Install PHP Dependencies

```bash
composer install
```

### Step 4: Create Directories

```bash
mkdir -p uploads logs temp cache
chmod -R 755 uploads logs temp cache
```

### Step 5: Setup Environment File

```bash
# Copy example configuration
cp .env.example .env

# Edit with your settings
nano .env
# or
vi .env
```

### Step 6: Create Database

```bash
# Create database
mysql -u root -p
CREATE DATABASE resume_analyzer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Import schema
mysql -u root -p resume_analyzer < database/schema.sql

# (Optional) Import sample data
mysql -u root -p resume_analyzer < database/sample_data.sql
```

### Step 7: Configure Database Connection

Edit `.env` file with your database credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=resume_analyzer
```

### Step 8: Set File Permissions

```bash
# Make .env readable only by owner
chmod 600 .env

# Set directory permissions
chmod -R 755 uploads
chmod -R 755 logs
chmod -R 755 temp
```

---

## Configuration

### Essential Configuration

#### 1. Application Settings

```env
APP_NAME="Smart Resume Analyzer"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
```

#### 2. Database Configuration

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=password
DB_NAME=resume_analyzer
DB_CHARSET=utf8mb4
```

#### 3. Security Keys

```env
# Generate random keys for production
APP_KEY=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -base64 32)
CSRF_TOKEN_LENGTH=32
```

Generate keys:
```bash
# On Linux/Mac
openssl rand -base64 32

# In PHP
bin2hex(random_bytes(32))
```

#### 4. Session Settings

```env
SESSION_LIFETIME=120
SESSION_COOKIE_SECURE=false  # Set to true in production with HTTPS
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=Strict
```

#### 5. File Upload Settings

```env
MAX_RESUME_SIZE=5242880  # 5MB in bytes
UPLOAD_DIRECTORY=./uploads
ALLOWED_MIME_TYPES=application/pdf
```

### Optional Configuration

#### NLP API Setup

**Option A: HuggingFace**

```env
NLP_API_PROVIDER=huggingface
HUGGINGFACE_API_KEY=your_api_key_here
HUGGINGFACE_MODEL=bert-base-uncased
```

Get API key at: https://huggingface.co/settings/tokens

**Option B: OpenAI**

```env
NLP_API_PROVIDER=openai
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
```

Get API key at: https://platform.openai.com/api-keys

#### Email Configuration (SMTP)

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@resumeanalyzer.com
MAIL_FROM_NAME="Smart Resume Analyzer"
```

#### Logging Configuration

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_PATH=./logs
LOG_MAX_FILES=10
```

---

## Troubleshooting

### Database Connection Issues

**Error: "Connection refused"**

```bash
# Check if MySQL is running
sudo service mysql status

# Start MySQL
sudo service mysql start

# Check MySQL is listening
netstat -tulpn | grep 3306
```

**Error: "Access denied for user"**

```bash
# Verify credentials in .env
# Test connection
mysql -u root -p -h localhost

# Create fresh database user
mysql -u root -p
CREATE USER 'resume_analyzer'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON resume_analyzer.* TO 'resume_analyzer'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Permission Issues

```bash
# Fix upload directory permissions
chmod -R 755 uploads
sudo chown -R www-data:www-data uploads

# Fix logs directory permissions
chmod -R 755 logs
sudo chown -R www-data:www-data logs

# Fix .env permissions
chmod 600 .env
```

### PHP Extensions Missing

```bash
# Install missing extensions
# PHP-MySQLi (required)
sudo apt-get install php-mysqli

# PHP-JSON (required)
sudo apt-get install php-json

# PHP-Curl (for API calls)
sudo apt-get install php-curl

# Restart PHP
sudo systemctl restart php-fpm
```

### Composer Issues

```bash
# Clear Composer cache
composer clear-cache

# Update dependencies
composer update

# Install with optimized autoloader
composer install --optimize-autoloader --no-dev
```

### PDF Processing Issues

**pdftotext not found:**

```bash
# Install pdftotext
sudo apt-get install poppler-utils

# Verify installation
which pdftotext
```

**smalot/pdfparser not working:**

```bash
# Reinstall the package
composer require smalot/pdfparser:^2.0
```

---

## Post-Installation

### 1. Verify Installation

Check if application loads:
```bash
php -S localhost:8000
```

Visit: http://localhost:8000

### 2. Create Admin Account

```bash
# Through web interface
1. Register new account
2. Go to database and update user role
mysql -u root -p resume_analyzer
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

### 3. Test Key Features

- [ ] Registration works
- [ ] Login works
- [ ] Resume upload works
- [ ] Analysis runs successfully
- [ ] Results display correctly
- [ ] Admin dashboard accessible

### 4. Production Deployment

When deploying to production:

```env
# Security settings
APP_ENV=production
APP_DEBUG=false
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true

# Use stronger session settings
SESSION_LIFETIME=60
CSRF_TOKEN_LENGTH=64

# Configure proper logging
LOG_LEVEL=error
```

### 5. SSL/TLS Certificate

```bash
# Using Let's Encrypt (free)
sudo apt-get install certbot python3-certbot-apache
sudo certbot certonly --apache -d yourdomain.com
```

### 6. Nginx Configuration Example

```nginx
server {
    listen 443 ssl http2;
    server_name resumeanalyzer.com;

    ssl_certificate /etc/letsencrypt/live/resumeanalyzer.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/resumeanalyzer.com/privkey.pem;

    root /var/www/resume-analyzer;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 7. Apache Configuration Example

```apache
<VirtualHost *:443>
    ServerName resumeanalyzer.com
    DocumentRoot /var/www/resume-analyzer

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/resumeanalyzer.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/resumeanalyzer.com/privkey.pem

    <Directory /var/www/resume-analyzer>
        AllowOverride All
        Require all granted

        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ index.php [QSA,L]
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```

### 8. Database Backup

```bash
# Create backup
mysqldump -u root -p resume_analyzer > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore from backup
mysql -u root -p resume_analyzer < backup_20240101_000000.sql
```

---

## Support & Troubleshooting

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| White screen of death | Check error logs: `tail -f logs/application.log` |
| Database not found | Run: `mysql -u root -p < database/schema.sql` |
| Uploads not working | Check permissions: `chmod -R 755 uploads` |
| Session not persisting | Verify session.save_path in php.ini |
| Slow performance | Enable caching, optimize database indexes |

### Getting Help

- **Documentation**: See README.md
- **Issues**: GitHub Issues
- **Email**: support@resumeanalyzer.local
- **Forum**: Community discussions

---

## Next Steps

1. **Configure** your application settings
2. **Test** all key features
3. **Set up** email notifications (optional)
4. **Configure** NLP API (optional)
5. **Deploy** to production when ready

For detailed information, refer to README.md and API documentation.

---

**Installation Date**: [Current Date]
**Version**: 1.0.0
**Last Updated**: August 2024
