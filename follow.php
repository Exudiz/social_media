<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the user_id parameter is present in the URL
if (isset($_GET['user_id'])) {
    $followee_id = $_GET['user_id'];
    $follower_id = $_SESSION['user_id'];

    // Database connection
    $conn = get_db_connection();

    // Insert the followee-follower relationship into the database
    $sql_follow = "INSERT INTO followers (follower_id, followee_id) VALUES (?, ?)";
    $stmt_follow = mysqli_prepare($conn, $sql_follow);
    mysqli_stmt_bind_param($stmt_follow, "ii", $follower_id, $followee_id);

    if (mysqli_stmt_execute($stmt_follow)) {
        // The user has been followed successfully
        mysqli_close($conn); // Close the database connection
        header("Location: user_profile.php?user_id=$followee_id");
        exit();
    } else {
        // Handle the follow error
        mysqli_close($conn); // Close the database connection
        $error = "Error following the user: " . mysqli_error($conn);
    }
}

// Handle any follow errors
if (isset($error)) {
    // You can display the error message or handle it as needed
    echo $error;
}
?>
