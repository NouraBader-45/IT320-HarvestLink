<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Farmer Home</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="farmer-theme">
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Farmer Area</p>
        </div>
      </a>

      <div class="header-utilities">
        <a href="manage-profile.php" class="profile-link" title="Profile">
          <img src="<?php echo htmlspecialchars(get_profile_image($user)); ?>" alt="Profile" />
        </a>

        <div class="menu-wrap">
          <button class="hamburger-btn" data-target="farmerMenu" aria-label="Open menu">
            <span></span><span></span><span></span>
          </button>
          <div class="dropdown-menu" id="farmerMenu">
            <a href="farmer-home.php">Home</a>
            <a href="manage-profile.php">Edit Profile</a>
            <a href="add-product.php">Add Product</a>
            <a href="my-products.php">My Products</a>
            <a href="incoming-requests.php">Incoming Requests</a>
           <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <main class="page-shell">
    <div class="container">
      <div class="role-top">
        <div class="role-intro">
          <span class="role-bubble">Farmer Home</span>
          <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>
          <p>Manage your surplus products and respond to requests from charitable organizations.</p>
        </div>
      </div>

      <div class="role-highlight">
        <h3>Your Main Actions</h3>
        <p>Use the cards below to post products, view your listings, and manage incoming donation requests.</p>
      </div>

      <section class="cards-section">
        <div class="feature-card">
          <div class="feature-icon">+</div>
          <h3>Add Surplus Product</h3>
          <p>Create a new listing with quantity, expiration date, condition, and image.</p>
          <div class="card-actions">
            <a href="add-product.php" class="btn btn-primary">Add Product</a>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">≣</div>
          <h3>My Products</h3>
          <p>Review your current listings, update them, or delete them if they are no longer available.</p>
          <div class="card-actions">
            <a href="my-products.php" class="btn btn-outline">Open Listings</a>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">✓</div>
          <h3>Incoming Requests</h3>
          <p>Approve or reject requests submitted by charitable organizations.</p>
          <div class="card-actions">
            <a href="incoming-requests.php" class="btn btn-primary">Manage Requests</a>
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