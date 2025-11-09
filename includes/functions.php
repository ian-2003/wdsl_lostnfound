<?php
/**
 * Logs a specific action performed by an administrator into the system_logs table.
 *
 * This function should be called AFTER a successful database operation within an admin script.
 *
 * @param mysqli $conn The active database connection object.
 * @param string $action_description A human-readable description of the action performed. e.g., "Approved post #123".
 */
function log_system_action($conn, $action_description) {
    // Check if the current user is a logged-in admin before logging.
    if (isset($_SESSION['id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $admin_id = $_SESSION['id'];
        
        $sql = "INSERT INTO system_logs (admin_id, action) VALUES (?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $admin_id, $action_description);
            // This is a "fire and forget" operation. We execute it but don't need to check the result
            // for the user's flow. In a high-security environment, you might add error logging here.
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>