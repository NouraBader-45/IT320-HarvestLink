<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$user = current_user();

$error = '';
$success = '';
if (isset($_GET['updated'])) {
    $success = 'Profile updated successfully.';
}

$role = $user['role'];

if ($role === 'farmer') {
    $homePage = 'farmer-home.php';
} elseif ($role === 'charity') {
    $homePage = 'charity-home.php';
} else {
    $homePage = 'admin-home.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if ($full_name === '' || strlen($full_name) < 4 || strlen($full_name) > 50) {
        $error = 'Full name must be between 4 and 50 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1');
        $stmt->bind_param('si', $email, $user['user_id']);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'This email is already used by another account.';
        } else {
            $profileImagePath = $user['profile_image'] ?: 'uploads/profiles/default_profile.png';

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $originalName = $_FILES['profile_image']['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions)) {
                    $error = 'Invalid image type. Please upload JPG, JPEG, PNG, or WEBP.';
                } else {
                    $uploadDir = __DIR__ . '/../uploads/profiles/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = 'user_' . $user['user_id'] . '_' . time() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                        $profileImagePath = 'uploads/profiles/' . $fileName;
                    } else {
                        $error = 'Failed to upload profile image.';
                    }
                }
            }

            if ($error === '') {
                $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone_number = ?, profile_image = ?, updated_at = NOW() WHERE user_id = ?');
                $stmt->bind_param('ssssi', $full_name, $email, $phone_number, $profileImagePath, $user['user_id']);
                if ($stmt->execute()) {
    header('Location: manage-profile.php?updated=1');
    exit;
} else {
    $error = 'Failed to update profile. Please try again.';
}
            }
        }
    }
}

$profileImage = get_profile_image($user);
?>

<!DOCTYPE html>
<html lang="en">
<head> 
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Manage Profile</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="<?php echo $homePage; ?>" aria-label="Go to Home">
        <img src="../assets/images/logo.png" alt="HarvestLink Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>A digital bridge between surplus and need</p>
        </div>
      </a>

      <div class="header-utilities">
        <a href="manage-profile.php" class="profile-link" title="Profile">
          <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" />
        </a>

        <div class="menu-wrap">
          <button class="hamburger-btn" data-target="profileMenu" aria-label="Open menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="dropdown-menu" id="profileMenu">
            <a href="<?php echo $homePage; ?>">Home</a>
            <a href="manage-profile.php">Edit Profile</a>
           <a href="logout.php" class="logout-btn"
   onclick="return confirm('Are you sure you want to log out?');">
   Logout
</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Manage Profile</h2>
      <p>Update your personal information and profile image.</p>
    </div>
  </section>

  <section>
    <div class="form-wrap">
      <h3>Profile Information</h3>
      <p class="subtext">Keep your account information accurate and up to date.</p>

      <?php if ($error): ?>
        <div class="note-box" style="border-left-color:#b94a48; color:#b94a48;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="note-box" style="border-left-color:#2f7a4f; color:#2f7a4f;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <form id="profile-form" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="full-name">Full Name</label>
          <input id="full-name" name="full_name" type="text" placeholder="Enter full name"
                 value="<?php echo htmlspecialchars($user['full_name']); ?>" required />
        </div>

        <div class="form-group">
          <label for="profile-email">Email Address</label>
          <input id="profile-email" name="email" type="email" placeholder="Enter email address"
                 value="<?php echo htmlspecialchars($user['email']); ?>" required />
        </div>

        <div class="form-group">
          <label for="phone">Contact Number</label>
          <input id="phone" name="phone_number" type="text" placeholder="Enter contact number"
                 value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" />
        </div>

        <div class="form-group">
          <label for="image">Profile Image</label>
          <input id="image" name="profile_image" type="file" accept="image/*" />
        </div>

        <div class="form-group">
          <label>Current Profile Image</label>
          <img src="<?php echo htmlspecialchars($profileImage); ?>?t=<?php echo time(); ?>" alt="Current Profile"
     style="width:120px;height:120px;border-radius:50%;object-fit:cover;" /> </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="<?php echo $homePage; ?>" class="btn btn-outline">Cancel</a>
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