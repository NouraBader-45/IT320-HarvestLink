<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('charity');

$user = current_user();

$charityStmt = db()->prepare('SELECT charity_id FROM charitable_organizations WHERE user_id = ? LIMIT 1');
$charityStmt->bind_param('i', $user['user_id']);
$charityStmt->execute();
$charity = $charityStmt->get_result()->fetch_assoc();

if (!$charity) {
    die('Charity profile not found.');
}

$stmt = db()->prepare("SELECT r.*, p.crop_type, u.full_name AS farmer_name
                       FROM donation_requests r
                       JOIN surplus_products p ON r.product_id = p.product_id
                       JOIN farmers f ON p.farmer_id = f.farmer_id
                       JOIN users u ON f.user_id = u.user_id
                       WHERE r.charity_id = ?
                       ORDER BY r.request_date DESC");
$stmt->bind_param('i', $charity['charity_id']);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | My Requests</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>My Requests</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="charity-home.php">Home</a>
        <a href="browse-products.php">Browse Products</a>
        <a class="active" href="my-requests.php">My Requests</a>
        <a href="logout.php" class="logout-btn">Logout</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>My Requests</h2>
      <p>Track the status of your submitted requests.</p>
    </div>
  </section>

  <section class="container section">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Farmer Name</th>
            <th>Requested Quantity</th>
            <th>Status</th>
            <th>Request Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests->num_rows === 0): ?>
            <tr>
              <td colspan="5">You have no submitted requests.</td>
            </tr>
          <?php endif; ?>

          <?php while ($row = $requests->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['crop_type']); ?></td>
              <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
              <td><?php echo htmlspecialchars($row['requested_quantity']); ?></td>
              <td><?php echo htmlspecialchars($row['request_status']); ?></td>
              <td><?php echo htmlspecialchars($row['request_date']); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>
</body>
</html>