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

// Handle updating user's information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    $dob = $_POST['dob'];
    $relationship = $_POST['relationship'];
    $gender = $_POST['gender'];
    $location = $_POST['location'];

    // Verify the current password
    $sql = "SELECT password FROM users WHERE id='$profile_user_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $storedPassword = $row['password'];
        if (!password_verify($currentPassword, $storedPassword)) {
            $error = "Invalid current password!";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New password and confirm password do not match!";
        } else {
            // Update the user's information in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql_update_info = "UPDATE users SET username='$username', email='$email', phone='$phone', password='$hashedPassword', first_name='$first_name', last_name='$last_name', address='$address', bio='$bio', dob='$dob', relationship='$relationship', gender='$gender', location='$location' WHERE id='$profile_user_id'";
            if (mysqli_query($conn, $sql_update_info)) {
                $message = "Profile information updated successfully!";
                // Update the user_info session variable
                $_SESSION['user_info'] = array(
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashedPassword,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'address' => $address,
                    'bio' => $bio,
                    'dob' => $dob,
                    'relationship' => $relationship,
                    'gender' => $gender,
                    'location' => $location
                );
            } else {
                $error = "Error updating profile information: " . mysqli_error($conn);
            }
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Settings</title>
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
    <h2>User Settings</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?user_id=" . $profile_user_id); ?>" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo $userInfo['username']; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $userInfo['email']; ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $userInfo['phone']; ?>">
        </div>
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $userInfo['first_name']; ?>">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $userInfo['last_name']; ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?php echo $userInfo['address']; ?>">
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $userInfo['bio']; ?></textarea>
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $userInfo['dob']; ?>">
        </div>
        <div class="form-group">
            <label for="relationship">Relationship</label>
            <select class="form-control" id="relationship" name="relationship">
                <option value="Single" <?php if ($userInfo['relationship'] === 'Single') echo 'selected'; ?>>Single</option>
                <option value="Married" <?php if ($userInfo['relationship'] === 'Married') echo 'selected'; ?>>Married</option>
                <option value="Divorced" <?php if ($userInfo['relationship'] === 'Divorced') echo 'selected'; ?>>Divorced</option>
                <option value="In a Relationship" <?php if ($userInfo['relationship'] === 'In a Relationship') echo 'selected'; ?>>In a Relationship</option>
            </select>
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select class="form-control" id="gender" name="gender">
                <option value="Male" <?php if ($userInfo['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if ($userInfo['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                <option value="Other" <?php if ($userInfo['gender'] === 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="<?php echo $userInfo['location']; ?>">
        </div>
        <button type="submit" class="btn btn-primary" name="update_info">Update Info</button>
    </form>
</div>
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
