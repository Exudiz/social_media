<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the post ID is provided in the URL
if (!isset($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

// Get the post ID from the URL
$post_id = $_GET['post_id'];

// Database connection
$conn = get_db_connection();

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

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_post'])) {
    $newContent = $_POST['new_content'];

    // Update the post in the database
    $sql_update_post = "UPDATE posts SET content='$newContent' WHERE id='$post_id'";
    if (mysqli_query($conn, $sql_update_post)) {
        $message = "Post updated successfully!";
        // Redirect the user back to profile.php after 3 seconds
        header("Refresh: 3; URL=profile.php");

        // After post is edited
        insertUserActivity($_SESSION['user_id'], "Post Edit", "Edited a post with ID: " . $post_id);
    } else {
        $error = "Error updating post: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
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
    <h2>Edit Post</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>
    <div class="card mb-3">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?post_id=" . $post_id); ?>" method="POST">
                <div class="form-group">
                    <textarea class="form-control" name="new_content" rows="3"><?php echo htmlspecialchars($row_post['content']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="update_post">Update Post</button>
            </form>
        </div>
    </div>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
