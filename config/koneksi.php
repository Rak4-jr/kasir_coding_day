<?php
// Konfigurasi koneksi database (default XAMPP: user root, tanpa password)
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'db_kasir';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Koneksi database gagal. Pastikan MySQL di XAMPP sudah Start dan database.sql sudah di-import. (' . htmlspecialchars($ex->getMessage()) . ')');
}
