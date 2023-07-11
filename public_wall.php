<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    if (basename($_SERVER['PHP_SELF']) !== 'public_wall.php') {
        header("Location: public_wall.php");
        exit();
    }
}

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch posts from the users being followed by the logged-in user
$loggedInUserId = $_SESSION['user_id'];
$sql = "SELECT posts.*, users.username FROM posts
        INNER JOIN followers ON posts.user_id = followers.followee_id
        INNER JOIN users ON posts.user_id = users.id
        WHERE followers.follower_id = '$loggedInUserId'
        ORDER BY posts.created_at DESC";
$result = mysqli_query($conn, $sql);
$posts = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Social Media Website</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <?php if (!empty($posts)) { ?>
        <h2>Public Wall</h2>
        <?php foreach ($posts as $post) { ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $post['username']; ?></h5>
                    <p class="card-text"><?php echo $post['content']; ?></p>
                    <p class="card-text"><small><?php echo $post['created_at']; ?></small></p>
                    <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">View Profile</a>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <h2>No posts to display on the public wall.</h2>
    <?php } ?>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
