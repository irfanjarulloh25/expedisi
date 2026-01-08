<?php
session_start();
include "../config/koneksi.php";
header('Content-Type: application/json');

$resi = trim($_POST['no_resi'] ?? '');
$result = ['valid' => false];

if ($resi) {

    // 1. Cek keberadaan paket
    $q = mysqli_query($conn, "
        SELECT id 
        FROM paket 
        WHERE no_resi = '$resi' 
        LIMIT 1
    ");

    if (mysqli_num_rows($q) > 0) {

        $p = mysqli_fetch_assoc($q);
        $paket_id = $p['id'];

        // 2. CEK STATUS TERAKHIR (TAMBAHAN BARU)
        $qsStatus = mysqli_query($conn, "
            SELECT status 
            FROM status_paket 
            WHERE paket_id = '$paket_id'
            ORDER BY id DESC 
            LIMIT 1
        ");

        if ($lastStatus = mysqli_fetch_assoc($qsStatus)) {
            if (strtolower($lastStatus['status']) === 'paket sedang diantar') {
                // ❌ Sudah diantar → tidak boleh scan lagi
                echo json_encode(['valid' => false]);
                exit;
            }
        }

        // 3. LOGIKA LAMA (TIDAK DIUBAH)
        $qs = mysqli_query($conn, "
            SELECT jenis_scan 
            FROM scan_paket 
            WHERE paket_id = '$paket_id' 
            ORDER BY scan_time DESC 
            LIMIT 1
        ");

        $last = mysqli_fetch_assoc($qs);

        if (!$last || $last['jenis_scan'] !== 'masuk') {
            $result['valid'] = true;
        }
    }
}

echo json_encode($result);
