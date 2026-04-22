<?php
// هذا الملف مسؤول عن الاتصال بقاعدة البيانات

const DB_HOST = '127.0.0.1';
const DB_PORT = '8889'; // لو تستخدمين MAMP
const DB_NAME = 'harvestlink_db';
const DB_USER = 'root';
const DB_PASS = 'root';

function db() {
    static $conn = null;

    // لو الاتصال موجود خلاص نرجعه
    if ($conn) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        die("Database Error: " . $conn->connect_error);
    }

    // عشان يدعم العربي
    $conn->set_charset("utf8mb4");

    return $conn;
}
