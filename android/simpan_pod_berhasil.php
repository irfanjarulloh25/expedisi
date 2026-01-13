<?php
include 'koneksi.php';

// 1. Ambil data dari Android
$no_resi      = isset($_POST['no_resi']) ? $_POST['no_resi'] : '';
$nama_pod     = isset($_POST['nama_pod']) ? $_POST['nama_pod'] : ''; // Nama dari EditText etNama
$keterangan   = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
$kurir_id     = isset($_POST['kurir_id']) ? $_POST['kurir_id'] : null;
$foto_base64  = isset($_POST['foto']) ? $_POST['foto'] : '';

// 2. Buat Status Khusus (Diterima - Nama Penerima)
$status_final = "Diterima - " . $nama_pod;

// 3. Cari ID Paket dari nomor resi
$query_cari = mysqli_query($conn, "SELECT id FROM paket WHERE no_resi = '$no_resi'");
$data_paket = mysqli_fetch_assoc($query_cari);

if ($data_paket) {
    $id_paket = $data_paket['id'];
    $nama_foto = "POD_" . $no_resi . "_" . time() . ".jpg";
    $target_path = "../pages/uploads/fotoPenerima/" . $nama_foto;

    if (file_put_contents($target_path, base64_decode($foto_base64))) {
        
        mysqli_begin_transaction($conn);
        try {
            // 4. Update status_paket (Menggabungkan status dengan nama inputan)
            $sql_status = "INSERT INTO status_paket (paket_id, status, keterangan, foto_penerima, updated_by, updated_at) 
                           VALUES ('$id_paket', '$status_final', '$keterangan', '$nama_foto', '$kurir_id', NOW())
                           ON DUPLICATE KEY UPDATE 
                           status = '$status_final', 
                           keterangan = '$keterangan', 
                           foto_penerima = '$nama_foto', 
                           updated_by = '$kurir_id', 
                           updated_at = NOW()";
            mysqli_query($conn, $sql_status);

            // 5. CEGAH DOUBLE SCAN: Cek apakah sudah ada input terkirim dalam 30 detik terakhir
            $cek_recent = mysqli_query($conn, "SELECT id FROM scan_paket 
                                              WHERE paket_id = '$id_paket' 
                                              AND jenis_scan = 'terkirim' 
                                              AND scan_time > DATE_SUB(NOW(), INTERVAL 30 SECOND)");

            if (mysqli_num_rows($cek_recent) == 0) {
                $sql_scan = "INSERT INTO scan_paket (paket_id, jenis_scan, kurir_id, scan_time) 
                             VALUES ('$id_paket', 'terkirim', '$kurir_id', NOW())";
                mysqli_query($conn, $sql_scan);
            }

            mysqli_commit($conn);
            echo "Berhasil Simpan Data POD";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Gagal DB: " . mysqli_error($conn);
        }
    } else {
        echo "Gagal upload foto ke server.";
    }
} else {
    echo "Nomor Resi tidak ditemukan.";
}
?>