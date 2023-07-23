<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Insert user activity function
function insertUserActivity($userId, $activityType, $activityDetails) {
    global $conn;
    $activityDate = date('Y-m-d H:i:s');

    $sql = "INSERT INTO user_activity (user_id, activity_type, activity_date, activity_details)
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $userId, $activityType, $activityDate, $activityDetails);
    mysqli_stmt_execute($stmt);
}

// Fetch the user's recent activities
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM user_activity WHERE user_id = ? ORDER BY activity_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch user's information
$userInfo = isset($_SESSION['user_info']) ? $_SESSION['user_info'] : array();

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recent Activity</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Recent Activity</h2>
    <?php if (mysqli_num_rows($activityResult) > 0): ?>
        <?php while ($activityRow = mysqli_fetch_assoc($activityResult)): ?>
            <a class="dropdown-item" href="#">
                <strong><?php echo $activityRow['activity_type']; ?>:</strong>
                <?php echo $activityRow['activity_details']; ?>
                <span class="text-muted"> (<?php echo $activityRow['activity_date']; ?>)</span>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No recent activity found.</p>
    <?php endif; ?>
    </div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
