<?php
header('Content-Type: application/json');
include 'koneksi.php';

$kurir_id = isset($_POST['kurir_id']) ? $_POST['kurir_id'] : '';

if(empty($kurir_id)){
    echo json_encode([]);
    exit;
}

// Query mengambil data paket diterima hari ini oleh kurir login
$sql = "SELECT p.no_resi, p.nama_penerima, ROUND(p.harga_cod) as nominal_cod 
        FROM status_paket st
        JOIN paket p ON st.paket_id = p.id
        WHERE st.updated_by = '$kurir_id' 
        AND st.status LIKE 'Diterima%' 
        AND p.harga_cod > 0
        AND DATE(st.updated_at) = CURDATE()";

$result = mysqli_query($conn, $sql);
$data = array();

while($row = mysqli_fetch_assoc($result)){
    $row['nominal_cod'] = (float)$row['nominal_cod'];
    $data[] = $row;
}

echo json_encode($data);
?>