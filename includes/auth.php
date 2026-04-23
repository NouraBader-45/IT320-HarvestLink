<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// نجيب المستخدم الحالي
function current_user()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['role'] = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}

function redirect_by_role(string $role): void
{
    if ($role === 'farmer') {
        header('Location: farmer-home.php');
    } elseif ($role === 'charity') {
        header('Location: charity-home.php');
    } else {
        header('Location: admin-home.php');
    }
    exit;
}

function get_profile_image(?array $user): string
{
    if (!$user) {
        return '../assets/images/default-profile.png';
    }

    $image = trim((string) ($user['profile_image'] ?? ''));

    if ($image === '') {
        return '../assets/images/default-profile.png';
    }

    return '../' . ltrim($image, '/');
}

function add_notification(int $userId, ?int $requestId, string $message): void
{
    $stmt = db()->prepare('INSERT INTO notifications (user_id, request_id, message) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $userId, $requestId, $message);
    $stmt->execute();
}

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}