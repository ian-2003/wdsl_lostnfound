<?php
// We must start the session on every page that requires login.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require 'db_connect.php';

// --- 1. Fetch user's profile picture for the navbar ---
$sql_user_data = "SELECT profile_picture FROM users WHERE user_id = ?";
$profile_picture = 'default.png'; // Default picture
if ($stmt_user = $conn->prepare($sql_user_data)) {
    $stmt_user->bind_param("i", $_SESSION['id']);
    if ($stmt_user->execute()) {
        $stmt_user->bind_result($db_pic);
        if ($stmt_user->fetch() && !empty($db_pic)) {
            $profile_picture = $db_pic;
        }
    }
    $stmt_user->close();
}

// --- 2. Fetch unread notification count ---
$unread_count = 0;
$sql_count = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE";
if ($stmt_count = $conn->prepare($sql_count)) {
    $stmt_count->bind_param("i", $_SESSION['id']);
    if ($stmt_count->execute()) {
        $stmt_count->bind_result($count);
        if ($stmt_count->fetch()) {
            $unread_count = $count;
        }
    }
    $stmt_count->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPCF Lost & Found</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Custom CSS (The new stylesheet) -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="site-wrapper">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
      <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo.png" alt="SPCF Logo" style="height: 40px;">
            SPCF Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="post_item.php"><i class="fas fa-plus-circle me-1"></i> Report Item</a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative" href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                <img src="uploads/profile_pictures/<?php echo htmlspecialchars($profile_picture); ?>" class="rounded-circle me-2" alt="User" style="width: 25px; height: 25px; object-fit: cover;">
                <?php echo htmlspecialchars($_SESSION["first_name"]); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                  <li><a class="dropdown-item" href="admin/index.php">Admin Dashboard</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- The <main> tag will now wrap the primary content of each page -->
    <main>