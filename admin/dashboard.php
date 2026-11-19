<?php
/*
 * admin/dashboard.php - Totals and latest orders
 */
require_once __DIR__ . '/includes/auth.php';

$totalFoods     = $pdo->query("SELECT COUNT(*) FROM food_items")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalOrders    = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Delivered','Cancelled')")->fetchColumn();
$totalSales     = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'Delivered'")->fetchColumn();

$latestOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon" style="background:#fc8019"><i class="bi bi-egg-fried"></i></div>
            <div><div class="text-muted small">Food Items</div><h3 class="mb-0 fw-bold"><?= (int) $totalFoods ?></h3></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon" style="background:#0d6efd"><i class="bi bi-people"></i></div>
            <div><div class="text-muted small">Customers</div><h3 class="mb-0 fw-bold"><?= (int) $totalCustomers ?></h3></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon" style="background:#198754"><i class="bi bi-receipt"></i></div>
            <div><div class="text-muted small">Total Orders</div><h3 class="mb-0 fw-bold"><?= (int) $totalOrders ?></h3>
                <small class="text-muted"><?= (int) $pendingOrders ?> active</small></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon" style="background:#6f42c1"><i class="bi bi-currency-rupee"></i></div>
            <div><div class="text-muted small">Delivered Sales</div><h3 class="mb-0 fw-bold"><?= rupees($totalSales) ?></h3></div>
        </div></div>
    </div>
</div>

<div class="card stat-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Latest Orders</h5>
            <a href="orders.php" class="btn btn-sm btn-orange">View all orders</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>#</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($latestOrders as $o): ?>
                    <tr>
                        <td><?= (int) $o['id'] ?></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><?= rupees($o['total_amount']) ?></td>
                        <td><span class="badge <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
                        <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$latestOrders): ?>
                    <tr><td colspan="5" class="text-muted text-center">No orders yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
