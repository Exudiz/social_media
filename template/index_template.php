<!DOCTYPE html>
<html>
<head>
    <title>Social Media Website</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<?php if (!isset($_SESSION['user_id'])) { ?>
    <!-- Registration form -->
    <div class="container" id="register">
        <h2>Registration</h2>
        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="register">Register</button>
        </form>
    </div>

    <!-- Login form -->
    <div class="container" id="login">
        <h2>Login</h2>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="email_or_username">Email or Username</label>
                <input type="text" class="form-control" id="email_or_username" name="email_or_username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="login">Login</button>
            <a href="#forgotPasswordModal" class="btn btn-link" data-toggle="modal">Forgot Password?</a>
        </form>
    </div>
<?php } else { ?>
  <!-- Public wall content -->
  <div class="container">
      <?php if (!empty($posts)) { ?>
          <h2>Public Wall</h2>
          <?php foreach ($posts as $post) { ?>
              <div class="card mb-3">
                  <div class="card-body">
                      <h5 class="card-title"><a href="profile.php?user_id=<?php echo $post['user_id']; ?>"><?php echo $post['username']; ?></a></h5>
                      <p class="card-text"><?php echo $post['content']; ?></p>
                      <p class="card-text"><small><?php echo $post['created_at']; ?></small></p>
                      <div class="post-actions">
                          <a href="#" class="post-like">Like</a>
                          <a href="#" class="post-share">Share</a>
                      </div>
                      <div class="row">
                          <div class="col-md-4">
                              <p class="card-text">Likes: <?php echo $post['likes']; ?></p>
                          </div>
                          <div class="col-md-4">
                              <?php if (isset($post['shares'])) { ?>
                                  <p class="card-text">Shares: <?php echo $post['shares']; ?></p>
                              <?php } else { ?>
                                  <p class="card-text">Shares: 0</p>
                              <?php } ?>
                          </div>
                          <div class="col-md-4">
                              <p class="card-text">Comments: <?php echo count($post['comments']); ?></p>
                          </div>
                      </div>

                      <?php // Show comment section ?>
                      <?php if (!empty($post['comments'])) { ?>
                          <h6>Comments:</h6>
                          <ul>
                              <?php foreach ($post['comments'] as $comment) { ?>
                                  <li><?php echo $comment['content']; ?></li>
                              <?php } ?>
                          </ul>
                      <?php } ?>

                      <?php // Add comment form ?>
                      <form action="add_comment.php" method="POST">
                          <div class="form-group">
                              <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                              <input type="text" class="form-control" name="comment_content" placeholder="Add a comment">
                          </div>
                          <button type="submit" class="btn btn-primary">Add Comment</button>
                      </form>
                  </div>
              </div>
          <?php } ?>
      <?php } else { ?>
          <p>No posts found.</p>
      <?php } ?>
  </div>
<?php } ?>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="form-group">
                        <label for="forgot_email">Email</label>
                        <input type="email" class="form-control" id="forgot_email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="forgot_password">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="js/activity.js"></script>
</body>
</html>
