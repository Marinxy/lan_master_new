***Begin Patch***
***Add File: igdb_scan_spec.md***

* # IGDB SCAN Button Specification
*
* This document describes how to integrate an **IGDB “SCAN” button** for each game entry in your LAN Party app. The purpose is to fetch and enrich game data from [IGDB](https://api-docs.igdb.com/).
*
* ---
*
* ## 1. Prerequisites
* * **Twitch Developer App** (to get `Client ID` and `Client Secret`).
* * **OAuth App Access Token** via Client Credentials flow:
* ```http
  ```
* POST [https://id.twitch.tv/oauth2/token?client_id=...&client_secret=...&grant_type=client_credentials](https://id.twitch.tv/oauth2/token?client_id=...&client_secret=...&grant_type=client_credentials)
* ```
  ```
* Response → `access_token`, `expires_in`.
* * Every IGDB request requires headers:
* ```http
  ```
* Client-ID: <your_client_id>
* Authorization: Bearer <access_token>
* ```
  ```
*
* ---
*
* ## 2. Endpoints Used
* * **Search**: `POST https://api.igdb.com/v4/search`
* * Body (APICalypse):
* ````
  ```  
  ````
* ```
  fields name,game;  
  ```
* ```
  search "Quake III Arena";  
  ```
* ```
  where game != null;  
  ```
* ```
  limit 5;  
  ```
* ````
  ```  
  ````
* * Returns candidate `game` IDs.
*
* * **Games**: `POST https://api.igdb.com/v4/games`
* * Body (example):
* ````
  ```  
  ````
* ```
  fields id,name,summary,first_release_date,  
  ```
* ```
         genres.name,game_modes.name,player_perspectives.name,  
  ```
* ```
         platforms,aggregated_rating,aggregated_rating_count,  
  ```
* ```
         cover.image_id,screenshots.image_id,  
  ```
* ```
         multiplayer_modes.campaigncoop,multiplayer_modes.dropin,  
  ```
* ```
         multiplayer_modes.lancoop,multiplayer_modes.offlinecoop,  
  ```
* ```
         multiplayer_modes.onlinecoop,multiplayer_modes.offlinemax,  
  ```
* ```
         multiplayer_modes.onlinecoopmax;  
  ```
* ```
  where id = (12345,23456) & platforms = (6); // platform 6 = PC (Windows)  
  ```
* ```
  limit 5;  
  ```
* ````
  ```  
  ````
*
* * (Optional) **Multiquery** for batching.
* * (Optional) **Covers / Screenshots** endpoints for direct image fetching.
*
* ---
*
* ## 3. Image URLs
* Constructed from `image_id`:
* ```
  ```
* [https://images.igdb.com/igdb/image/upload/t_{SIZE}/{image_id}.jpg](https://images.igdb.com/igdb/image/upload/t_{SIZE}/{image_id}.jpg)
* ```
  ```
* Preset sizes: `t_thumb`, `t_cover_big`, `t_screenshot_big`, `t_720p`, `t_1080p`, etc.
*
* Example:
* ```
  ```
* [https://images.igdb.com/igdb/image/upload/t_cover_big/abcd1234.jpg](https://images.igdb.com/igdb/image/upload/t_cover_big/abcd1234.jpg)
* ```
  ```
*
* ---
*
* ## 4. Data to Store Per Game
* * `igdb_game_id`
* * `name`
* * `summary`
* * `first_release_date`
* * `genres[]`
* * `game_modes[]`
* * `player_perspectives[]`
* * `platforms[]` (must include 6 = PC)
* * `multiplayer_modes` (LAN-relevant: `lancoop`, `offlinecoop`, `offlinemax`, `onlinecoop`)
* * `cover.image_id` (→ cover URL)
* * `screenshots[].image_id` (→ screenshot URLs)
* * `aggregated_rating`, `aggregated_rating_count`
* * `source_url` (IGDB link, optional)
*
* ---
*
* ## 5. UI Behavior for SCAN Button
* 1. User clicks **SCAN** next to a game.
* 2. System queries `/search`.
* 3. If multiple candidates → show **modal** with preview and let user confirm correct game.
* 4. Once confirmed → fetch details via `/games`.
* 5. Store results in local DB; update row with enriched info (summary, cover, rating, etc).
* 6. Display a **preview card** in UI with: cover, summary, genres, modes, coop/offline info.
* 7. Provide actions: **Re-scan** and **Unlink**.
*
* ---
*
* ## 6. Matching Heuristics
* * Prefer results with:
* * Platform includes 6 (PC/Windows).
* * Title closest to your original name.
* * Highest `aggregated_rating_count`.
* * Matching release year (if available).
*
* * If ambiguity remains → require manual admin confirmation.
*
* ---
*
* ## 7. Import/Export & Caching
* * Cache tokens; refresh before expiry.
* * Batch multiple queries with **Multiquery** when scanning many games.
* * Rate-limit requests to avoid 429.
* * Store IGDB ID once linked → reuse instead of re-scanning every time.
*
* ---
*
* ## 8. Legal/Usage Notes
* * IGDB via Twitch is free for non-commercial projects.
* * For commercial usage, contact `partner@igdb.com`.
* * Do not expose your `Client Secret` in frontend. Keep it in server.
*
* ---
*
* ## 9. Deliverables for Coding Agent
* * Add **SCAN** button to each game row.
* * Implement `/search` → `/games` workflow.
* * Build modal for multiple matches.
* * Save enriched fields to DB.
* * Show preview card in UI.
* * Add “Re-scan / Unlink” options.
* * Respect token handling, caching, and rate limits.
*
* ---

*** End Patch***
