<?php
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the post ID is provided in the URL
if (!isset($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

// Get the post ID from the URL
$post_id = $_GET['post_id'];

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the post from the database
$sql_post = "SELECT * FROM posts WHERE id='$post_id'";
$result_post = mysqli_query($conn, $sql_post);
if (mysqli_num_rows($result_post) > 0) {
    $row_post = mysqli_fetch_assoc($result_post);
    // Check if the post belongs to the logged-in user
    if ($row_post['user_id'] !== $_SESSION['user_id']) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Delete the post from the database
$sql_delete_post = "DELETE FROM posts WHERE id='$post_id'";
if (mysqli_query($conn, $sql_delete_post)) {
    // Delete the associated comments
    $sql_delete_comments = "DELETE FROM comments WHERE post_id='$post_id'";
    mysqli_query($conn, $sql_delete_comments);

    $message = "Post deleted successfully!";
} else {
    $error = "Error deleting post: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Post</title>
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
    <h2>Delete Post</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php
        $user_id = $_SESSION['user_id'];
        echo '<meta http-equiv="refresh" content="3; URL=profile.php?user_id='.$user_id.'">';
        exit();
        ?>
    <?php } ?>
    <?php if (!isset($message)) { ?>
        <p>Are you sure you want to delete this post?</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?post_id=" . $post_id); ?>" method="POST">
            <button type="submit" class="btn btn-danger" name="delete_post">Delete</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php } ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
