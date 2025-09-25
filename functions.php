<?php
// Database connection and utility functions
// Supports both SQLite (local) and PostgreSQL (production/Unraid)

// Simple caching system for database queries
class SimpleCache {
    private $cacheDir;
    private $cacheExpiry;
    
    public function __construct($cacheDir = 'cache/db', $cacheExpiry = 300) { // 5 minutes default
        $this->cacheDir = $cacheDir;
        $this->cacheExpiry = $cacheExpiry;
        $this->initCache();
    }
    
    private function initCache() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    private function getCacheKey($key) {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
    
    public function get($key) {
        $cacheFile = $this->getCacheKey($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = file_get_contents($cacheFile);
        $cached = json_decode($data, true);
        
        if (!$cached || !isset($cached['timestamp']) || !isset($cached['data'])) {
            return null;
        }
        
        // Check if cache has expired
        if (time() - $cached['timestamp'] > $this->cacheExpiry) {
            unlink($cacheFile);
            return null;
        }
        
        return $cached['data'];
    }
    
    public function set($key, $data) {
        $cacheFile = $this->getCacheKey($key);
        $cached = [
            'timestamp' => time(),
            'data' => $data
        ];
        
        file_put_contents($cacheFile, json_encode($cached));
    }
    
    public function clear() {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    public function clearExpired() {
        $files = glob($this->cacheDir . '/*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cached = json_decode($data, true);
            
            if (!$cached || !isset($cached['timestamp']) || 
                time() - $cached['timestamp'] > $this->cacheExpiry) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
}

// Global cache instance
$dbCache = new SimpleCache();

// Database configuration
$use_postgresql = false; // Set to true when migrating to Unraid
$pg_host = 'localhost';
$pg_port = '5432';
$pg_dbname = 'langamelist';
$pg_user = 'langamelist_user';
$pg_password = 'your_password_here';

try {
    if ($use_postgresql) {
        // PostgreSQL connection for production/Unraid
        $db = new PDO("pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname", $pg_user, $pg_password);
    } else {
        // SQLite connection for local development
        $db = new PDO('sqlite:games.db');
    }
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    // User-friendly error message
    $error_message = "Unable to connect to the database. ";
    if ($use_postgresql) {
        $error_message .= "Please check your PostgreSQL server configuration.";
    } else {
        $error_message .= "Please ensure the SQLite database file is accessible.";
    }
    
    die($error_message);
}

// Function to get all games with filtering and sorting
function getGames($filters = [], $sort = []) {
    global $db, $dbCache;
    
    // Create cache key based on filters and sort parameters
    $cacheKey = 'games_' . md5(serialize($filters) . serialize($sort));
    
    // Try to get cached result first
    $cachedResult = $dbCache->get($cacheKey);
    if ($cachedResult !== null) {
        return $cachedResult;
    }
    
    $sql = "SELECT * FROM games WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($filters['search'])) {
        if (strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false) {
            // PostgreSQL uses ILIKE for case-insensitive search
            $sql .= " AND (title ILIKE ? OR genre ILIKE ?)";
        } else {
            // SQLite uses LIKE for search
            $sql .= " AND (title LIKE ? OR genre LIKE ?)";
        }
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    if (!empty($filters['p_min'])) {
        $sql .= " AND p_limit >= ?";
        $params[] = $filters['p_min'];
    }
    
    if (!empty($filters['p_max'])) {
        $sql .= " AND p_limit <= ?";
        $params[] = $filters['p_max'];
    }
    
    if (!empty($filters['p_samepc_min'])) {
        $sql .= " AND p_samepc >= ?";
        $params[] = $filters['p_samepc_min'];
    }
    
    if (!empty($filters['r_min'])) {
        $sql .= " AND r_year >= ?";
        $params[] = $filters['r_min'];
    }
    
    if (!empty($filters['r_max'])) {
        $sql .= " AND r_year <= ?";
        $params[] = $filters['r_max'];
    }
    
    if (isset($filters['online']) && $filters['online'] === 'yes') {
        $sql .= " AND online = true";
    }
    
    if (isset($filters['offline']) && $filters['offline'] === 'yes') {
        $sql .= " AND offline = true";
    }
    
    if (isset($filters['free']) && $filters['free'] === 'yes') {
        if (strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false) {
            $sql .= " AND (price = 'Free' OR price IS NULL OR price = '')";
        } else {
            $sql .= " AND (price = 'Free' OR price IS NULL OR price = '')";
        }
    }
    
    if (isset($filters['standalone']) && $filters['standalone'] === 'yes') {
        if (strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false) {
            $sql .= " AND price IS NOT NULL AND price != '' AND price != 'Free'";
        } else {
            $sql .= " AND price IS NOT NULL AND price != '' AND price != 'Free'";
        }
    }
    
    // Apply sorting
    $sort_columns = [
        'title' => 'title',
        'p_limit' => 'p_limit',
        'p_samepc' => 'p_samepc',
        'genre' => 'genre',
        'r_year' => 'r_year'
    ];
    
    $order_by = [];
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($sort["s{$i}"]) && isset($sort_columns[$sort["s{$i}"]])) {
            $direction = !empty($sort["so{$i}"]) && strtoupper($sort["so{$i}"]) === 'ASC' ? 'ASC' : 'DESC';
            $order_by[] = $sort_columns[$sort["s{$i}"]] . ' ' . $direction;
        }
    }
    
    if (empty($order_by)) {
        $order_by[] = 'title ASC';
    }
    
    $sql .= " ORDER BY " . implode(', ', $order_by);
    
    // Add LIMIT for better performance (adjust as needed)
    $sql .= " LIMIT 1000";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache the successful result
        $dbCache->set($cacheKey, $result);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching games: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . json_encode($params));
        
        // Return empty array instead of crashing
        return [];
    }
}

// Function to get game count
function getGameCount() {
    global $db, $dbCache;
    
    // Create cache key for game count
    $cacheKey = 'game_count';
    
    // Try to get cached result first
    $cachedResult = $dbCache->get($cacheKey);
    if ($cachedResult !== null) {
        return $cachedResult;
    }
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM games");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result ? $result['count'] : 0;
        
        // Cache the successful result
        $dbCache->set($cacheKey, $count);
        
        return $count;
    } catch (PDOException $e) {
        error_log("Error getting game count: " . $e->getMessage());
        return 0;
    }
}

// Function to generate sort URL
function sortUrl($current_params, $sort_field, $direction = 'DESC') {
    $params = $current_params;
    $params['s1'] = $sort_field;
    $params['so1'] = $direction;
    return '?' . http_build_query($params);
}

// Function to escape HTML
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to insert sample game data
function insertGame($title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price = null, $price_url = null, $image_url = null, $system_requirements = null) {
    global $db, $dbCache;

    try {
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO games (title, slug, p_limit, p_samepc, genre, subgenre, r_year, online, offline, price, price_url, image_url, system_requirements, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . (strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false ? "CURRENT_TIMESTAMP" : "datetime('now')") . ")
        ");

        $result = $stmt->execute([$title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price, $price_url, $image_url, $system_requirements]);
        
        // Clear cache when data is modified
        if ($result) {
            $dbCache->clear();
        }
        
        return $result;

    } catch (PDOException $e) {
        error_log("Error inserting game '$title': " . $e->getMessage());
        error_log("Game data: " . json_encode([
            'title' => $title,
            'slug' => $slug,
            'p_limit' => $p_limit,
            'p_samepc' => $p_samepc,
            'genre' => $genre,
            'subgenre' => $subgenre,
            'r_year' => $r_year
        ]));
        
        // Return user-friendly error message
        return "Failed to save game '$title'. Please check the data and try again.";
    }
}
?>
