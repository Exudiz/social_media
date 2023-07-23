<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Fetch all hashtags and their counts
function get_all_hashtags($conn) {
    $sql_hashtags = "SELECT tag, COUNT(*) AS count FROM hashtags GROUP BY tag";
    $result_hashtags = mysqli_query($conn, $sql_hashtags);
    $hashtags = array();

    if (mysqli_num_rows($result_hashtags) > 0) {
        while ($row_hashtags = mysqli_fetch_assoc($result_hashtags)) {
            $hashtags[] = $row_hashtags;
        }
    }

    return $hashtags;
}

// Database connection
$conn = get_db_connection();

$hashtags = get_all_hashtags($conn);

mysqli_close($conn);
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Hashtags</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<div class="container">
    <h1>Hashtags</h1>

    <?php if (count($hashtags) > 0): ?>
        <?php foreach ($hashtags as $tag): ?>
            <h3><?php echo htmlspecialchars($tag['tag']); ?></h3>
            <p>Count: <?php echo $tag['count']; ?></p>

            <?php
            $conn = get_db_connection(); // Re-establish the database connection
            $posts = get_posts_by_hashtag($tag['tag'], $conn);
            mysqli_close($conn); // Close the database connection after fetching posts

            if (count($posts) > 0):
            ?>
                <ul>
                    <?php foreach ($posts as $post): ?>
                        <li><?php echo htmlspecialchars($post['content']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No posts found for this hashtag.</p>
            <?php endif; ?>

            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hashtags found.</p>
    <?php endif; ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
