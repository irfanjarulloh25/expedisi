<?php
include __DIR__ . '/../config/koneksi.php';

$berat = (float) ($_POST['berat'] ?? 0);
$kota  = $_POST['kota'] ?? '';

$harga = 0;

if ($berat && $kota) {
    $stmt = mysqli_prepare($conn, "SELECT harga FROM harga_pengiriman WHERE kota=? AND ? BETWEEN berat_min AND berat_max LIMIT 1");
    mysqli_stmt_bind_param($stmt, "sd", $kota, $berat);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $harga = (float) $row['harga'];
    }
}

echo json_encode(['harga' => $harga]);
