<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');

$user = current_user();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropType = trim($_POST['crop_type'] ?? '');
    $quantity = (float) ($_POST['quantity'] ?? 0);
    $expirationDate = $_POST['expiration_date'] ?? '';
    $condition = $_POST['product_condition'] ?? '';
    $imagePath = null;

    if ($cropType === '' || strlen($cropType) < 3) {
        $error = 'Crop type must be at least 3 characters.';
    } elseif ($quantity <= 0) {
        $error = 'Quantity must be a positive number.';
    } elseif ($expirationDate === '' || strtotime($expirationDate) < strtotime(date('Y-m-d'))) {
        $error = 'Expiration date must be today or a future date.';
    } elseif (!in_array($condition, ['Fresh', 'Near Expiry'], true)) {
        $error = 'Please choose a valid product condition.';
    } else {
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            ensure_directory($uploadDir);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'product_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($extension);
            $fullPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
                $imagePath = 'uploads/products/' . $fileName;
            }
        }

        $farmerStmt = db()->prepare('SELECT farmer_id FROM farmers WHERE user_id = ? LIMIT 1');
        $farmerStmt->bind_param('i', $user['user_id']);
        $farmerStmt->execute();
        $farmer = $farmerStmt->get_result()->fetch_assoc();

        if (!$farmer) {
            $error = 'Farmer profile was not found.';
        } else {
            $stmt = db()->prepare('INSERT INTO surplus_products (farmer_id, crop_type, quantity, expiration_date, product_condition, image) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('isdsss', $farmer['farmer_id'], $cropType, $quantity, $expirationDate, $condition, $imagePath);

            if ($stmt->execute()) {
                $success = 'Product posted successfully.';
            } else {
                $error = 'Failed to post the product.';
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
  <title>HarvestLink | Add Product</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Add Product</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="farmer-home.php">Home</a>
        <a class="active" href="add-product.php">Add Product</a>
        <a href="my-products.php">My Products</a>
        <a href="logout.php" class="logout-btn">Logout</a>      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Add Surplus Product</h2>
      <p>Create a new listing for charitable organizations to review.</p>
    </div>
  </section>

  <section>
    <div class="form-wrap">
      <h3>New Product Listing</h3>
      <p class="subtext">Provide clear and accurate information.</p>

      <?php if ($success): ?>
        <div class="note-box" style="border-left-color:#2f7a4f; color:#2f7a4f;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="note-box" style="border-left-color:#b94a48; color:#b94a48;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="crop_type">Crop Type</label>
          <input id="crop_type" name="crop_type" type="text" placeholder="e.g. Tomatoes, Dates" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" step="0.01" placeholder="e.g. 120" required />
          </div>

          <div class="form-group">
            <label for="product_condition">Product Condition</label>
            <select id="product_condition" name="product_condition" required>
              <option value="" selected disabled>Select condition</option>
              <option value="Fresh">Fresh</option>
              <option value="Near Expiry">Near Expiry</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="expiration_date">Expiration Date</label>
          <input id="expiration_date" name="expiration_date" type="date" required />
        </div>

        <div class="form-group">
          <label for="image">Product Image</label>
          <input id="image" name="image" type="file" accept="image/*" />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Post Product</button>
          <a href="farmer-home.php" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </section>
</body>
</html>