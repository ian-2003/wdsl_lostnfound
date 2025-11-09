<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin') { exit; }

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($report_id > 0 && !empty($action)) {
    $sql_report = "SELECT item_id, comment_id FROM reports WHERE report_id = ?";
    $stmt_report = $conn->prepare($sql_report);
    $stmt_report->bind_param("i", $report_id);
    $stmt_report->execute();
    $result = $stmt_report->get_result();
    $report = $result->fetch_assoc();

    if ($report) {
        if ($action == 'dismiss') {
            $sql_resolve = "UPDATE reports SET status = 'resolved' WHERE report_id = ?";
            $stmt_resolve = $conn->prepare($sql_resolve);
            $stmt_resolve->bind_param("i", $report_id);
            $stmt_resolve->execute();

            log_system_action($conn, "Dismissed report #" . $report_id . ".");
            $_SESSION['message'] = "Report has been dismissed.";
            $_SESSION['message_type'] = "success";

        } elseif ($action == 'delete') {
            if ($report['item_id']) {
                $conn->query("DELETE FROM items WHERE item_id = " . $report['item_id']);
            } elseif ($report['comment_id']) {
                $conn->query("DELETE FROM comments WHERE comment_id = " . $report['comment_id']);
            }
            
            $sql_resolve = "UPDATE reports SET status = 'resolved' WHERE report_id = ?";
            $stmt_resolve = $conn->prepare($sql_resolve);
            $stmt_resolve->bind_param("i", $report_id);
            $stmt_resolve->execute();

            log_system_action($conn, "Deleted content and resolved report #" . $report_id . ".");
            $_SESSION['message'] = "The reported content has been deleted and the report is resolved.";
            $_SESSION['message_type'] = "success";
        }
    }
} else {
    $_SESSION['message'] = "Invalid action.";
    $_SESSION['message_type'] = "danger";
}

header("location: manage_reports.php");
exit();
?>