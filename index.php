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

// Assuming the column name for storing likes count is 'like_count'
$sql_update_likes = "UPDATE posts SET like_count = like_count + 1 WHERE id = ?";
$stmt_update_likes = mysqli_prepare($conn, $sql_update_likes);
mysqli_stmt_bind_param($stmt_update_likes, "i", $post_id);
mysqli_stmt_execute($stmt_update_likes);

// Handle liking a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_post'])) {
    $post_id = $_POST['post_id'];

    // Check if the user has already liked the post
    $query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        // The user has not liked the post yet, so add a like
        $insert_query = "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "ii", $post_id, $_SESSION['user_id']);
        $success = mysqli_stmt_execute($stmt_insert);

        if ($success) {
            // Update the post's like count in the 'posts' table
            $update_query = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "i", $post_id);
            mysqli_stmt_execute($stmt_update);

            // Redirect to avoid form resubmission
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            $error = "Error liking the post: " . mysqli_error($conn);
        }
    } else {
        // The user has already liked the post
        // You can display an error message or handle it as per your requirement
        // For example, redirect back to the profile page or wherever appropriate
        // header("Location: profile.php");
        // exit();
    }
}

// Handle unliking a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unlike_post'])) {
    $post_id = $_POST['post_id'];

    // Check if the user has already liked the post
    $query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // The user has liked the post, so remove the like
        $success = remove_like($conn, $_SESSION['user_id'], $post_id);

        if ($success) {
            // Update the post's like count in the 'posts' table
            $update_query = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "i", $post_id);
            mysqli_stmt_execute($stmt_update);

            // Redirect to avoid form resubmission
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            $error = "Error unliking the post: " . mysqli_error($conn);
        }
    } else {
        // The user has not liked the post, so nothing to unlike
        // You can display an error message or handle it as per your requirement
        // For example, redirect back to the profile page or wherever appropriate
        // header("Location: profile.php");
        // exit();
    }
}

// Handle sharing a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['share_post'])) {
    $shared_post_id = $_POST['share_post_id'];

    // Fetch the content and original poster's user ID of the shared post from the 'posts' table
    $query = "SELECT content, user_id AS original_poster_id FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $shared_post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $shared_post_content = $row['content'];
        $original_poster_id = $row['original_poster_id'];

        // Check if the post is being shared by someone other than the original poster
        if ($original_poster_id !== $_SESSION['user_id']) {
            // Insert the shared post into the 'posts' table for the current user
            $query = "INSERT INTO posts (user_id, content, visibility) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            $visibility = 1; // Set the visibility of the shared post to "public"
            mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $shared_post_content, $visibility);
            $success = mysqli_stmt_execute($stmt);

            if ($success) {
                // Update the shared_posts_count in the users table for the original poster
                $update_query = "UPDATE users SET shared_posts_count = shared_posts_count + 1 WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt_update, "i", $original_poster_id);
                mysqli_stmt_execute($stmt_update);

                // Redirect to avoid form resubmission
                header("Location: {$_SERVER['REQUEST_URI']}");
                exit();
            } else {
                $error = "Error sharing post: " . mysqli_error($conn);
            }
        } else {
            $error = "You cannot share your own post!";
        }
    } else {
        $error = "Shared post not found!";
    }
}

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

        // Check if the post is liked by the current user
        $row_posts['is_liked'] = is_post_liked_by_user($conn, $post_id, $_SESSION['user_id']);

        // Check if the post is shared by someone
        $row_posts['shared_by'] = get_post_shared_by_user($conn, $post_id);

        $posts[] = $row_posts;
    }
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

// Display posts and comments
foreach ($posts as $post) {
    // Display post details
    echo "Post ID: " . $post['id'] . "<br>";
    echo "Content: " . $post['content'] . "<br>";
    echo "Likes: " . $post['likes'] . "<br>";

    // Display whether the post is liked by the current user
    if (isset($post['is_liked'])) {
        if ($post['is_liked']) {
            echo "You have liked this post.<br>";
        } else {
            echo "You have not liked this post.<br>";
        }
    } else {
        echo "Like status not available.<br>";
    }

    // Display users who liked the post
    if (!empty($post['liked_by'])) {
        $likedUserIds = explode(',', $post['liked_by']);
        echo "Liked by: ";
        foreach ($likedUserIds as $likedUserId) {
            // Trim the user ID to remove any leading/trailing spaces
            $likedUserId = trim($likedUserId);

            // Check if $likedUserId is not empty and is a valid integer before accessing the username
            if (!empty($likedUserId) && is_numeric($likedUserId)) {
                $username = get_username_by_id($conn, $likedUserId);
                if ($username !== null) {
                    echo $username . ", ";
                }
            }
        }
        echo "<br>";
    }

    // Display whether the post is shared or not
    if (isset($post['shared_by'])) {
        $sharedByUsername = get_username_by_id($conn, $post['shared_by']);
        echo "Shared by: " . $sharedByUsername . "<br>";
    } else {
        echo "Not shared<br>";
    }

    // Display comments for each post
    echo "Comments: <br>";
    foreach ($post['comments'] as $comment) {
        echo "Comment ID: " . $comment['id'] . "<br>";
        echo "Content: " . $comment['content'] . "<br>";
        echo "<br>";
    }
    echo "<br>";
}

$conn->close();

include 'template/index_template.php';
?>
