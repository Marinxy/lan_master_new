<?php
// Database initialization and connection

try {
    $db = new PDO('sqlite:games.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create games table
    $db->exec("
        CREATE TABLE IF NOT EXISTS games (
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
        )
    ");

    // Create indexes for better search performance
    $db->exec("CREATE INDEX IF NOT EXISTS idx_games_title ON games(title)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_games_genre ON games(genre)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_games_year ON games(r_year)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_games_online ON games(online)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_games_offline ON games(offline)");

    echo "Database initialized successfully!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Function to insert sample game data
function insertGame($title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price = null, $price_url = null, $image_url = null, $system_requirements = null) {
    global $db;

    try {
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO games (title, slug, p_limit, p_samepc, genre, subgenre, r_year, online, offline, price, price_url, image_url, system_requirements, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
        ");

        return $stmt->execute([$title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price, $price_url, $image_url, $system_requirements]);

    } catch (PDOException $e) {
        echo "Error inserting game '$title': " . $e->getMessage() . "\n";
        return false;
    }
}

// Insert some sample games
insertGame('Doom (2016)', 'doom-2016', 12, 1, 'FPS', 'Action', 2016, true, false, '€ 62.95', 'http://www.gamesrocket.de/download/DOOM.html?ref=986', 'http://www.gamesrocket.de/images/product_images/49349/images/covers/DOOM_big.jpg', null);

insertGame('Minecraft', 'minecraft', 8, 1, 'Open world', 'Sandbox', 2011, true, true, 'Free', 'https://minecraft.net', null, 'System requirements available on PCGamingWiki');

insertGame('Atomic Bomberman', 'atomic-bomberman', 10, 10, 'Action', 'Bomberman', 1997, false, true, null, null, null, null);

insertGame('Awesomenauts', 'awesomenauts', 6, 3, 'Platformer', 'MOBA', 2012, true, false, null, null, null, null);

echo "Sample games inserted successfully!\n";

?>
