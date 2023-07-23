<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the comment ID is provided in the POST data
if (!isset($_POST['comment_id'])) {
    header("Location: index.php");
    exit();
}

$commentId = $_POST['comment_id'];
$replyContent = $_POST['reply_content'];

// Database connection
$conn = get_db_connection();

// Check if the reply content is not empty
if (!empty($replyContent)) {
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Insert the reply into the database
    $sql_add_reply = "INSERT INTO replies (comment_id, user_id, username, content, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt_add_reply = mysqli_prepare($conn, $sql_add_reply);
    mysqli_stmt_bind_param($stmt_add_reply, "iiss", $commentId, $userId, $username, $replyContent);

    if (mysqli_stmt_execute($stmt_add_reply)) {
        $replyId = mysqli_insert_id($conn);
        // Reply added successfully
        echo "success";
    } else {
        // Error adding reply
        echo "error";
    }
} else {
    // Reply content is empty
    echo "empty";
}

mysqli_close($conn);
?>
