<?php
// إعدادات قاعدة البيانات
// عدليها فقط إذا تغيّر البورت أو اسم القاعدة

const DB_HOST = 'localhost';
const DB_PORT = '3336';
const DB_NAME = 'harvestlink_db';
const DB_USER = 'root';
const DB_PASS = 'root';

function db()
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);

    if ($conn->connect_error) {
        die('Database Error: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}