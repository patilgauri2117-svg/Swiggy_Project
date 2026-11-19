<?php
/*
 * config/database.php
 * Connects to MySQL using PDO. Every page includes this file.
 * Default XAMPP settings: user "root" with an empty password.
 */

$host     = 'localhost';
$dbname   = 'food_delivery';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Show errors as exceptions and return rows as associative arrays
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage() .
        '<br>Make sure MySQL is running in XAMPP and you imported database/food_delivery.sql');
}

// Start the session once for the whole website (used for login and cart)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
