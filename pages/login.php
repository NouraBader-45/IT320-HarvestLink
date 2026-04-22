<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // هنا نجيب المستخدم حسب الإيميل
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // إذا المستخدم مو موجود أو كلمة المرور غلط نطلع رسالة واضحة
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $error = 'Invalid email or password.';
    } elseif ($user['account_status'] === 'blocked') {
        $error = 'Your account is blocked. Please contact the administrator.';
    } else {
        login_user($user);

        // بعد تسجيل الدخول نوجه المستخدم حسب الرول
        if ($user['role'] === 'farmer') {
            header('Location: farmer-home.php');
        } elseif ($user['role'] === 'charity') {
            header('Location: charity-home.php');
        } else {
            header('Location: admin-home.php');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Login</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html" aria-label="Go to Home">
        <img src="../assets/images/logo.png" alt="HarvestLink Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>A digital bridge between surplus and need</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="../index.html">Home</a>
        <a class="active" href="login.php">Login</a>
        <a href="register.php" class="btn btn-primary">Register</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Log In</h2>
      <p>Enter your credentials to access your HarvestLink account.</p>
    </div>
  </section>

  <section>
    <div class="form-wrap">
      <h3>Welcome Back</h3>
      <p class="subtext">Use your registered email and password.</p>

      <?php if ($error): ?>
        <div class="note-box" style="border-left-color:#b94a48; color:#b94a48;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" placeholder="example@email.com" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" required />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Login</button>
          <a href="register.php" class="btn btn-outline">Create Account</a>
        </div>
      </form>
    </div>
  </section>

  <footer class="main-footer">
    <div class="container footer-wrap">
      <p>HarvestLink Web Platform</p>
      <p>IT320 Practical Software Engineering — <span class="current-year"></span></p>
    </div>
  </footer>

  <script src="../js/main.js"></script>
</body>
</html>
