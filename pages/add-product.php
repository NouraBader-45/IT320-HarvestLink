<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit;
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop = $_POST['crop_type'];
    $qty = $_POST['quantity'];

    $stmt = db()->prepare("
        INSERT INTO surplus_products (farmer_id, crop_type, quantity, expiration_date, product_condition)
        VALUES ((SELECT farmer_id FROM farmers WHERE user_id=?), ?, ?, CURDATE(), 'Fresh')
    ");

    $stmt->bind_param("isd", $_SESSION['user_id'], $crop, $qty);
    $stmt->execute();

    $msg = "Product added!";
}
?>

<h2>Add Product</h2>
<p><?php echo $msg; ?></p>

<form method="POST">
<input name="crop_type" placeholder="Crop">
<input name="quantity" placeholder="Quantity">
<button>Add</button>
</form>
