<?php
require_once 'functions.php';

echo "CSV Import System for LAN Game List\n";
echo "==================================\n\n";

$csv_file = 'lan_games_list.csv';
$imported = 0;
$skipped = 0;
$errors = 0;

if (!file_exists($csv_file)) {
    die("CSV file not found: $csv_file\n");
}

// Read CSV file
$handle = fopen($csv_file, 'r');
if (!$handle) {
    die("Cannot open CSV file: $csv_file\n");
}

// Skip header row
$header = fgetcsv($handle, 1000, ',');

echo "Found " . count($header) . " columns in CSV\n";
echo "Columns: " . implode(', ', $header) . "\n\n";

echo "Starting import...\n";

// Process each row
while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
    // Skip empty rows
    if (empty(array_filter($row))) {
        continue;
    }

    // Map CSV columns to database fields
    $game_data = [
        'title' => $row[0] ?? '',
        'p_limit' => (int)($row[1] ?? 1),
        'p_samepc' => (int)($row[2] ?? 1),
        'genre' => $row[3] ?? '',
        'subgenre' => $row[4] ?? '',
        'r_year' => (int)($row[5] ?? 0),
        'online' => strtolower($row[6] ?? 'no') === 'yes',
        'offline' => strtolower($row[7] ?? 'no') === 'yes',
        'price' => $row[8] ?? null
    ];

    // Create slug from title
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $game_data['title'])));
    $game_data['slug'] = $slug;

    // Skip if title is empty or invalid
    if (empty($game_data['title']) || empty($game_data['genre']) || $game_data['r_year'] <= 1900) {
        echo "Skipping: " . ($game_data['title'] ?: 'Unknown') . " (insufficient data)\n";
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
        echo "✓ Imported: " . $game_data['title'] . " (" . $game_data['r_year'] . ")\n";
        $imported++;
    } else {
        echo "✗ Error importing: " . $game_data['title'] . "\n";
        $errors++;
    }

    // Progress indicator
    if (($imported + $skipped + $errors) % 50 === 0) {
        echo "Progress: $imported imported, $skipped skipped, $errors errors\n";
    }
}

fclose($handle);

echo "\n" . str_repeat("=", 50) . "\n";
echo "IMPORT COMPLETE!\n";
echo "Imported: $imported games\n";
echo "Skipped: $skipped games\n";
echo "Errors: $errors games\n";

if ($imported > 0) {
    echo "\nDatabase Stats:\n";
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM games");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total games in database: " . $result['count'] . "\n";
        
        $stmt = $db->query("SELECT genre, COUNT(*) as count FROM games GROUP BY genre ORDER BY count DESC LIMIT 5");
        echo "Top 5 genres:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['genre'] . ": " . $row['count'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error getting stats: " . $e->getMessage() . "\n";
    }
}
?>
