<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$user_id_to_delete = isset($_GET['id']) ? intval($_GET['id']) : 0;
$logged_in_user_id = $_SESSION['id'];

if ($user_id_to_delete > 0 && $user_id_to_delete != $logged_in_user_id) {
    $user_info_result = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $user_id_to_delete");
    if ($user_info_result->num_rows > 0) {
        $user_info = $user_info_result->fetch_assoc();
        $user_name = $user_info['first_name'] . ' ' . $user_info['last_name'];

        $sql = "DELETE FROM users WHERE user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                log_system_action($conn, "Deleted user: " . $user_name . " (User ID: " . $user_id_to_delete . ")");
                $_SESSION['message'] = "User account and all associated content have been permanently deleted.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting user.";
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = "User to be deleted not found.";
        $_SESSION['message_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "Invalid action. You cannot delete your own account.";
    $_SESSION['message_type'] = "danger";
}

$conn->close();
header("location: manage_users.php");
exit();
?>