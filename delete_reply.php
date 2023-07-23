<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the reply ID is provided in the GET data
if (!isset($_GET['reply_id'])) {
    header("Location: index.php");
    exit();
}

$replyId = $_GET['reply_id'];

// Database connection
$conn = get_db_connection();

// Fetch the reply
$sql_reply = "SELECT * FROM replies WHERE id = ?";
$stmt_reply = mysqli_prepare($conn, $sql_reply);
mysqli_stmt_bind_param($stmt_reply, "i", $replyId);
mysqli_stmt_execute($stmt_reply);
$result_reply = mysqli_stmt_get_result($stmt_reply);

if (mysqli_num_rows($result_reply) > 0) {
    $reply = mysqli_fetch_assoc($result_reply);
    $commentId = $reply['comment_id'];

    // Check if the user is the owner of the reply
    if ($reply['user_id'] != $_SESSION['user_id']) {
        header("Location: index.php");
        exit();
    }

    // Delete the reply from the database
    $sql_delete_reply = "DELETE FROM replies WHERE id = ?";
    $stmt_delete_reply = mysqli_prepare($conn, $sql_delete_reply);
    mysqli_stmt_bind_param($stmt_delete_reply, "i", $replyId);

    if (mysqli_stmt_execute($stmt_delete_reply)) {
        // Reply deleted successfully
        header("Location: reply_comment.php?comment_id=" . $commentId);
        exit();
    } else {
        // Error deleting reply
        header("Location: index.php");
        exit();
    }
} else {
    // Reply not found
    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>
