<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

    $userId = $_SESSION['user_id'];

    // Update the user's last activity timestamp
    $sql_update_activity = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $stmt_update_activity = mysqli_prepare($conn, $sql_update_activity);
    mysqli_stmt_bind_param($stmt_update_activity, "i", $userId);
    mysqli_stmt_execute($stmt_update_activity);

    mysqli_close($conn);
}
?>
