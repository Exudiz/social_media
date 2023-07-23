<?php
require_once 'utils/config.php';
require_once 'utils/functions.php';

// Database connection
$conn = get_db_connection();

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $receiver = $_POST['receiver'];
    $messageContent = $_POST['message_content'];
    $senderId = $_SESSION['user_id'];

    // Validate the form inputs
    if (empty($receiver) || empty($messageContent)) {
        $error = "Receiver and message content cannot be empty!";
    } else {
        // Retrieve the receiver's user ID based on username or user ID
        $sql_receiver = "SELECT id FROM users WHERE username = '$receiver' OR id = '$receiver'";
        $result_receiver = mysqli_query($conn, $sql_receiver);

        if (mysqli_num_rows($result_receiver) > 0) {
            $row_receiver = mysqli_fetch_assoc($result_receiver);
            $receiverId = $row_receiver['id'];

            // Insert the message into the database
            $sql_send_message = "INSERT INTO messages (sender_id, receiver_id, message_content, created_at)
                                VALUES ('$senderId', '$receiverId', '$messageContent', NOW())";

            if (mysqli_query($conn, $sql_send_message)) {
                $message = "Message sent successfully!";
            } else {
                $error = "Error sending message: " . mysqli_error($conn);
            }
        } else {
            $error = "Receiver not found!";
        }
    }
}

// Fetch the recipient user's ID
$recipientId = $_SESSION['user_id'];

// Retrieve received messages from the database
$sql = "SELECT m.*, u.username
        FROM messages m
        INNER JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = '$recipientId'
        ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
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
    <h2>Send Message</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="form-group">
            <label for="receiver">Receiver (Username or User ID):</label>
            <input type="text" class="form-control" name="receiver" id="receiver" required>
        </div>
        <div class="form-group">
            <label for="message_content">Message Content:</label>
            <textarea class="form-control" name="message_content" id="message_content" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="send_message">Send Message</button>
    </form>

    <h3>Received Messages</h3>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="message">
                <div class="message-sender">Sender: <?php echo $row['username']; ?></div>
                <div class="message-content"><?php echo $row['message_content']; ?></div>
                <div class="message-time">Received at: <?php echo $row['created_at']; ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No received messages.</p>
    <?php endif; ?>
</div>
<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
