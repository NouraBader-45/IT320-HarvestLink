<?php
// هنا نسوي إعداد الاتصال بقاعدة البيانات.
// الفكرة إننا نخلي كل شيء بمكان واحد عشان لو تغيرت بيانات الاتصال ما نلف على كل الملفات.

const DB_HOST = '127.0.0.1';
const DB_PORT = '8889';
const DB_NAME = 'harvestlink_db';
const DB_USER = 'root';
const DB_PASS = 'root';

function db(): mysqli
{
    static $connection = null;

    // إذا الاتصال انفتح قبل، نرجع نفس الاتصال ونوفر على نفسنا.
    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    // نخلي الترميز utf8mb4 عشان العربي والإنجليزي والإيموجي إذا احتجناها.
    $connection->set_charset('utf8mb4');

    return $connection;
}
