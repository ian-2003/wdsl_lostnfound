<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

// Fetch all posts with user information
$sql = "SELECT i.item_id, i.item_name, i.status, i.created_at, u.first_name, u.last_name 
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        ORDER BY i.created_at DESC";
$result = $conn->query($sql);
?>

<main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Posts</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Submitted By</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                            $status = $row['status'];
                                            $badge_class = 'bg-secondary';
                                            if ($status == 'approved') $badge_class = 'bg-success';
                                            if ($status == 'pending') $badge_class = 'bg-warning';
                                            if ($status == 'rejected') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($status == 'pending'): ?>
                                            <a href="update_post_status.php?id=<?php echo $row['item_id']; ?>&status=approved" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this item?');"><i class="fas fa-check"></i> Approve</a>
                                            <a href="update_post_status.php?id=<?php echo $row['item_id']; ?>&status=rejected" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this item?');"><i class="fas fa-times"></i> Reject</a>
                                        <?php else: ?>
                                            <!-- Optionally add a delete button or other actions here -->
                                            <a href="../item_details.php?id=<?php echo $row['item_id']; ?>" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No posts found.</td>
                            </tr>
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