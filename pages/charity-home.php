<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if ($_SESSION['role'] !== 'charity') {
    header('Location: login.php');
    exit;
}
$user = current_user();
?>
<!DOCTYPE html>
<html>
<head><title>Charity Home</title></head>
<body>
<h2>Welcome <?php echo htmlspecialchars($user['full_name']); ?> (Charity)</h2>
<a href="logout.php">Logout</a>
</body>
</html>
