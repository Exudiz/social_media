<?php
// follow.php

// Include the necessary files
require_once 'config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the user_id parameter is present in the URL
if (isset($_GET['user_id'])) {
    $followee_id = $_GET['user_id'];
    $follower_id = $_SESSION['user_id'];

    // Create a new database connection
    $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

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
