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
    $profile_user_id = $_SESSION['user_id'];
} else {
    $profile_user_id = $_GET['user_id'];
}

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

// Fetch user's posts from the database
$sql_posts = "SELECT * FROM posts WHERE user_id = '$profile_user_id' ORDER BY created_at DESC";
$result_posts = mysqli_query($conn, $sql_posts);
$posts = [];
if (mysqli_num_rows($result_posts) > 0) {
    while ($row = mysqli_fetch_assoc($result_posts)) {
        $posts[] = $row;
    }
}

// Fetch followers count
$sql_followers_count = "SELECT COUNT(*) AS count FROM followers WHERE followee_id = '$profile_user_id'";
$result_followers_count = mysqli_query($conn, $sql_followers_count);
$followers_count = 0;
if (mysqli_num_rows($result_followers_count) > 0) {
    $row = mysqli_fetch_assoc($result_followers_count);
    $followers_count = $row['count'];
}

// Fetch following count
$sql_following_count = "SELECT COUNT(*) AS count FROM followers WHERE follower_id = '$profile_user_id'";
$result_following_count = mysqli_query($conn, $sql_following_count);
$following_count = 0;
if (mysqli_num_rows($result_following_count) > 0) {
    $row = mysqli_fetch_assoc($result_following_count);
    $following_count = $row['count'];
}

// Handle adding a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_post'])) {
    $postContent = $_POST['post_content'];

    // Insert the post into the database
    $sql_add_post = "INSERT INTO posts (user_id, username, content) VALUES ('$profile_user_id', '$username', '$postContent')";
    if (mysqli_query($conn, $sql_add_post)) {
        $message = "Post added successfully!";
        // Refresh the page after adding a post to display the updated list of posts
        header("Refresh: 0");
    } else {
        $error = "Error adding post: " . mysqli_error($conn);
    }
}

// Handle adding a comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $commentContent = $_POST['comment_content'];
    $postId = $_POST['post_id'];

    // Insert the comment into the database
    $sql_add_comment = "INSERT INTO comments (post_id, user_id, username, content) VALUES ('$postId', '$profile_user_id', '$username', '$commentContent')";
    if (mysqli_query($conn, $sql_add_comment)) {
        $message = "Comment added successfully!";
        // Refresh the page after adding a comment to display the updated list of comments
        header("Refresh: 0");
    } else {
        $error = "Error adding comment: " . mysqli_error($conn);
    }
}

// Follow/unfollow action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['follow_action'])) {
    $follower_id = $_SESSION['user_id'];
    $followee_id = $_POST['followee_id'];

    // Check if the user is already following the profile user
    $sql_check_follow = "SELECT * FROM followers WHERE follower_id='$follower_id' AND followee_id='$followee_id'";
    $result_check_follow = mysqli_query($conn, $sql_check_follow);
    if (mysqli_num_rows($result_check_follow) > 0) {
        // User is already following, unfollow
        $sql_unfollow = "DELETE FROM followers WHERE follower_id='$follower_id' AND followee_id='$followee_id'";
        if (mysqli_query($conn, $sql_unfollow)) {
            $message = "You have unfollowed the user.";
            header("Refresh: 0");
        } else {
            $error = "Error unfollowing user: " . mysqli_error($conn);
        }
    } else {
        // User is not following, follow
        $sql_follow = "INSERT INTO followers (follower_id, followee_id) VALUES ('$follower_id', '$followee_id')";
        if (mysqli_query($conn, $sql_follow)) {
            $message = "You are now following the user.";
            header("Refresh: 0");
        } else {
            $error = "Error following user: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
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
    <h2>User Profile: <?php echo $username; ?></h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } else { ?>
        <a href="followers.php?user_id=<?php echo $profile_user_id; ?>">View Followers</a> (<?php echo $followers_count; ?>)
        <a href="following.php?user_id=<?php echo $profile_user_id; ?>">View Following</a> (<?php echo $following_count; ?>)

        <?php
        // Check if the user is already following the profile user
        $follower_id = $_SESSION['user_id'];
        $sql_check_follow = "SELECT * FROM followers WHERE follower_id='$follower_id' AND followee_id='$profile_user_id'";
        $result_check_follow = mysqli_query($conn, $sql_check_follow);
        $already_following = mysqli_num_rows($result_check_follow) > 0;
        ?>

        <?php if (!$already_following) { ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="followee_id" value="<?php echo $profile_user_id; ?>">
                <button type="submit" class="btn btn-primary" name="follow_action">Follow</button>
            </form>
        <?php } else { ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="followee_id" value="<?php echo $profile_user_id; ?>">
                <button type="submit" class="btn btn-secondary" name="follow_action">Unfollow</button>
            </form>
        <?php } ?>

        <h3>Your Posts</h3>
        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?user_id=" . $profile_user_id); ?>" method="POST">
            <div class="form-group">
                <textarea class="form-control" name="post_content" rows="3" placeholder="Write something..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" name="add_post">Add Post</button>
        </form>
        <h3>Your Wall</h3>
        <?php if (!empty($posts)) { ?>
            <?php foreach ($posts as $post) { ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo $post['username']; ?>
                            <small><?php echo $post['created_at']; ?></small>
                        </h5>
                        <p class="card-text"><?php echo $post['content']; ?></p>
                        <?php if ($post['user_id'] === $_SESSION['user_id']) { ?>
                            <div>
                                <a href="#" class="edit-post" data-post-id="<?php echo $post['id']; ?>">Edit</a>
                                <a href="#" class="delete-post" data-post-id="<?php echo $post['id']; ?>">Delete</a>
                            </div>
                        <?php } ?>
                        <div class="comments">
                            <?php
                            // Fetch the comments for the current post
                            $sql_comments = "SELECT * FROM comments WHERE post_id = '$post[id]' ORDER BY created_at ASC";
                            $result_comments = mysqli_query($conn, $sql_comments);
                            if (mysqli_num_rows($result_comments) > 0) {
                                while ($row = mysqli_fetch_assoc($result_comments)) {
                                    echo '<div class="comment">';
                                    echo '<p><strong>'.$row['username'].'</strong>: '.$row['content'].'</p>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No comments yet.</p>';
                            }
                            ?>
                        </div>
                        <form class="comment-form" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
                            <div class="form-group">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <input type="text" class="form-control" name="comment_content" placeholder="Write a comment...">
                            </div>
                            <button type="submit" class="btn btn-primary" name="add_comment">Comment</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No posts to display on your wall.</p>
        <?php } ?>
    <?php } ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
