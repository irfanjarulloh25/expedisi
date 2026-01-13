<?php
header('Content-Type: application/json');
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kurir_id = $_POST['kurir_id'];

    // Query diperbaiki: Mengecualikan paket yang sudah 'terkirim' ATAU 'bermasalah'
    $sql = "SELECT p.* FROM paket p 
            JOIN scan_paket s ON p.id = s.paket_id 
            WHERE s.kurir_id = '$kurir_id' 
            AND s.jenis_scan = 'diantar' 
            AND p.id NOT IN (
                SELECT paket_id FROM scan_paket 
                WHERE jenis_scan = 'terkirim' OR jenis_scan = 'bermasalah'
            )";
            
    $result = mysqli_query($conn, $sql);
    $array_paket = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $array_paket[] = $row;
    }

    echo json_encode([
        "status" => true,
        "data" => $array_paket
    ]);
}
?>