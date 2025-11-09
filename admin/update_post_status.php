<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $item_id = intval($_GET['id']);
    $new_status = $_GET['status'];
    $allowed_statuses = ['approved', 'rejected'];

    if (in_array($new_status, $allowed_statuses)) {
        $sql_update = "UPDATE items SET status = ? WHERE item_id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("si", $new_status, $item_id);
            if ($stmt_update->execute()) {
                $sql_info = "SELECT user_id, item_name FROM items WHERE item_id = ?";
                if($stmt_info = $conn->prepare($sql_info)){
                    $stmt_info->bind_param("i", $item_id);
                    $stmt_info->execute();
                    $result = $stmt_info->get_result();
                    $item_info = $result->fetch_assoc();
                    
                    log_system_action($conn, ucfirst($new_status) . " post: '" . $item_info['item_name'] . "' (Item ID: " . $item_id . ")");

                    $message = "Your post for '" . htmlspecialchars($item_info['item_name']) . "' has been " . $new_status . ".";
                    $link = "../item_details.php?id=" . $item_id;

                    $sql_notify = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
                    if($stmt_notify = $conn->prepare($sql_notify)){
                        $stmt_notify->bind_param("iss", $item_info['user_id'], $message, $link);
                        $stmt_notify->execute();
                    }
                    $stmt_info->close();
                }
            }
            $stmt_update->close();
        }
    }
}

header("location: manage_posts.php");
exit();
?>