<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Fetch the username from the session or database
$username = isset($_SESSION['user_info']['username']) ? $_SESSION['user_info']['username'] : '';

// Fetch the user's profile logo from the session or set a default logo
$userLogo = isset($_SESSION['user_logo']) ? $_SESSION['user_logo'] : 'uploads/default-logo.png';

// Fetch user's information
$userInfo = isset($_SESSION['user_info']) ? $_SESSION['user_info'] : array();

// Database connection
$conn = get_db_connection();

// Fetch the count of notifications for the current user
$notificationCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $notificationCount = $row['count'];
}

// Close the database connection
mysqli_close($conn);
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
                        <img src="<?php echo $userLogo; ?>" alt="Profile Logo" width="30" height="30" class="mr-2">
                        <?php echo $username; ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/profile.php">Profile</a>
                        <a class="dropdown-item" href="/settings.php">Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/about.php">About</a>
                        <a class="dropdown-item" href="/profile_upload.php">Upload</a>
                        <a class="dropdown-item" href="/message.php">Message</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                        <?php if (basename($_SERVER['PHP_SELF']) !== 'search.php'): ?>
                            <a class="dropdown-item" href="search.php">Search</a>
                        <?php endif; ?>

                        <!-- Recent Activity Dropdown -->
                        <div class="dropdown-divider"></div>
                        <?php if (basename($_SERVER['PHP_SELF']) !== 'recent_activity.php'): ?>
                            <a class="dropdown-item" href="recent_activity.php">Recent Activity</a>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">
                        Notifications<?php if ($notificationCount > 0) : ?>
                            <span class="badge badge-primary"><?php echo $notificationCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
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
