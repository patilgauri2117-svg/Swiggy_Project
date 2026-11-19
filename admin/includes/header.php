<?php
/*
 * admin/includes/header.php
 * Layout for admin pages: top bar + side menu.
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Admin') ?> | Swiggy Admin</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body { background: var(--light-grey); }
        .admin-sidebar { background: #171a29; min-height: 100vh; }
        .admin-sidebar .nav-link { color: #b5b7c0; border-radius: 8px; margin-bottom: 4px; }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active { background: var(--orange); color: #fff; }
        .stat-card { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,.06); }
        .stat-card .icon { width: 56px; height: 56px; border-radius: 14px; display: flex;
                           align-items: center; justify-content: center; font-size: 1.6rem; color: #fff; }
        .table img { width: 56px; height: 56px; object-fit: cover; border-radius: 10px; }
        @media (max-width: 767px) { .admin-sidebar { min-height: auto; } }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Side menu -->
        <nav class="col-md-3 col-lg-2 admin-sidebar p-3">
            <a href="dashboard.php" class="logo d-block mb-4 text-decoration-none">
                <i class="bi bi-bag-heart-fill"></i> Swiggy
            </a>
            <ul class="nav flex-column">
                <li><a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a class="nav-link <?= $currentPage == 'food-items.php' ? 'active' : '' ?>" href="food-items.php"><i class="bi bi-egg-fried"></i> Food Items</a></li>
                <li><a class="nav-link <?= $currentPage == 'orders.php' ? 'active' : '' ?>" href="orders.php"><i class="bi bi-receipt"></i> Orders</a></li>
                <li><a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe"></i> View Website</a></li>
                <li><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Page content -->
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><?= e($pageTitle ?? 'Admin') ?></h3>
                <span class="text-muted"><i class="bi bi-person-circle"></i> <?= e($_SESSION['admin_name']) ?></span>
            </div>
            <?php show_flash(); ?>
