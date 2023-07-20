<?php

// Function to check if a notification already exists
function isNotificationExists($user_id, $type, $post_id, $source_id, $conn) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? AND type = ? AND post_id = ? AND source_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issii", $user_id, $type, $post_id, $source_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

// Rest of the code...

// Function to retrieve notifications for a user
function getNotifications($user_id, $conn)
{
    $sql_notifications = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt_notifications = mysqli_prepare($conn, $sql_notifications);
    mysqli_stmt_bind_param($stmt_notifications, "i", $user_id);
    mysqli_stmt_execute($stmt_notifications);
    $result_notifications = mysqli_stmt_get_result($stmt_notifications);
    $notifications = array();

    if (mysqli_num_rows($result_notifications) > 0) {
        while ($row_notifications = mysqli_fetch_assoc($result_notifications)) {
            $notifications[] = $row_notifications;
        }
    }

    return $notifications;
}

// Example usage - Inserting a notification only if it doesn't exist
function insertNotificationIfNotExists($user_id, $type, $source_id, $conn)
{
    if (!isNotificationExists($user_id, $type, $source_id, $conn)) {
        return insertNotification($user_id, $type, $source_id, $conn);
    }

    return null;
}

?>
