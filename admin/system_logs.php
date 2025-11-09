<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

$sql = "SELECT 
            l.action, l.created_at,
            u.first_name, u.last_name, u.school_id
        FROM system_logs l
        JOIN users u ON l.admin_id = u.user_id
        ORDER BY l.created_at DESC
        LIMIT 200"; // Limit to the most recent 200 logs for performance

$logs = $conn->query($sql);
?>
<main class="container mt-4">
    <h1>System Activity Logs</h1>
    <p>This page shows the most recent 200 actions performed by administrators.</p>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Administrator</th>
                            <th>Action Performed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs && $logs->num_rows > 0): while($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date("M d, Y, g:i A", strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?> (<?php echo htmlspecialchars($log['school_id']); ?>)</td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center">No system logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php 
$conn->close(); 
require_once '../includes/footer.php'; 
?>