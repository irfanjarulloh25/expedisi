<?php
include 'koneksi.php';

$kurir_id = isset($_POST['kurir_id']) ? $_POST['kurir_id'] : '';

if(empty($kurir_id)){
    echo json_encode([]);
    exit;
}

// Menampilkan data status_paket yang diupdate kurir hari ini
$sql = "SELECT p.no_resi, p.nama_penerima, p.alamat_penerima as alamat, 
               p.telp_penerima as telp, p.harga_cod, st.status 
        FROM status_paket st
        JOIN paket p ON st.paket_id = p.id
        WHERE st.updated_by = '$kurir_id' 
        AND DATE(st.updated_at) = CURDATE() 
        ORDER BY st.updated_at DESC";

$result = mysqli_query($conn, $sql);
$data = array();

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>