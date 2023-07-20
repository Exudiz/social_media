<?php
// following.php

// Include the necessary files
require_once 'config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the user_id parameter is present in the URL
if (isset($_GET['user_id'])) {
    $follower_id = $_GET['user_id'];

    // Create a new database connection
    $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch the user's username from the database
    $sql_user = "SELECT username FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($conn, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "i", $follower_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    if (mysqli_num_rows($result_user) > 0) {
        $user = mysqli_fetch_assoc($result_user);
        $follower_username = $user['username'];

        // Fetch the following list from the database
        $sql_following = "SELECT users.id, users.username FROM users INNER JOIN followers ON users.id = followers.followee_id WHERE followers.follower_id = ?";
        $stmt_following = mysqli_prepare($conn, $sql_following);
        mysqli_stmt_bind_param($stmt_following, "i", $follower_id);
        mysqli_stmt_execute($stmt_following);
        $result_following = mysqli_stmt_get_result($stmt_following);
        $following = mysqli_fetch_all($result_following, MYSQLI_ASSOC);

        mysqli_free_result($result_following);
    } else {
        // Handle user not found error
        $error = "User not found!";
    }

    mysqli_close($conn); // Close the database connection
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Following</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2><?php echo $follower_username; ?>'s Following</h2>
    <?php if (isset($following)): ?>
        <ul>
            <?php foreach ($following as $followee): ?>
                <li><a href="user_profile.php?user_id=<?php echo $followee['id']; ?>"><?php echo $followee['username']; ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Not following anyone.</p>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
