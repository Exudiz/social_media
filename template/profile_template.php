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
