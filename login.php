<?php
/*
 * login.php - Customer login
 * Checks the email and password with password_verify() and stores the user in the session.
 */
require_once 'config/database.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$email = '';
// If the user came from checkout, send them back there after login
$redirectTo = (($_GET['redirect'] ?? '') === 'checkout') ? 'checkout.php' : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);   // new session id after login (security)
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect($redirectTo);
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-center mb-1">Login</h3>
            <p class="text-center text-muted mb-4">Welcome back! Log in to order food</p>

            <?php show_flash(); ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
                <button type="submit" class="btn btn-orange w-100 py-2">Login</button>
            </form>
            <p class="text-center mt-3 mb-1">New to Swiggy? <a href="register.php" class="text-orange">Create an account</a></p>
            <p class="text-center small text-muted mb-0">Demo customer: rahul@example.com / rahul123</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
