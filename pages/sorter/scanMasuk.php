<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_nama'] !== 'sorter') {
    header('Location: ../../login.php');
    exit;
}

include '../../config/koneksi.php';
$user = $_SESSION['user_id'];

// Ambil gudang_id user
$qKaryawan = mysqli_query($conn, "SELECT gudang_id FROM karyawan WHERE id = '$user' LIMIT 1");
$gudang_id = 0;
if ($row = mysqli_fetch_assoc($qKaryawan)) { $gudang_id = $row['gudang_id']; }

// Ambil nama gudang
$namaGudang = 'Gudang';
if ($gudang_id) {
    $qGudang = mysqli_query($conn, "SELECT nama_gudang FROM gudang WHERE id = '$gudang_id' LIMIT 1");
    if ($g = mysqli_fetch_assoc($qGudang)) { $namaGudang = $g['nama_gudang']; }
}

// PROSES SIMPAN FINAL
if (isset($_POST['selesai'])) {
    $resiList = json_decode($_POST['resi_list'], true);
    
    if (is_array($resiList) && count($resiList) > 0) {
        foreach ($resiList as $resiRaw) {
            $resi = trim(mysqli_real_escape_string($conn, $resiRaw));
            $q = mysqli_query($conn, "SELECT id FROM paket WHERE no_resi = '$resi' LIMIT 1");
            if ($p = mysqli_fetch_assoc($q)) {
                $paket_id = $p['id'];
                mysqli_query($conn, "INSERT INTO scan_paket (paket_id, jenis_scan, gudang_id, scan_by) VALUES ('$paket_id', 'masuk', '$gudang_id', '$user')");
                mysqli_query($conn, "INSERT INTO status_paket (paket_id, status, keterangan, updated_by) VALUES ('$paket_id', 'Paket masuk', '$namaGudang', '$user')");
            }
        }
        $_SESSION['success_msg'] = "Berhasil simpan " . count($resiList) . " paket ke $namaGudang";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>


<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
            <h4 class="text-xl font-semibold mb-3">Scan Paket Masuk</h4>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap mb-4 gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input id="resiInput" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200" placeholder="Scan Resi di sini..." autofocus>
                    </div>
                    <div class="flex-none">
                        <button type="button" id="btnScan" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
                    </div>
                </div>

                <ul id="listResi" class="mb-4 border border-gray-200 rounded min-h-[50px] bg-gray-50"></ul>

                <form method="POST" onsubmit="return submitData()">
                    <input type="hidden" name="resi_list" id="resi_list_input">
                    <input type="hidden" id="user_id" value="<?= $user ?>">
                    <div class="text-right">
                        <button type="submit" name="selesai" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold">Simpan Selesai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<audio id="beep"><source src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" type="audio/ogg"></audio>

<script>
<?php if(isset($_SESSION['success_msg'])): ?>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= $_SESSION['success_msg'] ?>', timer: 3000, showConfirmButton: false });
<?php unset($_SESSION['success_msg']); endif; ?>
</script>

<script src="../../assets/javascript/scanMasuk.js"></script>