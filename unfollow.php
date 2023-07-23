<?php
require_once 'config.php';
require_once 'functions.php';

// Check if the user_id parameter is present in the URL
if (isset($_GET['user_id'])) {
    $followee_id = $_GET['user_id'];
    $follower_id = $_SESSION['user_id'];

    // Database connection
    $conn = get_db_connection();

    // Check if the followee-follower relationship exists in the database
    $sql_check_follow = "SELECT * FROM followers WHERE follower_id = ? AND followee_id = ?";
    $stmt_check_follow = mysqli_prepare($conn, $sql_check_follow);
    mysqli_stmt_bind_param($stmt_check_follow, "ii", $follower_id, $followee_id);
    mysqli_stmt_execute($stmt_check_follow);
    $result_check_follow = mysqli_stmt_get_result($stmt_check_follow);

    if (mysqli_num_rows($result_check_follow) > 0) {
        // Unfollow the user by deleting the relationship from the database
        $sql_unfollow = "DELETE FROM followers WHERE follower_id = ? AND followee_id = ?";
        $stmt_unfollow = mysqli_prepare($conn, $sql_unfollow);
        mysqli_stmt_bind_param($stmt_unfollow, "ii", $follower_id, $followee_id);

        if (mysqli_stmt_execute($stmt_unfollow)) {
            // The user has been unfollowed successfully
            mysqli_close($conn); // Close the database connection
            header("Location: user_profile.php?user_id=$followee_id");
            exit();
        } else {
            // Handle the unfollow error
            mysqli_close($conn); // Close the database connection
            $error = "Error unfollowing the user: " . mysqli_error($conn);
        }
    } else {
        // The followee-follower relationship does not exist, so the user is not currently following
        $error = "You are not following this user.";
    }
}

// Handle any unfollow errors
if (isset($error)) {
    // You can display the error message or handle it as needed
    echo $error;
}
?>
