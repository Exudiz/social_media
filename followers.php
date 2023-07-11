<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    header("Location: index.php");
    exit();
}

$profile_user_id = $_GET['user_id'];

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch user profile information
$sql = "SELECT * FROM users WHERE id='$profile_user_id'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $username = $row['username'];
} else {
    $error = "User profile not found!";
}

// Fetch followers
$sql_followers = "SELECT users.id, users.username FROM users
                INNER JOIN followers ON users.id = followers.follower_id
                WHERE followers.followee_id = '$profile_user_id'";
$result_followers = mysqli_query($conn, $sql_followers);
$followers = [];
if (mysqli_num_rows($result_followers) > 0) {
    while ($row = mysqli_fetch_assoc($result_followers)) {
        $followers[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Followers</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Followers</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } else { ?>
        <?php if (!empty($followers)) { ?>
            <ul>
                <?php foreach ($followers as $follower) { ?>
                    <li><a href="profile.php?user_id=<?php echo $follower['id']; ?>"><?php echo $follower['username']; ?></a></li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No followers.</p>
        <?php } ?>
    <?php } ?>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
