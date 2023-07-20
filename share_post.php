<?php
// Retrieve the post ID from the AJAX request
$postId = $_POST['post_id'];

// Increment the share count in the database
$sql_share_post = "INSERT INTO post_shares (post_id) VALUES (?)";
$stmt_share_post = mysqli_prepare($conn, $sql_share_post);
mysqli_stmt_bind_param($stmt_share_post, "i", $postId);

if (mysqli_stmt_execute($stmt_share_post)) {
    // Get the updated share count
    $sql_share_count = "SELECT COUNT(*) AS shares_count FROM post_shares WHERE post_id = ?";
    $stmt_share_count = mysqli_prepare($conn, $sql_share_count);
    mysqli_stmt_bind_param($stmt_share_count, "i", $postId);
    mysqli_stmt_execute($stmt_share_count);
    $result_share_count = mysqli_stmt_get_result($stmt_share_count);

    if ($result_share_count && mysqli_num_rows($result_share_count) > 0) {
        $row_share_count = mysqli_fetch_assoc($result_share_count);
        $sharesCount = $row_share_count['shares_count'];

        // Return the updated share count as a JSON response
        echo json_encode(['shares_count' => $sharesCount]);
    }
} else {
    // Handle error
    echo json_encode(['error' => 'Failed to share the post.']);
}
?>
