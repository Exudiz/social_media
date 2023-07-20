<?php
require_once 'config.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Database connection
    $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $userId = $_SESSION['user_id'];

    // Update the user's last activity timestamp
    $sql_update_activity = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $stmt_update_activity = mysqli_prepare($conn, $sql_update_activity);
    mysqli_stmt_bind_param($stmt_update_activity, "i", $userId);
    mysqli_stmt_execute($stmt_update_activity);

    mysqli_close($conn);
}
?>
