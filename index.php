<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';
require_once 'utils/signup_login.php';

// Initialize $profile_user_id variable to avoid undefined variable warnings
$profile_user_id = null;

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    if (isset($_SESSION['user_id'])) {
        $profile_user_id = $_SESSION['user_id'];
    } else {
        // Handle the case when 'user_id' is not set in the session
        // For example, redirect to the login page or display an error message.
        // You can also set a default user ID here if applicable.
    }
} else {
    $profile_user_id = $_GET['user_id'];
}

// Database connection
$conn = get_db_connection();

// Fetch user profile information
$username = ''; // Initialize $username variable
if (isset($_SESSION['user_row'])) {
    $row = $_SESSION['user_row'];
    if (isset($row['username'])) {
        $username = $row['username'];
    } else {
        $error = "Username not found!";
    }
} else {
    $sql = "SELECT * FROM users WHERE id='$profile_user_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_row'] = $row;
        if (isset($row['username'])) {
            $username = $row['username'];
        } else {
            $error = "Username not found!";
        }
    } else {
        $error = "User profile not found!";
    }
}

// Fetch user's information
$userInfo = array();
if (isset($_SESSION['user_info'])) {
    $userInfo = $_SESSION['user_info'];
} else {
    $sql_user_info = "SELECT * FROM users WHERE id='$profile_user_id'";
    $result_user_info = mysqli_query($conn, $sql_user_info);
    if (mysqli_num_rows($result_user_info) > 0) {
        $row_user_info = mysqli_fetch_assoc($result_user_info);
        $userInfo = $row_user_info;
        $_SESSION['user_info'] = $userInfo;
    }
}

mysqli_close($conn);

include 'template/index_template.php';
?>
