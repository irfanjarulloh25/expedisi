<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// cek apakah ada id
if (!isset($_GET['id'])) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'title' => 'Error',
        'text' => 'ID karyawan tidak ditemukan'
    ];
    header("Location: listKaryawan.php");
    exit;
}

$id = (int)$_GET['id'];

// =======================
// Ambil nama file foto
// =======================
$res = mysqli_query($conn, "SELECT foto FROM karyawan WHERE id=$id LIMIT 1");
if ($res && mysqli_num_rows($res) > 0) {
    $data = mysqli_fetch_assoc($res);
    if ($data['foto']) {
        $filePath = __DIR__ . '/../uploads/karyawan/' . $data['foto'];
        if (file_exists($filePath)) {
            unlink($filePath); // hapus file foto lama
        }
    }
}

// =======================
// Hapus data karyawan
// =======================
$query = mysqli_query($conn, "DELETE FROM karyawan WHERE id=$id");

// =======================
// Simpan flash message
// =======================
if ($query) {
    $_SESSION['flash'] = [
        'type' => 'success',
        'title' => 'Berhasil',
        'text' => 'Karyawan berhasil dihapus'
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'title' => 'Gagal',
        'text' => 'Gagal hapus karyawan: ' . mysqli_error($conn)
    ];
}

// redirect ke listKaryawan.php
header("Location: listKaryawan.php");
exit;
