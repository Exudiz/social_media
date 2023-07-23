<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

// Function to get the list of users the current user is following
function getFollowing($conn, $user_id) {
    $following = array();
    $sql_following = "SELECT followee_id FROM followers WHERE follower_id = ?";
    $stmt_following = mysqli_prepare($conn, $sql_following);
    mysqli_stmt_bind_param($stmt_following, "i", $user_id);
    mysqli_stmt_execute($stmt_following);
    $result_following = mysqli_stmt_get_result($stmt_following);
    while ($row = mysqli_fetch_assoc($result_following)) {
        $following[] = $row['followee_id'];
    }
    return $following;
}

// Fetch the list of users the current user is following
$following_users = getFollowing($conn, $_SESSION['user_id']);
$following_users[] = $_SESSION['user_id']; // Include the current user in the posts

// Fetch posts from all users and the followed users
$sql_posts = "SELECT p.*, u.username
              FROM posts p
              INNER JOIN users u ON p.user_id = u.id
              WHERE (p.user_id IN (" . implode(',', $following_users) . ") OR p.visibility = 1)  -- Show posts from followed users or public posts (visibility = 1)
              ORDER BY p.created_at DESC";
$result_posts = mysqli_query($conn, $sql_posts);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Public Wall</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<!-- Public wall content -->
<div class="container">
    <?php if (mysqli_num_rows($result_posts) > 0): ?>
        <?php while ($post = mysqli_fetch_assoc($result_posts)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $post['username']; ?></h5>
                    <p class="card-text"><?php echo $post['content']; ?></p>
                    <p class="card-text"><small><?php echo $post['created_at']; ?></small></p>
                    <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">View Profile</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <h2>No posts to display on the public wall.</h2>
    <?php endif; ?>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
