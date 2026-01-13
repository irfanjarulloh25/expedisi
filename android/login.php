<?php
header('Content-Type: application/json');
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk mengambil data karyawan beserta nama role-nya
    $sql = "SELECT k.*, r.nama_role 
            FROM karyawan k 
            JOIN roles r ON k.role_id = r.id 
            WHERE k.email = '$email'";
            
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi password hash Bcrypt
        if (password_verify($password, $row['password'])) {
            echo json_encode([
                "status" => true,
                "message" => "Login Berhasil",
                "data" => [
                    "id" => (int)$row['id'],
                    "nama" => $row['nama'],
                    "email" => $row['email'],
                    "role" => $row['nama_role'],
                    "role_id" => (int)$row['role_id'],
                    "gudang_id" => $row['gudang_id'] ? (int)$row['gudang_id'] : 0
                ]
            ]);
        } else {
            echo json_encode(["status" => false, "message" => "Password salah"]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Email tidak ditemukan"]);
    }
}
?>