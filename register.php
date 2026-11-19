<?php
/*
 * register.php - Customer registration
 * Validates the form, hashes the password and saves the user in MySQL.
 */
require_once 'config/database.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$name = $email = $phone = $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ----- Validation -----
    if (strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit mobile number.';
    }
    if (strlen($address) < 10) {
        $errors[] = 'Please enter your full delivery address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Is the email already registered?
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered. Please log in.';
        }
    }

    // ----- Save the user -----
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $hash]);

        set_flash('success', 'Registration successful! Please log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-center mb-1">Create Account</h3>
            <p class="text-center text-muted mb-4">Join Swiggy and start ordering</p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($name) ?>" minlength="3" required>
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($phone) ?>"
                           pattern="[6-9][0-9]{9}" maxlength="10" required>
                    <div class="invalid-feedback">Enter a 10-digit mobile number.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <textarea name="address" class="form-control" rows="2" minlength="10" required><?= e($address) ?></textarea>
                    <div class="invalid-feedback">Please enter your full address.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                        <div class="invalid-feedback">Minimum 6 characters.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-orange w-100 py-2">Register</button>
            </form>
            <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php" class="text-orange">Login</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
