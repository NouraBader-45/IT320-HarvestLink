<?php
session_start();
session_destroy(); // إنهاء الجلسة بالكامل
header("Location: ../login.php"); // التوجيه لصفحة تسجيل الدخول الجديدة
exit();
?>