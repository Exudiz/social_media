<?php
require_once 'config.php';

$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_content'])) {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Insert the comment into the database
    $sql_add_comment = "INSERT INTO comments (post_id, user_id, username, content) VALUES ('$post_id', '$user_id', '$username', '$comment_content')";
    if (mysqli_query($conn, $sql_add_comment)) {
        // Comment added successfully
        // You can redirect to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        $error = "Error adding comment: " . mysqli_error($conn);
    }
}

?>
