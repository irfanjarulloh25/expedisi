<?php
include __DIR__ . '/../config/koneksi.php';

$q = $_POST['q'] ?? '';
$result = [];

if ($q) {
    $stmt = mysqli_prepare($conn, "SELECT DISTINCT kota FROM harga_pengiriman WHERE kota LIKE ? ORDER BY kota ASC LIMIT 10");
    $search = "%$q%";
    mysqli_stmt_bind_param($stmt, "s", $search);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {
        $result[] = $row['kota'];
    }
}

echo json_encode($result);
