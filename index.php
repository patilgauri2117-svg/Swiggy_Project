<?php
/*
 * index.php - Home page
 * Shows hero + search, food categories, popular restaurants and popular dishes.
 */
$pageTitle = 'Home';
require_once 'includes/header.php';

// Food categories
$categories = $pdo->query("SELECT * FROM food_categories ORDER BY id")->fetchAll();

// Top rated restaurants
$restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY rating DESC LIMIT 6")->fetchAll();

// Popular food items (with restaurant name and category image for fallback picture)
$foods = $pdo->query("SELECT f.*, r.name AS restaurant_name, c.image AS category_image
                      FROM food_items f
                      JOIN restaurants r ON r.id = f.restaurant_id
                      JOIN food_categories c ON c.id = f.category_id
                      WHERE f.is_popular = 1
                      ORDER BY f.id LIMIT 8")->fetchAll();
?>

<!-- ===== Hero Section ===== -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <h1>Hungry? Order Your Favourite Food!</h1>
                <p class="lead mb-4">Pizza, biryani, burgers and more from the best restaurants near you,
                    delivered in under 40 minutes.</p>

                <!-- Search sends the keyword to restaurants.php -->
                <form action="restaurants.php" method="get" class="search-box d-flex">
                    <input type="text" name="search" class="form-control" required
                           placeholder="Search for food or restaurants...">
                    <button class="btn btn-orange" type="submit"><i class="bi bi-search"></i> Search</button>
                </form>
            </div>
            <div class="col-md-5 text-center d-none d-md-block">
                <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600" alt="Delicious food"
                     class="hero-img" onerror="this.onerror=null;this.src='assets/images/hero.svg';">
            </div>
        </div>
    </div>
</section>

<div class="container"><div class="mt-4"><?php show_flash(); ?></div></div>

<!-- ===== Food Categories ===== -->
<section class="py-5">
    <div class="container">
        <h3 class="section-title">What's on your <span>mind?</span></h3>
        <div class="row g-4 justify-content-center">
            <?php foreach ($categories as $cat): ?>
                <div class="col-4 col-md-2">
                    <a href="menu.php?category=<?= (int) $cat['id'] ?>" class="category-item">
                        <img src="<?= e($cat['image']) ?>" alt="<?= e($cat['name']) ?>">
                        <h6><?= e($cat['name']) ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== Popular Restaurants ===== -->
<section class="py-5 bg-light-grey">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="section-title">Popular <span>Restaurants</span></h3>
            <a href="restaurants.php" class="text-orange text-decoration-none mb-4">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($restaurants as $r) { restaurant_card($r); } ?>
        </div>
    </div>
</section>

<!-- ===== Popular Food Items ===== -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="section-title">Popular <span>Dishes</span></h3>
            <a href="menu.php" class="text-orange text-decoration-none mb-4">Full menu <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($foods as $item) { food_card($item); } ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
