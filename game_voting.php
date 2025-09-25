<?php
require_once 'database.php';
require_once 'user_auth.php';

// Create game votes table
function createGameVotesTable() {
    global $db;
    
    try {
        // Create game_votes table
        $sql = "CREATE TABLE IF NOT EXISTS game_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            game_id INT NOT NULL,
            vote_type ENUM('thumbs_up') DEFAULT 'thumbs_up',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_game_vote (user_id, game_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
            INDEX idx_game_votes_game_id (game_id),
            INDEX idx_game_votes_user_id (user_id),
            INDEX idx_game_votes_created_at (created_at)
        )";
        
        $db->exec($sql);
        echo "Game votes table created successfully.\n";
        
        // Add vote_count column to games table if it doesn't exist
        $sql_alter = "ALTER TABLE games ADD COLUMN IF NOT EXISTS vote_count INT DEFAULT 0";
        $db->exec($sql_alter);
        echo "Vote count column added to games table.\n";
        
    } catch (PDOException $e) {
        echo "Error creating game votes table: " . $e->getMessage() . "\n";
    }
}

// Vote for a game
function voteForGame($user_id, $game_id) {
    global $db;
    
    try {
        // Check if user already voted for this game
        $stmt = $db->prepare("SELECT id FROM game_votes WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$user_id, $game_id]);
        
        if ($stmt->fetch()) {
            // User already voted, remove the vote (toggle)
            $stmt = $db->prepare("DELETE FROM game_votes WHERE user_id = ? AND game_id = ?");
            $stmt->execute([$user_id, $game_id]);
            
            // Update vote count in games table
            updateGameVoteCount($game_id);
            
            return ['success' => true, 'action' => 'removed', 'message' => 'Vote removed'];
        } else {
            // Add new vote
            $stmt = $db->prepare("INSERT INTO game_votes (user_id, game_id, vote_type) VALUES (?, ?, 'thumbs_up')");
            $stmt->execute([$user_id, $game_id]);
            
            // Update vote count in games table
            updateGameVoteCount($game_id);
            
            return ['success' => true, 'action' => 'added', 'message' => 'Vote added'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error voting: ' . $e->getMessage()];
    }
}

// Update vote count for a game
function updateGameVoteCount($game_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            UPDATE games 
            SET vote_count = (
                SELECT COUNT(*) 
                FROM game_votes 
                WHERE game_id = ? AND vote_type = 'thumbs_up'
            ) 
            WHERE id = ?
        ");
        $stmt->execute([$game_id, $game_id]);
    } catch (PDOException $e) {
        error_log("Error updating vote count: " . $e->getMessage());
    }
}

// Get user's vote for a game
function getUserVoteForGame($user_id, $game_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT vote_type FROM game_votes WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$user_id, $game_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Get top voted games
function getTopVotedGames($limit = 20) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            SELECT g.*, 
                   COALESCE(g.vote_count, 0) as votes,
                   COUNT(gv.id) as actual_votes
            FROM games g
            LEFT JOIN game_votes gv ON g.id = gv.game_id
            GROUP BY g.id
            ORDER BY votes DESC, actual_votes DESC, g.title ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get voting statistics
function getVotingStatistics() {
    global $db;
    
    try {
        $stats = [];
        
        // Total votes
        $stmt = $db->query("SELECT COUNT(*) as total_votes FROM game_votes");
        $stats['total_votes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_votes'];
        
        // Total games with votes
        $stmt = $db->query("SELECT COUNT(DISTINCT game_id) as games_with_votes FROM game_votes");
        $stats['games_with_votes'] = $stmt->fetch(PDO::FETCH_ASSOC)['games_with_votes'];
        
        // Total users who voted
        $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as users_who_voted FROM game_votes");
        $stats['users_who_voted'] = $stmt->fetch(PDO::FETCH_ASSOC)['users_who_voted'];
        
        // Most voted game
        $stmt = $db->query("
            SELECT g.title, COUNT(gv.id) as votes 
            FROM games g 
            JOIN game_votes gv ON g.id = gv.game_id 
            GROUP BY g.id, g.title 
            ORDER BY votes DESC 
            LIMIT 1
        ");
        $most_voted = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['most_voted_game'] = $most_voted ? $most_voted : ['title' => 'None', 'votes' => 0];
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'total_votes' => 0,
            'games_with_votes' => 0,
            'users_who_voted' => 0,
            'most_voted_game' => ['title' => 'Error', 'votes' => 0]
        ];
    }
}

// Handle AJAX requests - ONLY for voting actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to vote']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['game_id'])) {
        $game_id = intval($_POST['game_id']);
        $result = voteForGame($user_id, $game_id);
        
        // Get updated vote count
        $stmt = $db->prepare("SELECT vote_count FROM games WHERE id = ?");
        $stmt->execute([$game_id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['vote_count'] = $game ? $game['vote_count'] : 0;
        
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Game ID required']);
    }
    exit;
}

// If called directly, create the table
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    createGameVotesTable();
    echo "Game voting system database setup complete!\n";
}
?>