<?php
/**
 * User Authentication System for LAN Game List
 * 
 * Features:
 * - User registration with email and password
 * - Secure password hashing
 * - Login/logout functionality
 * - Session management
 * - User profile management
 */

require_once 'functions.php';

// Create users table if it doesn't exist
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT 1,
            last_login DATETIME
        )
    ");
    
    // Create user_sessions table for session management
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            session_token TEXT UNIQUE NOT NULL,
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    // Create indexes for better performance
    $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sessions_token ON user_sessions(session_token)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sessions_user ON user_sessions(user_id)");
    
    
} catch (PDOException $e) {
    die("❌ Error creating user tables: " . $e->getMessage());
}

/**
 * User Authentication Functions
 */

// Hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Register new user
function registerUser($username, $email, $password) {
    global $db;
    
    try {
        // Check if username or email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $passwordHash = hashPassword($password);
        
        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, updated_at)
            VALUES (?, ?, ?, datetime('now'))
        ");
        
        if ($stmt->execute([$username, $email, $passwordHash])) {
            $userId = $db->lastInsertId();
            return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Login user
function loginUser($username, $password) {
    global $db;
    
    try {
        // Get user by username
        $stmt = $db->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = datetime('now'), updated_at = datetime('now') WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Create session for user
function createUserSession($userId) {
    global $db;
    
    try {
        // Generate secure session token
        $sessionToken = bin2hex(random_bytes(32));
        
        // Set expiration (24 hours from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Get client info
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Insert session
        $stmt = $db->prepare("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $sessionToken, $ipAddress, $userAgent, $expiresAt])) {
            return $sessionToken;
        } else {
            return false;
        }
        
    } catch (PDOException $e) {
        return false;
    }
}

// Verify user session
function verifySession($sessionToken) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.email, u.last_login 
            FROM user_sessions s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.session_token = ? AND s.expires_at > datetime('now') AND u.is_active = 1
        ");
        $stmt->execute([$sessionToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return false;
    }
}

// Get current user from session
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['session_token'])) {
        return verifySession($_SESSION['session_token']);
    }
    
    return false;
}

// Logout user
function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['session_token'])) {
        global $db;
        
        try {
            // Remove session from database
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
            $stmt->execute([$_SESSION['session_token']]);
        } catch (PDOException $e) {
            // Continue with logout even if DB cleanup fails
        }
        
        // Clear session
        session_destroy();
    }
    
    return true;
}

// Check if user is logged in
function isLoggedIn() {
    return getCurrentUser() !== false;
}

// Get user by ID
function getUserById($userId) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    return false;
}
}

// Admin Functions

// Check if user is admin
function isAdmin($userId) {
    global $db;

    try {
        $stmt = $db->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // For now, consider the first user as admin
        // In production, you might want to add an admin flag to users table
        if ($user && $userId == 1) {
            return true;
        }

        return false;
    } catch (PDOException $e) {
        return false;
    }
}

// Get game by ID for editing
function getGameById($gameId) {
    global $db;

    try {
        $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$gameId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Update game information
function updateGame($gameId, $title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price = null, $price_url = null, $image_url = null, $system_requirements = null) {
    global $db, $dbCache;

    try {
        $stmt = $db->prepare("
            UPDATE games
            SET title = ?, slug = ?, p_limit = ?, p_samepc = ?, genre = ?, subgenre = ?, r_year = ?, online = ?, offline = ?, price = ?, price_url = ?, image_url = ?, system_requirements = ?, updated_at = datetime('now')
            WHERE id = ?
        ");

        $result = $stmt->execute([$title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price, $price_url, $image_url, $system_requirements, $gameId]);
        
        // Clear cache when data is modified
        if ($result) {
            $dbCache->clear();
        }
        
        return $result;

    } catch (PDOException $e) {
        echo "Error updating game: " . $e->getMessage() . "\n";
        return false;
    }
}

// Delete game
function deleteGame($gameId) {
    global $db, $dbCache;

    try {
        $stmt = $db->prepare("DELETE FROM games WHERE id = ?");
        $result = $stmt->execute([$gameId]);
        
        // Clear cache when data is modified
        if ($result) {
            $dbCache->clear();
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

// Get all games for admin list
function getAllGamesAdmin($limit = 100, $offset = 0) {
    global $db;

    try {
        $stmt = $db->prepare("SELECT * FROM games ORDER BY title LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get games count for admin
function getGamesCountAdmin() {
    global $db;

    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM games");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (PDOException $e) {
        return 0;
    }
}

?>
