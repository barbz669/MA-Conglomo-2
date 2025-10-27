<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (role_id, full_name, email, password_hash) 
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([$role, $fullname, $email, $hash]);
            $success = 'Registration successful! Await admin approval.';
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #fff7f7, #ffeaea);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }

    .register-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      width: 450px;
      max-width: 95%;
      padding: 40px 35px;
      text-align: left;
    }

    h2 {
      color: #b30000;
      text-align: center;
      margin-bottom: 25px;
      font-size: 1.8rem;
      font-weight: 600;
    }

    .alert {
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 0.95rem;
    }

    .alert-danger {
      background-color: #ffe5e5;
      color: #b30000;
    }

    .alert-success {
      background-color: #e6ffee;
      color: #006600;
    }

    .form-group {
      margin-bottom: 18px;
      position: relative;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #333;
    }

    input,
    select {
      width: 100%;
      padding: 12px 40px 12px 12px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 1rem;
      transition: border-color 0.2s ease;
      appearance: none;
      -webkit-appearance: none;
      background-color: #fff;
      box-sizing: border-box;
    }

    input:focus,
    select:focus {
      border-color: #b30000;
      outline: none;
    }

    /* Password eye icon */
    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #b30000;
      font-size: 1.1rem;
      transition: color 0.3s ease;
    }

    .toggle-password:hover {
      color: #800000;
    }

    /* Dropdown arrow */
    select {
      background-image: url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' fill='%23b30000' viewBox='0 0 24 24'><path d='M7 10l5 5 5-5z'/></svg>");
      background-repeat: no-repeat;
      background-position: right 15px center;
      background-size: 16px;
    }

    button {
      background-color: #b30000;
      border: none;
      color: #fff;
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      font-weight: bold;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    button:hover {
      background-color: #990000;
      transform: scale(1.03);
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.95rem;
      color: #555;
    }

    .login-link a {
      color: #b30000;
      font-weight: 600;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .register-container {
        padding: 30px 25px;
      }
      h2 {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Create an Account</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="register-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="fullname" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="password" required>
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
      </div>

      <div class="form-group">
        <label>Role</label>
        <select name="role" required>
          <option value="" disabled selected>Select Role</option>
          <option value="1">Inventory Keeper</option>
          <option value="2">Medical Representative</option>
        </select>
      </div>

      <button type="submit">Register</button>
    </form>

    <p class="login-link">
      Already have an account? <a href="/maconglomo_app/public/login.php">Login here</a>
    </p>
  </div>

  <script>
    function togglePassword(id, icon) {
      const input = document.getElementById(id);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    }
  </script>
</body>
</html>
