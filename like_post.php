<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the 'post_id' parameter exists and the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    // Sanitize the 'post_id' parameter to prevent SQL injection
    $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);

    // Database connection
    $conn = get_db_connection();

    // Check if the user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        // User is not logged in, return an error response
        http_response_code(401); // Unauthorized status code
        echo "Unauthorized: Please log in to like a post.";
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Call the 'add_like' function to add the like to the database
    $result = add_like($conn, $user_id, $post_id);
    if ($result === true) {
        // Like added successfully
        echo "success";
        exit();
    } else {
        // Unable to add the like, return an error response
        http_response_code(500); // Internal server error status code
        echo "Error: $result";
        exit();
    }
} else {
    // Invalid request, return an error response
    http_response_code(400); // Bad request status code
    echo "Bad request: Invalid parameters.";
    exit();
}
?>
