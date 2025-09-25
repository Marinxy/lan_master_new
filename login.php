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

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get current user
$currentUser = getCurrentUser();
$gameCount = getGameCount();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    // Validation
    if (empty($username) || empty($password)) {
        $message = 'Username and password are required';
        $messageType = 'error';
    } else {
        // Attempt login
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Create session
            $sessionToken = createUserSession($result['user']['id']);
            
            if ($sessionToken) {
                // Set session
                $_SESSION['session_token'] = $sessionToken;
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $result['user']['username'];
                
                // Set remember me cookie if requested (30 days)
                if ($rememberMe) {
                    setcookie('remember_token', $sessionToken, time() + (30 * 24 * 60 * 60), '/');
                }
                
                $message = 'Login successful! Redirecting...';
                $messageType = 'success';
                
                // Redirect to main page
                header('refresh:1;url=index.php');
                exit;
            } else {
                $message = 'Session creation failed. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - LAN Game List</title>
    <meta charset="UTF-8">
    <meta name="author" content="LAN Game List">
    <meta name="description" content="Login to LAN Game List account">
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
            <a href='index.php'>GAMES</a> - <a href='#'>ABOUT</a> - <a href='#'>CONTACT</a>        </div>
        <div class='right'>
            <div class='inline-block'>
                <span class='ticker'><?php echo substr($gameCount, 0, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 1, 1); ?></span><span class='ticker'><?php echo substr($gameCount, 2, 1); ?></span></div><div class='inline-block'>&nbsp;games in database</div>        </div>
    </div>
    <h1>Login</h1>

    <div class="midsection">    
        <div class="content" style="max-width: 400px; margin: 0 auto;">
            
            <?php if ($message): ?>
                <div style="margin: 20px 0; padding: 10px; border-radius: 5px; <?php 
                    echo $messageType === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; 
                ?>">
                    <?php echo h($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #333;">Login to Your Account</h2>
                
                <div style="margin-bottom: 15px;">
                    <label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username:</label>
                    <input type="text" id="username" name="username" maxlength="50" required 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
                           value="<?php echo isset($_POST['username']) ? h($_POST['username']) : ''; ?>">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
                    <input type="password" id="password" name="password" maxlength="255" required 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px;">
                        <input type="checkbox" name="remember_me" value="1" style="margin-right: 5px;"> 
                        Remember me (30 days)
                    </label>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        Login
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="signup.php" style="color: #007cba;">Sign up here</a></p>
                <p><a href="#" style="color: #666; font-size: 14px;">Forgot your password?</a></p>
            </div>
            
        </div>
    </div>
    
    <div class='footer'><p>Copyright 2025 LAN Game List</p></div>
</div>
</body>
</html>
