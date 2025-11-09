<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security check: User must be logged in to comment
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Redirect to login if not logged in
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

// Check if the form was submitted correctly
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id']) && isset($_POST['comment_text'])) {
    
    $item_id = intval($_POST['item_id']);
    $user_id = $_SESSION['id'];
    $comment_text = trim($_POST['comment_text']);

    // Basic validation: ensure comment is not empty
    if (!empty($comment_text)) {
        // Insert the comment into the database
        $sql = "INSERT INTO comments (item_id, user_id, comment_text) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $item_id, $user_id, $comment_text);
            $stmt->execute();
            
            // --- Notification Logic ---
            // Get the user_id of the person who posted the item
            $sql_owner = "SELECT user_id, item_name FROM items WHERE item_id = ?";
            if ($stmt_owner = $conn->prepare($sql_owner)) {
                $stmt_owner->bind_param("i", $item_id);
                $stmt_owner->execute();
                $result_owner = $stmt_owner->get_result();
                $item_info = $result_owner->fetch_assoc();
                $item_owner_id = $item_info['user_id'];
                $item_name = $item_info['item_name'];
                
                // Only send a notification if the commenter is not the item owner
                if ($item_owner_id != $user_id) {
                    $message = htmlspecialchars($_SESSION['first_name']) . " commented on your post for '" . htmlspecialchars($item_name) . "'.";
                    $link = "item_details.php?id=" . $item_id;
                    
                    $sql_notify = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
                    if ($stmt_notify = $conn->prepare($sql_notify)) {
                        $stmt_notify->bind_param("iss", $item_owner_id, $message, $link);
                        $stmt_notify->execute();
                    }
                }
            }
        }
    }
}

// Redirect back to the item page, regardless of outcome
// A more robust system might add error handling with session messages
header("location: item_details.php?id=" . $item_id);
exit();
?>