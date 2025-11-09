<?php
require_once 'includes/header.php'; // The header will already have the DB connection
// No need to require db_connect.php again as it's in the header

$user_id = $_SESSION['id'];
$notifications = [];

// Fetch all notifications for the current user, newest first
$sql = "SELECT notification_id, message, link, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
?>

<main class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Notifications</h1>
        <?php if (!empty($notifications) && $unread_count > 0): // $unread_count is from the header ?>
            <a href="mark_notifications_read.php" class="btn btn-primary">Mark all as Read</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <div class="list-group-item text-center p-4">
                        <p class="text-muted mb-0">You have no notifications yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="list-group-item list-group-item-action <?php echo !$notification['is_read'] ? 'bg-light' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small class="text-muted"><?php echo date("M d, Y", strtotime($notification['created_at'])); ?></small>
                            </div>
                            <small class="text-muted"><?php echo date("g:i A", strtotime($notification['created_at'])); ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>