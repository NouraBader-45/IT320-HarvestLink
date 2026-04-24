<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('charity');

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Charity Home</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="charity-theme">
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html" aria-label="Go to Home">
        <img src="../assets/images/logo.png" alt="HarvestLink Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Charity Area</p>
        </div>
      </a>

      <div class="header-utilities">
        <a href="manage-profile.php" class="profile-link" title="Profile">
          <img src="<?php echo htmlspecialchars(get_profile_image($user)); ?>" alt="Profile" />
        </a>

        <div class="menu-wrap">
          <button class="hamburger-btn" data-target="charityMenu" aria-label="Open menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="dropdown-menu" id="charityMenu">
            <a href="charity-home.php">Home</a>
            <a href="manage-profile.php">Edit Profile</a>
            <a href="browse-products.php">Browse Products</a>
            <a href="my-requests.php">My Requests</a>
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
          <span class="role-bubble">Charity Home</span>
          <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>
          <p>
            Browse available surplus products, submit donation requests, and track the progress
            of your organization’s requests.
          </p>
        </div>
      </div>

      <div class="role-highlight">
        <h3>Your Main Actions</h3>
        <p>
          Use the cards below to explore available agricultural products, view product details,
          submit donation requests, and follow request status updates.
        </p>
      </div>

      <section class="cards-section">
        <div class="feature-card">
          <div class="feature-icon">⌕</div>
          <h3>Browse Products</h3>
          <p>
            Search and review available surplus product listings posted by farmers.
          </p>
          <div class="card-actions">
            <a href="browse-products.php" class="btn btn-primary">Browse Now</a>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">i</div>
          <h3>View Product Details</h3>
          <p>
            Open full product information such as farmer name, quantity, condition,
            and expiration date before submitting a request.
          </p>
          <div class="card-actions">
            <a href="browse-products.php" class="btn btn-outline">Open Products</a>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">↗</div>
          <h3>My Requests</h3>
          <p>
            Track your submitted requests and check whether each request is pending,
            approved, rejected, or delivered.
          </p>
          <div class="card-actions">
            <a href="my-requests.php" class="btn btn-primary">Track Requests</a>
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