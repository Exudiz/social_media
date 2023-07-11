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

// Fetch user's logo path
$userLogo = isset($_SESSION['user_logo']) ? $_SESSION['user_logo'] : '';
if (empty($userLogo)) {
    $userLogo = 'uploads/default-logo.png'; // Set the default logo path
}

// Fetch user's banner path
$userBanner = isset($_SESSION['user_banner']) ? $_SESSION['user_banner'] : '';
if (empty($userBanner)) {
    $userBanner = 'uploads/default-banner.png'; // Set the default banner path
}

// Fetch user profile information
$username = ''; // Initialize $username variable
$userLogo = 'uploads/default-logo.png'; // Default logo path
$userBanner = 'uploads/default-banner.png'; // Default banner path

if (isset($_SESSION['user_row'])) {
    $row = $_SESSION['user_row'];
    if (isset($row['username'])) {
        $username = $row['username'];
    } else {
        $error = "Username not found!";
    }
} else {
    $sql = "SELECT * FROM users WHERE id='$profile_user_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_row'] = $row;
        if (isset($row['username'])) {
            $username = $row['username'];
        } else {
            $error = "Username not found!";
        }
        // Fetch the logo and banner paths from the user profile table
        $userLogo = $row['logo'];
        $userBanner = $row['banner'];
    } else {
        $error = "User profile not found!";
    }
}

// Fetch user's information
$userInfo = array();
if (isset($_SESSION['user_info'])) {
    $userInfo = $_SESSION['user_info'];
} else {
    $sql_user_info = "SELECT * FROM users WHERE id='$profile_user_id'";
    $result_user_info = mysqli_query($conn, $sql_user_info);
    if (mysqli_num_rows($result_user_info) > 0) {
        $row_user_info = mysqli_fetch_assoc($result_user_info);
        $userInfo = $row_user_info;
        $_SESSION['user_info'] = $userInfo;
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

// Fetch the posts
$sql_posts = "SELECT * FROM posts WHERE (user_id='$profile_user_id' OR (user_id='" . $_SESSION['user_id'] . "' AND visibility = 0) OR (visibility = 1) OR (visibility = 2 AND user_id IN (SELECT followee_id FROM followers WHERE follower_id = '" . $_SESSION['user_id'] . "'))) ORDER BY created_at DESC";
$result_posts = mysqli_query($conn, $sql_posts);
$posts = array();
if (mysqli_num_rows($result_posts) > 0) {
    while ($row_posts = mysqli_fetch_assoc($result_posts)) {
        // Fetch the comments for each post
        $post_id = $row_posts['id'];
        $sql_comments = "SELECT * FROM comments WHERE post_id='$post_id'";
        $result_comments = mysqli_query($conn, $sql_comments);
        $comments = array();
        if (mysqli_num_rows($result_comments) > 0) {
            while ($row_comments = mysqli_fetch_assoc($result_comments)) {
                $comments[] = $row_comments;
            }
        }
        $row_posts['comments'] = $comments;
        $posts[] = $row_posts;
    }
}

// Handle adding a post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_post'])) {
    $postContent = $_POST['post_content'];
    $postVisibility = $_POST['post_visibility'];

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
        $sql_add_post = "INSERT INTO posts (user_id, username, content, created_at, visibility) VALUES ('$profile_user_id', '$username', '$postContent', NOW(), '$visibility')";
        if (mysqli_query($conn, $sql_add_post)) {
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

// Handle adding a comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    // Check if the comment content is not empty
    if (!empty($comment_content)) {
        // Fetch the username from the 'users' table
        $sql_username = "SELECT username FROM users WHERE id='$user_id'";
        $result_username = mysqli_query($conn, $sql_username);
        if (mysqli_num_rows($result_username) > 0) {
            $row_username = mysqli_fetch_assoc($result_username);
            $username = $row_username['username'];

            // Insert the comment into the database
            $sql_add_comment = "INSERT INTO comments (post_id, user_id, content, username, created_at) VALUES ('$post_id', '$user_id', '$comment_content', '$username', NOW())";
            if (mysqli_query($conn, $sql_add_comment)) {
                $message = "Comment added successfully!";
                // Refresh the page after adding a comment to display the updated list of comments
                header("Refresh: 0");
            } else {
                $error = "Error adding comment: " . mysqli_error($conn);
            }
        } else {
            $error = "User not found!";
        }
    } else {
        $error = "Comment content cannot be empty!";
    }
}

// Function to calculate time ago
function time_ago($datetime) {
    $current_time = new DateTime();
    $post_time = new DateTime($datetime);
    $interval = $post_time->diff($current_time);

    if ($interval->y > 0) {
        return $interval->format("%y year" . ($interval->y > 1 ? "s" : "") . " ago");
    } elseif ($interval->m > 0) {
        return $interval->format("%m month" . ($interval->m > 1 ? "s" : "") . " ago");
    } elseif ($interval->d > 0) {
        return $interval->format("%d day" . ($interval->d > 1 ? "s" : "") . " ago");
    } elseif ($interval->h > 0) {
        return $interval->format("%h hour" . ($interval->h > 1 ? "s" : "") . " ago");
    } elseif ($interval->i > 0) {
        return $interval->format("%i minute" . ($interval->i > 1 ? "s" : "") . " ago");
    } else {
        return "Just now";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile/profile_image.css">
    <link rel="stylesheet" href="css/profile/main_body.css">
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
                  <img src="<?php echo $userLogo; ?>" alt="Profile Logo" class="logo-image">
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
                          <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                              <div>
                                  <a href="edit_post.php?post_id=<?php echo $post['id']; ?>">Edit Post</a>
                                  <a href="delete_post.php?post_id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</a>
                              </div>
                          <?php endif; ?>
                      </div>
                      <p class="card-text"><?php echo htmlspecialchars($post['content']); ?></p>
                  </div>
                    <?php if (!empty($post['comments'])): ?>
                        <div class="card-footer">
                            <h6>Comments:</h6>
                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if ($comment['user_id'] === $_SESSION['user_id']): ?>
                                        <div>
                                            <a href="edit_comment.php?comment_id=<?php echo $comment['id']; ?>">Edit Comment</a>
                                            <a href="delete_comment.php?comment_id=<?php echo $comment['id']; ?>" onclick="return confirm('Are you sure you want to delete this comment?')">Delete Comment</a>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mb-0">
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                        <small>by <?php echo htmlspecialchars($comment['username']); ?>, <?php echo time_ago($comment['created_at']); ?></small>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-footer">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
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
            <p>No posts to display on your wall.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
