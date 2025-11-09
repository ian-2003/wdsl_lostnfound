<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

// Fetch all users to display
$sql = "SELECT user_id, school_id, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Users</h1>
        <a href="add_user.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New User</a>
    </div>

    <?php 
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>School ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Date Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['school_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo date("M d, Y", strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                        <?php // Prevent admin from deleting their own account ?>
                                        <?php if ($user['user_id'] != $_SESSION['id']): ?>
                                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No users found.</td>
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