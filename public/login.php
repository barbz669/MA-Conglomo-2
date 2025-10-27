<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT u.*, r.name AS role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ((int)$user['is_approved'] !== 1) {
            $error = 'Account pending approval.';
        } elseif ((int)$user['is_active'] !== 1) {
            $error = 'Account inactive.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role_id']   = (int)$user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['must_change_password'] = (int)$user['must_change_password'];

            if ($_SESSION['must_change_password'] === 1) {
                header("Location: /maconglomo_app/public/change_credentials.php");
                exit;
            }

            switch ($_SESSION['role_id']) {
                case 1:
                    header("Location: /maconglomo_app/admin/dashboard.php");
                    exit;
                case 2:
                    header("Location: /maconglomo_app/inventory/dashboard.php");
                    exit;
                case 3:
                    header("Location: /maconglomo_app/medrep/dashboard.php");
                    exit;
                default:
                    header("Location: /maconglomo_app/public/logout.php");
                    exit;
            }
        }
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Maconglomo App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<div class="login-container">
    <!-- Left: Form -->
    <div class="login-form">
        <h2>Welcome Back!</h2>
        <p>Sign in to access your dashboard</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"  placeholder="email or username" required required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"  placeholder="password" required required>
            </div>

            <button class="btn w-100">Login</button>
        </form>

        <div class="text-center mt-3">
            <small>Don't have an account? <a href="/maconglomo_app/public/register.php">Register here</a></small>
        </div>
    </div>

    <!-- Right: Welcome panel -->
    <div class="login-side">
        <h2>Glad to see you!</h2>
        <p>Take control of your inventory, clients, and team, all in one place.</p>
    </div>
</div>

</body>
</html>
