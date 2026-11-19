<?php
/*
 * menu.php - Food menu
 *   menu.php?restaurant=1  -> menu of one restaurant
 *   menu.php?category=2    -> all dishes of one category
 *   menu.php               -> all dishes
 */
$pageTitle = 'Menu';
require_once 'includes/header.php';

$restaurantId = (int) ($_GET['restaurant'] ?? 0);
$categoryId   = (int) ($_GET['category'] ?? 0);
$restaurant   = null;
$heading      = 'All <span>Dishes</span>';

$sql = "SELECT f.*, r.name AS restaurant_name, c.image AS category_image
        FROM food_items f
        JOIN restaurants r ON r.id = f.restaurant_id
        JOIN food_categories c ON c.id = f.category_id";

if ($restaurantId > 0) {
    // Load restaurant details for the banner
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();

    $stmt = $pdo->prepare($sql . " WHERE f.restaurant_id = ? ORDER BY c.name, f.name");
    $stmt->execute([$restaurantId]);
} elseif ($categoryId > 0) {
    $stmt = $pdo->prepare("SELECT name FROM food_categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $categoryName = $stmt->fetchColumn();
    if ($categoryName) {
        $heading = e($categoryName) . ' <span>Dishes</span>';
    }

    $stmt = $pdo->prepare($sql . " WHERE f.category_id = ? ORDER BY f.name");
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY r.name, f.name");
}
$foods = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM food_categories ORDER BY id")->fetchAll();
?>

<div class="container py-5">
    <?php show_flash(); ?>

    <?php if ($restaurant): ?>
        <!-- Restaurant banner -->
        <div class="restaurant-banner mb-5">
            <div class="row align-items-center g-4">
                <div class="col-md-4">
                    <img src="<?= e(img_src($restaurant['image'])) ?>" alt="<?= e($restaurant['name']) ?>"
                         onerror="this.onerror=null;this.src='assets/images/restaurant.svg';">
                </div>
                <div class="col-md-8">
                    <h2 class="fw-bold"><?= e($restaurant['name']) ?></h2>
                    <p class="text-muted mb-2"><?= e($restaurant['cuisine']) ?></p>
                    <p class="text-muted mb-3"><i class="bi bi-geo-alt"></i> <?= e($restaurant['location']) ?></p>
                    <span class="rating me-3"><i class="bi bi-star-fill"></i> <?= e($restaurant['rating']) ?></span>
                    <span class="fw-semibold"><i class="bi bi-clock"></i> <?= (int) $restaurant['delivery_time'] ?> mins</span>
                </div>
            </div>
        </div>
        <h3 class="section-title">Menu</h3>
    <?php else: ?>
        <h3 class="section-title"><?= $heading ?></h3>
        <!-- Category filter buttons -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="menu.php" class="btn btn-sm <?= $categoryId == 0 ? 'btn-orange' : 'btn-outline-orange' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="menu.php?category=<?= (int) $cat['id'] ?>"
                   class="btn btn-sm <?= $categoryId == $cat['id'] ? 'btn-orange' : 'btn-outline-orange' ?>">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (count($foods) > 0): ?>
        <div class="row g-4">
            <?php foreach ($foods as $item) { food_card($item); } ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <p class="text-muted">No food items found.</p>
            <a href="restaurants.php" class="btn btn-orange">Browse Restaurants</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
