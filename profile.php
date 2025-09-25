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

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$currentUser = getCurrentUser();
$gameCount = getGameCount();

// Get full user details
$userDetails = getUserById($currentUser['id']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = 'Email is required';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address';
            $messageType = 'error';
        } else {
            // Check if email is already taken by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $currentUser['id']]);
            if ($stmt->fetch()) {
                $message = 'Email address is already in use';
                $messageType = 'error';
            } else {
                // Update email
                $stmt = $db->prepare("UPDATE users SET email = ?, updated_at = datetime('now') WHERE id = ?");
                if ($stmt->execute([$email, $currentUser['id']])) {
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                    $userDetails['email'] = $email;
                } else {
                    $message = 'Failed to update profile';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - LAN Game List</title>
    <meta charset="UTF-8">
    <meta name="author" content="LAN Game List">
    <meta name="description" content="User profile page">
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
                <span style="color: #007cba;">Welcome, <?php echo h($currentUser['username']); ?>!</span> |
                <a href='profile.php'>Profile</a> |
                <a href='logout.php'>Logout</a>
            </div>
        </div>
        <div class='headermenu'>
            <a href='index.php'>GAMES</a> - <a href='#'>ABOUT</a> - <a href='#'>CONTACT</a>        </div>
        <div class='right'>
            <div class='inline-block'>
                <span class='ticker'><?php echo substr($gameCount, 0, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 1, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 2, 1); ?></span></div><div class='inline-block'>&nbsp;games in database</div>        </div>
    </div>
    <h1>My Profile</h1>

    <div class="midsection">    
        <div class="content" style="max-width: 600px; margin: 0 auto;">
            
            <?php if ($message): ?>
                <div style="margin: 20px 0; padding: 10px; border-radius: 5px; <?php 
                    echo $messageType === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; 
                ?>">
                    <?php echo h($message); ?>
                </div>
            <?php endif; ?>

            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #333;">Account Information</h2>
                
                <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 5px;">
                    <h3 style="margin-top: 0; color: #555;">Profile Details</h3>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>Username:</strong> <?php echo h($userDetails['username']); ?>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>Email:</strong> <?php echo h($userDetails['email']); ?>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($userDetails['created_at'])); ?>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>Last Login:</strong> <?php 
                            echo $userDetails['last_login'] ? date('F j, Y \a\t g:i A', strtotime($userDetails['last_login'])) : 'Never';
                        ?>
                    </div>
                </div>
                
                <form method="POST" action="profile.php" style="background: white; padding: 15px; border-radius: 5px;">
                    <h3 style="margin-top: 0; color: #555;">Update Email</h3>
                    
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div style="margin-bottom: 15px;">
                        <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">New Email Address:</label>
                        <input type="email" id="email" name="email" maxlength="100" required 
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
                               value="<?php echo h($userDetails['email']); ?>">
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                            Update Email
                        </button>
                    </div>
                </form>
                
                <div style="margin-top: 20px; text-align: center;">
                    <p><a href="index.php" style="color: #007cba;">← Back to Game List</a></p>
                </div>
            </div>
            
        </div>
    </div>
    
    <div class='footer'><p>Copyright 2025 LAN Game List</p></div>
</div>
</body>
</html>
