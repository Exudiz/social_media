<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the comment ID is provided in the URL
if (!isset($_GET['comment_id'])) {
    header("Location: index.php");
    exit();
}

// Get the comment ID from the URL
$comment_id = $_GET['comment_id'];

// Database connection
$conn = get_db_connection();

// Fetch the comment from the database
$sql_comment = "SELECT * FROM comments WHERE id='$comment_id'";
$result_comment = mysqli_query($conn, $sql_comment);
if (mysqli_num_rows($result_comment) > 0) {
    $row_comment = mysqli_fetch_assoc($result_comment);
    // Check if the comment belongs to the logged-in user
    if ($row_comment['user_id'] !== $_SESSION['user_id']) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Delete the comment from the database
$sql_delete_comment = "DELETE FROM comments WHERE id='$comment_id'";
if (mysqli_query($conn, $sql_delete_comment)) {
    $message = "Comment deleted successfully!";
} else {
    $error = "Error deleting comment: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Comment</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
    <meta http-equiv="refresh" content="3;url=profile.php">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Delete Comment</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
