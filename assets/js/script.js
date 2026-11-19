/*
 * assets/js/script.js
 * Small JavaScript helpers for the customer website.
 */

document.addEventListener('DOMContentLoaded', function () {

    // 1. Show "Adding..." on the Add to Cart button when it is clicked
    document.querySelectorAll('.add-to-cart').forEach(function (btn) {
        btn.closest('form').addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML = 'Adding...';
        });
    });

    // 2. Ask before removing an item from the cart
    document.querySelectorAll('.confirm-remove').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm('Remove this item from your cart?')) {
                event.preventDefault();
            }
        });
    });

    // 3. Simple client-side check on forms marked with "needs-validation"
    //    (the server checks everything again in PHP)
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // 4. Hide success/info alerts automatically after 4 seconds
    setTimeout(function () {
        document.querySelectorAll('.alert-success, .alert-info').forEach(function (alert) {
            if (window.bootstrap) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        });
    }, 4000);
});
