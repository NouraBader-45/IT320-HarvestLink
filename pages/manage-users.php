<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if (isset($_GET['toggle'])) {
    $userId = (int) $_GET['toggle'];

    $userStmt = db()->prepare('SELECT account_status FROM users WHERE user_id = ? LIMIT 1');
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();

    if ($userRow) {
        $newStatus = $userRow['account_status'] === 'blocked' ? 'active' : 'blocked';
        $updateStmt = db()->prepare('UPDATE users SET account_status = ? WHERE user_id = ?');
        $updateStmt->bind_param('si', $newStatus, $userId);
        $updateStmt->execute();
    }

    header('Location: manage-users.php');
    exit;
}

$users = db()->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HarvestLink | Manage Users</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="main-header">
    <div class="container header-wrap">
      <a class="logo-area" href="../index.html">
        <img src="../assets/images/logo.png" alt="Logo" />
        <div class="logo-text">
          <h1>HarvestLink</h1>
          <p>Manage Users</p>
        </div>
      </a>

      <nav class="nav-links">
        <a href="admin-home.php">Home</a>
        <a class="active" href="manage-users.php">Manage Users</a>
        <a href="manage-listings.php">Manage Listings</a>
        <a href="logout.php" class="logout-btn"
   onclick="return confirm('Are you sure you want to log out?');">
   Logout
</a>
      </nav>
    </div>
  </header>

  <section class="container section">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $users->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['role']); ?></td>
              <td><?php echo htmlspecialchars($row['account_status']); ?></td>
              <td>
                <a href="manage-users.php?toggle=<?php echo (int) $row['user_id']; ?>" class="btn btn-danger">
                  <?php echo $row['account_status'] === 'blocked' ? 'Unblock User' : 'Block User'; ?>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>
</body>
</html>