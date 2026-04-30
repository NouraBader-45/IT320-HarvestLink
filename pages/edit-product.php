<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');

$user = current_user();
$success = '';
$error = '';

if (!isset($_GET['id'])) {
    die('Invalid request.');
}

$productId = (int) $_GET['id'];

/* Get Farmer ID */
$farmerStmt = db()->prepare('SELECT farmer_id FROM farmers WHERE user_id = ? LIMIT 1');
$farmerStmt->bind_param('i', $user['user_id']);
$farmerStmt->execute();
$farmer = $farmerStmt->get_result()->fetch_assoc();

if (!$farmer) {
    die('Farmer not found.');
}

/* Fetch product (ONLY farmer's product) */
$stmt = db()->prepare('SELECT * FROM surplus_products WHERE product_id = ? AND farmer_id = ? LIMIT 1');
$stmt->bind_param('ii', $productId, $farmer['farmer_id']);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('Unauthorized access.');
}

/* Handle Form Submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cropType = trim($_POST['crop_type']);
    $quantity = $_POST['quantity'];
    $condition = $_POST['product_condition'];
    $expiry = $_POST['expiration_date'];

    /* Validation */
    if (empty($cropType) ||empty($quantity) || empty($condition) || empty($expiry)) {
        $error = 'All fields are required.';
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $error = 'Quantity must be a positive number.';
    } elseif ($expiry < date('Y-m-d')) {
        $error = 'Expiration date cannot be in the past.';
    } else {

        $imagePath = $product['image'];

        /* Image Upload */
        if (!empty($_FILES['image']['name'])) {
            $targetDir = '../uploads/';
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        /* Update Query */
        $updateStmt = db()->prepare('UPDATE surplus_products 
            SET crop_type=?, quantity=?, product_condition=?, expiration_date=?, image=? 
            WHERE product_id=? AND farmer_id=?');

        $updateStmt->bind_param(
            'sisssii',
            $cropType,
            $quantity,
            $condition,
            $expiry,
            $imagePath,
            $productId,
            $farmer['farmer_id']
        );

        if ($updateStmt->execute()) {
            $success = 'Product details updated successfully.';

            /* Refresh data */
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Update failed. Try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HarvestLink | Edit Product</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- HEADER (same as other pages) -->
<header class="main-header">
  <div class="container header-wrap">
    <a class="logo-area" href="../index.html">
      <img src="../assets/images/logo.png" alt="Logo" />
      <div class="logo-text">
        <h1>HarvestLink</h1>
        <p>Edit Product</p>
      </div>
    </a>

    <nav class="nav-links">
      <a href="farmer-home.php">Home</a>
      <a href="add-product.php">Add Product</a>
      <a href="my-products.php" class="active">My Products</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
  </div>
</header>

<!-- HERO -->
<section class="page-hero">
  <div class="container">
    <h2>Edit Product</h2>
    <p>Update your product details </p>
  </div>
</section>

<!-- FORM SECTION -->
<section class="container section">
  
  <!-- SUCCESS MESSAGE -->
  <?php if ($success): ?>
    <div class="note-box" style="border-left-color:#2f7a4f; color:#2f7a4f; margin-bottom:20px;">
      <?php echo htmlspecialchars($success); ?>
    </div>
  <?php endif; ?>

  <!-- ERROR MESSAGE -->
  <?php if ($error): ?>
    <div class="note-box" style="border-left-color:#b00020; color:#b00020; margin-bottom:20px;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <div class="form-card">

  <form method="POST" enctype="multipart/form-data" class="form-grid">

    <div class="form-group">
      <label>Crop Type</label>
      <input type="text" name="crop_type"
        value="<?php echo htmlspecialchars($product['crop_type']); ?>" required>
    </div>

    <div class="form-group">
      <label>Quantity</label>
      <input type="number" name="quantity"
        value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
    </div>

    <div class="form-group">
      <label>Product Condition</label>
      <select name="product_condition" required>
        <option value="Fresh" <?php if($product['product_condition']=='Fresh') echo 'selected'; ?>>Fresh</option>
        <option value="Good" <?php if($product['product_condition']=='Good') echo 'selected'; ?>>Good</option>
      </select>
    </div>

    <div class="form-group">
      <label>Expiration Date</label>
      <input type="date" name="expiration_date"
        value="<?php echo $product['expiration_date']; ?>" required>
    </div>

    <div class="form-group full">
      <label>Replace Image (optional)</label>
      <input type="file" name="image">
    </div>

    <div class="form-actions full">
      <button type="submit" class="btn">Save Changes</button>
      <a href="my-products.php" class="btn btn-outline">Cancel</a>
    </div>

  </form>
</div>
</section>

</body>
</html>