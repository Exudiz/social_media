<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Function to establish a database connection
function connectDatabase() {
    global $dbConfig;

    $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

// Function to validate and sanitize user input
function sanitize_input($input) {
    return htmlspecialchars(trim($input));
}

// Function to check if the logged-in user is following the profile user
function check_follow_status($follower_id, $followee_id) {
    $conn = connectDatabase();

    $sql_check_follow = "SELECT COUNT(*) AS count FROM followers WHERE follower_id = ? AND followee_id = ?";
    $stmt_check_follow = mysqli_prepare($conn, $sql_check_follow);
    mysqli_stmt_bind_param($stmt_check_follow, "ii", $follower_id, $followee_id);
    mysqli_stmt_execute($stmt_check_follow);
    $result_check_follow = mysqli_stmt_get_result($stmt_check_follow);

    if ($result_check_follow) {
        $row_check_follow = mysqli_fetch_assoc($result_check_follow);
        mysqli_close($conn);
        return ($row_check_follow['count'] > 0);
    }

    mysqli_close($conn);
    return false;
}

// Function to get the username by user ID
function get_username_by_id($user_id) {
    $conn = connectDatabase();

    $sql_get_username = "SELECT username FROM users WHERE id = ?";
    $stmt_get_username = mysqli_prepare($conn, $sql_get_username);
    mysqli_stmt_bind_param($stmt_get_username, "i", $user_id);
    mysqli_stmt_execute($stmt_get_username);
    $result_get_username = mysqli_stmt_get_result($stmt_get_username);

    if ($result_get_username && mysqli_num_rows($result_get_username) > 0) {
        $row_get_username = mysqli_fetch_assoc($result_get_username);
        return $row_get_username['username'];
    }

    return null;
}

// Database connection
$conn = get_db_connection();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Validate and sanitize the user ID
$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

// Fetch user's profile information from the database
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $profile_user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $username = get_username_by_id($profile_user_id); // Use the function here
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
$sql_posts = "SELECT * FROM posts WHERE (user_id=? OR (user_id=? AND visibility = 0) OR (visibility = 1) OR (visibility = 2 AND user_id IN (SELECT followee_id FROM followers WHERE follower_id = ?))) ORDER BY created_at DESC";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "iii", $profile_user_id, $_SESSION['user_id'], $_SESSION['user_id']);
mysqli_stmt_execute($stmt_posts);
$result_posts = mysqli_stmt_get_result($stmt_posts);
$posts = array();

if (mysqli_num_rows($result_posts) > 0) {
    while ($row_posts = mysqli_fetch_assoc($result_posts)) {
        // Fetch the username of the post author
        $post_author_username = get_username_by_id($row_posts['user_id']);
        $row_posts['username'] = $post_author_username;

        // Fetch the comments for each post
        $post_id = $row_posts['id'];
        $sql_comments = "SELECT * FROM comments WHERE post_id=?";
        $stmt_comments = mysqli_prepare($conn, $sql_comments);
        mysqli_stmt_bind_param($stmt_comments, "i", $post_id);
        mysqli_stmt_execute($stmt_comments);
        $result_comments = mysqli_stmt_get_result($stmt_comments);
        $comments = array();

        if (mysqli_num_rows($result_comments) > 0) {
            while ($row_comments = mysqli_fetch_assoc($result_comments)) {
                // Fetch the username of the comment author
                $comment_author_username = get_username_by_id($row_comments['user_id']);
                $row_comments['username'] = $comment_author_username;

                // Fetch the replies for each comment
                $comment_id = $row_comments['id'];
                $sql_replies = "SELECT * FROM replies WHERE comment_id=?";
                $stmt_replies = mysqli_prepare($conn, $sql_replies);
                mysqli_stmt_bind_param($stmt_replies, "i", $comment_id);
                mysqli_stmt_execute($stmt_replies);
                $result_replies = mysqli_stmt_get_result($stmt_replies);
                $replies = array();

                if (mysqli_num_rows($result_replies) > 0) {
                    while ($row_replies = mysqli_fetch_assoc($result_replies)) {
                        // Fetch the username of the reply author
                        $reply_author_username = get_username_by_id($row_replies['user_id']);
                        $row_replies['username'] = $reply_author_username;

                        $replies[] = $row_replies;
                    }
                }

                $row_comments['replies'] = $replies;
                $comments[] = $row_comments;
            }
        }

        $row_posts['comments'] = $comments;
        $posts[] = $row_posts;
    }
}

// Function to get the visibility label
function get_visibility_label($visibility) {
    switch ($visibility) {
        case 0:
            return 'Only Me';
        case 1:
            return 'Public';
        case 2:
            return 'Followers Only';
        default:
            return 'Unknown';
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile/profile_image.css">
    <link rel="stylesheet" href="css/profile/main_body.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
        .hashtag {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
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
                    <!-- Rest of the code for displaying profile image and banner -->
                <?php endif; ?>
            </div>

            <div class="username">
                <h3><?php echo $username; ?></h3>
            </div>
            <div class="follow-count">
                <?php if ($profile_user_id !== $_SESSION['user_id']): ?>
                    <?php if (check_follow_status($_SESSION['user_id'], $profile_user_id)): ?>
                        <a href="unfollow.php?user_id=<?php echo $profile_user_id; ?>">Unfollow</a>
                    <?php else: ?>
                        <a href="follow.php?user_id=<?php echo $profile_user_id; ?>">Follow</a>
                    <?php endif; ?>
                <?php endif; ?>
                <span>Followers (<?php echo $followers_count; ?>)</span>
                <?php if ($profile_user_id === $_SESSION['user_id']): ?>
                    <span>Following (<?php echo $following_count; ?>)</span>
                <?php endif; ?>
            </div>

            <h3>Your Posts</h3>

            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($profile_user_id === $_SESSION['user_id']): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?user_id=". $profile_user_id . "&tab=posts"); ?>" method="POST">
                    <div class="form-group">
                        <textarea class="form-control" name="post_content" rows="3" placeholder="Write something..." required></textarea>
                    </div>
                    <input type="hidden" name="post_visibility" value="2"> <!-- Always set to Followers Only -->
                    <button type="submit" class="btn btn-primary" name="add_post">Add Post</button>
                </form>
            <?php endif; ?>

            <h3>Your Wall</h3>

            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="card-text"><?php echo $post['content']; ?></p>
                            <p class="card-text">
                                <?php if (!empty($post['hashtags'])): ?>
                                    <?php foreach ($post['hashtags'] as $tag): ?>
                                        <span class="hashtag" onclick="searchHashtag('<?php echo urlencode($tag); ?>')"><?php echo $tag; ?> (<?php echo get_hashtag_count($tag); ?>)</span>&nbsp;
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </p>
                            <p class="card-text"><small class="text-muted"><?php echo time_ago($post['created_at']); ?></small></p>
                            <?php if ($profile_user_id === $_SESSION['user_id']): ?>
                                <p class="card-text"><small class="text-muted">Visibility: <?php echo get_visibility_label($post['visibility']); ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($post['comments'])): ?>
                                <div class="post-comments">
                                    <?php foreach ($post['comments'] as $comment): ?>
                                        <div class="comment">
                                            <span class="comment-username"><?php echo $comment['username']; ?>:</span>
                                            <span class="comment-content"><?php echo nl2br(sanitize_input($comment['content'])); ?></span>
                                            <?php if (!empty($comment['replies'])): ?>
                                                <div class="comment-replies">
                                                    <?php foreach ($comment['replies'] as $reply): ?>
                                                        <div class="reply">
                                                            <span class="reply-username"><?php echo $reply['username']; ?>:</span>
                                                            <span class="reply-content"><?php echo nl2br(sanitize_input($reply['content'])); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Custom JavaScript -->
    <script>
        // Function to handle hashtag search
        function searchHashtag(tag) {
            // You can implement the hashtag search functionality here
            console.log('Searching for hashtag:', tag);
        }
    </script>
</body>
</html>
