<?php
require_once 'user_auth.php';
require_once 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for remember me cookie
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $user = verifySession($_COOKIE['remember_token']);
    if ($user) {
        $_SESSION['session_token'] = $_COOKIE['remember_token'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
}

// Get current user
$currentUser = getCurrentUser();

// Handle admin actions
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentUser && isAdmin($currentUser['id'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_game' && isset($_POST['game_id'])) {
        $gameId = (int)$_POST['game_id'];
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $p_limit = (int)($_POST['p_limit'] ?? 1);
        $p_samepc = (int)($_POST['p_samepc'] ?? 1);
        $genre = trim($_POST['genre'] ?? '');
        $subgenre = trim($_POST['subgenre'] ?? '');
        $r_year = (int)($_POST['r_year'] ?? 1990);
        $online = isset($_POST['online']);
        $offline = isset($_POST['offline']);
        $price = trim($_POST['price'] ?? '');
        $price_url = trim($_POST['price_url'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $system_requirements = trim($_POST['system_requirements'] ?? '');

        if (empty($title) || empty($genre)) {
            $message = 'Title and genre are required';
            $messageType = 'error';
        } else {
            $success = updateGame($gameId, $title, $slug, $p_limit, $p_samepc, $genre, $subgenre, $r_year, $online, $offline, $price, $price_url, $image_url, $system_requirements);
            if ($success) {
                $message = 'Game updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update game';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete_game' && isset($_POST['game_id'])) {
        $gameId = (int)$_POST['game_id'];
        if (deleteGame($gameId)) {
            $message = 'Game deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete game';
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$filters = [];
$filters['search'] = $_GET['search'] ?? '';
$filters['p_min'] = $_GET['p_min'] ?? '';
$filters['p_max'] = $_GET['p_max'] ?? '';
$filters['p_samepc_min'] = $_GET['p_samepc_min'] ?? '';
$filters['r_min'] = $_GET['r_min'] ?? '';
$filters['r_max'] = $_GET['r_max'] ?? '';
$filters['online'] = $_GET['online'] ?? '';
$filters['offline'] = $_GET['offline'] ?? '';
$filters['free'] = $_GET['free'] ?? '';
$filters['standalone'] = $_GET['standalone'] ?? '';

// Get sort parameters
$sort = [];
$sort['s1'] = $_GET['s1'] ?? 'title';
$sort['so1'] = $_GET['so1'] ?? 'ASC';
$sort['s2'] = $_GET['s2'] ?? '';
$sort['so2'] = $_GET['so2'] ?? '';
$sort['s3'] = $_GET['s3'] ?? '';
$sort['so3'] = $_GET['so3'] ?? '';

// Get filtered games
$games = getGames($filters, $sort);
$gameCount = getGameCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>LAN Game List - PC Game info for LAN parties and eSports</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="Felix Klastrup, Emvevi">
	<meta name="description" content="All the game info you need to find games for your next multiplayer session, including max players, genres, release years, off- and on-line capabilites and prices for each game.">
	<meta property="og:image" content="logo1_icon_100x100.png">
	<link rel="stylesheet" type="text/css" href="includes/style.css">
	<script src='recaptcha/api.js'></script>
	<script src='includes/app.js' defer></script>
</head>
<body>

<!-- Google Analytics tracking code block start -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-70217512-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- Google Analytics tracking code block start -->

<div class="body">
	<div class="header">
		<div>
			<div class='inline-block'>
				<a href="index.php"><img src='logo1.png'></a>
			</div>
			<div class='right'>
                <?php if ($currentUser): ?>
                    <span style="color: #007cba;">Welcome, <?php echo h($currentUser['username']); ?>!</span> |
                    <a href='profile.php'>Profile</a> |
                    <a href='logout.php'>Logout</a>
                <?php else: ?>
                    <a href='login.php'>Login</a> or <a href='signup.php'>sign up</a>
                <?php endif; ?>
            </div>
		</div>
		<div class='headermenu'>
			<a href='#'>ADD GAME</a> - <a href='#'>ABOUT</a> - <a href='#'>CONTACT</a>				</div>
		<div class='right'>
			<div class='inline-block'>
				<span class='ticker'><?php echo substr($gameCount, 0, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 1, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 2, 1); ?></span></div><div class='inline-block'>&nbsp;games in database</div>		</div>
		</div>
		<h1>LAN Game List</h1>

	<?php if ($message): ?>
	<div style="margin: 20px auto; max-width: 800px; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; <?php
		echo $messageType === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;';
	?>">
		<?php echo h($message); ?>
	</div>
	<?php endif; ?>

	<div class="midsection">	
	<div class="menu">
		<form class='auto_submit' action="index.php" method="get">
			<p class="menusubheader bold">Filter games</p>
			<table>
				<tr><td colspan="2">Title/genre search:</td></tr>
				<tr><td colspan="2"><input class="text_search" type="text" name="search" maxlength="25" value="<?php echo h($filters['search']); ?>"></td></tr>
				<tr><td><label for='p_min'>Player limit min.</label></td><td><input class="text_small" type="text" name="p_min" id="p_min" maxlength="3" value="<?php echo h($filters['p_min']); ?>"></td></tr>
				<tr><td><label for='p_max'>Player limit max.</label></td><td><input class="text_small" type="text" name="p_max" id="p_max" maxlength="3" value="<?php echo h($filters['p_max']); ?>"></td></tr>
				<tr><td><label for='p_samepc_min'>Local limit min.</label></td><td><input class="text_small" type="text" name="p_samepc_min" id="p_samepc_min" maxlength="3" value="<?php echo h($filters['p_samepc_min']); ?>"></td></tr>
				<tr><td><label for='r_min'>Release earliest</label></td><td><input class="text_small" type="text" name="r_min" id="r_min" maxlength="4" value="<?php echo h($filters['r_min']); ?>"></td></tr>
				<tr><td><label for='r_max'>Release latest</label></td><td><input class="text_small" type="text" name="r_max" id="r_max" maxlength="4" value="<?php echo h($filters['r_max']); ?>"></td></tr>
			</table>
			<table class="menu">
				<tr><td><input type="checkbox" name="online" id="online" value="yes"<?php echo $filters['online'] === 'yes' ? ' checked' : ''; ?>><label for='online'>Online</label></td></tr>
				<tr><td><input type="checkbox" name="offline" id="offline" value="yes"<?php echo $filters['offline'] === 'yes' ? ' checked' : ''; ?>><label for='offline'>Offline LAN</label></td></tr>
				<tr><td><input type="checkbox" name="free" id="free" value="yes"<?php echo $filters['free'] === 'yes' ? ' checked' : ''; ?>><label for='free'>Free</label></td></tr>
				<tr><td><input type="checkbox" name="standalone" id="standalone" value="yes"<?php echo $filters['standalone'] === 'yes' ? ' checked' : ''; ?>><label for='standalone'>Stand-alone</label></td></tr>
			</table>
			<p class="menusubheader bold">Sort by</p>
			<table class="menu">
				<tr><td>
					<select name="s1">
						<option value="title"<?php echo $sort['s1'] === 'title' ? ' selected' : ''; ?>>Title</option>
						<option value="p_limit"<?php echo $sort['s1'] === 'p_limit' ? ' selected' : ''; ?>>Player limit</option>
						<option value="p_samepc"<?php echo $sort['s1'] === 'p_samepc' ? ' selected' : ''; ?>>Local limit</option>
						<option value="genre"<?php echo $sort['s1'] === 'genre' ? ' selected' : ''; ?>>Genre</option>
						<option value="r_year"<?php echo $sort['s1'] === 'r_year' ? ' selected' : ''; ?>>Release year</option>
					</select>
					<select name="so1">
						<option value="ASC"<?php echo $sort['so1'] === 'ASC' ? ' selected' : ''; ?>>ASC</option>
						<option value="DESC"<?php echo $sort['so1'] === 'DESC' ? ' selected' : ''; ?>>DESC</option>
					</select>
				</td></tr>
			</table>
			<p><input type="submit" value="Find games"></p>
		</form>
			</div>

	<div class="content">
	<table class="gamelist">
		<tr>
			<th class='title'><a href="<?php echo sortUrl($_GET, 'title', 'ASC'); ?>">Title of Game/Mod/Expansion/DLC</a></th>
			<th class='p_limit'><a href="<?php echo sortUrl($_GET, 'p_limit', 'DESC'); ?>">Player limit</a></th>
			<th class='p_samepc'><a href="<?php echo sortUrl($_GET, 'p_samepc', 'DESC'); ?>">Local limit</a></th>
			<th class='genre'><a href="<?php echo sortUrl($_GET, 'genre', 'ASC'); ?>">Genre</a></th>
			<th class='subgenre'>Subgenre</th>
			<th class='r_year'><a href="<?php echo sortUrl($_GET, 'r_year', 'DESC'); ?>">Re-<br>lease</a></th>
			<th class='online'>On-<br>line</th>
			<th class='offline'>Off-<br>line</th>
			<th class='link'>Price</th>
			<?php if ($currentUser && isAdmin($currentUser['id'])): ?><th class='link'>Actions</th><?php endif; ?>
		</tr>
<?php foreach ($games as $game): ?>
<tr class='index'>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo h($game['title']); ?></a></td>
	<td class='right'><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo $game['p_limit']; ?></a></td>
	<td class='right'><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo $game['p_samepc']; ?></a></td>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo h($game['genre']); ?></a></td>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo h($game['subgenre'] ?? ''); ?></a></td>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo $game['r_year']; ?></a></td>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo $game['online'] ? 'Yes' : 'No'; ?></a></td>
	<td><a class='black' href='game.php?id=<?php echo $game['id']; ?>'><?php echo $game['offline'] ? 'Yes' : 'No'; ?></a></td>
	<td class='right'><?php if ($game['price']): ?><a href="<?php echo h($game['price_url'] ?? '#'); ?>"><?php echo h($game['price']); ?></a><?php endif; ?></td>
	<?php if ($currentUser && isAdmin($currentUser['id'])): ?><td class='right'><button onclick="toggleEditForm(<?php echo $game['id']; ?>)" style="background: #007cba; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer;">Edit</button></td><?php endif; ?>
</tr>
<?php if ($currentUser && isAdmin($currentUser['id'])): ?>
<tr id="edit-form-<?php echo $game['id']; ?>" style="display: none; background: #f9f9f9;" class="edit-form">
	<td colspan="<?php echo isset($currentUser) && isAdmin($currentUser['id']) ? '10' : '9'; ?>" style="padding: 20px;">
		<div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
			<h3 style="margin-top: 0; color: #333;">Edit Game: <?php echo h($game['title']); ?></h3>

			<form method="POST" action="index.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
				<input type="hidden" name="action" value="update_game">
				<input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Title:</label>
					<div style="display: flex; gap: 10px;">
						<input type="text" name="title" value="<?php echo h($game['title']); ?>" required style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" id="title-<?php echo $game['id']; ?>">
						<button type="button" onclick="scanIGDB(<?php echo $game['id']; ?>)" style="background: #17a2b8; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; white-space: nowrap;">Scan IGDB</button>
					</div>
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Slug:</label>
					<input type="text" name="slug" value="<?php echo h($game['slug']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Player Limit:</label>
					<input type="number" name="p_limit" value="<?php echo $game['p_limit']; ?>" min="1" max="999" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Same PC Limit:</label>
					<input type="number" name="p_samepc" value="<?php echo $game['p_samepc']; ?>" min="1" max="999" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Genre:</label>
					<input type="text" name="genre" value="<?php echo h($game['genre']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Subgenre:</label>
					<input type="text" name="subgenre" value="<?php echo h($game['subgenre'] ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Release Year:</label>
					<input type="number" name="r_year" value="<?php echo $game['r_year']; ?>" min="1990" max="2025" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div>
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Price:</label>
					<input type="text" name="price" value="<?php echo h($game['price'] ?? ''); ?>" placeholder="e.g. $29.99 or Free" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div style="grid-column: span 2;">
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Price URL:</label>
					<input type="url" name="price_url" value="<?php echo h($game['price_url'] ?? ''); ?>" placeholder="https://store.example.com" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div style="grid-column: span 2;">
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">Image URL:</label>
					<input type="url" name="image_url" value="<?php echo h($game['image_url'] ?? ''); ?>" placeholder="https://example.com/image.jpg" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
				</div>

				<div style="grid-column: span 2;">
					<label style="display: block; margin-bottom: 5px; font-weight: bold;">System Requirements:</label>
					<textarea name="system_requirements" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"><?php echo h($game['system_requirements'] ?? ''); ?></textarea>
				</div>

				<div style="grid-column: span 2;">
					<label style="font-weight: bold;">Capabilities:</label><br>
					<label style="margin-right: 15px;"><input type="checkbox" name="online" <?php echo $game['online'] ? 'checked' : ''; ?>> Online</label>
					<label><input type="checkbox" name="offline" <?php echo $game['offline'] ? 'checked' : ''; ?>> Offline LAN</label>
				</div>

				<div style="grid-column: span 2; text-align: center; margin-top: 15px;">
					<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-right: 10px;">Update Game</button>
					<button type="button" onclick="toggleEditForm(<?php echo $game['id']; ?>)" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Cancel</button>
					<form method="POST" action="index.php" style="display: inline; margin-left: 10px;">
						<input type="hidden" name="action" value="delete_game">
						<input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
						<button type="submit" onclick="return confirm('Are you sure you want to delete this game?')" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Delete Game</button>
					</form>
				</div>
			</form>
		</div>
	</td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
	</table>
	</div>
	</div>
	<div class='footer'><p>Copyright 2025 LAN Game List</p></div>
</div>

<!-- IGDB Search Modal -->
<div id="igdb-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div style="background: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #333;">Search IGDB</h2>
            <button onclick="closeIGDBModal()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Close</button>
        </div>

        <div style="margin-bottom: 20px;">
            <input type="text" id="igdb-search-input" placeholder="Search for games..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            <button onclick="searchIGDB()" style="background: #007cba; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;">Search</button>
        </div>

        <div id="igdb-results" style="display: none;">
            <h3>Search Results</h3>
            <div id="igdb-results-list" style="max-height: 400px; overflow-y: auto;"></div>
        </div>

        <div id="igdb-loading" style="display: none; text-align: center; padding: 20px;">
            <div style="border: 4px solid #f3f3f3; border-top: 4px solid #007cba; border-radius: 50%; width: 40px; height: 40px; animation: spin 2s linear infinite; margin: 0 auto 20px;"></div>
            <p>Searching IGDB...</p>
        </div>

        <div id="igdb-error" style="display: none; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-top: 15px;"></div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* IGDB Modal Improvements */
#igdb-modal {
    backdrop-filter: blur(4px);
}

#igdb-modal > div {
    max-width: 90vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* IGDB Loading State */
#igdb-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

#igdb-loading::before {
    content: '';
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color, #007cba);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* IGDB Results */
.igdb-result {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f9f9f9;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.igdb-result:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.igdb-result.selected {
    background: #d4edda;
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
}

.igdb-result-cover {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 15px;
    transition: transform 0.2s ease;
}

.igdb-result:hover .igdb-result-cover {
    transform: scale(1.05);
}

.igdb-result-info {
    flex: 1;
    min-width: 0;
}

.igdb-result-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 5px;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.igdb-result-year {
    color: #666;
    margin-bottom: 5px;
    font-size: 14px;
}

.igdb-result-genres {
    color: #007cba;
    font-size: 14px;
    margin-bottom: 8px;
}

.use-result-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
    min-width: 120px;
}

.use-result-btn:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.use-result-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
    animation: pulse 1.5s infinite;
}

/* IGDB Search Input */
#igdb-search-input {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#igdb-search-input:focus {
    border-color: var(--primary-color, #007cba);
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
    outline: none;
}

/* IGDB Error State */
#igdb-error {
    border-left: 4px solid #dc3545;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design for IGDB Modal */
@media (max-width: 768px) {
    #igdb-modal > div {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    .igdb-result {
        padding: 12px;
    }
    
    .igdb-result > div {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .igdb-result-cover {
        margin-right: 0;
        margin-bottom: 10px;
        align-self: center;
    }
    
    .igdb-result-title {
        font-size: 16px;
        white-space: normal;
    }
}

/* Dark mode support for IGDB modal */
[data-theme="dark"] .igdb-result {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] .igdb-result:hover {
    background: #4a5568;
}

[data-theme="dark"] .igdb-result.selected {
    background: #2f855a;
    border-color: #38a169;
}

[data-theme="dark"] .igdb-result-title {
    color: #f7fafc;
}

[data-theme="dark"] #igdb-search-input {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] #igdb-search-input:focus {
    border-color: #63b3ed;
    box-shadow: 0 0 0 2px rgba(99, 179, 237, 0.2);
}
</style>

<script>
let currentGameId = null;
function toggleEditForm(gameId) {
    const formRow = document.getElementById('edit-form-' + gameId);
    if (formRow.style.display === 'none' || formRow.style.display === '') {
        // Hide all other edit forms first
        const allEditForms = document.querySelectorAll('.edit-form');
        allEditForms.forEach(form => form.style.display = 'none');
        formRow.style.display = 'table-row';
    } else {
        formRow.style.display = 'none';
    }
}

// Close edit form when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.edit-form') && !event.target.closest('button')) {
        const allEditForms = document.querySelectorAll('.edit-form');
        allEditForms.forEach(form => form.style.display = 'none');
    }
});

// IGDB Functions
function scanIGDB(gameId) {
    currentGameId = gameId;
    const titleInput = document.getElementById('title-' + gameId);
    const searchInput = document.getElementById('igdb-search-input');

    if (titleInput && titleInput.value.trim()) {
        searchInput.value = titleInput.value.trim();
    }

    document.getElementById('igdb-modal').style.display = 'block';
    searchIGDB();
}

function closeIGDBModal() {
    document.getElementById('igdb-modal').style.display = 'none';
    document.getElementById('igdb-results').style.display = 'none';
    document.getElementById('igdb-loading').style.display = 'none';
    document.getElementById('igdb-error').style.display = 'none';
}

async function searchIGDB() {
    const searchInput = document.getElementById('igdb-search-input');
    const query = searchInput.value.trim();

    if (!query) {
        showIGDBError('Please enter a search term');
        return;
    }

    // Show loading state
    document.getElementById('igdb-loading').style.display = 'block';
    document.getElementById('igdb-results').style.display = 'none';
    document.getElementById('igdb-error').style.display = 'none';

    try {
        const response = await fetch('ajax_igdb.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=search_igdb&query=' + encodeURIComponent(query)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.error) {
            const errorMessage = data.message || data.error;
            showIGDBError(errorMessage);
            if (window.showToast) {
                window.showToast(`IGDB Search Error: ${errorMessage}`, 'error');
            }
        } else if (data.games) {
            displayIGDBResults(data.games);
            if (window.showToast) {
                window.showToast(`Found ${data.games.length} games from IGDB`, 'success');
            }
        } else {
            const errorMsg = 'Invalid response format from server';
            showIGDBError(errorMsg);
            if (window.showToast) {
                window.showToast(errorMsg, 'error');
            }
        }
    } catch (error) {
        console.error('IGDB Search Error:', error);
        const errorMsg = 'Failed to search IGDB. Please check your connection and try again.';
        showIGDBError(errorMsg);
        if (window.showToast) {
            window.showToast(errorMsg, 'error');
        }
    } finally {
        document.getElementById('igdb-loading').style.display = 'none';
    }
}

function showIGDBError(message) {
    const errorDiv = document.getElementById('igdb-error');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    document.getElementById('igdb-results').style.display = 'none';
}

function displayIGDBResults(games) {
    const resultsList = document.getElementById('igdb-results-list');
    resultsList.innerHTML = '';

    if (games.length === 0) {
        resultsList.innerHTML = '<p style="text-align: center; color: #666;">No games found. Try a different search term.</p>';
    } else {
        games.forEach(game => {
            const gameDiv = document.createElement('div');
            gameDiv.className = 'igdb-result';
            gameDiv.onclick = () => selectIGDBResult(game);

            // Build rating display
            const ratingDisplay = game.rating ? `<div style="margin-top: 5px; color: #28a745; font-weight: bold;">⭐ ${game.rating}/100 (${game.rating_count || 0} ratings)</div>` : '';

            gameDiv.innerHTML = `
                <div style="display: flex; align-items: flex-start;">
                    ${game.cover_url ? `<img src="${game.cover_url}" alt="${game.name}" class="igdb-result-cover" onerror="this.style.display='none'">` : '<div style="width: 80px; height: 80px; background: #ddd; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-right: 15px; color: #666; font-size: 12px;">No Image</div>'}
                    <div class="igdb-result-info">
                        <div class="igdb-result-title">${escapeHtml(game.name || game.title)}</div>
                        <div class="igdb-result-year">${game.release_year || 'Unknown Year'}</div>
                        <div class="igdb-result-genres">${(game.genres || []).join(', ') || 'No genres available'}</div>
                        ${ratingDisplay}
                        <div style="margin-top: 8px; color: #666; font-size: 14px;">${game.summary ? game.summary.substring(0, 150) + '...' : 'No description available'}</div>
                    </div>
                </div>
                <button class="use-result-btn" onclick="event.stopPropagation(); populateGameData(${game.igdbId || game.id})">Use This Game</button>
            `;

            resultsList.appendChild(gameDiv);
        });
    }

    document.getElementById('igdb-results').style.display = 'block';
}

function selectIGDBResult(game) {
    // Toggle selection
    const allResults = document.querySelectorAll('.igdb-result');
    allResults.forEach(result => result.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

async function populateGameData(igdbId) {
    // Show loading state on the button
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Loading...';
    button.disabled = true;

    try {
        const response = await fetch('ajax_igdb.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_igdb_details&id=' + igdbId
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.error) {
            const errorMessage = data.message || data.error;
            showIGDBError(errorMessage);
            if (window.showToast) {
                window.showToast(`IGDB Details Error: ${errorMessage}`, 'error');
            }
            return;
        }

        // Update form fields
        const form = document.querySelector(`#edit-form-${currentGameId} form`);
        
        if (!form) {
            throw new Error('Game form not found');
        }

        let fieldsUpdated = 0;

        // Basic fields
        if (data.title) {
            const titleField = form.querySelector('input[name="title"]');
            if (titleField) {
                titleField.value = data.title;
                fieldsUpdated++;
            }
        }
        if (data.slug) {
            const slugField = form.querySelector('input[name="slug"]');
            if (slugField) {
                slugField.value = data.slug;
                fieldsUpdated++;
            }
        }
        if (data.release_year) {
            const yearField = form.querySelector('input[name="r_year"]');
            if (yearField) {
                yearField.value = data.release_year;
                fieldsUpdated++;
            }
        }
        if (data.genre) {
            const genreField = form.querySelector('input[name="genre"]');
            if (genreField) {
                genreField.value = data.genre;
                fieldsUpdated++;
            }
        }
        if (data.subgenre) {
            const subgenreField = form.querySelector('input[name="subgenre"]');
            if (subgenreField) {
                subgenreField.value = data.subgenre;
                fieldsUpdated++;
            }
        }

        // Checkboxes
        const onlineField = form.querySelector('input[name="online"]');
        const offlineField = form.querySelector('input[name="offline"]');
        if (onlineField) {
            onlineField.checked = data.online || false;
            fieldsUpdated++;
        }
        if (offlineField) {
            offlineField.checked = data.offline || false;
            fieldsUpdated++;
        }

        // Optional fields
        if (data.image_url) {
            const imageField = form.querySelector('input[name="image_url"]');
            if (imageField) {
                imageField.value = data.image_url;
                fieldsUpdated++;
            }
        }

        if (data.system_requirements) {
            const reqField = form.querySelector('textarea[name="system_requirements"]');
            if (reqField) {
                reqField.value = data.system_requirements;
                fieldsUpdated++;
            }
        }

        // Close modal
        closeIGDBModal();

        // Show success message
        const successMsg = `Game data populated successfully! Updated ${fieldsUpdated} fields.`;
        if (window.showToast) {
            window.showToast(successMsg, 'success');
        } else {
            alert(successMsg);
        }
    } catch (error) {
        console.error('IGDB Details Error:', error);
        const errorMsg = 'Failed to get game details from IGDB. Please try again.';
        showIGDBError(errorMsg);
        if (window.showToast) {
            window.showToast(errorMsg, 'error');
        }
    } finally {
        // Restore button state
        button.textContent = originalText;
        button.disabled = false;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-search when typing (debounced)
let searchTimeout;
document.getElementById('igdb-search-input').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.trim().length >= 3) {
            searchIGDB();
        }
    }, 500);
});
</script>

</body></html>
