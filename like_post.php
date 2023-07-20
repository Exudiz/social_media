<?php
// Retrieve the post ID from the AJAX request
$postId = $_POST['post_id'];

// Increment the like count in the database
$sql_like_post = "INSERT INTO post_likes (post_id) VALUES (?)";
$stmt_like_post = mysqli_prepare($conn, $sql_like_post);
mysqli_stmt_bind_param($stmt_like_post, "i", $postId);

if (mysqli_stmt_execute($stmt_like_post)) {
    // Get the updated like count
    $sql_like_count = "SELECT COUNT(*) AS likes_count FROM post_likes WHERE post_id = ?";
    $stmt_like_count = mysqli_prepare($conn, $sql_like_count);
    mysqli_stmt_bind_param($stmt_like_count, "i", $postId);
    mysqli_stmt_execute($stmt_like_count);
    $result_like_count = mysqli_stmt_get_result($stmt_like_count);

    if ($result_like_count && mysqli_num_rows($result_like_count) > 0) {
        $row_like_count = mysqli_fetch_assoc($result_like_count);
        $likesCount = $row_like_count['likes_count'];

        // Return the updated like count as a JSON response
        echo json_encode(['likes_count' => $likesCount]);
    }
} else {
    // Handle error
    echo json_encode(['error' => 'Failed to like the post.']);
}
?>
