<?php
/*
 * admin/includes/auth.php
 * Include this at the top of every admin page.
 * If the admin is not logged in, send them to the admin login page.
 */
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('login.php');
}
