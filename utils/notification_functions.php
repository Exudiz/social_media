<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Function to check if a notification already exists
function isNotificationExists($user_id, $type, $post_id, $source_id, $conn)
{
    $sql = "SELECT * FROM notifications WHERE user_id = ? AND type = ? AND post_id = ? AND source_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issi", $user_id, $type, $post_id, $source_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

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

// Function to retrieve notifications for a user with messages
public function getNotificationsWithMessages($user_id)
{
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['is_read'] = (bool) $row['is_read']; // Convert is_read to boolean
            $notifications[] = $row;
        }
    }

    return $notifications;
}

// Function to insert a notification
function insertNotification($user_id, $type, $post_id, $source_id, $message, $conn)
{
  // Check if the notification type should be excluded
  if ($this->shouldExcludeNotification($type)) {
      return null;
  }

  // Log debug information
  $debugMessage = "Inserting notification - User ID: $user_id, Type: $type, Post ID: $post_id, Source ID: $source_id, Message: $message";
  logDebug($debugMessage);

  // Check if the notification already exists
  if ($this->isNotificationExists($user_id, $type, $post_id, $source_id)) {
      return null;
  }

    // Your code to insert the notification into the database goes here
    $sql = "INSERT INTO notifications (user_id, type, post_id, source_id, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issis", $user_id, $type, $post_id, $source_id, $message);
    mysqli_stmt_execute($stmt);
    $insertedId = mysqli_insert_id($conn);
    return $insertedId;
}

// Example usage - Inserting a notification only if it doesn't exist
function insertNotificationIfNotExists($user_id, $type, $source_id, $conn)
{
    if (!isNotificationExists($user_id, $type, $source_id, $conn)) {
        // Replace 'Your message here' with the actual notification message
        $message = 'Your message here';
        return insertNotification($user_id, $type, $source_id, $message, $conn);
    }

    return null;
}

// Database connection
$conn = get_db_connection();

$notification = new Notification($conn);

// Fetch the user ID from the session or adjust it based on your authentication logic
$user_id = $_SESSION['user_id'];

// Fetch the notifications for the user
$notifications = $notification->getNotificationsWithMessages($user_id);

$conn->close();
?>
