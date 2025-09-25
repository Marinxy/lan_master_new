<?php
require_once 'functions.php';

echo "Importing all games from HTML files...\n";

$games_dir = 'games/';
$imported = 0;
$skipped = 0;
$errors = 0;

if (!is_dir($games_dir)) {
    die("Games directory not found: $games_dir\n");
}

// Get all HTML files
$files = glob($games_dir . '*.html');
echo "Found " . count($files) . " game files\n\n";

// Function to extract game data from HTML
function extractGameData($html_content, $filename) {
    // Extract title
    if (preg_match('/<title>([^<]+) - LAN Game List<\/title>/', $html_content, $matches)) {
        $title = trim($matches[1]);
    } else {
        return null; // Skip if no title found
    }

    // Create slug from filename
    $slug = basename($filename, '.html');

    // Extract game data from table
    $data = [];
    
    // Look for table rows with game info
    $pattern = '/<tr[^>]*>\s*<td[^>]*>([^<]+)<\/td>\s*<td[^>]*>([^<]+)<\/td>/s';
    preg_match_all($pattern, $html_content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $field = trim($match[1]);
        $value = trim($match[2]);
        
        switch ($field) {
            case 'Multiplayer limit':
                $data['p_limit'] = (int)$value;
                break;
            case 'Same PC limit':
                $data['p_samepc'] = (int)$value;
                break;
            case 'Genre':
                $data['genre'] = $value;
                break;
            case 'Subgenre':
                $data['subgenre'] = $value;
                break;
            case 'Release':
                $data['r_year'] = (int)$value;
                break;
            case 'Online':
                $data['online'] = strtolower($value) === 'yes';
                break;
            case 'Offline LAN':
                $data['offline'] = strtolower($value) === 'yes';
                break;
            case 'Price':
                $data['price'] = $value;
                break;
        }
    }

    // Check if we have minimum required data
    if (!isset($data['p_limit']) || !isset($data['genre']) || !isset($data['r_year'])) {
        return null;
    }

    // Set defaults
    $data['title'] = $title;
    $data['slug'] = $slug;
    $data['p_samepc'] = $data['p_samepc'] ?? 1;
    $data['online'] = $data['online'] ?? false;
    $data['offline'] = $data['offline'] ?? false;
    $data['subgenre'] = $data['subgenre'] ?? '';
    $data['price'] = $data['price'] ?? null;

    return $data;
}

foreach ($files as $file) {
    $html_content = file_get_contents($file);
    $game_data = extractGameData($html_content, $file);
    
    if (!$game_data) {
        echo "Skipping: " . basename($file) . " (insufficient data)\n";
        $skipped++;
        continue;
    }

    // Insert game
    $success = insertGame(
        $game_data['title'],
        $game_data['slug'],
        $game_data['p_limit'],
        $game_data['p_samepc'],
        $game_data['genre'],
        $game_data['subgenre'],
        $game_data['r_year'],
        $game_data['online'],
        $game_data['offline'],
        $game_data['price']
    );

    if ($success) {
        echo "✓ Imported: " . $game_data['title'] . "\n";
        $imported++;
    } else {
        echo "✗ Error importing: " . $game_data['title'] . "\n";
        $errors++;
    }
}

echo "\nImport Summary:\n";
echo "✓ Imported: $imported games\n";
echo "- Skipped: $skipped games\n";
echo "✗ Errors: $errors games\n";

if ($imported > 0) {
    echo "\nTotal games in database: ";
    $stmt = $db->query("SELECT COUNT(*) as count FROM games");
    echo $stmt->fetch(PDO::FETCH_ASSOC)['count'] . "\n";
}
?>
