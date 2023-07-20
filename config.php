<?php
session_start();
// Database configuration
$dbConfig = [
    'host' => '213.171.200.29',
    'username' => 'Exudiz',
    'password' => 'Amie9586W!',
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

$uploadConfig = array(
    'uploadPath' => 'uploads/', // Specify the upload directory path
    'allowedExtensions' => array('jpg', 'jpeg', 'png'), // Specify the allowed file extensions
    'maxFileSize' => 2 * 1024 * 1024 // Specify the maximum file size (in bytes)
);
?>
