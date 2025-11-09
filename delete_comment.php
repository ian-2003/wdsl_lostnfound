<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security check: User must be logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

$item_id = 0; // Default redirect item_id

if (isset($_GET['id'])) {
    $comment_id = intval($_GET['id']);
    
    // First, find the item_id for redirecting back
    $sql_find_item = "SELECT item_id, user_id FROM comments WHERE comment_id = ?";
    if($stmt_find = $conn->prepare($sql_find_item)) {
        $stmt_find->bind_param("i", $comment_id);
        $stmt_find->execute();
        $result = $stmt_find->get_result();
        if($result->num_rows == 1) {
            $comment = $result->fetch_assoc();
            $item_id = $comment['item_id'];
            $comment_owner_id = $comment['user_id'];
            
            // Authorization check: Is the logged-in user the owner or an admin?
            if ($_SESSION['id'] == $comment_owner_id || $_SESSION['role'] == 'admin') {
                $sql_delete = "DELETE FROM comments WHERE comment_id = ?";
                if ($stmt_delete = $conn->prepare($sql_delete)) {
                    $stmt_delete->bind_param("i", $comment_id);
                    $stmt_delete->execute();
                }
            }
        }
    }
}

// Redirect back to the item details page
if ($item_id > 0) {
    header("location: item_details.php?id=" . $item_id);
} else {
    // Fallback redirect if item_id wasn't found
    header("location: index.php");
}
exit();
?>```
---

#### **3. Updated File: `item_details.php`**

This is a significant update to the previous version. We are now fetching comments from the database and displaying them in a list. The placeholder comment section is replaced with dynamic PHP code that generates the comment list.

**Instructions:** Replace the entire content of your existing `item_details.php` file with this new, updated code.

```php
<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Initialize variables
$item = null;
$comments = [];
$item_not_found = false;

// Check if an ID is present in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $item_not_found = true;
} else {
    $item_id = intval($_GET['id']);

    // --- 1. Fetch Item Details ---
    $sql_item = "SELECT i.item_id, i.user_id, i.item_name, i.description, i.photo, i.category, i.location_found, i.date_found, i.post_type, i.status, i.created_at, u.first_name, u.last_name 
            FROM items i 
            JOIN users u ON i.user_id = u.user_id 
            WHERE i.item_id = ?";

    if ($stmt_item = $conn->prepare($sql_item)) {
        $stmt_item->bind_param("i", $item_id);
        $stmt_item->execute();
        $result_item = $stmt_item->get_result();

        if ($result_item->num_rows == 1) {
            $item = $result_item->fetch_assoc();

            $is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
            $is_visible = $item['status'] == 'approved' || $item['status'] == 'claimed';

            if (!$is_admin && !$is_visible) {
                $item = null;
                $item_not_found = true;
            } else {
                // --- 2. Fetch Comments for this Item ---
                $sql_comments = "SELECT c.comment_id, c.comment_text, c.created_at, u.user_id, u.first_name, u.last_name, u.profile_picture 
                                 FROM comments c 
                                 JOIN users u ON c.user_id = u.user_id 
                                 WHERE c.item_id = ? 
                                 ORDER BY c.created_at ASC";
                if ($stmt_comments = $conn->prepare($sql_comments)) {
                    $stmt_comments->bind_param("i", $item_id);
                    $stmt_comments->execute();
                    $result_comments = $stmt_comments->get_result();
                    while ($row = $result_comments->fetch_assoc()) {
                        $comments[] = $row;
                    }
                }
            }
        } else {
            $item_not_found = true;
        }
        $stmt_item->close();
    } else {
        $item_not_found = true;
    }
}
?>

<main class="container mt-4 mb-5">
    <?php if ($item): ?>
        <!-- Item Details Card (No changes here from previous step) -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><?php echo htmlspecialchars($item['item_name']); ?></h2>
                <?php
                    $status = $item['status'];
                    $post_type = $item['post_type'];
                    $badge_class = 'bg-secondary';
                    $badge_text = ucfirst($status);

                    if ($status == 'approved') {
                        $badge_class = ($post_type == 'found') ? 'bg-success' : 'bg-warning text-dark';
                        $badge_text = ucfirst($post_type);
                    } elseif ($status == 'claimed') {
                        $badge_class = 'bg-info';
                    } elseif ($status == 'pending') {
                        $badge_class = 'bg-warning text-dark';
                    } elseif ($status == 'rejected') {
                        $badge_class = 'bg-danger';
                    }
                ?>
                <span class="badge <?php echo $badge_class; ?> fs-6"><?php echo $badge_text; ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <img src="uploads/item_photos/<?php echo htmlspecialchars($item['photo'] ? $item['photo'] : 'default-image.png'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['item_name']); ?>" style="width: 100%; height: 350px; object-fit: cover;">
                    </div>
                    <div class="col-md-7">
                        <h4>Description</h4>
                        <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        <ul class="list-group list-group-flush mt-4">
                            <li class="list-group-item"><i class="fas fa-layer-group me-2 text-primary"></i><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></li>
                            <li class="list-group-item"><i class="fas fa-map-marker-alt me-2 text-primary"></i><strong>Location:</strong> <?php echo htmlspecialchars($item['location_found']); ?></li>
                            <li class="list-group-item"><i class="fas fa-calendar-alt me-2 text-primary"></i><strong>Date <?php echo ucfirst($item['post_type']); ?>:</strong> <?php echo date("F j, Y", strtotime($item['date_found'])); ?></li>
                            <li class="list-group-item"><i class="fas fa-user me-2 text-primary"></i><strong>Posted By:</strong> <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></li>
                        </ul>
                        <?php if ($item['status'] == 'approved' && $item['post_type'] == 'found'): ?>
                            <div class="d-grid mt-4"><button class="btn btn-success btn-lg"><i class="fas fa-hand-paper me-2"></i> Claim This Item</button></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW AND UPDATED COMMENT SYSTEM -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Comments (<?php echo count($comments); ?>)</h4>
            </div>
            <div class="card-body">
                <!-- New Comment Form -->
                <div class="mb-4">
                    <form action="post_comment.php" method="POST">
                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                        <div class="mb-2">
                            <textarea class="form-control" name="comment_text" rows="2" placeholder="Write a public comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                    </form>
                </div>

                <!-- Existing Comments List -->
                <div id="comments-list">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">No comments yet. Be the first to say something!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="uploads/profile_pictures/<?php echo htmlspecialchars($comment['profile_picture']); ?>" class="rounded-circle" alt="User" style="width: 50px; height: 50px;">
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                                            <small class="text-muted ms-2"><?php echo date("M d, Y \a\\t g:i A", strtotime($comment['created_at'])); ?></small>
                                        </div>
                                        <?php if ($_SESSION['id'] == $comment['user_id'] || $_SESSION['role'] == 'admin'): ?>
                                            <a href="delete_comment.php?id=<?php echo $comment['comment_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this comment?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-1 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php elseif ($item_not_found): ?>
        <div class="alert alert-danger text-center">
            <h4>Item Not Found</h4>
            <p>The item you are looking for does not exist or you do not have permission to view it.</p>
            <a href="index.php" class="btn btn-primary">Return to Homepage</a>
        </div>
    <?php endif; ?>
</main>

<?php
$conn->close();
require_once 'includes/footer.php';
?>