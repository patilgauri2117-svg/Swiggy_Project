<?php
/*
 * admin/orders.php - View all customer orders and update their status
 */
require_once __DIR__ . '/includes/auth.php';

$statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];

// ---------- Update status ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status  = $_POST['status'] ?? '';

    if (in_array($status, $statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        set_flash('success', 'Order #' . $orderId . ' status changed to ' . $status . '.');
    } else {
        set_flash('danger', 'Invalid status.');
    }
    redirect('orders.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

// ---------- Filter by status (optional) ----------
$filter = $_GET['status'] ?? '';
if (in_array($filter, $statuses)) {
    $stmt = $pdo->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id
                           WHERE o.status = ? ORDER BY o.created_at DESC, o.id DESC");
    $stmt->execute([$filter]);
} else {
    $filter = '';
    $stmt = $pdo->query("SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id
                         ORDER BY o.created_at DESC, o.id DESC");
}
$orders = $stmt->fetchAll();

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");

$pageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Status filter -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="orders.php" class="btn btn-sm <?= $filter == '' ? 'btn-orange' : 'btn-outline-orange' ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a href="orders.php?status=<?= urlencode($s) ?>" class="btn btn-sm <?= $filter == $s ? 'btn-orange' : 'btn-outline-orange' ?>"><?= e($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="card stat-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Date</th><th>Status</th><th style="min-width:230px">Update Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <?php $itemStmt->execute([$o['id']]); ?>
                    <tr>
                        <td><strong><?= (int) $o['id'] ?></strong></td>
                        <td>
                            <strong><?= e($o['customer_name']) ?></strong><br>
                            <small class="text-muted"><?= e($o['phone']) ?> · <?= e($o['email']) ?></small><br>
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($o['address']) ?></small>
                        </td>
                        <td class="small">
                            <?php foreach ($itemStmt->fetchAll() as $oi): ?>
                                <?= e($oi['food_name']) ?> × <?= (int) $oi['quantity'] ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td><?= rupees($o['total_amount']) ?><br><small class="text-muted"><?= e($o['payment_method']) ?></small></td>
                        <td class="small"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                        <td><span class="badge <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach ($statuses as $s): ?>
                                        <option <?= $o['status'] == $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-orange">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr><td colspan="7" class="text-center text-muted">No orders found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
