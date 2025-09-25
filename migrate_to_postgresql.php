<?php
// PostgreSQL migration script
echo "PostgreSQL Migration Script for LAN Game List\n";
echo "==========================================\n\n";

// PostgreSQL connection parameters (update these)
$pg_host = 'localhost';
$pg_port = '5432';
$pg_dbname = 'langamelist';
$pg_user = 'langamelist_user';
$pg_password = 'your_password_here';

echo "1. Exporting data from SQLite...\n";

// Connect to SQLite
try {
    $sqlite = new PDO('sqlite:games.db');
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all games
    $games = $sqlite->query("SELECT * FROM games ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($games) . " games in SQLite database\n";
    
} catch (PDOException $e) {
    die("SQLite connection failed: " . $e->getMessage());
}

echo "\n2. Creating PostgreSQL schema...\n";

// PostgreSQL schema
$pg_schema = "
CREATE TABLE IF NOT EXISTS games (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    p_limit INTEGER NOT NULL,
    p_samepc INTEGER NOT NULL,
    genre TEXT NOT NULL,
    subgenre TEXT,
    r_year INTEGER NOT NULL,
    online BOOLEAN NOT NULL DEFAULT false,
    offline BOOLEAN NOT NULL DEFAULT false,
    price TEXT,
    price_url TEXT,
    image_url TEXT,
    system_requirements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_games_title ON games(title);
CREATE INDEX IF NOT EXISTS idx_games_genre ON games(genre);
CREATE INDEX IF NOT EXISTS idx_games_year ON games(r_year);
CREATE INDEX IF NOT EXISTS idx_games_online ON games(online);
CREATE INDEX IF NOT EXISTS idx_games_offline ON games(offline);

-- Create user if needed
-- CREATE USER langamelist_user WITH PASSWORD 'your_password_here';
-- GRANT ALL PRIVILEGES ON DATABASE langamelist TO langamelist_user;
-- GRANT ALL ON games TO langamelist_user;
";

echo "PostgreSQL schema:\n";
echo $pg_schema . "\n";

echo "\n3. Migration SQL (run this in your PostgreSQL database):\n";
echo "------------------------------------------------------\n";

echo "-- Insert game data\n";
foreach ($games as $game) {
    // Escape single quotes for SQL
    $title = str_replace("'", "''", $game['title']);
    $slug = str_replace("'", "''", $game['slug']);
    $genre = str_replace("'", "''", $game['genre']);
    $subgenre = $game['subgenre'] ? str_replace("'", "''", $game['subgenre']) : 'NULL';
    $price = $game['price'] ? "'" . str_replace("'", "''", $game['price']) . "'" : 'NULL';
    $price_url = $game['price_url'] ? "'" . str_replace("'", "''", $game['price_url']) . "'" : 'NULL';
    $image_url = $game['image_url'] ? "'" . str_replace("'", "''", $game['image_url']) . "'" : 'NULL';
    $system_requirements = $game['system_requirements'] ? "'" . str_replace("'", "''", $game['system_requirements']) . "'" : 'NULL';
    
    echo "INSERT INTO games (title, slug, p_limit, p_samepc, genre, subgenre, r_year, online, offline, price, price_url, image_url, system_requirements, created_at, updated_at) VALUES ('$title', '$slug', {$game['p_limit']}, {$game['p_samepc']}, '$genre', $subgenre, {$game['r_year']}, " . ($game['online'] ? 'true' : 'false') . ", " . ($game['offline'] ? 'true' : 'false') . ", $price, $price_url, $image_url, $system_requirements, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON CONFLICT (slug) DO UPDATE SET title=EXCLUDED.title, p_limit=EXCLUDED.p_limit, p_samepc=EXCLUDED.p_samepc, genre=EXCLUDED.genre, subgenre=EXCLUDED.subgenre, r_year=EXCLUDED.r_year, online=EXCLUDED.online, offline=EXCLUDED.offline, price=EXCLUDED.price, price_url=EXCLUDED.price_url, image_url=EXCLUDED.image_url, system_requirements=EXCLUDED.system_requirements, updated_at=CURRENT_TIMESTAMP;\n";
}

echo "\n-- Update functions.php to use PostgreSQL:\n";
echo "Replace SQLite connection with:\n";
echo "\$db = new PDO('pgsql:host=\$pg_host;port=\$pg_port;dbname=\$pg_dbname', \$pg_user, \$pg_password);\n";

echo "\nMigration script completed!\n";
echo "Make sure to:\n";
echo "1. Update the PostgreSQL connection parameters above\n";
echo "2. Create the PostgreSQL database and user\n";
echo "3. Run the schema and INSERT statements in your PostgreSQL database\n";
echo "4. Update functions.php to use PostgreSQL connection\n";
?>
