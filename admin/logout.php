<?php
/*
 * admin/logout.php - Logs the admin out
 */
require_once __DIR__ . '/../config/database.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name']);
redirect('login.php');
