<?php
/**
 * Image Download Handler
 * Downloads images from URLs and saves them locally
 */

require_once 'database.php';

// Set JSON response header
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check request method
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Invalid request method',
        'message' => 'This endpoint only accepts POST requests'
    ]);
    exit;
}

// Get parameters
$gameId = (int)($_POST['game_id'] ?? 0);
$imageUrl = trim($_POST['image_url'] ?? '');

if ($gameId <= 0) {
    echo json_encode([
        'error' => 'Invalid game ID',
        'message' => 'Please provide a valid game ID'
    ]);
    exit;
}

if (empty($imageUrl)) {
    echo json_encode([
        'error' => 'Invalid image URL',
        'message' => 'Please provide a valid image URL'
    ]);
    exit;
}

try {
    // Validate URL
    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    // Create img directory if it doesn't exist
    $imgDir = __DIR__ . '/img';
    if (!is_dir($imgDir)) {
        if (!mkdir($imgDir, 0755, true)) {
            throw new Exception('Failed to create img directory');
        }
    }

    // Download the image
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'LAN Game List/1.0',
            'follow_location' => true,
            'max_redirects' => 3
        ]
    ]);

    $imageData = file_get_contents($imageUrl, false, $context);
    
    if ($imageData === false) {
        throw new Exception('Failed to download image from URL');
    }

    // Detect image type
    $imageInfo = getimagesizefromstring($imageData);
    if ($imageInfo === false) {
        throw new Exception('Invalid image data');
    }

    // Determine file extension
    $extension = '';
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $extension = 'jpg';
            break;
        case IMAGETYPE_PNG:
            $extension = 'png';
            break;
        case IMAGETYPE_GIF:
            $extension = 'gif';
            break;
        case IMAGETYPE_WEBP:
            $extension = 'webp';
            break;
        default:
            throw new Exception('Unsupported image format');
    }

    // Generate filename
    $filename = $gameId . '.' . $extension;
    $filepath = $imgDir . '/' . $filename;

    // Remove existing image files for this game ID
    $existingFiles = glob($imgDir . '/' . $gameId . '.*');
    foreach ($existingFiles as $existingFile) {
        if (is_file($existingFile)) {
            unlink($existingFile);
        }
    }

    // Save the image
    if (file_put_contents($filepath, $imageData) === false) {
        throw new Exception('Failed to save image file');
    }

    // Update database to clear the remote image_url since we now have a local image
    $stmt = $db->prepare('UPDATE games SET image_url = NULL WHERE id = ?');
    $stmt->execute([$gameId]);

    echo json_encode([
        'success' => true,
        'message' => 'Image downloaded and saved successfully',
        'local_path' => 'img/' . $filename,
        'filename' => $filename
    ]);

} catch (Exception $e) {
    error_log('Image Download Error: ' . $e->getMessage());
    error_log('Game ID: ' . $gameId . ', URL: ' . $imageUrl);
    
    echo json_encode([
        'error' => 'Download failed',
        'message' => $e->getMessage()
    ]);
}
?>