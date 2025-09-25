<?php
// Database connection and utility functions
// Supports both SQLite (local) and PostgreSQL (production/Unraid)

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
    die("Database connection failed: " . $e->getMessage());
}

// Function to get all games with filtering and sorting
function getGames($filters = [], $sort = []) {
    global $db;
    
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
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get game count
function getGameCount() {
    global $db;
    $stmt = $db->query("SELECT COUNT(*) as count FROM games");
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
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
    global $db;

    try {
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO games (title, slug, p_limit, p_samepc, genre, subgenre, r_year, online, offline, price, price_url, image_url, system_requirements, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . (strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false ? "CURRENT_TIMESTAMP" : "datetime('now')") . ")
        ");

        return $stmt->execute([$title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price, $price_url, $image_url, $system_requirements]);

    } catch (PDOException $e) {
        echo "Error inserting game '$title': " . $e->getMessage() . "\n";
        return false;
    }
}
?>
