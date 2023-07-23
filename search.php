<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

// Search functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];

    // Store the search query in the search history table
    $user_id = $_SESSION['user_id'];
    $timestamp = date("Y-m-d H:i:s");
    $sql_search_history = "INSERT INTO search_history (user_id, search_term, search_date) VALUES ('$user_id', '$searchTerm', '$timestamp')";
    mysqli_query($conn, $sql_search_history);

    // Fetch user search results from the database
    $sql_user = "SELECT id, username FROM users
            WHERE username LIKE '%$searchTerm%'
            OR email LIKE '%$searchTerm%'
            OR phone LIKE '%$searchTerm%'";
    $result_user = mysqli_query($conn, $sql_user);

    if ($result_user) {
        $userResults = [];
        if (mysqli_num_rows($result_user) > 0) {
            while ($row_user = mysqli_fetch_assoc($result_user)) {
                $userResults[] = $row_user;
            }
        }
    } else {
        $error = "Error executing user search query: " . mysqli_error($conn);
    }

    // Fetch hashtag search results from the database
    $sql_hashtag = "SELECT hashtags.tag, posts.content, users.username, users.id AS user_id, COUNT(hashtags.tag) AS tag_count
            FROM hashtags
            INNER JOIN posts ON hashtags.post_id = posts.id
            INNER JOIN users ON posts.user_id = users.id
            WHERE hashtags.tag LIKE '%$searchTerm%'
            GROUP BY hashtags.tag";
    $result_hashtag = mysqli_query($conn, $sql_hashtag);

    if ($result_hashtag) {
        $hashtagResults = [];
        if (mysqli_num_rows($result_hashtag) > 0) {
            while ($row_hashtag = mysqli_fetch_assoc($result_hashtag)) {
                $hashtagResults[] = $row_hashtag;
            }
        }
    } else {
        $error = "Error executing hashtag search query: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Users and Hashtags</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Search Users and Hashtags</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="form-group">
            <label for="search_term">Search Term:</label>
            <input type="text" class="form-control" id="search_term" name="search_term" required>
        </div>
        <button type="submit" class="btn btn-primary" name="search">Search</button>
    </form>

    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>

    <?php if (!empty($userResults)) { ?>
        <h3>User Results</h3>
        <?php foreach ($userResults as $result) { ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><a href="user_profile.php?user_id=<?php echo $result['id']; ?>"><?php echo $result['username']; ?></a></h5>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($hashtagResults)) { ?>
        <h3>Hashtag Results</h3>
        <?php foreach ($hashtagResults as $result) { ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $result['tag']; ?></h5>
                    <p class="card-text">Content: <?php echo $result['content']; ?></p>
                    <p class="card-text">Posted by: <a href="user_profile.php?user_id=<?php echo $result['user_id']; ?>"><?php echo $result['username']; ?></a></p>
                    <p class="card-text">Tag Count: <?php echo $result['tag_count']; ?></p>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (empty($userResults) && empty($hashtagResults)) { ?>
        <p>No users or hashtags found.</p>
    <?php } ?>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
