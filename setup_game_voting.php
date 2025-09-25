<?php
// Standalone setup script for game voting system

try {
    $pdo = new PDO('sqlite:games.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create game_votes table
    $sql = "CREATE TABLE IF NOT EXISTS game_votes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        game_id INTEGER NOT NULL,
        vote_type TEXT DEFAULT 'thumbs_up',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, game_id)
    )";
    
    $pdo->exec($sql);
    echo "Game votes table created successfully.\n";
    
    // Add vote_count column to games table if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE games ADD COLUMN vote_count INTEGER DEFAULT 0");
        echo "Vote count column added to games table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') === false) {
            echo "Vote count column already exists or error: " . $e->getMessage() . "\n";
        } else {
            echo "Vote count column already exists.\n";
        }
    }
    
    // Create indexes for better performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_game_votes_game_id ON game_votes(game_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_game_votes_user_id ON game_votes(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_game_votes_created_at ON game_votes(created_at)");
    
    echo "Game voting system database setup complete!\n";
    
} catch (PDOException $e) {
    echo "Error setting up game voting system: " . $e->getMessage() . "\n";
}
?>