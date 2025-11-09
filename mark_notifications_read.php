<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security check: User must be logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

$user_id = $_SESSION['id'];

// SQL to update all unread notifications for the user to be read
$sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect back to the notifications page
header("location: notifications.php");
exit();
?>