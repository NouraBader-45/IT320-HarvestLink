<?php
// هذا الملف فيه كل الأشياء المتعلقة بالجلسات (session) وتسجيل الدخول.

require_once __DIR__ . '/../config/database.php';

session_start();

// دالة تساعدنا نرجع المستخدم الحالي من الداتابيس
function current_user()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// تسجيل دخول المستخدم
function login_user(array $user)
{
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
}

// تسجيل خروج
function logout_user()
{
    session_unset();
    session_destroy();
}

// نتاكد إن المستخدم مسجل دخول
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// نتاكد من الرول
function require_role($role)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}
