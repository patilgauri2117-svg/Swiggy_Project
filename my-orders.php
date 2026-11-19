<?php
/*
 * my-orders.php - Order history of the logged-in customer
 */
require_once 'config/database.php';

if (!is_logged_in()) {
    set_flash('warning', 'Please log in to see your orders.');
    redirect('login.php');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Prepared once, used for every order
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");

$pageTitle = 'My Orders';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <?php show_flash(); ?>
    <h3 class="section-title">My <span>Orders</span></h3>

    <?php if (count($orders) === 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-receipt text-orange" style="font-size: 4rem;"></i>
            <h5 class="mt-3">No orders yet</h5>
            <a href="restaurants.php" class="btn btn-orange mt-2">Order Now</a>
        </div>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
        <?php
        $itemStmt->execute([$order['id']]);
        $orderItems = $itemStmt->fetchAll();
        ?>
        <div class="card summary-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="mb-0">Order #<?= (int) $order['id'] ?></h5>
                        <small class="text-muted"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></small>
                    </div>
                    <span class="badge rounded-pill fs-6 align-self-start <?= status_class($order['status']) ?>">
                        <?= e($order['status']) ?>
                    </span>
                </div>
                <?php foreach ($orderItems as $oi): ?>
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= e($oi['food_name']) ?> × <?= (int) $oi['quantity'] ?></span>
                        <span><?= rupees($oi['price'] * $oi['quantity']) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($order['address']) ?></small>
                    <strong>Total: <?= rupees($order['total_amount']) ?> <small class="text-muted fw-normal">(<?= e($order['payment_method']) ?>, incl. delivery)</small></strong>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
