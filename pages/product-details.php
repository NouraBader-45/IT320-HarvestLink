<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('charity');

$user = current_user();
$success = '';
$error = '';
$productId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare("SELECT p.*, u.full_name AS farmer_name, c.charity_id
                       FROM surplus_products p
                       JOIN farmers f ON p.farmer_id = f.farmer_id
                       JOIN users u ON f.user_id = u.user_id
                       JOIN charitable_organizations c ON c.user_id = ?
                       WHERE p.product_id = ? LIMIT 1");
$stmt->bind_param('ii', $user['user_id'], $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('Product not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestedQuantity = (float) ($_POST['requested_quantity'] ?? 0);

    if ($requestedQuantity <= 0 || $requestedQuantity > (float) $product['quantity']) {
        $error = 'Invalid quantity. Please enter a value between 1 and available quantity.';
    } else {
        $insertStmt = db()->prepare('INSERT INTO donation_requests (product_id, charity_id, requested_quantity) VALUES (?, ?, ?)');
        $insertStmt->bind_param('iid', $productId, $product['charity_id'], $requestedQuantity);

        if ($insertStmt->execute()) {
            $requestId = db()->insert_id;

            // إشعار للمزارع
            $farmerUserStmt = db()->prepare('SELECT u.user_id
                                             FROM farmers f
                                             JOIN users u ON f.user_id = u.user_id
                                             WHERE f.farmer_id = ? LIMIT 1');
            $farmerUserStmt->bind_param('i', $product['farmer_id']);
            $farmerUserStmt->execute();
            $farmerUser = $farmerUserStmt->get_result()->fetch_assoc();

            if ($farmerUser) {
                add_notification((int) $farmerUser['user_id'], $requestId, 'A new donation request was submitted for ' . $product['crop_type']);
            }

            $success = 'Request submitted successfully.';
        } else {
            $error = 'Failed to submit request.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Product Details</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Product Details</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="charity-home.php">Home</a>
        <a href="browse-products.php">Browse Products</a>
        <a href="my-requests.php">My Requests</a>
        <a href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <section class="container section">
    <div class="detail-card">
      <div class="detail-visual">
        <img src="<?php echo !empty($product['image']) ? '../' . htmlspecialchars($product['image']) : '../assets/images/logo.png'; ?>" alt="Product Image" />
      </div>

      <div class="detail-info">
        <span class="role-badge">Available Listing</span>
        <h3><?php echo htmlspecialchars($product['crop_type']); ?></h3>

        <div class="detail-list">
          <div><strong>Farmer Name:</strong> <?php echo htmlspecialchars($product['farmer_name']); ?></div>
          <div><strong>Quantity Available:</strong> <?php echo htmlspecialchars($product['quantity']); ?></div>
          <div><strong>Expiration Date:</strong> <?php echo htmlspecialchars($product['expiration_date']); ?></div>
          <div><strong>Product Condition:</strong> <?php echo htmlspecialchars($product['product_condition']); ?></div>
        </div>

        <?php if ($success): ?>
          <div class="note-box" style="border-left-color:#2f7a4f; color:#2f7a4f;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="note-box" style="border-left-color:#b94a48; color:#b94a48;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label for="requested_quantity">Requested Quantity</label>
            <input id="requested_quantity" name="requested_quantity" type="number" step="0.01" placeholder="Enter quantity" required />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="browse-products.php" class="btn btn-outline">Back</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</body>
</html>