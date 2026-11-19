<?php
/*
 * logout.php - Logs the customer out (the cart is kept).
 */
require_once 'config/database.php';

unset($_SESSION['user_id'], $_SESSION['user_name']);
set_flash('success', 'You have been logged out.');
redirect('index.php');
