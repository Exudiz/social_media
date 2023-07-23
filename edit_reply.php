<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the reply ID is provided in the POST data
if (!isset($_POST['reply_id'])) {
    header("Location: index.php");
    exit();
}

$replyId = $_POST['reply_id'];
$replyContent = $_POST['reply_content'];

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

    // Update the reply content in the database
    $sql_update_reply = "UPDATE replies SET content = ? WHERE id = ?";
    $stmt_update_reply = mysqli_prepare($conn, $sql_update_reply);
    mysqli_stmt_bind_param($stmt_update_reply, "si", $replyContent, $replyId);

    if (mysqli_stmt_execute($stmt_update_reply)) {
        // Reply updated successfully
        echo "success";
    } else {
        // Error updating reply
        echo "error";
    }
} else {
    // Reply not found
    echo "not_found";
}

mysqli_close($conn);
?>
