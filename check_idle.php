<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

    // Fetch the user's last activity timestamp from the database
    $sql_last_activity = "SELECT last_activity FROM users WHERE id = ?";
    $stmt_last_activity = mysqli_prepare($conn, $sql_last_activity);
    mysqli_stmt_bind_param($stmt_last_activity, "i", $userId);
    mysqli_stmt_execute($stmt_last_activity);
    $result_last_activity = mysqli_stmt_get_result($stmt_last_activity);

    if (mysqli_num_rows($result_last_activity) > 0) {
        $row_last_activity = mysqli_fetch_assoc($result_last_activity);
        $lastActivity = strtotime($row_last_activity['last_activity']);
        $idleDuration = 15 * 60; // Idle duration in seconds (adjust as needed)
        $currentTime = time();

        // Check if the user has been idle for the specified duration
        if ($currentTime - $lastActivity > $idleDuration) {
            // User has been idle, log them out
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        }
    }

    mysqli_close($conn);
}
?>
