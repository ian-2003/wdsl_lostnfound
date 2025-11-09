<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

$sql = "SELECT 
            c.claim_id, c.proof_of_ownership, c.created_at,
            i.item_id, i.item_name,
            u.user_id, u.first_name, u.last_name
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        JOIN users u ON c.user_id = u.user_id
        WHERE c.status = 'pending'
        ORDER BY c.created_at ASC";

$claims = $conn->query($sql);
?>
<main class="container mt-4">
    <h1>Manage Pending Claims</h1>
    <p>Review user claims and approve or reject them.</p>

    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>"><?php echo $_SESSION['message']; ?></div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Claimant</th>
                            <th>Item Claimed</th>
                            <th>Proof of Ownership</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($claims && $claims->num_rows > 0): while($claim = $claims->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?></td>
                            <td>
                                <a href="../item_details.php?id=<?php echo $claim['item_id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($claim['item_name']); ?>
                                </a>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($claim['proof_of_ownership'])); ?></td>
                            <td><?php echo date("M d, Y", strtotime($claim['created_at'])); ?></td>
                            <td>
                                <a href="update_claim_status.php?id=<?php echo $claim['claim_id']; ?>&action=approved" class="btn btn-sm btn-success" onclick="return confirm('Approve this claim? This will reject all other pending claims for this item.');"><i class="fas fa-check"></i> Approve</a>
                                <a href="update_claim_status.php?id=<?php echo $claim['claim_id']; ?>&action=rejected" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this claim?');"><i class="fas fa-times"></i> Reject</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">No pending claims.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php $conn->close(); require_once '../includes/footer.php'; ?>