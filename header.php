<?php
require_once 'config.php';

// Fetch the username from the session or database
$username = isset($_SESSION['user_info']['username']) ? $_SESSION['user_info']['username'] : '';

// Fetch the user's profile logo from the session or set a default logo
$userLogo = isset($_SESSION['user_logo']) ? $_SESSION['user_logo'] : 'uploads/default-logo.png';

// Fetch user's information
$userInfo = isset($_SESSION['user_info']) ? $_SESSION['user_info'] : array();
?>

<link rel="stylesheet" href="css/header.css">

<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php">Logo</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <?php echo $userInfo['username']; ?>
                        <img src="<?php echo $userLogo; ?>" alt="Profile Logo" width="30" height="30" class="mr-2">
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/profile.php">Profile</a>
                        <a class="dropdown-item" href="/settings.php">Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/about.php">About</a>
                        <a class="dropdown-item" href="/profile_upload.php">Upload</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
                <?php if (basename($_SERVER['PHP_SELF']) !== 'search.php'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Search Users</a>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Login/Register</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>
</nav>
