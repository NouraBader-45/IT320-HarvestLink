<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$fullName = '';
$email = '';
$role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // هنا نطبق منطق الباكلوق: الاسم مطلوب، الإيميل صحيح، الباسورد 8 أحرف، والرول Farmer أو Charity فقط.
    if (strlen($fullName) < 4 || strlen($fullName) > 100) {
        $error = 'Full name must be between 4 and 100 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password and Confirm Password do not match.';
    } elseif (!in_array($role, ['farmer', 'charity'], true)) {
        $error = 'Please choose a valid role.';
    } else {
        $checkStmt = db()->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $existingUser = $checkStmt->get_result()->fetch_assoc();

        if ($existingUser) {
            $error = 'This email is already registered.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insertUserStmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $insertUserStmt->bind_param('ssss', $fullName, $email, $passwordHash, $role);

            if ($insertUserStmt->execute()) {
                $userId = db()->insert_id;

                // إذا كان Farmer ندخله في farmers، وإذا Charity ندخله في charitable_organizations.
                if ($role === 'farmer') {
                    $insertFarmerStmt = db()->prepare('INSERT INTO farmers (user_id) VALUES (?)');
                    $insertFarmerStmt->bind_param('i', $userId);
                    $insertFarmerStmt->execute();
                } else {
                    $organizationName = $fullName;
                    $insertCharityStmt = db()->prepare('INSERT INTO charitable_organizations (user_id, organization_name) VALUES (?, ?)');
                    $insertCharityStmt->bind_param('is', $userId, $organizationName);
                    $insertCharityStmt->execute();
                }

                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Something went wrong while creating the account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Register</title>
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
        <a href="login.php">Login</a>
        <a class="active" href="register.php">Register</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Register</h2>
      <p>Create an account as a Farmer or a Charitable Organization.</p>
    </div>
  </section>

  <section>
    <div class="form-wrap">
      <h3>Create Account</h3>
      <p class="subtext">Complete the form below to join HarvestLink.</p>

      <?php if ($error): ?>
        <div class="note-box" style="border-left-color:#b94a48; color:#b94a48;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input id="full_name" name="full_name" type="text" placeholder="Enter full name" value="<?php echo htmlspecialchars($fullName); ?>" required />
        </div>

        <div class="form-group">
          <label for="reg-email">Email Address</label>
          <input id="reg-email" name="email" type="email" placeholder="Enter email address" value="<?php echo htmlspecialchars($email); ?>" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="reg-password">Password</label>
            <input id="reg-password" name="password" type="password" placeholder="Enter password" required />
          </div>

          <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input id="confirm-password" name="confirm_password" type="password" placeholder="Confirm password" required />
          </div>
        </div>

        <div class="form-group">
          <label for="role">Role Selection</label>
          <select id="role" name="role" required>
            <option value="" <?php echo $role === '' ? 'selected' : ''; ?> disabled>Select role</option>
            <option value="farmer" <?php echo $role === 'farmer' ? 'selected' : ''; ?>>Farmer</option>
            <option value="charity" <?php echo $role === 'charity' ? 'selected' : ''; ?>>Charity</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Create Account</button>
          <a href="login.php" class="btn btn-outline">Back to Login</a>
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
