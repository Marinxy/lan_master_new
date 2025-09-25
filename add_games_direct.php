<?php
try {
    $db = new PDO('sqlite:games.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert more sample games for testing
    $games = [
        ['Counter-Strike: Global Offensive', 'counter-strike-global-offensive', 10, 1, 'FPS', 'Tactical', 2012, 1, 0, 'Free', 'https://store.steampowered.com/app/730/', null, null],
        ['Team Fortress 2', 'team-fortress-2', 32, 1, 'FPS', 'Class-based', 2007, 1, 0, 'Free', 'https://store.steampowered.com/app/440/', null, null],
        ['Left 4 Dead 2', 'left-4-dead-2', 8, 4, 'FPS', 'Co-op', 2009, 1, 1, '$9.99', 'https://store.steampowered.com/app/550/', null, null],
        ['Borderlands 2', 'borderlands-2', 4, 4, 'FPS', 'RPG', 2012, 1, 1, '$19.99', 'https://store.steampowered.com/app/49520/', null, null],
        ['Warcraft III: The Frozen Throne', 'warcraft-iii-frozen-throne', 12, 1, 'RTS', 'Fantasy', 2003, 1, 1, '$14.99', 'https://us.shop.battle.net/en-us/product/warcraft-iii-reforged', null, null],
        ['StarCraft II', 'starcraft-ii', 8, 1, 'RTS', 'Sci-fi', 2010, 1, 0, '$14.99', 'https://starcraft2.com/', null, null],
        ['Age of Empires II', 'age-of-empires-ii', 8, 1, 'RTS', 'Historical', 1999, 1, 1, '$19.99', 'https://www.microsoft.com/en-us/p/age-of-empires-ii-definitive-edition/9n9q9b6b5j8k', null, null],
        ['Rocket League', 'rocket-league', 8, 4, 'Sports', 'Racing', 2015, 1, 1, 'Free', 'https://www.rocketleague.com/', null, null]
    ];

    foreach ($games as $game) {
        try {
            $stmt = $db->prepare("
                INSERT OR REPLACE INTO games (title, slug, p_limit, p_samepc, genre, subgenre, r_year, online, offline, price, price_url, image_url, system_requirements, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
            ");
            $stmt->execute($game);
            echo "Added: " . $game[0] . "\n";
        } catch (PDOException $e) {
            echo "Error adding " . $game[0] . ": " . $e->getMessage() . "\n";
        }
    }

    echo "All games added successfully!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
