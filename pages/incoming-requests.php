<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');

$user = current_user();

$farmerStmt = db()->prepare('SELECT farmer_id FROM farmers WHERE user_id = ? LIMIT 1');
$farmerStmt->bind_param('i', $user['user_id']);
$farmerStmt->execute();
$farmer = $farmerStmt->get_result()->fetch_assoc();

if (!$farmer) {
    die('Farmer profile not found.');
}

if (isset($_GET['action'], $_GET['request_id'])) {
    $requestId = (int) $_GET['request_id'];
    $action = $_GET['action'];

    if (in_array($action, ['Approved', 'Rejected'], true)) {
        $updateStmt = db()->prepare("UPDATE donation_requests r
                                     JOIN surplus_products p ON r.product_id = p.product_id
                                     SET r.request_status = ?, r.decision_date = NOW()
                                     WHERE r.request_id = ? AND p.farmer_id = ?");
        $updateStmt->bind_param('sii', $action, $requestId, $farmer['farmer_id']);
        $updateStmt->execute();

        // إشعار للجمعية
        $charityUserStmt = db()->prepare("SELECT u.user_id
                                          FROM donation_requests r
                                          JOIN charitable_organizations c ON r.charity_id = c.charity_id
                                          JOIN users u ON c.user_id = u.user_id
                                          WHERE r.request_id = ? LIMIT 1");
        $charityUserStmt->bind_param('i', $requestId);
        $charityUserStmt->execute();
        $charityUser = $charityUserStmt->get_result()->fetch_assoc();

        if ($charityUser) {
            add_notification((int) $charityUser['user_id'], $requestId, 'Your request status was updated to ' . $action);
        }

        header('Location: incoming-requests.php');
        exit;
    }
}

$stmt = db()->prepare("SELECT r.*, p.crop_type, u.full_name AS charity_name
                       FROM donation_requests r
                       JOIN surplus_products p ON r.product_id = p.product_id
                       JOIN charitable_organizations c ON r.charity_id = c.charity_id
                       JOIN users u ON c.user_id = u.user_id
                       WHERE p.farmer_id = ?
                       ORDER BY r.request_date DESC");
$stmt->bind_param('i', $farmer['farmer_id']);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Incoming Requests</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Incoming Requests</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="farmer-home.php">Home</a>
        <a href="my-products.php">My Products</a>
        <a class="active" href="incoming-requests.php">Incoming Requests</a>
        <a href="logout.php" class="logout-btn">Logout</a>
      </nav>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h2>Incoming Donation Requests</h2>
      <p>Approve or reject requests from charitable organizations.</p>
    </div>
  </section>

  <section class="container section">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Charity Name</th>
            <th>Requested Quantity</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests->num_rows === 0): ?>
            <tr>
              <td colspan="5">No requests found.</td>
            </tr>
          <?php endif; ?>

          <?php while ($row = $requests->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['crop_type']); ?></td>
              <td><?php echo htmlspecialchars($row['charity_name']); ?></td>
              <td><?php echo htmlspecialchars($row['requested_quantity']); ?></td>
              <td><?php echo htmlspecialchars($row['request_status']); ?></td>
              <td>
                <?php if ($row['request_status'] === 'Pending'): ?>
                  <a href="incoming-requests.php?action=Approved&request_id=<?php echo (int) $row['request_id']; ?>" class="btn btn-success">Approve</a>
                  <a href="incoming-requests.php?action=Rejected&request_id=<?php echo (int) $row['request_id']; ?>" class="btn btn-danger">Reject</a>
                <?php else: ?>
                  View only
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>
</body>
</html>