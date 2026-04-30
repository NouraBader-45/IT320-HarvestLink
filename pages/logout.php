<?php
require_once __DIR__ . '/../includes/auth.php';

logout_user();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Location: login.php?logged_out=1');
exit;