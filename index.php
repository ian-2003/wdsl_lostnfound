<?php
require_once 'includes/header.php'; // The header already includes db_connect.php

// --- 1. INITIALIZE SEARCH & FILTER VARIABLES ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';

// --- 2. FETCH DISTINCT CATEGORIES FOR THE DROPDOWN ---
$categories = [];
$sql_cat = "SELECT DISTINCT category FROM items WHERE status = 'approved' AND category IS NOT NULL AND category != '' ORDER BY category ASC";
$result_cat = $conn->query($sql_cat);
if ($result_cat) {
    while ($row = $result_cat->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}


// --- 3. DYNAMICALLY BUILD THE SQL QUERY ---
$sql = "SELECT i.item_id, i.item_name, i.description, i.photo, i.location_found, i.date_found, i.post_type, u.first_name, u.last_name 
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        WHERE i.status = 'approved'";

$params = [];
$types = '';

// Add search term condition (searches name and description)
if (!empty($search_term)) {
    $sql .= " AND (i.item_name LIKE ? OR i.description LIKE ?)";
    $like_search_term = "%" . $search_term . "%";
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $types .= 'ss';
}

// Add category filter condition
if (!empty($filter_category)) {
    $sql .= " AND i.category = ?";
    $params[] = $filter_category;
    $types .= 's';
}

// Add post type (lost/found) filter condition
if (!empty($filter_post_type)) {
    $sql .= " AND i.post_type = ?";
    $params[] = $filter_post_type;
    $types .= 's';
}

$sql .= " ORDER BY i.created_at DESC";

// --- 4. PREPARE AND EXECUTE THE QUERY ---
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<main class="container mt-4">

    <?php 
    if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Welcome, <?php echo htmlspecialchars($_SESSION["first_name"]); ?>!</h1>
            <p class="col-md-8 fs-4">This is the central hub for all lost and found items on campus. Use the filters below to find what you're looking for.</p>
            <a href="post_item.php" class="btn btn-primary btn-lg" type="button">Report a Lost/Found Item</a>
        </div>
    </div>

    <!-- UPDATED Search and Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="index.php" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" placeholder="Search by item name or description..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php if ($filter_category == $category) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="post_type" class="form-select">
                            <option value="">Lost or Found</option>
                            <option value="lost" <?php if ($filter_post_type == 'lost') echo 'selected'; ?>>Lost</option>
                            <option value="found" <?php if ($filter_post_type == 'found') echo 'selected'; ?>>Found</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                        <a href="index.php" class="btn btn-secondary ms-2" title="Reset Filters"><i class="fas fa-sync-alt"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($item = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="uploads/item_photos/<?php echo htmlspecialchars($item['photo'] ? $item['photo'] : 'default-image.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['item_name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                            
                            <span class="badge <?php echo $item['post_type'] == 'found' ? 'bg-success' : 'bg-warning text-dark'; ?> mb-2 align-self-start">
                                <?php echo ucfirst($item['post_type']); ?>
                            </span>

                            <p class="card-text text-muted flex-grow-1"><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                            
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><i class="fas fa-map-marker-alt me-2"></i><strong>Location:</strong> <?php echo htmlspecialchars($item['location_found']); ?></li>
                                <li class="list-group-item"><i class="fas fa-calendar-alt me-2"></i><strong>Date:</strong> <?php echo date("F j, Y", strtotime($item['date_found'])); ?></li>
                            </ul>
                            
                            <a href="item_details.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary mt-auto">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col">
                <div class="alert alert-info text-center">
                    <h4>No Results Found</h4>
                    <p>Try adjusting your search or filter criteria. <a href="index.php">Click here to reset all filters.</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
require_once 'includes/footer.php';
?>