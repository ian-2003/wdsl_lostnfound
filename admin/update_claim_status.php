<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin') { exit; }

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($claim_id > 0 && ($action == 'approved' || $action == 'rejected')) {
    $sql_claim = "SELECT item_id, user_id FROM claims WHERE claim_id = ?";
    $stmt_claim = $conn->prepare($sql_claim);
    $stmt_claim->bind_param("i", $claim_id);
    $stmt_claim->execute();
    $result = $stmt_claim->get_result();
    $claim = $result->fetch_assoc();

    if ($claim) {
        $item_id = $claim['item_id'];
        $claimant_id = $claim['user_id'];
        $item_name_result = $conn->query("SELECT item_name FROM items WHERE item_id = $item_id");
        $item_name = $item_name_result->fetch_assoc()['item_name'];

        $conn->begin_transaction();
        try {
            $sql_update_claim = "UPDATE claims SET status = ? WHERE claim_id = ?";
            $stmt_update_claim = $conn->prepare($sql_update_claim);
            $stmt_update_claim->bind_param("si", $action, $claim_id);
            $stmt_update_claim->execute();

            $message = "Your claim for '" . htmlspecialchars($item_name) . "' has been " . $action . ".";
            $link = "../item_details.php?id=" . $item_id;
            $sql_notify = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
            $stmt_notify = $conn->prepare($sql_notify);
            $stmt_notify->bind_param("iss", $claimant_id, $message, $link);
            $stmt_notify->execute();
            
            if ($action == 'approved') {
                $conn->query("UPDATE items SET status = 'claimed' WHERE item_id = $item_id");
                
                $other_claims_result = $conn->query("SELECT claim_id, user_id FROM claims WHERE item_id = $item_id AND status = 'pending'");
                while ($other_claim = $other_claims_result->fetch_assoc()) {
                    $conn->query("UPDATE claims SET status = 'rejected' WHERE claim_id = " . $other_claim['claim_id']);
                    $msg_reject = "Your claim for '" . htmlspecialchars($item_name) . "' was not approved as the item has been claimed by another user.";
                    $stmt_notify->bind_param("iss", $other_claim['user_id'], $msg_reject, $link);
                    $stmt_notify->execute();
                }
            }
            
            log_system_action($conn, ucfirst($action) . " claim #" . $claim_id . " for item '" . $item_name . "'.");
            
            $conn->commit();
            $_SESSION['message'] = "The claim has been successfully " . $action . ".";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "An error occurred: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
} else {
    $_SESSION['message'] = "Invalid action.";
    $_SESSION['message_type'] = "danger";
}

header("location: manage_claims.php");
exit();
?>