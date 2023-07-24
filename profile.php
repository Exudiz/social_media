<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    $profile_user_id = $_SESSION['user_id'];
} else {
    $profile_user_id = $_GET['user_id'];
}

// Database connection
$conn = get_db_connection();

// Fetch user's profile information from the database
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $profile_user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $username = $user['username'];
    $userLogo = $user['logo'];
    $userBanner = $user['banner'];
} else {
    $error = "User profile not found!";
}

// Fetch followers count
$sql_followers_count = "SELECT COUNT(*) AS count FROM followers WHERE followee_id = ?";
$stmt_followers_count = mysqli_prepare($conn, $sql_followers_count);
mysqli_stmt_bind_param($stmt_followers_count, "i", $profile_user_id);
mysqli_stmt_execute($stmt_followers_count);
$result_followers_count = mysqli_stmt_get_result($stmt_followers_count);
$followers_count = 0;

if (mysqli_num_rows($result_followers_count) > 0) {
    $row = mysqli_fetch_assoc($result_followers_count);
    $followers_count = $row['count'];
}

// Fetch following count
$sql_following_count = "SELECT COUNT(*) AS count FROM followers WHERE follower_id = ?";
$stmt_following_count = mysqli_prepare($conn, $sql_following_count);
mysqli_stmt_bind_param($stmt_following_count, "i", $profile_user_id);
mysqli_stmt_execute($stmt_following_count);
$result_following_count = mysqli_stmt_get_result($stmt_following_count);
$following_count = 0;

if (mysqli_num_rows($result_following_count) > 0) {
    $row = mysqli_fetch_assoc($result_following_count);
    $following_count = $row['count'];
}

// Fetch the posts
$sql_posts = "SELECT p.*, COUNT(pl.user_id) AS likes
              FROM posts p
              LEFT JOIN post_likes pl ON p.id = pl.post_id
              WHERE (p.user_id = ? OR
                     (p.user_id = ? AND p.visibility = 0) OR
                     (p.visibility = 1 AND p.user_id = ?) OR
                     (p.visibility = 2 AND p.user_id = ?) OR
                     (p.user_id IN
                      (SELECT followee_id FROM followers WHERE follower_id = ?) AND p.visibility = 1))
              GROUP BY p.id
              ORDER BY p.created_at DESC";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "iiiii", $profile_user_id, $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
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

// Fetch the user's logo
$sql_user_logo = "SELECT logo FROM users WHERE id = ?";
$stmt_user_logo = mysqli_prepare($conn, $sql_user_logo);
mysqli_stmt_bind_param($stmt_user_logo, "i", $profile_user_id);
mysqli_stmt_execute($stmt_user_logo);
$result_user_logo = mysqli_stmt_get_result($stmt_user_logo);
$user_logo = '';

if (mysqli_num_rows($result_user_logo) > 0) {
    $row_user_logo = mysqli_fetch_assoc($result_user_logo);
    $user_logo = $row_user_logo['logo'];
}

// Handle adding a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_post'])) {
    $postContent = $_POST['post_content'];
    $postVisibility = $_POST['post_visibility'];

    // Extract hashtags from the post content
    preg_match_all('/#\w+\b/', $postContent, $matches);
    $hashtags = $matches[0];

    // Check if the post content is not empty
    if (!empty($postContent)) {
        // Map the visibility option to the corresponding visibility value
        $visibilityMap = [
            'public' => 1,  // Public - anyone can see the post
            'only_me' => 0,  // Only Me - only the user can see the post
            'followers_only' => 2  // Followers Only - only the user's followers can see the post
        ];

        // Set the visibility value based on the selected option
        $visibility = isset($visibilityMap[$postVisibility]) ? $visibilityMap[$postVisibility] : 1;  // Default to Public if the option is not recognized

        // Insert the post into the database with the specified visibility
        $post_id = add_post($conn, $profile_user_id, $username, $postContent, $visibility);

        if ($post_id) {
            // Insert the hashtags into the database
            foreach ($hashtags as $tag) {
                $tag = strtolower($tag);
                add_hashtag($conn, $post_id, $tag);
            }

            $message = "Post added successfully!";
            // Refresh the page after adding a post to display the updated list of posts
            header("Refresh: 0");
        } else {
            $error = "Error adding post: " . mysqli_error($conn);
        }
    } else {
        $error = "Post content cannot be empty!";
    }
}

// Handle the like request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_post'])) {
    $post_id = $_POST['post_id'];

    // Update the likes count for the post
    $success = like_post($conn, $post_id, $_SESSION['user_id']);

    if ($success) {
        // Refresh the page after liking a post to display the updated number of likes
        header("Refresh: 0");
    } else {
        $error = "Failed to like the post.";
    }
}

// Fetch hashtags for each post
$hashtags = array();

foreach ($posts as &$post) {
    $post_id = $post['id'];
    $hashtags = get_post_hashtags($conn, $post_id);
    $post['hashtags'] = $hashtags;
}

unset($post); // Unset the reference variable

mysqli_close($conn);

include 'template/profile_template.php';
?>
