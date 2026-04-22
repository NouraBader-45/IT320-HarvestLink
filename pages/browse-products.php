<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$result = db()->query("SELECT * FROM surplus_products WHERE product_status='Available'");
?>

<h2>Browse Products</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <div>
        <p><?php echo $row['crop_type']; ?></p>
        <p><?php echo $row['quantity']; ?></p>
    </div>
<?php endwhile; ?>

<a href="logout.php">Logout</a>
