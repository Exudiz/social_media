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

$user = null; // Initialize user variable
if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $username = $user['username'];
    $userLogo = $user['logo'];
    $userBanner = $user['banner'];
} else {
    $error = "User profile not found!";
}

// Check if the $user array is not empty and if "username" key is set before accessing it
if (!empty($user) && isset($user['username'])) {
    $username = $user['username'];
    $userLogo = $user['logo'];
    $userBanner = $user['banner'];
} else {
    // Handle the situation when the user profile is not found
    // For example, you can display an error message or redirect to a specific page
    $error = "User profile not found!";
    // For example, redirect to the homepage
    // header("Location: index.php");
    // exit();
}

// Fetch followers count
$sql_followers_count = "SELECT COUNT(*) AS count FROM followers WHERE followee_id = ?";
$stmt_followers_count = mysqli_prepare($conn, $sql_followers_count);
mysqli_stmt_bind_param($stmt_followers_count, "i", $profile_user_id);
mysqli_stmt_execute($stmt_followers_count);
$result_followers_count = mysqli_stmt_get_result($stmt_followers_count);

$followers_count = 0; // Initialize followers count
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

$following_count = 0; // Initialize following count
if (mysqli_num_rows($result_following_count) > 0) {
    $row = mysqli_fetch_assoc($result_following_count);
    $following_count = $row['count'];
}

// Fetch the posts
$sql = "SELECT p.*, u.username AS original_poster_username
        FROM posts p
        LEFT JOIN shared_posts sp ON p.id = sp.post_id
        LEFT JOIN users u ON sp.original_poster_id = u.id
        WHERE p.user_id = ? OR (sp.user_id = ? AND sp.original_poster_id IS NOT NULL)
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $profile_user_id, $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = array();

// Fetch the posts and store them in the $posts array
while ($row = $result->fetch_assoc()) {
    // Check if the user has liked the post
    $query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt_likes = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt_likes, "ii", $row['id'], $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_likes);
    $result_likes = mysqli_stmt_get_result($stmt_likes);

    if (mysqli_num_rows($result_likes) > 0) {
        // The user has liked the post
        $row['is_liked'] = true;
    } else {
        // The user has not liked the post
        $row['is_liked'] = false;
    }

    $posts[] = $row;
}

// Fetch comments for each post
foreach ($posts as &$post) {
    $post_id = $post['id'];
    $comments = get_comments($conn, $post_id);
    $post['comments'] = $comments;
}
unset($post);

// Handle liking a comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_comment'])) {
    $comment_id = $_POST['comment_id'];

    // Check if the user has already liked the comment
    $query = "SELECT * FROM comment_likes WHERE comment_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $comment_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        // The user has not liked the comment yet, so add a like
        $insert_query = "INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "ii", $comment_id, $_SESSION['user_id']);
        $success = mysqli_stmt_execute($stmt_insert);

        if ($success) {
            // Update the comment's like count in the 'comments' table
            $update_query = "UPDATE comments SET like_count = like_count + 1 WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "i", $comment_id);
            mysqli_stmt_execute($stmt_update);

            // Redirect to avoid form resubmission
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            $error = "Error liking the comment: " . mysqli_error($conn);
        }
    } else {
        $error = "You have already liked this comment!";
    }
}

// Fetch the user's logo
$sql_user_logo = "SELECT logo FROM users WHERE id = ?";
$stmt_user_logo = mysqli_prepare($conn, $sql_user_logo);
mysqli_stmt_bind_param($stmt_user_logo, "i", $profile_user_id);
mysqli_stmt_execute($stmt_user_logo);
$result_user_logo = mysqli_stmt_get_result($stmt_user_logo);

$user_logo = ''; // Initialize user_logo variable
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

// Fetch shared posts for the user
$query = "SELECT * FROM shared_posts WHERE user_id = ?";
$stmt_shared_posts = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_shared_posts, "i", $profile_user_id);
mysqli_stmt_execute($stmt_shared_posts);
$result_shared_posts = mysqli_stmt_get_result($stmt_shared_posts);

$shared_posts = array(); // Initialize shared_posts array
while ($row_shared_posts = mysqli_fetch_assoc($result_shared_posts)) {
    // Store each shared post in the array
    $shared_posts[] = $row_shared_posts;
}

// Inside the loop that displays posts
foreach ($posts as &$post) {
    $post_id = $post['id'];

    // Fetch the original poster's username directly from the 'posts' table
    $query = "SELECT u.username AS original_poster_username
              FROM users u
              INNER JOIN shared_posts sp ON u.id = sp.original_poster_id
              WHERE sp.post_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Check if the post is being shared
        if ($row['original_poster_username'] !== $_SESSION['username']) {
            $post['shared_by'] = $row['original_poster_username'];
            $post['original_poster_username'] = $_SESSION['username'];
            $post['original_poster_id'] = $_SESSION['user_id'];
        } else {
            // The post is not shared, it was made by the user
            $post['shared_by'] = null;
        }
    } else {
        // The post is not shared, it was made by the user
        $post['shared_by'] = null;
    }
}
unset($post); // Unset the reference variable

// Handle sharing a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['share_post'])) {
    $shared_post_id = $_POST['share_post_id'];

    // Fetch the content and original poster's user ID of the shared post from the 'posts' table
    $query = "SELECT p.content, p.user_id AS original_poster_id
              FROM posts p
              WHERE p.id = ?";
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
            // Insert the shared post into the 'shared_posts' table for the current user
            $query = "INSERT INTO shared_posts (user_id, post_id, original_poster_id, content) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iiis", $_SESSION['user_id'], $shared_post_id, $original_poster_id, $shared_post_content);
            $success = mysqli_stmt_execute($stmt);

            if ($success) {
                $message = "Post shared successfully!";

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
        $delete_query = "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, "ii", $post_id, $_SESSION['user_id']);
        $success = mysqli_stmt_execute($stmt_delete);

        if ($success) {
            // Update the post's like count in the 'posts' table
            $update_query = "UPDATE posts SET like_count = like_count - 1 WHERE id = ?";
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
        // The user has not liked the post yet
        // You can display an error message or handle it as per your requirement
        // For example, redirect back to the profile page or wherever appropriate
        // header("Location: profile.php");
        // exit();
    }
}

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
            $update_query = "UPDATE posts SET like_count = like_count + 1 WHERE id = ?";
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
        $error = "You have already liked this post!";
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
