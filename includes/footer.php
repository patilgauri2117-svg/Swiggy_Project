<?php
/*
 * includes/footer.php
 * Common footer and JavaScript files.
 */
?>
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-5">
                <h5 class="logo text-white"><i class="bi bi-bag-heart-fill"></i> Swiggy</h5>
                <p class="small mt-2" id="about">
                    <strong>About Us:</strong> Swiggy is a food delivery website made as a college project.
                    Discover the best restaurants in your city and get your favourite food
                    delivered hot and fresh to your doorstep.
                </p>
            </div>
            <div class="col-6 col-md-3">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="my-orders.php">My Orders</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4" id="contact">
                <h6 class="text-white">Contact</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-geo-alt"></i> FC Road, Pune, Maharashtra</li>
                    <li><i class="bi bi-telephone"></i> +91 98765 43210</li>
                    <li><i class="bi bi-envelope"></i> support@swiggy-demo.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center small mb-0">&copy; <?= date('Y') ?> Swiggy. All rights reserved. | College Project</p>
    </div>
</footer>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
