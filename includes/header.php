<?php
/*
 * includes/header.php
 * Common <head> section. Set $pageTitle before including this file.
 */
require_once __DIR__ . '/../config/database.php';
$pageTitle = isset($pageTitle) ? $pageTitle . ' | Swiggy' : 'Swiggy - Order Food Online';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>

    <!-- Bootstrap 5 CSS and Bootstrap Icons (saved in assets/vendor, so no internet needed) -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Our own styles -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
