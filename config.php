<?php
// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '1010',
    'dbname' => 'social_media'
];

// Set the time zone
date_default_timezone_set('Europe/London');

$uploadConfig = array(
    'uploadPath' => 'uploads/', // Specify the upload directory path
    'allowedExtensions' => array('jpg', 'jpeg', 'png'), // Specify the allowed file extensions
    'maxFileSize' => 2 * 1024 * 1024 // Specify the maximum file size (in bytes)
);
?>
