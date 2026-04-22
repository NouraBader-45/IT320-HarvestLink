<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$user = current_user();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['full_name'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];

    $stmt = db()->prepare("UPDATE users SET full_name=?, phone_number=?, address=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $phone, $address, $user['user_id']);
    $stmt->execute();

    $msg = "Profile updated!";
    $user = current_user();
}
?>

<h2>Profile</h2>
<p><?php echo $msg; ?></p>

<form method="POST">
<input name="full_name" value="<?php echo $user['full_name']; ?>">
<input name="phone_number" value="<?php echo $user['phone_number']; ?>">
<input name="address" value="<?php echo $user['address']; ?>">
<button>Save</button>
</form>

<a href="logout.php">Logout</a>
