<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { exit; }

require_once 'includes/db_connect.php';

$redirect_item_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = !empty($_POST['item_id']) ? intval($_POST['item_id']) : null;
    $comment_id = !empty($_POST['comment_id']) ? intval($_POST['comment_id']) : null;
    $reporter_id = $_SESSION['id'];
    $reason = trim($_POST['reason']);
    
    // Determine the redirect item_id
    if ($item_id) { $redirect_item_id = $item_id; } 
    elseif ($comment_id) {
        $sql_find = "SELECT item_id FROM comments WHERE comment_id = ?";
        if($stmt_find = $conn->prepare($sql_find)) {
            $stmt_find->bind_param("i", $comment_id);
            $stmt_find->execute();
            $stmt_find->bind_result($found_item_id);
            if($stmt_find->fetch()) { $redirect_item_id = $found_item_id; }
        }
    }
    
    if (empty($reason) || (!$item_id && !$comment_id)) {
        $_SESSION['report_message'] = "Invalid report submission.";
        $_SESSION['report_message_type'] = "danger";
    } else {
        $sql = "INSERT INTO reports (item_id, comment_id, reporter_id, reason) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iiis", $item_id, $comment_id, $reporter_id, $reason);
            if ($stmt->execute()) {
                $_SESSION['report_message'] = "Your report has been submitted for review. Thank you for helping keep our community safe.";
                $_SESSION['report_message_type'] = "success";
            } else {
                $_SESSION['report_message'] = "There was an error submitting your report.";
                $_SESSION['report_message_type'] = "danger";
            }
        }
    }
}

if ($redirect_item_id > 0) {
    header("location: item_details.php?id=" . $redirect_item_id);
} else {
    header("location: index.php"); // Fallback
}
exit();
?>