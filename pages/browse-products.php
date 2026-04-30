<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('charity');

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = db()->prepare("SELECT p.*, u.full_name AS farmer_name
                           FROM surplus_products p
                           JOIN farmers f ON p.farmer_id = f.farmer_id
                           JOIN users u ON f.user_id = u.user_id
                           WHERE p.product_status = 'Available' AND p.crop_type LIKE ?
                           ORDER BY p.created_at DESC");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = db()->query("SELECT p.*, u.full_name AS farmer_name
                             FROM surplus_products p
                             JOIN farmers f ON p.farmer_id = f.farmer_id
                             JOIN users u ON f.user_id = u.user_id
                             WHERE p.product_status = 'Available'
                             ORDER BY p.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Browse Products</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Browse Products</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="charity-home.php">Home</a>
        <a class="active" href="browse-products.php">Browse Products</a>
        <a href="my-requests.php">My Requests</a>
        <a href="logout.php" class="logout-btn"
   onclick="return confirm('Are you sure you want to log out?');">
   Logout
</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Browse Available Products</h2>
      <p>Search and review agricultural surplus listings.</p>
    </div>
  </section>

  <section class="container section">
    <form method="GET" class="panel" style="margin-bottom:24px; padding:20px; border-radius:18px;">
      <div class="form-row">
        <div class="form-group">
          <label for="search">Search by Product Name</label>
          <input id="search" name="search" type="text" placeholder="e.g. Tomatoes" value="<?php echo htmlspecialchars($search); ?>" />
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="browse-products.php" class="btn btn-outline">Reset</a>
      </div>
    </form>

    <div class="product-grid">
      <?php if ($products->num_rows === 0): ?>
        <div class="note-box">No matching products were found.</div>
      <?php endif; ?>

      <?php while ($row = $products->fetch_assoc()): ?>
        <div class="product-card">
          <div class="image-box">
            <img src="<?php echo !empty($row['image']) ? '../' . htmlspecialchars($row['image']) : '../assets/images/logo.png'; ?>" alt="Product" />
          </div>
          <div class="content">
            <h4><?php echo htmlspecialchars($row['crop_type']); ?></h4>
            <p>Farmer: <?php echo htmlspecialchars($row['farmer_name']); ?></p>
            <div class="meta">
              <span><strong>Quantity:</strong> <?php echo htmlspecialchars($row['quantity']); ?></span>
              <span><strong>Condition:</strong> <?php echo htmlspecialchars($row['product_condition']); ?></span>
              <span><strong>Expiry:</strong> <?php echo htmlspecialchars($row['expiration_date']); ?></span>
            </div>
            <div class="action-row">
              <a href="product-details.php?id=<?php echo (int) $row['product_id']; ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</body>
</html>