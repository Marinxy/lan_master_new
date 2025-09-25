# 🎮 LAN Game List - Complete LAN Party Planning System

A comprehensive, database-driven LAN game tracking and planning system with 521 games, advanced search capabilities, and user authentication. Perfect for organizing LAN parties and managing game libraries.

![LAN Game List](https://img.shields.io/badge/Games-521-blue) 
![LAN Game List](https://img.shields.io/badge/Genres-25-green)
![LAN Game List](https://img.shields.io/badge/Release-1994--2018-orange)
![LAN Game List](https://img.shields.io/badge/License-MIT-blue)

## 🚀 Features

### 🎯 **Core Functionality**
- **521 Games Database** - Comprehensive collection of LAN-compatible games
- **Advanced Search & Filtering** - Find games by genre, player count, release year
- **User Authentication** - Complete signup/login system with secure sessions
- **Game Details** - Individual game pages with specifications
- **Responsive Design** - Works on desktop and mobile devices

### 🔍 **Search & Filter Capabilities**
- **Title/Genre Search** - Search across all game titles and genres
- **Player Count Filtering** - Find games that support specific player counts
- **Release Year Range** - Filter by game release periods
- **LAN Capabilities** - Online/offline LAN support filtering
- **Price Filtering** - Free vs paid games
- **Advanced Sorting** - Sort by title, player limit, genre, or release year

### 🔐 **User Management**
- **Secure Registration** - Email and password-based user accounts
- **Session Management** - 24-hour sessions with "Remember Me" option
- **Protected Content** - User profile pages with authentication
- **Password Security** - Modern password hashing (PHP 8.3+)
- **Profile Management** - User account information and settings

## 📊 Database Statistics

### **Game Library Overview**
- **Total Games**: 521
- **Genre Distribution**:
  - FPS: 179 games (34%)
  - Strategy: 141 games (27%)
  - Action: 59 games (11%)
  - Racing: 47 games (9%)
  - RPG: 25 games (5%)
- **Release Years**: 1994 - 2018 (24 years of gaming history)
- **Multiplayer Support**: Games supporting 1-999 players
- **LAN Compatibility**: Both online and offline LAN options

### **Popular Games Included**
- Counter-Strike: Global Offensive
- Team Fortress 2
- Left 4 Dead 2
- Borderlands 2
- Doom (2016)
- Minecraft
- ARK: Survival Evolved
- Tom Clancy's Rainbow Six Siege
- And 513+ more!

## 🛠 Installation & Setup

### **Prerequisites**
- PHP 8.0 or higher with SQLite support
- Web server (Apache, Nginx, or built-in PHP server)
- Modern web browser

### **Quick Start (Local Development)**
```bash
# Clone or download the repository
cd lan_master_new

# Start PHP built-in server
php -S localhost:8081

# Open in browser
http://localhost:8081/
```

### **Production Deployment**
```bash
# For Apache/Nginx servers
# Copy all files to your web server's document root

# Database will be auto-created on first access
# No manual database setup required
```

## 📁 File Structure

```
lan_master_new/
├── index.php                    # Main game list and search page
├── game.php                     # Individual game detail pages
├── signup.php                   # User registration
├── login.php                    # User login
├── profile.php                  # User profile (authenticated)
├── logout.php                   # User logout
├── user_auth.php                # Authentication system
├── functions.php                # Database and utility functions
├── csv_import_system.php        # CSV import functionality
├── import_csv.php              # Legacy CSV importer
├── database.php                # Database initialization
├── lan_games_list.csv          # Source game data (525 games)
├── games.db                    # SQLite database (auto-created)
├── includes/
│   └── style.css              # Website styling
├── games/                      # Original HTML game files (139 files)
└── README.md                   # This documentation
```

## 🎯 Usage Guide

### **For Anonymous Users**
1. **Browse Games**: Visit the main page to see all 521 games
2. **Search & Filter**: Use the search form to find specific games
3. **View Details**: Click on any game to see detailed information

### **For Registered Users**
1. **Sign Up**: Create account at `/signup.php`
2. **Login**: Access your account at `/login.php`
3. **Profile**: View and manage your profile at `/profile.php`
4. **Logout**: Securely logout via the header menu

### **Search Examples**
```bash
# Find FPS games
http://localhost:8081/?search=FPS

# Find games for 8+ players from 2015+
http://localhost:8081/?p_min=8&r_min=2015

# Find strategy games with LAN support
http://localhost:8081/?search=Strategy&offline=yes

# Sort by newest games
http://localhost:8081/?s1=r_year&so1=DESC
```

## 🔐 Authentication System

### **User Registration**
- **Secure Signup**: Email and password-based registration
- **Password Requirements**: Minimum 6 characters
- **Email Validation**: Proper email format required
- **Duplicate Prevention**: Username and email uniqueness

### **Session Management**
- **24-hour Sessions**: Automatic session expiration
- **Remember Me**: 30-day persistent login option
- **Secure Logout**: Complete session cleanup
- **Protected Routes**: Profile pages require authentication

### **Security Features**
- **Password Hashing**: Modern PHP password_hash() function
- **SQL Injection Protection**: Prepared statements throughout
- **Session Security**: Database-stored session tokens
- **Input Validation**: All user inputs sanitized

## 🗄️ Database Schema

### **Games Table**
```sql
CREATE TABLE games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    p_limit INTEGER NOT NULL,
    p_samepc INTEGER NOT NULL,
    genre TEXT NOT NULL,
    subgenre TEXT,
    r_year INTEGER NOT NULL,
    online BOOLEAN NOT NULL,
    offline BOOLEAN NOT NULL,
    price TEXT,
    price_url TEXT,
    image_url TEXT,
    system_requirements TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### **Users Table**
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1,
    last_login DATETIME
);
```

### **User Sessions Table**
```sql
CREATE TABLE user_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    session_token TEXT UNIQUE NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 📈 System Capabilities

### **Performance**
- **Instant Loading**: 521 games load in under 1 second
- **Optimized Queries**: Database indexing for fast searches
- **Responsive Design**: Works on all screen sizes
- **Lightweight**: Minimal dependencies, fast execution

### **Scalability**
- **Database Abstraction**: Easy migration between SQLite/PostgreSQL
- **CSV Import System**: Add new games from spreadsheets
- **Modular Design**: Easy to extend with new features
- **Batch Processing**: Handle large game libraries efficiently

### **Data Management**
- **CSV Import**: Import games from spreadsheet files
- **Duplicate Handling**: Automatic duplicate detection and merging
- **Data Validation**: Comprehensive input validation
- **Export Ready**: Easy data export for backups

## 🚀 Deployment Options

### **Local Development**
```bash
# Use built-in PHP server
php -S localhost:8081

# Or with Apache/Nginx
# Copy files to document root
# Database auto-creates on first access
```

### **Docker Deployment**
```dockerfile
FROM php:8.1-apache
COPY . /var/www/html/
RUN docker-php-ext-install pdo_sqlite
EXPOSE 80
```

### **Production Server**
- **Apache/Nginx**: Standard PHP setup
- **Database**: SQLite (file-based, no server required)
- **Permissions**: Web server needs write access for database
- **HTTPS**: Recommended for production use

## 🔄 Migration & Updates

### **Adding New Games**
```bash
# Import from CSV file
php csv_import_system.php new_games.csv

# Import from individual HTML files
php import_all_games.php
```

### **Database Migration (SQLite to PostgreSQL)**
```bash
# Generate migration SQL
php migrate_to_postgresql.php

# Update database configuration in functions.php
$use_postgresql = true;
$pg_host = 'your_postgresql_host';
$pg_port = '5432';
$pg_dbname = 'langamelist';
$pg_user = 'your_username';
$pg_password = 'your_password';
```

### **Unraid Deployment**
1. Create PostgreSQL container
2. Run migration script
3. Update configuration
4. Deploy web files to container

## 🎯 Perfect for LAN Party Planning

### **Use Cases**
- **Large LAN Parties**: Find games for 8+ players
- **Retro Gaming Nights**: Games from specific eras
- **Budget Gaming**: Filter for free games
- **Game Discovery**: Search by genre and capabilities

### **Example Queries**
```bash
# 8+ player games from 2015+
http://localhost:8081/?p_min=8&r_min=2015

# Classic FPS games for nostalgia
http://localhost:8081/?search=FPS&r_max=2010

# Free strategy games with LAN
http://localhost:8081/?search=Strategy&free=yes&offline=yes

# Newest multiplayer games
http://localhost:8081/?s1=r_year&so1=DESC&p_min=4
```

## 📋 Quick Commands

```bash
# Start development server
php -S localhost:8081

# Import new CSV file
php csv_import_system.php games.csv

# Check database stats
php -r "require_once 'functions.php'; echo getGameCount() . ' games in database';"

# Generate PostgreSQL migration
php migrate_to_postgresql.php

# Test search functionality
curl -s "http://localhost:8081/?search=FPS" | grep -c "game.php"
```

## 🤝 Contributing

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature-name`
3. **Make your changes**
4. **Test thoroughly**
5. **Submit a pull request**

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Original LAN Game List concept and data
- Open source community for PHP and SQLite
- All contributors and testers

## 📞 Support

For support, please create an issue in the GitHub repository or contact the maintainers.

---

**🎮 Happy LAN Gaming!** - Ready to plan your next LAN party with 521 games at your fingertips!
