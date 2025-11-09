<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

$sql = "SELECT 
            r.report_id, r.reason, r.created_at, r.item_id, r.comment_id,
            reporter.first_name as reporter_fname, reporter.last_name as reporter_lname,
            item.item_name,
            comment.comment_text
        FROM reports r
        JOIN users reporter ON r.reporter_id = reporter.user_id
        LEFT JOIN items item ON r.item_id = item.item_id
        LEFT JOIN comments comment ON r.comment_id = comment.comment_id
        WHERE r.status = 'pending'
        ORDER BY r.created_at ASC";

$reports = $conn->query($sql);
?>
<main class="container mt-4">
    <h1>Manage Reports</h1>
    <p>Review user-submitted reports of inappropriate or false content.</p>
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>"><?php echo $_SESSION['message']; ?></div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); endif; ?>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Reported Content</th>
                        <th>Reason</th>
                        <th>Reported By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports && $reports->num_rows > 0): while($report = $reports->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($report['item_id']): ?>
                                <strong>Post:</strong> "<?php echo htmlspecialchars(substr($report['item_name'], 0, 50)); ?>..."
                                <a href="../item_details.php?id=<?php echo $report['item_id']; ?>" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                            <?php else: ?>
                                <strong>Comment:</strong> "<?php echo htmlspecialchars(substr($report['comment_text'], 0, 50)); ?>..."
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                        <td><?php echo htmlspecialchars($report['reporter_fname'] . ' ' . $report['reporter_lname']); ?></td>
                        <td><?php echo date("M d, Y", strtotime($report['created_at'])); ?></td>
                        <td>
                            <a href="resolve_report.php?id=<?php echo $report['report_id']; ?>&action=dismiss" class="btn btn-sm btn-success" onclick="return confirm('Dismiss this report?');">Dismiss</a>
                            <a href="resolve_report.php?id=<?php echo $report['report_id']; ?>&action=delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete the content and resolve this report?');">Delete Content</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center">No pending reports.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>