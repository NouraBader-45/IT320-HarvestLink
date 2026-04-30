<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if (isset($_GET['toggle'])) {
    $productId = (int) $_GET['toggle'];

    $productStmt = db()->prepare('SELECT product_status FROM surplus_products WHERE product_id = ? LIMIT 1');
    $productStmt->bind_param('i', $productId);
    $productStmt->execute();
    $productRow = $productStmt->get_result()->fetch_assoc();

    if ($productRow) {
        $newStatus = $productRow['product_status'] === 'Blocked' ? 'Available' : 'Blocked';
        $updateStmt = db()->prepare('UPDATE surplus_products SET product_status = ? WHERE product_id = ?');
        $updateStmt->bind_param('si', $newStatus, $productId);
        $updateStmt->execute();
    }

    header('Location: manage-listings.php');
    exit;
}

$listings = db()->query("SELECT p.*, u.full_name AS farmer_name
                         FROM surplus_products p
                         JOIN farmers f ON p.farmer_id = f.farmer_id
                         JOIN users u ON f.user_id = u.user_id
                         ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Manage Listings</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Manage Listings</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="admin-home.php">Home</a>
        <a href="manage-users.php">Manage Users</a>
        <a class="active" href="manage-listings.php">Manage Listings</a>
        <a href="logout.php" class="logout-btn"
   onclick="return confirm('Are you sure you want to log out?');">
   Logout
</a>
      </nav>
    </div>
  </header>

  <section class="container section">
    <div class="product-grid">
      <?php while ($row = $listings->fetch_assoc()): ?>
        <div class="product-card">
          <div class="image-box">
            <img src="<?php echo !empty($row['image']) ? '../' . htmlspecialchars($row['image']) : '../assets/images/logo.png'; ?>" alt="Product" />
          </div>
          <div class="content">
            <h4><?php echo htmlspecialchars($row['crop_type']); ?></h4>
            <div class="meta">
              <span><strong>Farmer:</strong> <?php echo htmlspecialchars($row['farmer_name']); ?></span>
              <span><strong>Quantity:</strong> <?php echo htmlspecialchars($row['quantity']); ?></span>
              <span><strong>Status:</strong> <?php echo htmlspecialchars($row['product_status']); ?></span>
            </div>
            <div class="action-row">
              <a href="manage-listings.php?toggle=<?php echo (int) $row['product_id']; ?>" class="btn btn-danger">
                <?php echo $row['product_status'] === 'Blocked' ? 'Unblock Product' : 'Block Product'; ?>
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</body>
</html>