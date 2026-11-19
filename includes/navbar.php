<?php
/*
 * includes/navbar.php
 * Top navigation bar. Shows Login/Register or the user's name.
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand logo" href="index.php">
            <i class="bi bi-bag-heart-fill"></i> Swiggy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'restaurants.php' ? 'active' : '' ?>" href="restaurants.php">
                        <i class="bi bi-shop"></i> Restaurants</a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="my-orders.php"><i class="bi bi-receipt"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'login.php' ? 'active' : '' ?>" href="login.php">
                            <i class="bi bi-person"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'register.php' ? 'active' : '' ?>" href="register.php">
                            <i class="bi bi-person-plus"></i> Register</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="btn btn-orange position-relative ms-lg-2 mt-2 mt-lg-0" href="cart.php">
                        <i class="bi bi-cart3"></i> Cart
                        <?php if (cart_count() > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                                <?= cart_count() ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
