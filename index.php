<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';
require_once 'utils/signup_login.php';

// Initialize $profile_user_id variable to avoid undefined variable warnings
$profile_user_id = null;

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    if (isset($_SESSION['user_id'])) {
        $profile_user_id = $_SESSION['user_id'];
    } else {
        // Handle the case when 'user_id' is not set in the session
        // For example, redirect to the login page or display an error message.
        // You can also set a default user ID here if applicable.
    }
} else {
    $profile_user_id = $_GET['user_id'];
}

// Database connection
$conn = get_db_connection();

// Fetch the posts of the users that the current user is following
$sql_posts = "SELECT p.*, COUNT(pl.user_id) AS likes
              FROM posts p
              LEFT JOIN post_likes pl ON p.id = pl.post_id
              WHERE (p.user_id = ? OR
                     (p.user_id = ? AND p.visibility = 0) OR
                     (p.visibility = 1) OR
                     (p.visibility = 2 AND p.user_id IN
                      (SELECT followee_id FROM followers WHERE follower_id = ?) AND p.user_id = ?) OR
                     (p.user_id IN
                      (SELECT followee_id FROM followers WHERE follower_id = ?) AND p.visibility = 1))
              GROUP BY p.id
              ORDER BY p.created_at DESC";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "iiiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
mysqli_stmt_execute($stmt_posts);
$result_posts = mysqli_stmt_get_result($stmt_posts);
$posts = array();
if (mysqli_num_rows($result_posts) > 0) {
    while ($row_posts = mysqli_fetch_assoc($result_posts)) {
        // Fetch the comments for each post
        $post_id = $row_posts['id'];
        $comments = get_comments($conn, $post_id);
        $row_posts['comments'] = $comments;
        $posts[] = $row_posts;
    }
}

// Display the posts
foreach ($posts as $post) {
    // Display post details
    echo "Post ID: " . $post['id'] . "<br>";
    echo "Content: " . $post['content'] . "<br>";
    echo "Likes: " . $post['likes'] . "<br>";
    // Display comments for each post
    echo "Comments: <br>";
    foreach ($post['comments'] as $comment) {
        echo "Comment ID: " . $comment['id'] . "<br>";
        echo "Content: " . $comment['content'] . "<br>";
        echo "<br>";
    }
    echo "<br>";
}

// Fetch user profile information
$username = ''; // Initialize $username variable
if (isset($_SESSION['user_row'])) {
    $row = $_SESSION['user_row'];
    if (isset($row['username'])) {
        $username = $row['username'];
    } else {
        $error = "Username not found!";
    }
} else {
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_row'] = $row;
        if (isset($row['username'])) {
            $username = $row['username'];
        } else {
            $error = "Username not found!";
        }
    } else {
        $error = "User profile not found!";
    }
    $stmt->close();
}

// Fetch user's information
$userInfo = array();
if (isset($_SESSION['user_info'])) {
    $userInfo = $_SESSION['user_info'];
} else {
    $sql_user_info = "SELECT * FROM users WHERE id=?";
    $stmt_user_info = $conn->prepare($sql_user_info);
    $stmt_user_info->bind_param("i", $profile_user_id);
    $stmt_user_info->execute();
    $result_user_info = $stmt_user_info->get_result();
    if ($result_user_info->num_rows > 0) {
        $row_user_info = $result_user_info->fetch_assoc();
        $userInfo = $row_user_info;
        $_SESSION['user_info'] = $userInfo;
    }
    $stmt_user_info->close();
}

$conn->close();

include 'template/index_template.php';
?>
