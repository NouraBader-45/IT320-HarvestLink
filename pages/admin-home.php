<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$user = current_user();

$usersCount = db()->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$listingsCount = db()->query("SELECT COUNT(*) AS total FROM surplus_products")->fetch_assoc()['total'] ?? 0;
$requestsCount = db()->query("SELECT COUNT(*) AS total FROM donation_requests")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Admin Home</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="admin-theme">
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Administrator Area</p>
        </div>
      </a>

      <div class="header-utilities">
        <a href="manage-profile.php" class="profile-link" title="Profile">
          <img src="<?php echo htmlspecialchars(get_profile_image($user)); ?>" alt="Profile" />
        </a>

        <div class="menu-wrap">
          <button class="hamburger-btn" data-target="adminMenu" aria-label="Open menu">
            <span></span><span></span><span></span>
          </button>
          <div class="dropdown-menu" id="adminMenu">
            <a href="admin-home.php">Home</a>
            <a href="manage-profile.php">Edit Profile</a>
            <a href="manage-users.php">Manage Users</a>
            <a href="manage-listings.php">Manage Listings</a>
            <a href="logout.php" class="logout-btn">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="page-shell">
    <div class="container">
      <div class="role-top">
        <div class="role-intro">
          <span class="role-bubble">Admin Home</span>
          <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>
          <p>Monitor the system and manage users and product listings.</p>
        </div>
      </div>

      <section class="stats-grid" style="margin-bottom:24px;">
        <div class="card">
          <h4>Total Users</h4>
          <p><?php echo (int) $usersCount; ?></p>
        </div>
        <div class="card">
          <h4>Total Listings</h4>
          <p><?php echo (int) $listingsCount; ?></p>
        </div>
        <div class="card">
          <h4>Total Requests</h4>
          <p><?php echo (int) $requestsCount; ?></p>
        </div>
      </section>

      <section class="cards-section">
        <div class="feature-card">
          <div class="feature-icon">U</div>
          <h3>Manage Users</h3>
          <p>Block or unblock farmer and charity accounts when necessary.</p>
          <div class="card-actions">
            <a href="manage-users.php" class="btn btn-primary">Manage Users</a>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">L</div>
          <h3>Manage Listings</h3>
          <p>Block or unblock inappropriate product listings.</p>
          <div class="card-actions">
            <a href="manage-listings.php" class="btn btn-primary">Manage Listings</a>
          </div>
        </div>
      </section>
    </div>
  </main>

  <footer class="main-footer">
    <div class="container footer-wrap">
      <p>HarvestLink Web Platform</p>
      <p>IT320 Practical Software Engineering</p>
    </div>
  </footer>

  <script src="../js/main.js"></script>
</body>
</html>