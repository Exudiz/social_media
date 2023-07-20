<?php
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

$commentId = $_GET['comment_id'];

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the comment
$sql_comment = "SELECT * FROM comments WHERE id = ?";
$stmt_comment = mysqli_prepare($conn, $sql_comment);
mysqli_stmt_bind_param($stmt_comment, "i", $commentId);
mysqli_stmt_execute($stmt_comment);
$result_comment = mysqli_stmt_get_result($stmt_comment);

if (mysqli_num_rows($result_comment) > 0) {
    $comment = mysqli_fetch_assoc($result_comment);
    $postId = $comment['post_id'];
} else {
    // Comment not found
    header("Location: index.php");
    exit();
}

// Fetch the post
$sql_post = "SELECT * FROM posts WHERE id = ?";
$stmt_post = mysqli_prepare($conn, $sql_post);
mysqli_stmt_bind_param($stmt_post, "i", $postId);
mysqli_stmt_execute($stmt_post);
$result_post = mysqli_stmt_get_result($stmt_post);

if (mysqli_num_rows($result_post) > 0) {
    $post = mysqli_fetch_assoc($result_post);
} else {
    // Post not found
    header("Location: index.php");
    exit();
}

// Handle adding a reply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_reply'])) {
    $replyContent = $_POST['reply_content'];

    // Check if the reply content is not empty
    if (!empty($replyContent)) {
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        if ($username === null) {
            // Fetch the username based on the user's ID
            $sql_username = "SELECT username FROM users WHERE id = ?";
            $stmt_username = mysqli_prepare($conn, $sql_username);
            mysqli_stmt_bind_param($stmt_username, "i", $userId);
            mysqli_stmt_execute($stmt_username);
            $result_username = mysqli_stmt_get_result($stmt_username);

            if ($result_username && mysqli_num_rows($result_username) > 0) {
                $row_username = mysqli_fetch_assoc($result_username);
                $username = $row_username['username'];
            }
        }

        // Insert the reply into the database
        $sql_add_reply = "INSERT INTO replies (comment_id, user_id, username, content, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt_add_reply = mysqli_prepare($conn, $sql_add_reply);
        mysqli_stmt_bind_param($stmt_add_reply, "iiss", $commentId, $userId, $username, $replyContent);

        if (mysqli_stmt_execute($stmt_add_reply)) {
            $replyId = mysqli_insert_id($conn);
            $message = "Reply added successfully!";
            // Refresh the page after adding a reply to display the updated list of replies
            header("Refresh: 0");
        } else {
            $error = "Error adding reply: " . mysqli_error($conn);
        }
    } else {
        $error = "Reply content cannot be empty!";
    }
}

// Fetch the replies for the comment
$sql_replies = "SELECT * FROM replies WHERE comment_id = ? ORDER BY created_at ASC";
$stmt_replies = mysqli_prepare($conn, $sql_replies);
mysqli_stmt_bind_param($stmt_replies, "i", $commentId);
mysqli_stmt_execute($stmt_replies);
$result_replies = mysqli_stmt_get_result($stmt_replies);
$replies = array();

if (mysqli_num_rows($result_replies) > 0) {
    while ($row_replies = mysqli_fetch_assoc($result_replies)) {
        $replies[] = $row_replies;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reply to Comment</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
        .reply-card {
            margin-bottom: 15px;
        }
        .reply-card .card-header {
            font-size: 14px;
            font-weight: bold;
        }
        .reply-card .card-text {
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h3>Reply to Comment</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($post['username']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($post['content']); ?></p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($comment['username']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($comment['content']); ?></p>
        </div>
    </div>

    <?php if (count($replies) > 0): ?>
        <h5>Replies:</h5>
        <?php foreach ($replies as $reply): ?>
            <div class="card reply-card">
                <div class="card-header">
                    <?php echo htmlspecialchars($reply['username']); ?>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($reply['content']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h5>Add a Reply:</h5>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?comment_id=" . $commentId); ?>" method="POST">
        <div class="form-group">
            <textarea class="form-control" name="reply_content" rows="3" placeholder="Write a reply..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="add_reply">Add Reply</button>
    </form>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
