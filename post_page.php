<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the post ID is provided in the URL
if (!isset($_GET['post_id'])) {
    header("Location: index.php"); // Redirect to the homepage if no post ID is provided
    exit();
}

// Get the post ID from the URL
$post_id = $_GET['post_id'];

// Database connection
$conn = get_db_connection();

// Fetch the post details from the database
$post = getPostDetails($conn, $post_id);

// Check if the post exists
if (!$post) {
    header("Location: index.php"); // Redirect to the homepage if the post doesn't exist
    exit();
}

// Fetch comments for the post
$comments = getCommentsForPost($conn, $post_id);

// Function to fetch post details from the database
function getPostDetails($conn, $post_id) {
    // Implement the database query to fetch the post details based on the post_id
    // Your code to fetch the post details goes here
    // For example:
    // $sql = "SELECT * FROM posts WHERE id = ?";
    // $stmt = mysqli_prepare($conn, $sql);
    // mysqli_stmt_bind_param($stmt, "i", $post_id);
    // mysqli_stmt_execute($stmt);
    // $result = mysqli_stmt_get_result($stmt);
    // $post = mysqli_fetch_assoc($result);
    // return $post;
}

// Function to fetch comments for a post from the database
function getCommentsForPost($conn, $post_id) {
    // Implement the database query to fetch the comments for the specified post_id
    // Your code to fetch comments goes here
    // For example:
    // $sql = "SELECT * FROM comments WHERE post_id = ?";
    // $stmt = mysqli_prepare($conn, $sql);
    // mysqli_stmt_bind_param($stmt, "i", $post_id);
    // mysqli_stmt_execute($stmt);
    // $result = mysqli_stmt_get_result($stmt);
    // $comments = array();
    // while ($row = mysqli_fetch_assoc($result)) {
    //     $comments[] = $row;
    // }
    // return $comments;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Page</title>
    <!-- Add your CSS and JavaScript links here -->
</head>
<body>
    <!-- Display the post content and details -->
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p><?php echo htmlspecialchars($post['content']); ?></p>
    <!-- Display comments -->
    <h2>Comments</h2>
    <?php if (count($comments) > 0): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li><?php echo htmlspecialchars($comment['content']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
</body>
</html>
