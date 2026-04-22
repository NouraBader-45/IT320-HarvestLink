<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'];

    // تحقق بسيط
    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {

        // هل الإيميل موجود؟
        $check = db()->prepare("SELECT user_id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "Email already exists";
        } else {

            // نحول الباسورد
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // ندخل المستخدم
            $stmt = db()->prepare("INSERT INTO users (full_name,email,password_hash,role) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $name, $email, $hash, $role);
            $stmt->execute();

            $userId = db()->insert_id;

            // حسب الرول
            if ($role == "farmer") {
                db()->query("INSERT INTO farmers (user_id) VALUES ($userId)");
            } else {
                db()->query("INSERT INTO charitable_organizations (user_id,organization_name) VALUES ($userId,'$name')");
            }

            header("Location: login.php");
            exit;
        }
    }
}
?>

<form method="POST">
<input name="full_name" placeholder="Name" required>
<input name="email" type="email" placeholder="Email" required>
<input name="password" type="password" placeholder="Password" required>
<input name="confirm_password" type="password" placeholder="Confirm" required>

<select name="role">
<option value="farmer">Farmer</option>
<option value="charity">Charity</option>
</select>

<button>Create</button>
</form>

<?php echo $error; ?>
