# 🚀 Installation Guide - LAN Game List

Complete installation guide for setting up the LAN Game List system locally and for production deployment.

## 📋 Requirements

### **System Requirements**
- **PHP**: 8.0 or higher
- **Web Server**: Apache 2.4+ or Nginx
- **Database**: SQLite (included) or PostgreSQL (production)
- **PHP Extensions**: 
  - `pdo_sqlite` (for SQLite)
  - `pdo_pgsql` (for PostgreSQL)
  - `curl` (for IGDB API)
  - `json`
  - `session`

### **Development Environment**
- **PHP CLI**: For running migrations and scripts
- **Composer**: Not required (no external dependencies)
- **Git**: For version control

## 🔧 Local Development Setup

### **1. Clone Repository**
```bash
git clone https://github.com/Marinxy/lan_master_new.git
cd lan_master_new
```

### **2. Start PHP Development Server**
```bash
php -S localhost:8081
```

### **3. Initial Setup**
1. **Database**: SQLite database (`games.db`) will be created automatically
2. **First User**: Register at `/signup.php` - becomes admin automatically
3. **Game Data**: 520+ games already imported from CSV

### **4. Access the Application**
- **Main Site**: http://localhost:8081/
- **User Registration**: http://localhost:8081/signup.php
- **User Login**: http://localhost:8081/login.php

## 🏭 Production Deployment

### **1. Web Server Setup**

#### **Apache Configuration**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/lan-game-list
    
    <Directory /var/www/lan-game-list>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Enable PHP
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

#### **Nginx Configuration**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/lan-game-list;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security
    location ~ /\.ht {
        deny all;
    }
    
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
}
```

### **2. Database Migration to PostgreSQL**

#### **Install PostgreSQL**
```bash
# Ubuntu/Debian
sudo apt install postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql
CREATE DATABASE langamelist;
CREATE USER lanuser WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE langamelist TO lanuser;
\q
```

#### **Update Database Configuration**
Edit `database.php`:
```php
$use_postgresql = true; // Change from false to true
$host = 'localhost';
$dbname = 'langamelist';
$user = 'lanuser';
$password = 'your_secure_password';
```

#### **Run Migration**
```bash
php migrate_to_postgresql.php
```

### **3. File Permissions**
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/lan-game-list

# Set file permissions
sudo find /var/www/lan-game-list -type f -exec chmod 644 {} \;
sudo find /var/www/lan-game-list -type d -exec chmod 755 {} \;

# Make database writable (SQLite only)
sudo chmod 664 /var/www/lan-game-list/games.db
sudo chmod 775 /var/www/lan-game-list/
```

### **4. Environment Configuration**

#### **Production PHP Settings**
Create `.htaccess` for security:
```apache
# Disable directory browsing
Options -Indexes

# Protect sensitive files
<Files "*.db">
    Require all denied
</Files>

<Files "*.md">
    Require all denied
</Files>

<Files "*.csv">
    Require all denied
</Files>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

## 🐳 Docker Deployment

### **Dockerfile**
```dockerfile
FROM php:8.1-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
```

### **Docker Compose**
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./games.db:/var/www/html/games.db
    environment:
      - PHP_DISPLAY_ERRORS=Off
      - PHP_LOG_ERRORS=On
    
  postgres:
    image: postgres:14
    environment:
      POSTGRES_DB: langamelist
      POSTGRES_USER: lanuser
      POSTGRES_PASSWORD: secure_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

volumes:
  postgres_data:
```

## 🔐 Security Considerations

### **1. Database Security**
- Use PostgreSQL in production
- Strong database passwords
- Restrict database access
- Regular backups

### **2. File Security**
- Protect sensitive files with `.htaccess`
- Set correct file permissions
- Disable directory browsing
- Regular security updates

### **3. IGDB API Security**
- Keep Client Secret secure
- Use environment variables in production
- Rotate credentials regularly
- Monitor API usage

### **4. User Security**
- Strong password requirements
- Session security
- CSRF protection
- Input validation

## 📊 Performance Optimization

### **1. Database Optimization**
- Add database indexes (already included)
- Optimize queries
- Consider caching for large datasets
- Regular database maintenance

### **2. Web Server Optimization**
- Enable gzip compression
- Set proper cache headers
- Optimize images
- Use CDN for static assets

### **3. PHP Optimization**
- Enable OPcache
- Tune PHP settings
- Use latest PHP version
- Monitor memory usage

## 🔧 Maintenance

### **1. Regular Updates**
- Update PHP regularly
- Security patches
- Database maintenance
- Log rotation

### **2. Monitoring**
- Error logs
- Access logs
- Database performance
- IGDB API usage

### **3. Backups**
- Database backups
- File system backups
- Configuration backups
- Test restore procedures

## 🆘 Troubleshooting

### **Common Issues**

#### **Database Connection Errors**
```bash
# Check file permissions
ls -la games.db

# Check PHP extensions
php -m | grep pdo
```

#### **IGDB API Issues**
```bash
# Test API connection
php -r "require 'igdb_api.php'; var_dump(searchIGDBGames('mario', 1));"
```

#### **Permission Errors**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/lan-game-list
sudo chmod 664 games.db
```

#### **PHP Errors**
```bash
# Check PHP error log
tail -f /var/log/apache2/error.log

# Test PHP syntax
php -l index.php
```

## 📞 Support

For issues and support:
1. Check error logs
2. Verify requirements
3. Test with minimal configuration
4. Create GitHub issue with details

---

**🎮 Ready to host amazing LAN parties with your game database!**
