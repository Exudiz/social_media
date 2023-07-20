<?php
require_once 'config.php';

class Notification
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Function to check if a notification already exists
    public function isNotificationExists($user_id, $type, $post_id, $source_id)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ? AND type = ? AND post_id = ? AND source_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $user_id, $type, $post_id, $source_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Function to retrieve notifications for a user
    public function getNotifications($user_id)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = array();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['is_read'] = (bool)$row['is_read']; // Convert is_read to boolean
                $notifications[] = $row;
            }
        }

        return $notifications;
    }

    // Function to insert a notification
    public function insertNotification($user_id, $type, $post_id, $source_id, $message)
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
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $user_id, $type, $post_id, $source_id, $message);
        $stmt->execute();
        $insertedId = $stmt->insert_id;
        return $insertedId;
    }

    // Function to determine if a notification type should be excluded
    public function shouldExcludeNotification($type)
    {
        // Modify this function according to your logic
        // Return true if the notification type should be excluded, otherwise return false
        return ($type === 'post_created' || $type === 'specific_action');
    }
}

// Database connection
$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$notification = new Notification($conn);

// Fetch the user ID from the session or adjust it based on your authentication logic
$user_id = $_SESSION['user_id'];

// Fetch the notifications for the user
$notifications = $notification->getNotifications($user_id);

$conn->close();
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile/notifications.css">

    <!-- Additional CSS styles -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <div class="container">
        <!-- Your HTML content goes here -->
        <h1>Notifications</h1>
        <?php if (empty($notifications)): ?>
            <p>No notifications found.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    <p class="time"><?php echo getTimeAgo($notification['created_at']); ?></p>
                    <a href="mark_notification.php?id=<?php echo $notification['id']; ?>&action=read">Mark as Read</a>
                    <a href="mark_notification.php?id=<?php echo $notification['id']; ?>&action=delete">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

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

<?php
function getTimeAgo($timestamp) {
    $timeAgo = '';
    $now = new DateTime();
    $created = new DateTime($timestamp);
    $interval = $created->diff($now);

    if ($interval->y > 0) {
        $timeAgo = $interval->format('%y year(s) ago');
    } elseif ($interval->m > 0) {
        $timeAgo = $interval->format('%m month(s) ago');
    } elseif ($interval->d > 0) {
        $timeAgo = $interval->format('%d day(s) ago');
    } elseif ($interval->h > 0) {
        $timeAgo = $interval->format('%h hour(s) ago');
    } elseif ($interval->i > 0) {
        $timeAgo = $interval->format('%i minute(s) ago');
    } else {
        $timeAgo = 'Just now';
    }

    return $timeAgo;
}
?>
