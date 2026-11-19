<?php
/*
 * restaurants.php - List of all restaurants
 * Also works as the search results page: restaurants.php?search=pizza
 */
$pageTitle = 'Restaurants';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    // Restaurants whose name or cuisine matches
    $like = '%' . $search . '%';
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE name LIKE ? OR cuisine LIKE ? ORDER BY rating DESC");
    $stmt->execute([$like, $like]);
    $restaurants = $stmt->fetchAll();

    // Dishes whose name matches
    $stmt = $pdo->prepare("SELECT f.*, r.name AS restaurant_name, c.image AS category_image
                           FROM food_items f
                           JOIN restaurants r ON r.id = f.restaurant_id
                           JOIN food_categories c ON c.id = f.category_id
                           WHERE f.name LIKE ? OR c.name LIKE ?
                           ORDER BY f.name");
    $stmt->execute([$like, $like]);
    $foods = $stmt->fetchAll();
} else {
    $restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY rating DESC")->fetchAll();
    $foods = [];
}
?>

<div class="container py-5">
    <?php show_flash(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h3 class="section-title mb-0">
            <?php if ($search !== ''): ?>
                Results for "<span><?= e($search) ?></span>"
            <?php else: ?>
                All <span>Restaurants</span>
            <?php endif; ?>
        </h3>
        <form class="d-flex gap-2" method="get" action="restaurants.php">
            <input type="text" name="search" class="form-control" placeholder="Search food or restaurant"
                   value="<?= e($search) ?>">
            <button class="btn btn-orange"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <?php if (count($restaurants) > 0): ?>
        <div class="row g-4">
            <?php foreach ($restaurants as $r) { restaurant_card($r); } ?>
        </div>
    <?php elseif ($search !== ''): ?>
        <p class="text-muted">No restaurants match your search.</p>
    <?php endif; ?>

    <?php if ($search !== ''): ?>
        <h4 class="section-title mt-5">Matching <span>Dishes</span></h4>
        <?php if (count($foods) > 0): ?>
            <div class="row g-4">
                <?php foreach ($foods as $item) { food_card($item); } ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No dishes match your search. <a href="restaurants.php" class="text-orange">See all restaurants</a></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
