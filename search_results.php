<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

// Search functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];

    // Fetch search results from the database
    $sql = "SELECT * FROM users WHERE username LIKE '%$searchTerm%'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $searchResults = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $searchResults[] = $row;
            }
        }
    } else {
        $error = "Error executing search query: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Users</title>
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
    <h2>Search Users</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="form-group">
            <label for="search_term">Search Term:</label>
            <input type="text" class="form-control" id="search_term" name="search_term" required>
        </div>
        <button type="submit" class="btn btn-primary" name="search">Search</button>
    </form>

    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (!empty($searchResults)) { ?>
        <h3>Search Results</h3>
        <ul>
            <?php foreach ($searchResults as $result) { ?>
                <li><a href="profile.php?user_id=<?php echo $result['id']; ?>"><?php echo $result['username']; ?></a></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>No users found.</p>
    <?php } ?>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
