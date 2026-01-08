<?php
require_once __DIR__ . '/config/koneksi.php';


$nik       = 'ADM001';
$nama      = 'Administrator';
$email     = 'admin@gmail.com';
$password  = password_hash('admin123', PASSWORD_DEFAULT);
$role_id   = 1; // Admin
$gudang_id = NULL; // admin biasanya tidak terikat gudang

$query = "
    INSERT INTO karyawan (nik, nama, email, password, role_id, gudang_id)
    VALUES ('$nik', '$nama', '$email', '$password', '$role_id', NULL)
";

if (mysqli_query($conn, $query)) {
    echo "✅ User Admin berhasil dibuat<br>";
    echo "Email: admin@gmail.com<br>";
    echo "Password: admin123";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
