<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    $profile_user_id = $_SESSION['user_id'];
} else {
    $profile_user_id = $_GET['user_id'];
}

// Database connection
$conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>About</title>
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
    <h2>About <?php echo $username; ?></h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } else { ?>
        <table class="table">
            <tbody>
            <tr>
                <th>Username</th>
                <td><?php echo $userInfo['username']; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo $userInfo['email']; ?></td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td><?php echo $userInfo['phone']; ?></td>
            </tr>
            <tr>
                <th>First Name</th>
                <td><?php echo $userInfo['first_name']; ?></td>
            </tr>
            <tr>
                <th>Last Name</th>
                <td><?php echo $userInfo['last_name']; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo $userInfo['address']; ?></td>
            </tr>
            <tr>
                <th>Bio</th>
                <td><?php echo $userInfo['bio']; ?></td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td><?php echo $userInfo['dob']; ?></td>
            </tr>
            <tr>
                <th>Relationship</th>
                <td><?php echo $userInfo['relationship']; ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>
                    <?php
                    $gender = $userInfo['gender'];
                    if ($gender === 'prefer_not_to_say') {
                        echo 'Prefer Not to Say';
                    } else {
                        echo $gender;
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Location</th>
                <td><?php echo $userInfo['location']; ?></td>
            </tr>
            </tbody>
        </table>
    <?php } ?>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>