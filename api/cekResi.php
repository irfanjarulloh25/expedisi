<?php
session_start();
include "../config/koneksi.php";
header('Content-Type: application/json');

/* ===============================
   VALIDASI AWAL
================================ */
$resi    = $_POST['no_resi'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$result = ['valid' => false, 'message' => ''];

if ($resi === '' || $user_id == 0) {
    $result['message'] = 'Sesi habis atau resi kosong. Silakan login ulang.';
    echo json_encode($result);
    exit;
}

/* ===============================
   AMBIL GUDANG USER
================================ */
$qUser = mysqli_query($conn, "
    SELECT gudang_id 
    FROM karyawan 
    WHERE id = '$user_id'
    LIMIT 1
");

if (!$qUser || mysqli_num_rows($qUser) === 0) {
    $result['message'] = 'Data user tidak ditemukan';
    echo json_encode($result);
    exit;
}

$user = mysqli_fetch_assoc($qUser);
$gudang_user = $user['gudang_id'];

/* ===============================
   CEK PAKET
================================ */
$qPaket = mysqli_query($conn, "
    SELECT id 
    FROM paket 
    WHERE no_resi = '$resi'
    LIMIT 1
");

if (!$qPaket || mysqli_num_rows($qPaket) === 0) {
    $result['message'] = 'Resi tidak ditemukan';
    echo json_encode($result);
    exit;
}

$paket = mysqli_fetch_assoc($qPaket);
$paket_id = $paket['id'];

/* ===============================
   AMBIL SCAN TERAKHIR
================================ */
$qLast = mysqli_query($conn, "
    SELECT jenis_scan, gudang_id, scan_time
    FROM scan_paket
    WHERE paket_id = '$paket_id'
    ORDER BY scan_time DESC
    LIMIT 1
");

/* ===============================
   LOGIKA UTAMA
================================ */
if ($qLast && mysqli_num_rows($qLast) > 0) {

    $last = mysqli_fetch_assoc($qLast);

    $jenis_terakhir  = $last['jenis_scan'];
    $gudang_terakhir = $last['gudang_id'];

    // ❌ TOLAK: sudah keluar di gudang yang sama
    if ($jenis_terakhir === 'keluar' && $gudang_terakhir == $gudang_user) {
        $result['message'] = 'Resi sudah discan keluar di gudang ini';
        echo json_encode($result);
        exit;
    }

    // ✅ SEMUA KONDISI LAIN BOLEH
    $result['valid'] = true;
    echo json_encode($result);
    exit;
}

/* ===============================
   BELUM PERNAH DISCAN
================================ */
$result['valid'] = true;
echo json_encode($result);
exit;
