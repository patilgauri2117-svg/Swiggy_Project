        </main>
    </div>
</div>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Ask before deleting anything
    document.querySelectorAll('.confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this?')) e.preventDefault();
        });
    });
</script>
</body>
</html>
