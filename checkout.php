<?php
/*
 * checkout.php - Place an order (Cash on Delivery)
 * 1. Customer must be logged in.
 * 2. Totals are calculated again on the server from database prices.
 * 3. The order and its items are saved inside a transaction.
 */
require_once 'config/database.php';

if (!is_logged_in()) {
    set_flash('warning', 'Please log in to place your order.');
    redirect('login.php?redirect=checkout');
}

$userId = $_SESSION['user_id'];

// ---------- Order confirmation screen: checkout.php?success=ORDER_ID ----------
if (isset($_GET['success'])) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([(int) $_GET['success'], $userId]);
    $order = $stmt->fetch();
    if (!$order) {
        redirect('my-orders.php');
    }

    $pageTitle = 'Order Placed';
    require_once 'includes/header.php';
    ?>
    <div class="container py-5">
        <div class="card summary-card mx-auto text-center" style="max-width: 560px;">
            <div class="card-body p-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4.5rem;"></i>
                <h3 class="fw-bold mt-3">Order Placed Successfully!</h3>
                <p class="text-muted">Thank you, <?= e($order['customer_name']) ?>. Your food is being prepared.</p>
                <div class="bg-light-grey rounded-3 p-3 my-4 text-start">
                    <div class="d-flex justify-content-between"><span>Order ID</span><strong>#<?= (int) $order['id'] ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Amount</span><strong><?= rupees($order['total_amount']) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Payment</span><strong><?= e($order['payment_method']) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Status</span><strong><?= e($order['status']) ?></strong></div>
                </div>
                <a href="my-orders.php" class="btn btn-orange me-2">View My Orders</a>
                <a href="index.php" class="btn btn-outline-orange">Back to Home</a>
            </div>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// ---------- Cart must not be empty ----------
if (empty($_SESSION['cart'])) {
    set_flash('warning', 'Your cart is empty.');
    redirect('cart.php');
}

// ---------- Load cart items with current prices from the database ----------
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM food_items WHERE id IN ($placeholders)");
$stmt->execute($ids);

$items = [];
$itemTotal = 0;
foreach ($stmt->fetchAll() as $row) {
    $row['qty'] = (int) $_SESSION['cart'][$row['id']];
    $row['subtotal'] = $row['price'] * $row['qty'];
    $itemTotal += $row['subtotal'];
    $items[] = $row;
}
if (count($items) === 0) {
    $_SESSION['cart'] = [];
    redirect('cart.php');
}
$grandTotal = $itemTotal + DELIVERY_FEE;

// Customer details to pre-fill the form
$stmt = $pdo->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$name    = $user['name'];
$phone   = $user['phone'];
$address = $user['address'];
$errors  = [];

// ---------- Place the order ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (strlen($name) < 3) {
        $errors[] = 'Please enter your name.';
    }
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit mobile number.';
    }
    if (strlen($address) < 10) {
        $errors[] = 'Please enter your full delivery address.';
    }

    if (empty($errors)) {
        try {
            // Transaction: either the order AND all its items are saved, or nothing is saved
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, phone, address, total_amount, payment_method, status)
                                   VALUES (?, ?, ?, ?, ?, 'Cash on Delivery', 'Pending')");
            $stmt->execute([$userId, $name, $phone, $address, $grandTotal]);
            $orderId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, food_item_id, food_name, quantity, price)
                                   VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$orderId, $item['id'], $item['name'], $item['qty'], $item['price']]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];   // empty the cart
            redirect('checkout.php?success=' . $orderId);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Could not place the order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <h3 class="section-title"><span>Checkout</span></h3>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" class="needs-validation" novalidate>
        <div class="row g-4">
            <!-- Delivery details -->
            <div class="col-lg-7">
                <div class="card summary-card">
                    <div class="card-body p-4">
                        <h5 class="mb-3"><i class="bi bi-geo-alt text-orange"></i> Delivery Details</h5>
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="name" class="form-control" value="<?= e($name) ?>" minlength="3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($phone) ?>"
                                   pattern="[6-9][0-9]{9}" maxlength="10" required>
                            <div class="invalid-feedback">Enter a 10-digit mobile number.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="address" class="form-control" rows="3" minlength="10" required><?= e($address) ?></textarea>
                            <div class="invalid-feedback">Please enter your full address.</div>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="bi bi-wallet2 text-orange"></i> Payment Method</h5>
                        <div class="form-check border rounded-3 p-3 ps-5">
                            <input class="form-check-input" type="radio" name="payment" id="cod" checked>
                            <label class="form-check-label fw-semibold" for="cod">
                                <i class="bi bi-cash-coin"></i> Cash on Delivery
                            </label>
                            <div class="small text-muted">Pay with cash when your food arrives.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order summary -->
            <div class="col-lg-5">
                <div class="card summary-card">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Order Summary</h5>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= e($item['name']) ?> × <?= $item['qty'] ?></span>
                                <span><?= rupees($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2"><span>Item Total</span><span><?= rupees($itemTotal) ?></span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><span><?= rupees(DELIVERY_FEE) ?></span></div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span><?= rupees($grandTotal) ?></span></div>
                        <button type="submit" class="btn btn-orange w-100 py-2">Place Order</button>
                        <a href="cart.php" class="btn btn-link w-100 text-muted">Back to cart</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
