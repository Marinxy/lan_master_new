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

### 🛠️ **Inline Admin System**
- **Inline Editing** - Edit games directly on the main page without leaving
- **Dropdown Forms** - Collapsible edit forms for each game
- **One-Click Access** - Edit buttons appear next to each game for admins
- **Real-time Updates** - Changes saved immediately without page refresh
- **Streamlined Workflow** - No need for separate admin pages
- **IGDB Integration** - Scan and import game data from IGDB database

### 🔐 **User Management**
- **Secure Registration** - Email and password-based user accounts
- **Session Management** - 24-hour sessions with "Remember Me" option
- **Protected Content** - User profile pages with authentication
- **Password Security** - Modern password hashing (PHP 8.3+)
- **Profile Management** - User account information and settings

### 🗳️ **Game Voting System**
- **Thumbs Up Voting** - Users can vote for their favorite games
- **Vote Toggling** - Click to vote, click again to remove vote
- **Real-time Updates** - Vote counts update instantly via AJAX
- **Vote Statistics** - Track total votes, games with votes, and most popular games
- **User-specific Voting** - Each user can vote once per game
- **Database Integration** - Votes stored in dedicated `game_votes` table
- **Admin Analytics** - Voting statistics and insights for administrators

### 🛠️ **Admin Features**
- **Inline Game Management** - Edit and delete games directly on the main page
- **Comprehensive Editing** - Modify all game properties including title, genre, player limits
- **One-Click Editing** - Edit buttons appear next to each game for admins
- **Dropdown Forms** - Collapsible edit interfaces that don't clutter the main view
- **IGDB Database Integration** - Scan and import game data from IGDB
- **Auto-Population** - Automatically fill forms with game information
- **Automatic Thumbnail Download** - IGDB cover images are automatically downloaded and saved locally
- **Local Image Management** - Images stored in `img/` folder with fallback system
- **CORS-Free Images** - No more external image loading issues or blocked content
- **Security Controls** - Admin-only access with proper authentication
- **Admin Access** - First registered user automatically becomes admin

### 🖼️ **Image Management System**
- **Automatic IGDB Downloads** - When selecting games from IGDB scan, thumbnails are automatically downloaded
- **Local Storage** - Images saved to `img/` folder as `{game_id}.jpg/png/jpeg`
- **Smart Fallback System** - Tries remote URL first, then local image, then placeholder
- **CORS Problem Solved** - No more blocked external images or loading issues
- **Multiple Format Support** - Supports JPG, PNG, JPEG, and WebP formats
- **Database Integration** - `image_url` field cleared when local image is saved
- **Error Handling** - Graceful fallback to remote URLs if download fails
- **Visual Feedback** - Toast notifications show download success/failure status

## 📊 Database Statistics

### **Game Library Overview**
- **Total Games**: 520
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
├── index.php                    # Main game list with inline admin editing
├── game.php                     # Individual game detail pages
├── signup.php                   # User registration
├── login.php                    # User login
├── profile.php                  # User profile (authenticated)
├── logout.php                   # User logout
├── ajax_igdb.php               # IGDB API AJAX endpoint
├── igdb_api.php                # IGDB API integration class
├── download_image.php          # IGDB thumbnail download handler
├── game_voting.php             # Game voting system and AJAX handlers
├── setup_game_voting.php       # Voting system database setup
├── user_auth.php                # Authentication system & admin functions
├── functions.php                # Database and utility functions
├── csv_import_system.php        # CSV import functionality
├── import_csv.php              # Legacy CSV importer
├── database.php                # Database initialization
├── lan_games_list.csv          # Source game data (525 games)
├── games.db                    # SQLite database (auto-created)
├── cache/                      # Caching system (auto-created)
│   ├── db/                     # Database query cache
│   └── igdb/                   # IGDB API response cache
├── includes/
│   └── style.css              # Website styling
├── games/                      # Original HTML game files (139 files)
├── docs/
│   └── igdb_scan.md           # IGDB integration specification
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
3. **Vote for Games**: Click the thumbs up button next to any game to vote
4. **Toggle Votes**: Click again to remove your vote
5. **View Vote Counts**: See real-time vote counts for all games
6. **Profile**: View and manage your profile at `/profile.php`
7. **Logout**: Securely logout via the header menu

### **For Administrators** (First User Only)
1. **Register**: Create the first account at `/signup.php` (automatically becomes admin)
2. **Login**: Use `/login.php` to authenticate
3. **Inline Editing**: Edit buttons appear next to each game on the main page
4. **Edit Games**: Click "Edit" button next to any game to open the inline form
5. **IGDB Scanning**: Click "Scan IGDB" to search for game data and auto-populate
6. **Select Results**: Choose from IGDB search results to import game details
7. **Automatic Thumbnails**: Game thumbnails are automatically downloaded and saved locally
8. **Visual Feedback**: Toast notifications confirm successful image downloads
9. **Modify Details**: Update all game properties in the collapsible form
10. **Delete Games**: Use "Delete" button with confirmation for safe removal
11. **Stay on Main Page**: All editing happens without leaving the game list

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

### **Game Votes Table**
```sql
CREATE TABLE game_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    game_id INTEGER NOT NULL,
    vote_type ENUM('thumbs_up') DEFAULT 'thumbs_up',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_game_vote (user_id, game_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_game_votes_game_id (game_id),
    INDEX idx_game_votes_user_id (user_id),
    INDEX idx_game_votes_created_at (created_at)
);
```

**Note**: The `games` table also includes a `vote_count` column that is automatically updated when votes are added or removed.

## 🔧 Recent Fixes & Improvements

### **Form Submission Fix (Latest)**
- **Issue Resolved**: Fixed interference between voting system and admin form submissions
- **Root Cause**: POST handler in `game_voting.php` was intercepting all form submissions
- **Solution**: Modified POST handler to only process voting actions (`action=vote`)
- **Impact**: Admin game editing forms now work correctly without JSON response errors
- **Technical Details**: Prevented `game_voting.php` from setting JSON headers for non-voting requests

### **Voting System Enhancements**
- **Real-time AJAX Voting**: Instant vote updates without page refresh
- **Vote Toggle Functionality**: Users can add/remove votes with single click
- **Database Optimization**: Efficient vote counting with automatic `vote_count` updates
- **User Experience**: Visual feedback for voting actions
- **Security**: Proper authentication checks for voting operations

### **Code Quality Improvements**
- **Separation of Concerns**: Voting logic isolated from main application logic
- **Error Handling**: Comprehensive error handling for voting operations
- **Performance**: Optimized database queries for vote counting
- **Maintainability**: Clean, modular code structure for voting system

## 🚀 Caching System

### **Overview**
The LAN Game List implements a comprehensive multi-layer caching system for optimal performance:

### **Database Query Caching**
- **Cache Location**: `cache/db/` directory
- **Cache Duration**: 5 minutes (300 seconds)
- **Performance Gains**:
  - `getGameCount()`: 2.9x faster execution
  - `getGames()`: 1.8x faster execution
- **Cached Functions**:
  - Game list queries with filters and sorting
  - Game count queries
  - Individual game lookups

### **IGDB API Caching**
- **Cache Location**: `cache/igdb/` directory
- **Cache Duration**: 24 hours (86400 seconds)
- **Cached Operations**:
  - Game search results from IGDB
  - Individual game details from IGDB
  - Reduces external API calls and improves response times

### **Cache Management**
```php
// Automatic cache invalidation on data changes
// Cache is cleared when games are added, updated, or deleted

// Manual cache management (admin functions)
$igdbApi = new IGDBApi();
$igdbApi->clearCache();           // Clear IGDB cache
$igdbApi->clearExpiredCache();    // Clear only expired entries
$stats = $igdbApi->getCacheStats(); // Get cache statistics

$dbCache = new SimpleCache('cache/db');
$dbCache->clear();                // Clear database cache
```

### **Cache Structure**
```
cache/
├── db/                          # Database query cache
│   ├── [hash].cache            # Cached query results
│   └── ...
└── igdb/                       # IGDB API cache
    ├── [hash].json             # Cached API responses
    └── ...
```

### **Technical Implementation**
- **File-based Storage**: JSON format for easy debugging
- **Hash-based Keys**: MD5 hashes of query parameters
- **Timestamp Validation**: Automatic expiry checking
- **Atomic Operations**: Safe concurrent access
- **Memory Efficient**: No in-memory cache overhead

## 📈 System Capabilities

### **Performance & Caching**
- **Instant Loading**: 521 games load in under 1 second
- **Advanced Caching System**: Multi-layer caching for optimal performance
  - **Database Query Caching**: 2.9x faster game count queries, 1.8x faster game list queries
  - **IGDB API Caching**: Cached external API responses to reduce latency
  - **Automatic Cache Management**: Smart cache invalidation on data changes
  - **File-based Storage**: Efficient cache storage in `cache/` directory
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
