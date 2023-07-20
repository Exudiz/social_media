<?php

// Function to calculate time ago
function time_ago($datetime) {
    $current_time = new DateTime();
    $post_time = new DateTime($datetime);
    $interval = $post_time->diff($current_time);

    if ($interval->y > 0) {
        return $interval->format("%y year" . ($interval->y > 1 ? "s" : "") . " ago");
    } elseif ($interval->m > 0) {
        return $interval->format("%m month" . ($interval->m > 1 ? "s" : "") . " ago");
    } elseif ($interval->d > 0) {
        return $interval->format("%d day" . ($interval->d > 1 ? "s" : "") . " ago");
    } elseif ($interval->h > 0) {
        return $interval->format("%h hour" . ($interval->h > 1 ? "s" : "") . " ago");
    } elseif ($interval->i > 0) {
        return $interval->format("%i minute" . ($interval->i > 1 ? "s" : "") . " ago");
    } else {
        return "Just now";
    }
}

// Function to get the count of a specific hashtag
function get_hashtag_count($conn, $tag) {
    $sql_hashtag_count = "SELECT COUNT(*) AS count FROM hashtags WHERE tag = ?";
    $stmt_hashtag_count = mysqli_prepare($conn, $sql_hashtag_count);
    mysqli_stmt_bind_param($stmt_hashtag_count, "s", $tag);
    mysqli_stmt_execute($stmt_hashtag_count);
    $result_hashtag_count = mysqli_stmt_get_result($stmt_hashtag_count);

    if ($result_hashtag_count) {
        $row_hashtag_count = mysqli_fetch_assoc($result_hashtag_count);
        return $row_hashtag_count['count'];
    }

    return 0;
}

// Function to get hashtags for each post
function get_post_hashtags($conn, $post_id) {
    $sql_hashtags = "SELECT * FROM hashtags WHERE post_id = ?";
    $stmt_hashtags = mysqli_prepare($conn, $sql_hashtags);
    mysqli_stmt_bind_param($stmt_hashtags, "i", $post_id);
    mysqli_stmt_execute($stmt_hashtags);
    $result_hashtags = mysqli_stmt_get_result($stmt_hashtags);

    $hashtags = array();

    if ($result_hashtags) {
        while ($row_hashtags = mysqli_fetch_assoc($result_hashtags)) {
            $hashtags[] = array(
                'tag' => $row_hashtags['tag']
            );
        }
        mysqli_free_result($result_hashtags);
    } else {
        $error = "Error retrieving hashtags for post ID: $post_id - " . mysqli_error($conn);
    }

    return $hashtags;
}

// Function to get comments for a post
function get_comments($conn, $post_id) {
    $sql_comments = "SELECT * FROM comments WHERE post_id=?";
    $stmt_comments = mysqli_prepare($conn, $sql_comments);
    mysqli_stmt_bind_param($stmt_comments, "i", $post_id);
    mysqli_stmt_execute($stmt_comments);
    $result_comments = mysqli_stmt_get_result($stmt_comments);
    $comments = array();

    if (mysqli_num_rows($result_comments) > 0) {
        while ($row_comments = mysqli_fetch_assoc($result_comments)) {
            // Fetch the user information for each comment
            $comment_user_id = $row_comments['user_id'];
            $comment_user = get_user_info($conn, $comment_user_id);

            // Fetch the replies for each comment
            $comment_id = $row_comments['id'];
            $replies = get_replies($conn, $comment_id);

            $row_comments['username'] = $comment_user['username'];
            $row_comments['logo'] = $comment_user['logo'];
            $row_comments['replies'] = $replies;
            $comments[] = $row_comments;
        }
    }

    return $comments;
}

// Function to get replies for a comment
function get_replies($conn, $comment_id) {
    $sql_replies = "SELECT * FROM replies WHERE comment_id=?";
    $stmt_replies = mysqli_prepare($conn, $sql_replies);
    mysqli_stmt_bind_param($stmt_replies, "i", $comment_id);
    mysqli_stmt_execute($stmt_replies);
    $result_replies = mysqli_stmt_get_result($stmt_replies);
    $replies = array();

    if (mysqli_num_rows($result_replies) > 0) {
        while ($row_replies = mysqli_fetch_assoc($result_replies)) {
            // Fetch the user information for each reply
            $reply_user_id = $row_replies['user_id'];
            $reply_user = get_user_info($conn, $reply_user_id);

            $row_replies['username'] = $reply_user['username'];
            $row_replies['logo'] = $reply_user['logo'];
            $replies[] = $row_replies;
        }
    }

    return $replies;
}

// Function to fetch user information
function get_user_info($conn, $user_id) {
    $sql_user = "SELECT username, logo FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($conn, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    if (mysqli_num_rows($result_user) > 0) {
        return mysqli_fetch_assoc($result_user);
    }

    return null;
}

// Function to add a post
function add_post($conn, $user_id, $username, $postContent, $visibility) {
    $sql_add_post = "INSERT INTO posts (user_id, username, content, created_at, visibility) VALUES (?, ?, ?, NOW(), ?)";
    $stmt_add_post = mysqli_prepare($conn, $sql_add_post);
    mysqli_stmt_bind_param($stmt_add_post, "isss", $user_id, $username, $postContent, $visibility);

    if (mysqli_stmt_execute($stmt_add_post)) {
        return mysqli_insert_id($conn);
    }

    return null;
}

// Function to add a hashtag
function add_hashtag($conn, $post_id, $tag) {
    $sql_add_hashtag = "INSERT INTO hashtags (post_id, tag) VALUES (?, ?)";
    $stmt_add_hashtag = mysqli_prepare($conn, $sql_add_hashtag);
    mysqli_stmt_bind_param($stmt_add_hashtag, "is", $post_id, $tag);
    mysqli_stmt_execute($stmt_add_hashtag);
}

// Function to get posts associated with a hashtag
function get_posts_by_hashtag($hashtag, $conn) {
    $sql_posts = "SELECT p.* FROM posts p JOIN hashtags h ON p.id = h.post_id WHERE h.tag = ? ORDER BY p.created_at DESC";
    $stmt_posts = mysqli_prepare($conn, $sql_posts);
    mysqli_stmt_bind_param($stmt_posts, "s", $hashtag);
    mysqli_stmt_execute($stmt_posts);
    $result_posts = mysqli_stmt_get_result($stmt_posts);
    $posts = array();

    if (mysqli_num_rows($result_posts) > 0) {
        while ($row_posts = mysqli_fetch_assoc($result_posts)) {
            $posts[] = $row_posts;
        }
    }

    return $posts;
}

?>
