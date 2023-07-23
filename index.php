<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';
require_once 'utils/ip.php';
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

mysqli_close($conn);
?>

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
                        <h5 class="card-title"><?php echo $post['username']; ?></h5>
                        <p class="card-text"><?php echo $post['content']; ?></p>
                        <p class="card-text"><small><?php echo $post['created_at']; ?></small></p>
                        <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">View Profile</a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <h2>No posts to display on the public wall.</h2>
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
