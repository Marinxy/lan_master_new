<?php
/**
 * IGDB API Integration for LAN Game List
 * 
 * Uses IGDB API v4 to search and retrieve game information
 * Documentation: https://api-docs.igdb.com/#getting-started
 */

class IGDBApi {
    private $clientId = '5sj5cuvzsvjerb0ibdoiktfflyz454';
    private $clientSecret = '6ws6zjsmy780qmnat14pwekj0fdckr';
    private $accessToken = null;
    private $tokenExpires = null;
    private $cacheDir = 'cache/igdb/';
    private $cacheExpiry = 3600; // 1 hour cache
    
    /**
     * Initialize cache directory
     */
    private function initCache() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cache key for a request
     */
    private function getCacheKey($type, $query) {
        return md5($type . '_' . $query);
    }
    
    /**
     * Get cached response if available and not expired
     */
    private function getCachedResponse($cacheKey) {
        $this->initCache();
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if (!$cacheData || !isset($cacheData['timestamp'], $cacheData['data'])) {
            return null;
        }
        
        // Check if cache is expired
        if (time() - $cacheData['timestamp'] > $this->cacheExpiry) {
            unlink($cacheFile); // Remove expired cache
            return null;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Cache response data
     */
    private function cacheResponse($cacheKey, $data) {
        $this->initCache();
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        $cacheData = [
            'timestamp' => time(),
            'data' => $data
        ];
        
        file_put_contents($cacheFile, json_encode($cacheData));
    }
    
    /**
     * Clear all cached data
     */
    public function clearCache() {
        if (!is_dir($this->cacheDir)) {
            return true;
        }
        
        $files = glob($this->cacheDir . '*.json');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache() {
        if (!is_dir($this->cacheDir)) {
            return true;
        }
        
        $files = glob($this->cacheDir . '*.json');
        $cleared = 0;
        
        foreach ($files as $file) {
            $cacheData = json_decode(file_get_contents($file), true);
            if (!$cacheData || !isset($cacheData['timestamp'])) {
                unlink($file);
                $cleared++;
                continue;
            }
            
            // Check if cache is expired
            if (time() - $cacheData['timestamp'] > $this->cacheExpiry) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        if (!is_dir($this->cacheDir)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'expired_files' => 0
            ];
        }
        
        $files = glob($this->cacheDir . '*.json');
        $totalSize = 0;
        $expiredFiles = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $cacheData = json_decode(file_get_contents($file), true);
            if (!$cacheData || !isset($cacheData['timestamp'])) {
                $expiredFiles++;
                continue;
            }
            
            if (time() - $cacheData['timestamp'] > $this->cacheExpiry) {
                $expiredFiles++;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'expired_files' => $expiredFiles,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Get OAuth2 access token from Twitch
     */
    private function getAccessToken() {
        // Check if we have a valid cached token
        if ($this->accessToken && $this->tokenExpires && time() < $this->tokenExpires) {
            return $this->accessToken;
        }
        
        $url = 'https://id.twitch.tv/oauth2/token';
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get access token: HTTP ' . $httpCode);
        }
        
        $tokenData = json_decode($response, true);
        if (!$tokenData || !isset($tokenData['access_token'])) {
            throw new Exception('Invalid token response');
        }
        
        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpires = time() + $tokenData['expires_in'] - 60; // 60 second buffer
        
        return $this->accessToken;
    }
    
    /**
     * Make API request to IGDB
     */
    private function makeRequest($endpoint, $query) {
        $token = $this->getAccessToken();
        
        $url = 'https://api.igdb.com/v4/' . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Client-ID: ' . $this->clientId,
            'Authorization: Bearer ' . $token,
            'Content-Type: text/plain'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('IGDB API request failed: HTTP ' . $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Search for games by name (following IGDB scan spec)
     */
    public function searchGames($query, $limit = 10) {
        // Create cache key for this search
        $cacheKey = $this->getCacheKey('search', $query . '_' . $limit);
        
        // Try to get cached response first
        $cachedResponse = $this->getCachedResponse($cacheKey);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }
        
        // Search for PC games using the recommended approach
        $searchQuery = 'search "' . addslashes($query) . '"; 
fields name,slug,summary,first_release_date,cover.url,genres.name,game_modes.name,aggregated_rating,aggregated_rating_count,multiplayer_modes.lancoop,multiplayer_modes.offlinecoop,multiplayer_modes.onlinecoop,multiplayer_modes.offlinemax,rating,rating_count; 
where category = 0; 
limit ' . $limit . ';';
        
        try {
            $results = $this->makeRequest('games', $searchQuery);
            
            if (!$results) {
                $response = [];
                $this->cacheResponse($cacheKey, $response);
                return $response;
            }
            
            // Process and format results
            $games = [];
            foreach ($results as $game) {
                $processedGame = $this->processGameData($game);
                if ($processedGame) {
                    $games[] = $processedGame;
                }
            }
            
            // Cache the successful response
            $this->cacheResponse($cacheKey, $games);
            return $games;
        } catch (Exception $e) {
            error_log('IGDB Search Error: ' . $e->getMessage());
            $errorResponse = ['error' => $e->getMessage()];
            // Don't cache error responses
            return $errorResponse;
        }
    }
    
    /**
     * Get detailed game information by ID
     */
    public function getGameDetails($igdbId) {
        // Create cache key for this game details request
        $cacheKey = $this->getCacheKey('details', $igdbId);
        
        // Try to get cached response first
        $cachedResponse = $this->getCachedResponse($cacheKey);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }
        
        $detailQuery = 'fields name,slug,summary,first_release_date,cover.url,genres.name,game_modes.name,multiplayer_modes.onlinecoop,multiplayer_modes.offlinecoop,multiplayer_modes.lancoop,rating,rating_count,websites.url,websites.category; 
where id = ' . $igdbId . ';';
        
        try {
            $results = $this->makeRequest('games', $detailQuery);
            
            if (!$results || count($results) === 0) {
                $errorResponse = ['error' => 'Game not found'];
                // Don't cache error responses
                return $errorResponse;
            }
            
            $gameData = $this->processGameData($results[0], true);
            
            // Cache the successful response
            $this->cacheResponse($cacheKey, $gameData);
            return $gameData;
        } catch (Exception $e) {
            error_log('IGDB Details Error: ' . $e->getMessage());
            $errorResponse = ['error' => $e->getMessage()];
            // Don't cache error responses
            return $errorResponse;
        }
    }
    
    /**
     * Process and format game data from IGDB response
     */
    private function processGameData($game, $detailed = false) {
        if (!isset($game['name'])) {
            return null;
        }
        
        // Extract cover URL
        $coverUrl = '';
        if (isset($game['cover']['url'])) {
            $coverUrl = 'https:' . str_replace('t_thumb', 't_cover_big', $game['cover']['url']);
        }
        
        // Extract genres
        $genres = [];
        if (isset($game['genres'])) {
            foreach ($game['genres'] as $genre) {
                if (isset($genre['name'])) {
                    $genres[] = $genre['name'];
                }
            }
        }
        
        // Extract game modes
        $modes = [];
        if (isset($game['game_modes'])) {
            foreach ($game['game_modes'] as $mode) {
                if (isset($mode['name'])) {
                    $modes[] = $mode['name'];
                }
            }
        }
        
        // Check multiplayer capabilities
        $online = false;
        $offline = false;
        if (isset($game['multiplayer_modes'])) {
            foreach ($game['multiplayer_modes'] as $mp) {
                if (isset($mp['onlinecoop']) && $mp['onlinecoop']) {
                    $online = true;
                }
                if (isset($mp['offlinecoop']) && $mp['offlinecoop']) {
                    $offline = true;
                }
                if (isset($mp['lancoop']) && $mp['lancoop']) {
                    $offline = true;
                }
            }
        }
        
        // Get release year
        $releaseYear = '';
        if (isset($game['first_release_date'])) {
            $releaseYear = date('Y', $game['first_release_date']);
        }
        
        // Get rating
        $rating = null;
        $ratingCount = 0;
        if (isset($game['rating'])) {
            $rating = round($game['rating'], 1);
        }
        if (isset($game['rating_count'])) {
            $ratingCount = $game['rating_count'];
        }
        
        $processedGame = [
            'id' => $game['id'],
            'igdbId' => $game['id'],
            'name' => $game['name'],
            'title' => $game['name'],
            'slug' => $game['slug'] ?? $this->generateSlug($game['name']),
            'summary' => $game['summary'] ?? '',
            'release_year' => $releaseYear,
            'first_release_date' => $game['first_release_date'] ?? null,
            'cover_url' => $coverUrl,
            'image_url' => $coverUrl,
            'genres' => $genres,
            'game_modes' => $modes,
            'genre' => count($genres) > 0 ? $genres[0] : '',
            'online' => $online,
            'offline' => $offline,
            'rating' => $rating,
            'rating_count' => $ratingCount
        ];
        
        // Add additional details for detailed requests
        if ($detailed && isset($game['websites'])) {
            $websites = [];
            foreach ($game['websites'] as $website) {
                if (isset($website['url']) && isset($website['category'])) {
                    $websites[] = [
                        'url' => $website['url'],
                        'category' => $website['category']
                    ];
                }
            }
            $processedGame['websites'] = $websites;
        }
        
        return $processedGame;
    }
    
    /**
     * Generate a URL-friendly slug from a game name
     */
    private function generateSlug($name) {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}

// Global functions for easy access
function searchIGDBGames($query, $limit = 10) {
    try {
        $igdb = new IGDBApi();
        return $igdb->searchGames($query, $limit);
    } catch (Exception $e) {
        error_log('IGDB Search Error: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

function getIGDBGameDetails($igdbId) {
    try {
        $igdb = new IGDBApi();
        return $igdb->getGameDetails($igdbId);
    } catch (Exception $e) {
        error_log('IGDB Details Error: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

function mapIGDBToGameData($igdbData) {
    if (isset($igdbData['error'])) {
        return $igdbData;
    }
    
    return [
        'title' => $igdbData['title'] ?? $igdbData['name'] ?? '',
        'slug' => $igdbData['slug'] ?? '',
        'genre' => $igdbData['genre'] ?? '',
        'subgenre' => count($igdbData['genres'] ?? []) > 1 ? $igdbData['genres'][1] : '',
        'release_year' => $igdbData['release_year'] ?? '',
        'online' => $igdbData['online'] ?? false,
        'offline' => $igdbData['offline'] ?? false,
        'image_url' => $igdbData['image_url'] ?? $igdbData['cover_url'] ?? '',
        'summary' => $igdbData['summary'] ?? '',
        'system_requirements' => $igdbData['summary'] ?? ''
    ];
}
?>
