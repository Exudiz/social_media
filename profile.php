<?php
require_once 'config.php';
require_once 'functions.php';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

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
              WHERE (p.user_id=? OR (p.user_id=? AND p.visibility = 0) OR (p.visibility = 1) OR (p.visibility = 2 AND p.user_id IN (SELECT followee_id FROM followers WHERE follower_id = ?)))
              GROUP BY p.id
              ORDER BY p.created_at DESC";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "iii", $profile_user_id, $_SESSION['user_id'], $_SESSION['user_id']);
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile/style.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="banner-image" style="position: relative;">
            <?php if (!empty($userLogo)): ?>
                <div class="logo-overlay" onclick="document.getElementById('logo-form').submit();">
                    <img src="<?php echo $userLogo; ?>" alt="Profile Logo" class="logo-image"> <?php echo $username; ?>
                </div>
            <?php else: ?>
                <div class="logo-overlay" onclick="document.getElementById('logo-form').submit();">
                    <img src="uploads/default-logo.png" alt="Default Logo" class="logo-image">
                </div>
            <?php endif; ?>

            <?php if (!empty($userBanner)): ?>
                <div class="banner-overlay" onclick="document.getElementById('banner-form').submit();">
                    <img src="<?php echo $userBanner; ?>" alt="Profile Banner" class="banner-image">
                </div>
            <?php else: ?>
                <div class="banner-overlay" onclick="document.getElementById('banner-form').submit();">
                    <img src="uploads/default-banner.png" alt="Default Banner" class="banner-image">
                </div>
            <?php endif; ?>
        </div>

        <div class="username">
            <h3><?php echo $username; ?></h3>
        </div>
        <div class="follow-count">
            <a href="followers.php?user_id=<?php echo $profile_user_id; ?>">Followers</a> (<?php echo $followers_count; ?>)
            <a href="following.php?user_id=<?php echo $profile_user_id; ?>">Following</a> (<?php echo $following_count; ?>)
        </div>

        <h3>Your Posts</h3>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?user_id=". $profile_user_id . "&tab=posts"); ?>" method="POST">
            <div class="form-group">
                <textarea class="form-control" name="post_content" rows="3" placeholder="Write something..." required></textarea>
            </div>
            <div class="form-group">
                <label for="post_visibility">Visibility:</label>
                <select class="form-control" name="post_visibility" id="post_visibility">
                    <option value="public">Public</option>
                    <option value="only_me">Only Me</option>
                    <option value="followers_only">Followers Only</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="add_post">Add Post</button>
        </form>

        <h3>Your Wall</h3>

        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                              <?php if (!empty($post['logo'])): ?>
                                  <img src="<?php echo $post['logo']; ?>" alt="Profile Logo" class="user-logo">
                              <?php endif; ?>
                                <?php echo htmlspecialchars($post['username']); ?>
                                <small><?php echo time_ago($post['created_at']); ?></small>
                            </h5>
                            <span class="visibility-info">
                                <?php
                                    $visibilityLabel = "";
                                    switch ($post['visibility']) {
                                        case 0:
                                            $visibilityLabel = "Only Me";
                                            break;
                                        case 1:
                                            $visibilityLabel = "Public";
                                            break;
                                        case 2:
                                            $visibilityLabel = "Followers Only";
                                            break;
                                        default:
                                            $visibilityLabel = "Unknown";
                                            break;
                                    }
                                    echo "(" . $visibilityLabel . ")";
                                ?>
                            </span>
                            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                <div>
                                    <a href="edit_post.php?post_id=<?php echo $post['id']; ?>">Edit Post</a>
                                    <a href="delete_post.php?post_id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="card-text"><?php echo htmlspecialchars($post['content']); ?></p>
                    </div>
                    <?php if (isset($post['hashtags'])): ?>
                        <div class="hashtags">
                            <?php foreach ($post['hashtags'] as $hashtag): ?>
                                <a href="hashtag.php?tag=<?php echo urlencode($hashtag['tag']); ?>">
                                    <span class="hashtag"><?php echo htmlspecialchars($hashtag['tag']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($post['comments'])): ?>
                        <div class="card-footer">
                            <h6>Comments:</h6>
                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="card comment-card">
                                    <div class="card-header">
                                        <?php if (!empty($comment['logo'])): ?>
                                            <img src="<?php echo $comment['logo']; ?>" alt="Profile Logo" class="user-logo">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                        <small><?php echo time_ago($comment['created_at']); ?></small>
                                        <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                                            <div>
                                                <a href="edit_comment.php?comment_id=<?php echo $comment['id']; ?>">Edit Comment</a>
                                                <a href="delete_comment.php?comment_id=<?php echo $comment['id']; ?>" onclick="return confirm('Are you sure you want to delete this comment?')">Delete Comment</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-footer">
                        <form action="add_comment.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="form-group">
                                <textarea class="form-control" name="comment_content" rows="1" placeholder="Write a comment..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="add_comment">Add Comment</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>

        <form id="logo-form" action="profile_upload.php" method="POST">
            <input type="hidden" name="type" value="logo">
        </form>

        <form id="banner-form" action="profile_upload.php" method="POST">
            <input type="hidden" name="type" value="banner">
        </form>

    <?php endif; ?>
</div>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
