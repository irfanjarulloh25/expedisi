<?php
// Pengaturan Database
$host     = "localhost";
$username = "root";
$password = "";
$database = "dbcobaexpedisi";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set timezone agar sesuai dengan waktu Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Set charset ke utf8mb4 agar mendukung karakter khusus/emoji
$conn->set_charset("utf8mb4");
?>