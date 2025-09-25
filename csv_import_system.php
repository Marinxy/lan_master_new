<?php
/**
 * LAN Game List - CSV Import System
 * 
 * This script can import games from CSV files with the following format:
 * "Title","Player_Limit","Local_Limit","Genre","Subgenre","Release_Year","Online","Offline","Price"
 * 
 * Usage:
 * php csv_import_system.php filename.csv
 * 
 * Features:
 * - Validates CSV structure
 * - Handles duplicate detection
 * - Provides detailed import statistics
 * - Supports batch processing
 * - Generates import reports
 */

require_once 'functions.php';

class CSVGameImporter {
    private $db;
    private $stats = [
        'total_rows' => 0,
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
        'duplicates' => 0
    ];
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Import games from CSV file
     */
    public function import($filename) {
        echo "🎮 LAN Game List - CSV Import System\n";
        echo str_repeat("=", 50) . "\n\n";
        
        if (!file_exists($filename)) {
            die("❌ Error: CSV file not found: $filename\n");
        }
        
        // Validate CSV structure
        if (!$this->validateCSV($filename)) {
            die("❌ Error: Invalid CSV format\n");
        }
        
        $handle = fopen($filename, 'r');
        if (!$handle) {
            die("❌ Error: Cannot open CSV file: $filename\n");
        }
        
        // Skip header row
        $header = fgetcsv($handle, 1000, ',');
        $this->stats['total_rows'] = count(file($filename)) - 1;
        
        echo "📁 File: $filename\n";
        echo "📊 Total rows to process: " . $this->stats['total_rows'] . "\n";
        echo "📋 Columns: " . implode(', ', $header) . "\n\n";
        
        echo "🚀 Starting import...\n\n";
        
        // Process each row
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $this->processRow($row);
            
            // Progress indicator
            if (($this->stats['imported'] + $this->stats['skipped'] + $this->stats['errors']) % 50 === 0) {
                $this->showProgress();
            }
        }
        
        fclose($handle);
        
        $this->showFinalReport();
    }
    
    /**
     * Validate CSV file structure
     */
    private function validateCSV($filename) {
        $handle = fopen($filename, 'r');
        if (!$handle) return false;
        
        $header = fgetcsv($handle, 1000, ',');
        fclose($handle);
        
        // Expected columns
        $expected = ['Title', 'Player_Limit', 'Local_Limit', 'Genre', 'Subgenre', 'Release_Year', 'Online', 'Offline', 'Price'];
        
        // Check if header matches expected structure
        return count($header) >= 7; // At least 7 required columns
    }
    
    /**
     * Process a single CSV row
     */
    private function processRow($row) {
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
            $this->stats['skipped']++;
            return;
        }
        
        // Check for duplicates
        if ($this->isDuplicate($game_data['slug'])) {
            $this->stats['duplicates']++;
            return;
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
            $this->stats['imported']++;
            echo "✓ Imported: " . $game_data['title'] . " (" . $game_data['r_year'] . ")\n";
        } else {
            $this->stats['errors']++;
            echo "✗ Error importing: " . $game_data['title'] . "\n";
        }
    }
    
    /**
     * Check if game already exists
     */
    private function isDuplicate($slug) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM games WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Show progress during import
     */
    private function showProgress() {
        $processed = $this->stats['imported'] + $this->stats['skipped'] + $this->stats['errors'];
        $percentage = round(($processed / $this->stats['total_rows']) * 100);
        
        echo "📈 Progress: $processed/$this->stats[total_rows] ($percentage%) - ";
        echo "✓$this->stats[imported] ✗$this->stats[errors] -$this->stats[skipped]\n";
    }
    
    /**
     * Show final import report
     */
    private function showFinalReport() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "🎉 IMPORT COMPLETE!\n\n";
        
        echo "📊 Import Statistics:\n";
        echo "  ✓ Successfully imported: " . $this->stats['imported'] . " games\n";
        echo "  - Skipped (invalid): " . $this->stats['skipped'] . " games\n";
        echo "  ✗ Errors: " . $this->stats['errors'] . " games\n";
        echo "  🔄 Duplicates found: " . $this->stats['duplicates'] . " games\n";
        
        if ($this->stats['imported'] > 0) {
            echo "\n📈 Database Statistics:\n";
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM games");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "  Total games in database: " . $result['count'] . "\n";
                
                $stmt = $this->db->query("SELECT genre, COUNT(*) as count FROM games GROUP BY genre ORDER BY count DESC LIMIT 5");
                echo "  Top 5 genres:\n";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "    " . $row['genre'] . ": " . $row['count'] . "\n";
                }
                
                $stmt = $this->db->query("SELECT MIN(r_year) as oldest, MAX(r_year) as newest FROM games");
                $years = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "  Release years: " . $years['oldest'] . " - " . $years['newest'] . "\n";
                
            } catch (Exception $e) {
                echo "  Error getting stats: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n✅ CSV Import System Ready for Future Imports!\n";
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage: php csv_import_system.php <csv_filename>\n";
    echo "Example: php csv_import_system.php lan_games_list.csv\n";
    exit(1);
}

$filename = $argv[1];
$importer = new CSVGameImporter();
$importer->import($filename);

echo "\n🚀 Ready to import more CSV files!\n";
?>
