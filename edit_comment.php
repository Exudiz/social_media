<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the comment ID is provided in the URL
if (!isset($_GET['comment_id'])) {
    header("Location: index.php");
    exit();
}

// Get the comment ID from the URL
$comment_id = $_GET['comment_id'];

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

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

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_comment'])) {
    $newContent = $_POST['new_content'];

    // Update the comment in the database
    $sql_update_comment = "UPDATE comments SET content='$newContent' WHERE id='$comment_id'";
    if (mysqli_query($conn, $sql_update_comment)) {
        $message = "Comment updated successfully!";
    } else {
        $error = "Error updating comment: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Comment</title>
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
    <h2>Edit Comment</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?comment_id=" . $comment_id); ?>" method="POST">
        <div class="form-group">
            <label for="new_content">Content:</label>
            <textarea class="form-control" name="new_content" rows="3"><?php echo htmlspecialchars($row_comment['content']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="update_comment">Update Comment</button>
    </form>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
