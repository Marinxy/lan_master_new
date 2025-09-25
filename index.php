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
<html>
<head>
	<title>LAN Game List - PC Game info for LAN parties and eSports</title>
	<meta charset="UTF-8">
	<meta name="author" content="Felix Klastrup, Emvevi">
	<meta name="description" content="All the game info you need to find games for your next multiplayer session, including max players, genres, release years, off- and on-line capabilites and prices for each game.">
	<meta property="og:image" content="logo1_icon_100x100.png">
	<link rel="stylesheet" type="text/css" href="includes/style.css">
	<script src='recaptcha/api.js'></script>
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
			<a href='#'>ADD GAME</a> - <a href='#'>ABOUT</a> - <a href='#'>CONTACT</a>		</div>
		<div class='right'>
			<div class='inline-block'>
				<span class='ticker'><?php echo substr($gameCount, 0, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 1, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 2, 1); ?></span></div><div class='inline-block'>&nbsp;games in database</div>		</div>
		</div>
		<h1>LAN Game List</h1>

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
</tr>
<?php endforeach; ?>
	</table>
	</div>
	</div>
	<div class='footer'><p>Copyright 2025 LAN Game List</p></div></div></body></html>
