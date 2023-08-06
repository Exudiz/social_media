<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';
require_once 'utils/notification_functions.php';

class Notification
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
  }
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
                  <?php if (isset($notification['message'])): ?>
                      <p><?php echo htmlspecialchars($notification['message']); ?></p>
                  <?php endif; ?>
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
