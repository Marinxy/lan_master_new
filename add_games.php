<?php
require_once 'functions.php';

// Insert more sample games for testing
insertGame('Counter-Strike: Global Offensive', 'counter-strike-global-offensive', 10, 1, 'FPS', 'Tactical', 2012, true, false, 'Free', 'https://store.steampowered.com/app/730/', null, null);

insertGame('Team Fortress 2', 'team-fortress-2', 32, 1, 'FPS', 'Class-based', 2007, true, false, 'Free', 'https://store.steampowered.com/app/440/', null, null);

insertGame('Left 4 Dead 2', 'left-4-dead-2', 8, 4, 'FPS', 'Co-op', 2009, true, true, '$9.99', 'https://store.steampowered.com/app/550/', null, null);

insertGame('Borderlands 2', 'borderlands-2', 4, 4, 'FPS', 'RPG', 2012, true, true, '$19.99', 'https://store.steampowered.com/app/49520/', null, null);

insertGame('Warcraft III: The Frozen Throne', 'warcraft-iii-frozen-throne', 12, 1, 'RTS', 'Fantasy', 2003, true, true, '$14.99', 'https://us.shop.battle.net/en-us/product/warcraft-iii-reforged', null, null);

insertGame('StarCraft II', 'starcraft-ii', 8, 1, 'RTS', 'Sci-fi', 2010, true, false, '$14.99', 'https://starcraft2.com/', null, null);

insertGame('Age of Empires II', 'age-of-empires-ii', 8, 1, 'RTS', 'Historical', 1999, true, true, '$19.99', 'https://www.microsoft.com/en-us/p/age-of-empires-ii-definitive-edition/9n9q9b6b5j8k', null, null);

insertGame('Rocket League', 'rocket-league', 8, 4, 'Sports', 'Racing', 2015, true, true, 'Free', 'https://www.rocketleague.com/', null, null);

echo "Additional games added successfully!\n";
?>
