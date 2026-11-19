<?php
/*
 * cart.php - Shopping cart
 * The cart is stored in the session as:  $_SESSION['cart'][food_id] = quantity
 * Prices are always read again from the database (never trusted from the browser).
 */
require_once 'config/database.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ---------- Handle cart actions (sent with POST forms) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $foodId = (int) ($_POST['food_id'] ?? 0);

    if ($action === 'add' && $foodId > 0) {
        // Make sure the food item really exists
        $stmt = $pdo->prepare("SELECT name FROM food_items WHERE id = ?");
        $stmt->execute([$foodId]);
        $name = $stmt->fetchColumn();

        if ($name) {
            $_SESSION['cart'][$foodId] = ($_SESSION['cart'][$foodId] ?? 0) + 1;
            set_flash('success', $name . ' added to cart.');
        }

        // Go back to the page the user was on (only pages of this website)
        $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        $host = parse_url($back, PHP_URL_HOST);
        if ($host !== null && $host !== strtok($_SERVER['HTTP_HOST'], ':')) {
            $back = 'index.php';
        }
        redirect($back);
    }

    if ($action === 'increase' && isset($_SESSION['cart'][$foodId])) {
        $_SESSION['cart'][$foodId]++;
    }

    if ($action === 'decrease' && isset($_SESSION['cart'][$foodId])) {
        $_SESSION['cart'][$foodId]--;
        if ($_SESSION['cart'][$foodId] <= 0) {
            unset($_SESSION['cart'][$foodId]);
        }
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][$foodId]);
        set_flash('info', 'Item removed from cart.');
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    redirect('cart.php');
}

// ---------- Load cart items from the database ----------
$cartItems = [];
$total = 0;

if (count($_SESSION['cart']) > 0) {
    $ids = array_keys($_SESSION['cart']);
    // Build "?,?,?" placeholders for the IN (...) list
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT f.*, r.name AS restaurant_name, c.image AS category_image
                           FROM food_items f
                           JOIN restaurants r ON r.id = f.restaurant_id
                           JOIN food_categories c ON c.id = f.category_id
                           WHERE f.id IN ($placeholders)");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $row) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $total += $row['subtotal'];
        $cartItems[] = $row;
    }

    // Remove items from the session that no longer exist in the database
    $foundIds = array_column($cartItems, 'id');
    foreach ($ids as $id) {
        if (!in_array($id, $foundIds)) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

$deliveryFee = $total > 0 ? DELIVERY_FEE : 0;
$grandTotal  = $total + $deliveryFee;

$pageTitle = 'Cart';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <?php show_flash(); ?>
    <h3 class="section-title">Your <span>Cart</span></h3>

    <?php if (count($cartItems) === 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-orange" style="font-size: 5rem;"></i>
            <h4 class="mt-3">Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added anything yet.</p>
            <a href="restaurants.php" class="btn btn-orange">Browse Restaurants</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart items -->
            <div class="col-lg-8">
                <div class="card summary-card">
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item d-flex align-items-center gap-3 py-3 border-bottom flex-wrap">
                                <img src="<?= e(img_src($item['image'])) ?>" alt="<?= e($item['name']) ?>"
                                     onerror="this.onerror=null;this.src='<?= e($item['category_image']) ?>';">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= e($item['name']) ?></h6>
                                    <small class="text-muted"><?= e($item['restaurant_name']) ?></small>
                                    <div class="small"><?= rupees($item['price']) ?> each</div>
                                </div>

                                <!-- Quantity - / + -->
                                <div class="qty-box">
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="action" value="decrease">
                                        <input type="hidden" name="food_id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit" title="Decrease">−</button>
                                    </form>
                                    <span><?= (int) $item['qty'] ?></span>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="action" value="increase">
                                        <input type="hidden" name="food_id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit" title="Increase">+</button>
                                    </form>
                                </div>

                                <div class="fw-semibold text-end" style="min-width: 90px;"><?= rupees($item['subtotal']) ?></div>

                                <form method="post" class="m-0 confirm-remove">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="food_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="restaurants.php" class="btn btn-outline-orange"><i class="bi bi-arrow-left"></i> Add more items</a>
                            <form method="post" class="m-0">
                                <input type="hidden" name="action" value="clear">
                                <button class="btn btn-link text-danger">Clear cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill summary -->
            <div class="col-lg-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="mb-3">Bill Details</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Item Total</span><span><?= rupees($total) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee</span><span><?= rupees($deliveryFee) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>To Pay</span><span><?= rupees($grandTotal) ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-orange w-100 py-2">Proceed to Checkout</a>
                        <?php if (!is_logged_in()): ?>
                            <p class="small text-muted text-center mt-2 mb-0">You will be asked to log in first.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
