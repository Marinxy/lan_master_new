# 🔌 API Documentation - LAN Game List

Complete API documentation for the LAN Game List system, including IGDB integration and internal endpoints.

## 📋 Overview

The LAN Game List system provides several API endpoints for managing games and integrating with external services.

## 🎮 IGDB Integration API

### **Authentication**
The system uses OAuth2 Client Credentials flow with Twitch/IGDB:

```php
// Automatic token management
$igdb = new IGDBApi();
$results = $igdb->searchGames('game title', 10);
```

### **Search Games**
**Endpoint**: `POST /ajax_igdb.php`

**Request**:
```http
POST /ajax_igdb.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

action=search_igdb&query=mario&limit=10
```

**Response**:
```json
{
  "games": [
    {
      "id": 2707,
      "igdbId": 2707,
      "name": "Mario & Sonic at the Olympic Winter Games",
      "title": "Mario & Sonic at the Olympic Winter Games",
      "slug": "mario-and-sonic-at-the-olympic-winter-games",
      "summary": "Mario & Sonic at the Olympic Winter Games is a sports game...",
      "release_year": "2009",
      "first_release_date": 1255392000,
      "cover_url": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wvr.jpg",
      "image_url": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wvr.jpg",
      "genres": ["Sport"],
      "game_modes": ["Single player", "Multiplayer", "Split screen"],
      "genre": "Sport",
      "online": false,
      "offline": false,
      "rating": 68.9,
      "rating_count": 32
    }
  ]
}
```

**Error Response**:
```json
{
  "error": "Search query is required"
}
```

### **Get Game Details**
**Endpoint**: `POST /ajax_igdb.php`

**Request**:
```http
POST /ajax_igdb.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

action=get_igdb_details&id=2707
```

**Response**:
```json
{
  "title": "Mario & Sonic at the Olympic Winter Games",
  "slug": "mario-and-sonic-at-the-olympic-winter-games",
  "genre": "Sport",
  "subgenre": "",
  "release_year": "2009",
  "online": false,
  "offline": false,
  "image_url": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wvr.jpg",
  "summary": "Mario & Sonic at the Olympic Winter Games is a sports game...",
  "system_requirements": "Mario & Sonic at the Olympic Winter Games is a sports game..."
}
```

### **Download IGDB Image**
**Endpoint**: `POST /download_image.php`

**Description**: Downloads and saves IGDB cover images locally to the `img/` folder. Automatically called when selecting games from IGDB scan results.

**Request**:
```http
POST /download_image.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

game_id=119&image_url=https://images.igdb.com/igdb/image/upload/t_cover_big/co2xgk.jpg
```

**Parameters**:
- `game_id` (required) - The game ID to save the image for
- `image_url` (required) - The IGDB image URL to download

**Success Response**:
```json
{
  "success": true,
  "message": "Image downloaded and saved successfully",
  "local_path": "img/119.jpg",
  "filename": "119.jpg"
}
```

**Error Responses**:
```json
{
  "error": "Invalid game ID",
  "message": "Please provide a valid game ID"
}
```

```json
{
  "error": "Invalid image URL",
  "message": "Please provide a valid image URL"
}
```

```json
{
  "error": "Download failed",
  "message": "Failed to download image: HTTP 404"
}
```

**Features**:
- **Automatic Format Detection**: Supports JPG, PNG, JPEG, WebP formats
- **Database Integration**: Clears `image_url` field when local image is saved
- **Error Handling**: Graceful failure with detailed error messages
- **File Validation**: Checks image validity before saving
- **Overwrite Protection**: Safely overwrites existing images

## 🔧 Internal Game Management API

### **Game CRUD Operations**

#### **Search/Filter Games**
**Endpoint**: `GET /index.php`

**Parameters**:
- `search` - Search term for title/genre
- `p_min` - Minimum players
- `p_max` - Maximum players  
- `p_samepc_min` - Minimum same PC players
- `r_min` - Minimum release year
- `r_max` - Maximum release year
- `online` - Filter online games (1/0)
- `offline` - Filter offline games (1/0)
- `sort` - Sort field (title, p_limit, genre, r_year)
- `order` - Sort order (ASC/DESC)

**Example**:
```
GET /index.php?search=fps&p_min=4&online=1&sort=title&order=ASC
```

#### **Update Game** (Admin Only)
**Endpoint**: `POST /index.php`

**Request**:
```http
POST /index.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

action=update_game&game_id=123&title=Counter-Strike&slug=counter-strike&p_limit=16&p_samepc=1&genre=FPS&subgenre=Tactical&r_year=1999&online=1&offline=1&price=Free&price_url=https://store.steampowered.com&image_url=https://example.com/cover.jpg&system_requirements=Windows 10, 4GB RAM
```

**Response**: HTTP redirect with success/error message

#### **Delete Game** (Admin Only)
**Endpoint**: `POST /index.php`

**Request**:
```http
POST /index.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

action=delete_game&game_id=123
```

**Response**: HTTP redirect with success/error message

## 👥 User Management API

### **User Registration**
**Endpoint**: `POST /signup.php`

**Request**:
```http
POST /signup.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

username=johndoe&email=john@example.com&password=securepass&password_confirm=securepass
```

**Response**: HTTP redirect with success/error message

### **User Login**
**Endpoint**: `POST /login.php`

**Request**:
```http
POST /login.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

username=johndoe&password=securepass&remember_me=1
```

**Response**: HTTP redirect with success/error message + session cookies

### **User Logout**
**Endpoint**: `GET /logout.php`

**Response**: HTTP redirect to main page + cleared cookies

## 📊 Database Schema

### **Games Table**
```sql
CREATE TABLE games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL UNIQUE,
    slug TEXT NOT NULL UNIQUE,
    p_limit INTEGER,
    p_samepc INTEGER,
    genre TEXT,
    subgenre TEXT,
    r_year INTEGER,
    online BOOLEAN,
    offline BOOLEAN,
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
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### **User Sessions Table**
```sql
CREATE TABLE user_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    session_token TEXT NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🔐 Authentication & Authorization

### **Session Management**
- Sessions stored in database with tokens
- 24-hour expiry (configurable)
- "Remember me" extends to 30 days
- Secure session handling with IP/User-Agent validation

### **Admin Privileges**
- First registered user becomes admin automatically
- Admin can edit/delete any game
- Admin has access to IGDB scan functionality
- Future: Role-based permissions system

### **API Security**
- CSRF protection on forms
- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection with HTML escaping

## 📈 Rate Limiting & Performance

### **IGDB API Limits**
- 4 requests per second per IP
- Automatic token refresh
- Error handling for rate limits
- Caching recommendations for production

### **Database Performance**
- Indexed columns: title, genre, r_year, username, email
- Optimized queries with prepared statements
- Pagination support for large datasets
- SQLite for development, PostgreSQL for production

## 🛠️ Development Tools

### **PHP Functions**

#### **Game Functions**
```php
// Get all games with filters
$games = getGames($filters, $sort, $limit, $offset);

// Get game count
$count = getGameCount($filters);

// Get single game
$game = getGameById($id);
$game = getGameBySlug($slug);

// Update game
$success = updateGame($id, $title, $slug, ...);

// Delete game
$success = deleteGame($id);
```

#### **User Functions**
```php
// Register user
$result = registerUser($username, $email, $password);

// Login user
$result = loginUser($username, $password, $rememberMe);

// Check if logged in
$isLoggedIn = isLoggedIn();

// Get current user
$user = getCurrentUser();

// Check admin status
$isAdmin = isAdmin($userId);
```

#### **IGDB Functions**
```php
// Search IGDB
$results = searchIGDBGames($query, $limit);

// Get IGDB details
$details = getIGDBGameDetails($igdbId);

// Map IGDB data
$mapped = mapIGDBToGameData($igdbData);
```

## 📝 Error Handling

### **Error Codes**

#### **IGDB API Errors**
- `400` - Bad Request (invalid query)
- `401` - Unauthorized (invalid credentials)
- `429` - Rate Limited
- `500` - Server Error

#### **Application Errors**
- `403` - Access Denied (not admin)
- `404` - Not Found (game/user not found)
- `422` - Validation Error (invalid input)
- `500` - Database Error

### **Error Responses**
```json
{
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": "Additional details if available"
}
```

## 🧪 Testing

### **Manual Testing**
```bash
# Test IGDB search
curl -X POST -d "action=search_igdb&query=mario" http://localhost:8081/ajax_igdb.php

# Test game details
curl -X POST -d "action=get_igdb_details&id=2707" http://localhost:8081/ajax_igdb.php

# Test database functions
php -r "require 'functions.php'; var_dump(getGameCount());"
```

### **Database Testing**
```bash
# Test database connection
php -r "require 'database.php'; echo 'Database connected successfully';"

# Test user functions
php -r "require 'user_auth.php'; var_dump(registerUser('test', 'test@example.com', 'password'));"
```

## 📚 Integration Examples

### **JavaScript Integration**
```javascript
// Search IGDB
async function searchIGDB(query) {
    const response = await fetch('/ajax_igdb.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=search_igdb&query=${encodeURIComponent(query)}`
    });
    
    const data = await response.json();
    return data.games || [];
}

// Populate form with IGDB data
async function populateFromIGDB(igdbId) {
    const response = await fetch('/ajax_igdb.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_igdb_details&id=${igdbId}`
    });
    
    const data = await response.json();
    
    // Populate form fields
    document.querySelector('input[name="title"]').value = data.title || '';
    document.querySelector('input[name="genre"]').value = data.genre || '';
    // ... more fields
}
```

### **CSV Import**
```php
// Import games from CSV
require_once 'csv_import_system.php';

$results = importGamesFromCSV('lan_games_list.csv');
echo "Imported: {$results['imported']}, Skipped: {$results['skipped']}";
```

## 🔄 Migration & Backup

### **Database Migration**
```bash
# Migrate SQLite to PostgreSQL
php migrate_to_postgresql.php
```

### **Data Export**
```php
// Export games to CSV
$games = getGames();
$csv = fopen('export.csv', 'w');
fputcsv($csv, ['title', 'genre', 'p_limit', 'r_year', 'online', 'offline']);

foreach ($games as $game) {
    fputcsv($csv, [
        $game['title'],
        $game['genre'],
        $game['p_limit'],
        $game['r_year'],
        $game['online'] ? 1 : 0,
        $game['offline'] ? 1 : 0
    ]);
}

fclose($csv);
```

## 🚀 Performance & Caching

### **API Response Caching**
All API endpoints implement intelligent caching for optimal performance:

#### **IGDB API Caching**
- **Cache Duration**: 24 hours
- **Cache Location**: `cache/igdb/` directory
- **Cached Endpoints**:
  - `POST /ajax_igdb.php?action=search_igdb`
  - `POST /ajax_igdb.php?action=get_igdb_details`
- **Benefits**: Reduces external API calls, faster response times

#### **Database Query Caching**
- **Cache Duration**: 5 minutes
- **Cache Location**: `cache/db/` directory
- **Performance Improvements**:
  - Game list queries: 1.8x faster
  - Game count queries: 2.9x faster
- **Automatic Invalidation**: Cache cleared on data modifications

#### **Cache Headers**
```http
# Cached responses include performance indicators
X-Cache-Status: HIT|MISS
X-Cache-Age: 120
X-Performance-Gain: 2.9x
```

#### **Cache Management**
```php
// Clear all caches
$igdbApi = new IGDBApi();
$igdbApi->clearCache();

$dbCache = new SimpleCache('cache/db');
$dbCache->clear();

// Get cache statistics
$stats = $igdbApi->getCacheStats();
// Returns: ['files' => 5, 'total_size' => 210500]
```

### **Rate Limiting**
- **IGDB API**: Respects upstream rate limits
- **Internal APIs**: No rate limiting (cached responses)
- **Recommendation**: Implement client-side caching for heavy usage

---

**🎮 API ready for building amazing LAN party tools!**
