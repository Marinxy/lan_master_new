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

$game_id = $_GET['id'] ?? null;

if (!$game_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$game) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching game: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo h($game['title']); ?> - LAN Game List</title>
	<meta charset="UTF-8">
	<meta name="author" content="Felix Klastrup, Emvevi">
	<meta name="description" content="LAN/Multiplayer game info for <?php echo h($game['title']); ?>">
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
		<h1><?php echo h($game['title']); ?></h1>

<div class='gamedata'>

		<div class='viewimage'>
		<?php if ($game['image_url']): ?>
		<img class='large' src='<?php echo h($game['image_url']); ?>'>
		<?php else: ?>
		<div class='large placeholder'>No image available</div>
		<?php endif; ?>
		</div>	<table class="edit">
		<col class="view_inner_col1">
		<col class="view_inner_col2">
		<tbody>
		<tr>
			<td>Multiplayer limit</td>
			<td><?php echo $game['p_limit']; ?></td>
		</tr>
		<tr>
			<td>Same PC limit</td>
			<td><?php echo $game['p_samepc']; ?></td>
		</tr>
		<tr>
			<td>Genre</td>
			<td><?php echo h($game['genre']); ?></td>
		</tr>
		<tr>
			<td>Subgenre</td>
			<td><?php echo h($game['subgenre'] ?? ''); ?></td>
		</tr>
		<tr>
			<td>Release</td>
			<td><?php echo $game['r_year']; ?></td>
			</tr>
		<tr>
			<td>Online</td>
			<td><?php echo $game['online'] ? 'Yes' : 'No'; ?></td>
		</tr>
		<tr>
			<td>Offline LAN</td>
			<td><?php echo $game['offline'] ? 'Yes' : 'No'; ?></td>
		</tr>
		
		<?php if ($game['price']): ?>
		<tr>
			<td>Price</td>
			<td><?php if ($game['price_url']): ?><a href='<?php echo h($game['price_url']); ?>'><?php echo h($game['price']); ?></a><?php else: ?><?php echo h($game['price']); ?><?php endif; ?></td>
		</tr>
		<?php endif; ?>
		
		<?php if ($game['system_requirements']): ?>
		<tr>
			<td>System Requirements</td>
			<td><?php echo h($game['system_requirements']); ?></td>
		</tr>
		<?php endif; ?>
				</tbody>
	</table>
</div>
	<div class='footer'><p>Copyright 2025 LAN Game List</p></div></div></body></html>
