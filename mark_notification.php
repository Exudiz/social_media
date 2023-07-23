<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $notificationId = $_GET['id'];
    $action = $_GET['action'];

    // Database connection
    $conn = get_db_connection();

    if ($action === 'read') {
        // Update the notification as read in the database
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $notificationId);
        mysqli_stmt_execute($stmt);

        // Update the count of unread notifications in the session
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $sql = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $_SESSION['notification_count'] = $row['count'];
        }
    } elseif ($action === 'delete') {
        // Delete the notification from the database
        $sql = "DELETE FROM notifications WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $notificationId);
        mysqli_stmt_execute($stmt);
    }

    // Close the database connection
    mysqli_close($conn);
}

// Redirect back to the notifications page
header("Location: notifications.php");
exit();
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- Additional CSS styles -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <div class="container">
        <!-- Your HTML content goes here -->
        <h1>Notifications</h1>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification">
                <p><?php echo $notification['message']; ?></p>
                <a href="mark_notification.php?id=<?php echo $notification['id']; ?>&action=read">Mark as Read</a>
                <a href="mark_notification.php?id=<?php echo $notification['id']; ?>&action=delete">Delete</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Include Bootstrap JS scripts -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Additional scripts -->
    <script>
        // Add your custom scripts here
    </script>
</body>
</html>
