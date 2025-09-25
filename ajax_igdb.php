<?php
/**
 * AJAX endpoint for IGDB API requests
 * Handles search and details requests for game data
 */

require_once 'igdb_api.php';

// Set JSON response header
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check request method
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get action parameter
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'search_igdb':
            $query = trim($_POST['query'] ?? '');
            
            if (empty($query)) {
                echo json_encode(['error' => 'Search query is required']);
                exit;
            }
            
            if (strlen($query) < 2) {
                echo json_encode(['error' => 'Search query must be at least 2 characters']);
                exit;
            }
            
            $limit = (int)($_POST['limit'] ?? 10);
            $limit = max(1, min(50, $limit)); // Limit between 1 and 50
            
            $results = searchIGDBGames($query, $limit);
            
            if (isset($results['error'])) {
                echo json_encode($results);
            } else {
                echo json_encode(['games' => $results]);
            }
            break;
            
        case 'get_igdb_details':
            $igdbId = (int)($_POST['id'] ?? 0);
            
            if ($igdbId <= 0) {
                echo json_encode(['error' => 'Valid IGDB ID is required']);
                exit;
            }
            
            $details = getIGDBGameDetails($igdbId);
            
            if (isset($details['error'])) {
                echo json_encode($details);
            } else {
                $mappedData = mapIGDBToGameData($details);
                echo json_encode($mappedData);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log('AJAX IGDB Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Server error occurred']);
}
?>
