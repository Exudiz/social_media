<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';
require_once 'utils/ip.php';

// Database connection
$conn = get_db_connection();

// Handle user registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if email or username is already taken
    $sql = "SELECT * FROM users WHERE email='$email' OR username='$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $error = "Email or username already taken!";
    } elseif ($password !== $confirmPassword) {
        $error = "Password and confirm password do not match!";
    } else {
        // Insert new user into the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, username, password) VALUES ('$email', '$username', '$hashedPassword')";
        if (mysqli_query($conn, $sql)) {
            $message = "Registration successful!";
            $_SESSION['user_id'] = mysqli_insert_id($conn); // Store the new user ID in the session
            header("Location: index.php");
            exit();
        } else {
            $error = "Error registering user: " . mysqli_error($conn);
        }
    }
}

// Handle user login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $emailOrUsername = $_POST['email_or_username'];
    $password = $_POST['password'];

    // Check if the entered email/username and password match
    $sql = "SELECT * FROM users WHERE (email='$emailOrUsername' OR username='$emailOrUsername')";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $storedPassword = $row['password'];

        // Verify the password
        if (password_verify($password, $storedPassword)) {
            // Login successful, store user ID and username in session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Fetch the user's actual location using IP geolocation
            $userIp = get_user_ip();
            $ipInfo = file_get_contents("http://ip-api.com/json/{$userIp}");
            $ipInfoData = json_decode($ipInfo, true);

            // Extract location data from the response
            $lastLoginLocation = isset($ipInfoData['city']) ? $ipInfoData['city'] : 'Unknown Location';

            // Update last login time and location in the database
            $currentDateTime = date('Y-m-d H:i:s');
            $profile_user_id = $row['id']; // The ID of the logged-in user

            // Update last_login and last_login_location fields in the database
            $sql_update_login = "UPDATE users SET last_login = '$currentDateTime', last_login_location = '$lastLoginLocation' WHERE id = '$profile_user_id'";
            mysqli_query($conn, $sql_update_login);

            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email/username or password!";
        }
    } else {
        $error = "Invalid email/username or password!";
    }
}

?>
