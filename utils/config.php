<?php
// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '1010',
    'dbname' => 'social_media'
];

// Function to establish a database connection
function get_db_connection() {
    global $dbConfig;

    $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

// Set the time zone
date_default_timezone_set('Europe/London');

// Fetch last login time and location
$lastLoginTime = '';
$lastLoginLocation = '';
if (isset($userInfo['last_login'])) {
    // Convert the last login time to UK format
    $lastLoginTime = date('d/m/Y H:i:s', strtotime($userInfo['last_login']));
    $lastLoginLocation = $userInfo['last_login_location'];
}

$uploadConfig = array(
    'uploadPath' => 'uploads/', // Specify the upload directory path
    'allowedExtensions' => array('jpg', 'jpeg', 'png'), // Specify the allowed file extensions
    'maxFileSize' => 2 * 1024 * 1024 // Specify the maximum file size (in bytes)
);
?>
