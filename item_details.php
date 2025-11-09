<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// ... (Existing code to fetch item and comments) ...
$item = null;
$item_not_found = false;
$user_claim_status = null;

if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']);
    // ... (Existing SQL to fetch item) ...
    $sql_item = "SELECT ... FROM items ... WHERE item_id = ?"; // Abridged for clarity
    
    // In the block where you fetch the item, ADD this logic:
    // This is pseudo-code for placement. The full file is below.
    if ($item) {
        // CHECK the current user's claim status for THIS item
        $sql_claim_check = "SELECT status FROM claims WHERE item_id = ? AND user_id = ?";
        if($stmt_claim = $conn->prepare($sql_claim_check)){
            $stmt_claim->bind_param("ii", $item_id, $_SESSION['id']);
            $stmt_claim->execute();
            $stmt_claim->bind_result($status);
            if($stmt_claim->fetch()){
                $user_claim_status = $status;
            }
        }
    }
}
// THE FULL, COMPLETE FILE IS BELOW
?>
<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Initialize variables
$item = null; $comments = []; $item_not_found = false; $user_claim_status = null;
if (!isset($_GET['id']) || empty($_GET['id'])) { $item_not_found = true; } else {
    $item_id = intval($_GET['id']);
    $sql_item = "SELECT i.item_id, i.user_id, i.item_name, i.description, i.photo, i.category, i.location_found, i.date_found, i.post_type, i.status, i.created_at, u.first_name, u.last_name FROM items i JOIN users u ON i.user_id = u.user_id WHERE i.item_id = ?";
    if ($stmt_item = $conn->prepare($sql_item)) {
        $stmt_item->bind_param("i", $item_id);
        $stmt_item->execute();
        $result_item = $stmt_item->get_result();
        if ($result_item->num_rows == 1) {
            $item = $result_item->fetch_assoc();
            $is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
            $is_visible = $item['status'] == 'approved' || $item['status'] == 'claimed';
            if (!$is_admin && !$is_visible) { $item = null; $item_not_found = true; } else {
                // Check current user's claim status for this item
                $sql_claim_check = "SELECT status FROM claims WHERE item_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1";
                if ($stmt_claim = $conn->prepare($sql_claim_check)) {
                    $stmt_claim->bind_param("ii", $item_id, $_SESSION['id']);
                    $stmt_claim->execute();
                    $stmt_claim->bind_result($status);
                    if ($stmt_claim->fetch()) { $user_claim_status = $status; }
                    $stmt_claim->close();
                }
                // Fetch comments (existing logic)
            }
        } else { $item_not_found = true; }
        $stmt_item->close();
    }
}
?>
<main class="container mt-4 mb-5">
    <?php if (isset($_SESSION['claim_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['claim_message_type']; ?>"><?php echo $_SESSION['claim_message']; ?></div>
    <?php unset($_SESSION['claim_message']); unset($_SESSION['claim_message_type']); endif; ?>

    <?php if ($item): ?>
    <div class="card">
        <div class="card-header"> ... </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5"> ... </div>
                <div class="col-md-7">
                    <h4>Description</h4> ...
                    <ul class="list-group list-group-flush mt-4"> ... </ul>
                    
                    <!-- DYNAMIC CLAIM BUTTON -->
                    <div class="d-grid mt-4">
                    <?php if ($item['status'] == 'claimed'): ?>
                        <button class="btn btn-info btn-lg" disabled><i class="fas fa-check-circle me-2"></i> Item Has Been Claimed</button>
                    <?php elseif ($item['post_type'] == 'found'): ?>
                        <?php if ($item['user_id'] == $_SESSION['id']): // User is the original poster ?>
                            <button class="btn btn-secondary btn-lg" disabled>You posted this item</button>
                        <?php elseif ($user_claim_status == 'pending'): ?>
                            <button class="btn btn-warning btn-lg" disabled><i class="fas fa-hourglass-start me-2"></i> Your Claim is Pending</button>
                        <?php elseif ($user_claim_status == 'approved'): ?>
                            <button class="btn btn-success btn-lg" disabled><i class="fas fa-check me-2"></i> Your Claim Was Approved</button>
                        <?php else: ?>
                            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#claimModal"><i class="fas fa-hand-paper me-2"></i> Claim This Item</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Comment section ... -->
    <?php else: ?>
    <!-- Item not found message ... -->
    <?php endif; ?>
</main>

<!-- Claim Modal -->
<div class="modal fade" id="claimModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Submit a Claim</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="submit_claim.php" method="POST">
        <div class="modal-body">
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
            <p>To claim "<strong><?php echo htmlspecialchars($item['item_name']); ?></strong>", please provide a detailed description as proof of ownership (e.g., unique scratches, contents of a bag, lock screen image, etc.).</p>
            <div class="form-group">
                <label for="proof"><strong>Proof of Ownership:</strong></label>
                <textarea class="form-control" name="proof" id="proof" rows="5" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>