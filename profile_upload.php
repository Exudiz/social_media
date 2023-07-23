<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Check if the user ID is provided in the URL
if (!isset($_GET['user_id'])) {
    $profile_user_id = $_SESSION['user_id'];
} else {
    $profile_user_id = $_GET['user_id'];
}

// Database connection
$conn = get_db_connection();

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

// Handle logo upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_logo'])) {
    // Fetch the logo details
    $logo = $_FILES['logo'];
    $logoName = $logo['name'];
    $logoTmpName = $logo['tmp_name'];
    $logoError = $logo['error'];

    // Check for logo upload errors
    if ($logoError === UPLOAD_ERR_OK) {
        // Get the image dimensions
        $logoSize = getimagesize($logoTmpName);
        $logoWidth = $logoSize[0];
        $logoHeight = $logoSize[1];

        // Check if the dimensions match the required size
        if ($logoWidth == 253 && $logoHeight == 199) {
            // Specify the directory where the logo images will be stored
            $uploadDirectory = 'uploads/';

            // Generate a unique filename for the logo image
            $logoPath = $uploadDirectory . uniqid() . '_' . $logoName;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($logoTmpName, $logoPath)) {
                // Store the logo path in the database for the user
                $sql_update_logo = "UPDATE users SET logo='$logoPath' WHERE id='$profile_user_id'";
                if (mysqli_query($conn, $sql_update_logo)) {
                    $_SESSION['user_logo'] = $logoPath;
                    $message = "Logo uploaded successfully!";
                } else {
                    $error = "Error updating logo path: " . mysqli_error($conn);
                }
            } else {
                $error = "Error uploading logo: Failed to move the file.";
            }
        } else {
            $error = "Invalid logo dimensions. Please upload an image with dimensions 132 x 132 pixels.";
        }
    } else {
        $error = "Error uploading logo: " . $logoError;
    }
}

// Handle banner upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_banner'])) {
    // Fetch the banner details
    $banner = $_FILES['banner'];
    $bannerName = $banner['name'];
    $bannerTmpName = $banner['tmp_name'];
    $bannerError = $banner['error'];

    // Check for banner upload errors
    if ($bannerError === UPLOAD_ERR_OK) {
        // Get the image dimensions
        $bannerSize = getimagesize($bannerTmpName);
        $bannerWidth = $bannerSize[0];
        $bannerHeight = $bannerSize[1];

        // Check if the dimensions match the required size
        if ($bannerWidth == 1315 && $bannerHeight == 500) {
            // Specify the directory where the banner images will be stored
            $uploadDirectory = 'uploads/';

            // Generate a unique filename for the banner image
            $bannerPath = $uploadDirectory . uniqid() . '_' . $bannerName;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($bannerTmpName, $bannerPath)) {
                // Store the banner path in the database for the user
                $sql_update_banner = "UPDATE users SET banner='$bannerPath' WHERE id='$profile_user_id'";
                if (mysqli_query($conn, $sql_update_banner)) {
                    $_SESSION['user_banner'] = $bannerPath;
                    $message = "Banner uploaded successfully!";
                } else {
                    $error = "Error updating banner path: " . mysqli_error($conn);
                }
            } else {
                $error = "Error uploading banner: Failed to move the file.";
            }
        } else {
            $error = "Invalid banner dimensions. Please upload an image with dimensions 1315 x 500 pixels.";
        }
    } else {
        $error = "Error uploading banner: " . $bannerError;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile Upload</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile_upload.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Profile Upload</h2>
    <?php if (isset($message)) : ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <label for="logo">Profile Logo:</label>
        <input type="file" name="logo" id="logo" required>
        <small>Maximum dimensions: 253 x 199 pixels</small>
        <br>
        <button type="submit" class="btn btn-primary" name="upload_logo">Upload Logo</button>
    </form>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <label for="banner">Profile Banner:</label>
        <input type="file" name="banner" id="banner">
        <small>Maximum dimensions: 1315 x 500 pixels</small>
        <br>
        <button type="submit" class="btn btn-primary" name="upload_banner">Upload Banner</button>
    </form>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
