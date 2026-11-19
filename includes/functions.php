<?php
/*
 * includes/functions.php
 * Small helper functions used on many pages.
 */

// Flat delivery charge added to every order (in rupees)
const DELIVERY_FEE = 40;

// Escape text before printing it in HTML (prevents XSS)
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Format a number as Indian Rupees, e.g. 199 -> ₹199.00
function rupees($amount)
{
    return '₹' . number_format((float) $amount, 2);
}

// Image path helper. Online images (http...) are used as they are.
// Local images need "../" in front when shown from the admin folder.
function img_src($path, $prefix = '')
{
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    return $prefix . $path;
}

// Is a customer logged in?
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Send the user to another page and stop the script
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

// Flash message: saved in the session, shown once on the next page
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
            . e($f['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// Total number of items in the cart (shown on the navbar badge)
function cart_count()
{
    return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
}

// Prints one food card. Used on the home page and the menu page.
function food_card($item)
{
    $fallback = !empty($item['category_image']) ? $item['category_image'] : 'assets/images/placeholder.svg';
    $vegClass = $item['is_veg'] ? 'veg' : 'nonveg';
    ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card food-card h-100">
            <img src="<?= e(img_src($item['image'])) ?>" class="card-img-top" alt="<?= e($item['name']) ?>"
                 onerror="this.onerror=null;this.src='<?= e($fallback) ?>';">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="veg-icon <?= $vegClass ?>" title="<?= $item['is_veg'] ? 'Veg' : 'Non-veg' ?>"></span>
                    <h6 class="card-title mb-0"><?= e($item['name']) ?></h6>
                </div>
                <?php if (!empty($item['restaurant_name'])): ?>
                    <small class="text-muted mb-1"><?= e($item['restaurant_name']) ?></small>
                <?php endif; ?>
                <p class="small text-secondary flex-grow-1"><?= e($item['description']) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="price"><?= rupees($item['price']) ?></span>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="food_id" value="<?= (int) $item['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-orange add-to-cart">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Prints one restaurant card. Used on the home page and restaurants page.
function restaurant_card($r)
{
    ?>
    <div class="col-sm-6 col-lg-4">
        <div class="card restaurant-card h-100">
            <div class="position-relative">
                <img src="<?= e(img_src($r['image'])) ?>" class="card-img-top" alt="<?= e($r['name']) ?>"
                     onerror="this.onerror=null;this.src='assets/images/restaurant.svg';">
                <span class="delivery-badge"><i class="bi bi-clock"></i> <?= (int) $r['delivery_time'] ?> mins</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title mb-1"><?= e($r['name']) ?></h5>
                    <span class="rating"><i class="bi bi-star-fill"></i> <?= e($r['rating']) ?></span>
                </div>
                <p class="text-muted small mb-1"><?= e($r['cuisine']) ?></p>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> <?= e($r['location']) ?></p>
                <a href="menu.php?restaurant=<?= (int) $r['id'] ?>" class="btn btn-outline-orange w-100">View Menu</a>
            </div>
        </div>
    </div>
    <?php
}

// CSS class for an order status badge, e.g. "Out for Delivery" -> "status-Out"
function status_class($status)
{
    return 'status-' . explode(' ', $status)[0];
}
