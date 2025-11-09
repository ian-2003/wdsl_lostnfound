<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SPCF Lost & Found</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="site-wrapper"> <!-- Use the same wrapper for consistency -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="../assets/img/logo.png" alt="SPCF Logo" style="height: 40px;"> Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="adminNavbar">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_posts.php"><i class="fas fa-clipboard-list me-1"></i> Manage Posts</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users me-1"></i> Manage Users</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_claims.php"><i class="fas fa-check-double me-1"></i> Manage Claims</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_reports.php"><i class="fas fa-exclamation-triangle me-1"></i> Manage Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="system_logs.php"><i class="fas fa-history me-1"></i> System Logs</a></li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-globe me-1"></i> View Site</a></li>
            <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
    
    <!-- Admin pages will be wrapped in this main tag -->
    <main>