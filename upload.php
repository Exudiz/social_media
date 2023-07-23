<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the form is submitted
if (isset($_POST['upload_photo'])) {
    // Get the user ID
    $user_id = $_SESSION['user_id'];

    // Check if a file is selected
    if (isset($_FILES['profile_photo'])) {
        $file = $_FILES['profile_photo'];

        // Get the file details
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // Check for errors
        if ($file_error === 0) {
            // Generate a unique file name
            $new_file_name = uniqid('profile_') . '_' . $file_name;

            // Set the file destination
            $file_destination = 'uploads/' . $new_file_name;

            // Move the file to the destination folder
            if (move_uploaded_file($file_tmp, $file_destination)) {
                // Save the file path in the database
                $conn = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
                if ($conn) {
                    $sql = "UPDATE users SET profile_photo='$file_destination' WHERE id='$user_id'";
                    mysqli_query($conn, $sql);
                    mysqli_close($conn);
                    $_SESSION['profile_photo'] = $file_destination;
                    $message = "Profile photo uploaded successfully!";
                } else {
                    $error = "Database connection failed.";
                }
            } else {
                $error = "Error uploading the file.";
            }
        } else {
            $error = "Error: " . $file_error;
        }
    } else {
        $error = "No file selected.";
    }
}

// Redirect back to the profile page
header("Location: profile.php");
exit();
?>
