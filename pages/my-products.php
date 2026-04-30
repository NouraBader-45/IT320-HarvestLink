<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');

$user = current_user();
$success = '';

$farmerStmt = db()->prepare('SELECT farmer_id FROM farmers WHERE user_id = ? LIMIT 1');
$farmerStmt->bind_param('i', $user['user_id']);
$farmerStmt->execute();
$farmer = $farmerStmt->get_result()->fetch_assoc();

if (!$farmer) {
    die('Farmer profile not found.');
}

if (isset($_GET['delete'])) {
    $productId = (int) $_GET['delete'];

    $deleteStmt = db()->prepare('DELETE FROM surplus_products WHERE product_id = ? AND farmer_id = ?');
    $deleteStmt->bind_param('ii', $productId, $farmer['farmer_id']);
    $deleteStmt->execute();

    header('Location: my-products.php?deleted=1');
    exit;
}

if (isset($_GET['deleted'])) {
    $success = 'Product deleted successfully.';
}

$listStmt = db()->prepare('SELECT * FROM surplus_products WHERE farmer_id = ? ORDER BY created_at DESC');
$listStmt->bind_param('i', $farmer['farmer_id']);
$listStmt->execute();
$products = $listStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | My Products</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>My Products</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="farmer-home.php">Home</a>
        <a href="add-product.php">Add Product</a>
        <a class="active" href="my-products.php">My Products</a>
        <a href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>My Products</h2>
      <p>Review and delete your surplus listings.</p>
    </div>
  </section>

  <section class="container section">
    <?php if ($success): ?>
      <div class="note-box" style="border-left-color:#2f7a4f; color:#2f7a4f; margin-bottom:20px;">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <div class="product-grid">
      <?php while ($row = $products->fetch_assoc()): ?>
        <div class="product-card">
          <div class="image-box">
            <img src="<?php echo !empty($row['image']) ? '../' . htmlspecialchars($row['image']) : '../assets/images/logo.png'; ?>" alt="Product" />
    </div>
          <div class="content">
            <h4><?php echo htmlspecialchars($row['crop_type']); ?></h4>
            <div class="meta">
              <span><strong>Quantity:</strong> <?php echo htmlspecialchars($row['quantity']); ?></span>
              <span><strong>Condition:</strong> <?php echo htmlspecialchars($row['product_condition']); ?></span>
              <span><strong>Expiry:</strong> <?php echo htmlspecialchars($row['expiration_date']); ?></span>
              <span><strong>Status:</strong> <?php echo htmlspecialchars($row['product_status']); ?></span>
            </div>
            <div class="action-row">
              <a href="my-products.php?delete=<?php echo (int) $row['product_id']; ?>" class="btn btn-danger">Delete</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</body>
</html>