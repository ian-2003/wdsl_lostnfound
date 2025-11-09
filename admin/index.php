<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_items = $conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc()['count'];
$pending_items = $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'pending'")->fetch_assoc()['count'];
$pending_claims = $conn->query("SELECT COUNT(*) as count FROM claims WHERE status = 'pending'")->fetch_assoc()['count']; // New stat
$unresolved_reports = $conn->query("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<main class="container mt-4">
    <h1 class="mb-4">System Overview</h1>
    <div class="row">
        <!-- Pending Claims Card -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h2 class="card-title"><?php echo $pending_claims; ?></h2><p class="card-text">Pending Claims</p></div>
                        <i class="fas fa-check-double fa-3x"></i>
                    </div>
                </div>
                <a href="manage_claims.php" class="card-footer text-white text-decoration-none">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- Pending Items Card -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h2 class="card-title"><?php echo $pending_items; ?></h2><p class="card-text">Pending Posts</p></div>
                        <i class="fas fa-hourglass-half fa-3x"></i>
                    </div>
                </div>
                <a href="manage_posts.php" class="card-footer text-white text-decoration-none">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- Total Users Card -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h2 class="card-title"><?php echo $total_users; ?></h2><p class="card-text">Total Users</p></div>
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
                <a href="manage_users.php" class="card-footer text-white text-decoration-none">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- Unresolved Reports Card -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h2 class="card-title"><?php echo $unresolved_reports; ?></h2><p class="card-text">Unresolved Reports</p></div>
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                    </div>
                </div>
                <a href="manage_reports.php" class="card-footer text-white text-decoration-none">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- Activity Log Placeholder -->
</main>

<?php
$conn->close();
require_once '../includes/footer.php'; 
?>