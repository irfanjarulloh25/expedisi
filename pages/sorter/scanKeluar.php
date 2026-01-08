<?php
session_start();
// 1. VALIDASI SESI & ROLE
if (!isset($_SESSION['user_id']) || $_SESSION['role_nama'] !== 'sorter') {
    header('Location: ../../login.php?error=akses_ditolak');
    exit;
}

// 2. KONEKSI DATABASE
include '../../config/koneksi.php';

// 3. AMBIL DATA PENTING DARI SESI & DB
$user_id = $_SESSION['user_id'];
$gudang_id = 0;
$qKaryawan = mysqli_query($conn, "SELECT gudang_id FROM karyawan WHERE id = '$user_id' LIMIT 1");
if ($row = mysqli_fetch_assoc($qKaryawan)) {
    $gudang_id = $row['gudang_id'];
}
if (!$gudang_id) {
    die("Error: Tidak dapat menemukan gudang untuk user ini.");
}

// Ambil nama gudang
$namaGudang = 'Gudang';
$qGudang = mysqli_query($conn, "SELECT nama_gudang FROM gudang WHERE id = '$gudang_id' LIMIT 1");
if ($g = mysqli_fetch_assoc($qGudang)) {
    $namaGudang = $g['nama_gudang'];
}

$success = '';
$error = '';

// PROSES SIMPAN
if (isset($_POST['selesai'])) {
    $supir_id = $_POST['supir_id'] ?? null;
    $resiList = json_decode($_POST['resi_list'] ?? '[]', true);

    if (empty($supir_id)) {
        $error = "Supir belum dipilih!";
    } elseif (empty($resiList) || !is_array($resiList)) {
        $error = "Belum ada resi yang di-scan!";
    }

    if (!$error) {
        // --- TAMBAHAN: Ambil nama supir untuk keterangan ---
        $namaSupir = 'Supir';
        $qSupirNama = mysqli_query($conn, "SELECT nama FROM karyawan WHERE id = '$supir_id' LIMIT 1");
        if ($sn = mysqli_fetch_assoc($qSupirNama)) {
            $namaSupir = $sn['nama'];
        }
        // --------------------------------------------------

        $conn->begin_transaction();
        try {
            foreach ($resiList as $resiRaw) {
                $resi = trim($conn->real_escape_string($resiRaw));
                $q = $conn->query("SELECT id FROM paket WHERE no_resi = '$resi' LIMIT 1");

                if ($p = $q->fetch_assoc()) {
                    $paket_id = $p['id'];
                    
                    // 1. Catat di tabel scan_paket
                    $conn->query("INSERT INTO scan_paket (paket_id, jenis_scan, gudang_id, scan_by, supir_id) 
                                 VALUES ('$paket_id', 'keluar', '$gudang_id', '$user_id', '$supir_id')");
                    
                    // 2. Update status_paket (PERUBAHAN DI SINI)
                    // Status: Paket keluar [NamaGudang]
                    // Keterangan: [NamaSupir]
                    $statusBaru = "Paket keluar " . $namaGudang;
                    $supirBaru = "Supir " . $namaSupir;
                    $conn->query("INSERT INTO status_paket (paket_id, status, keterangan, updated_by) 
                                 VALUES ('$paket_id', '$statusBaru', '$supirBaru', '$user_id')");
                }
            }
            $conn->commit();
            $_SESSION['success_msg'] = "Berhasil scan keluar " . count($resiList) . " paket.";
        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error = "Gagal menyimpan data: " . $exception->getMessage();
        }
    }
    
    if (!$error) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
            <h4 class="text-xl font-semibold mb-3">Scan Paket Keluar Gudang</h4>

            <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?= $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow rounded-lg p-6">
                <!-- Pilih Supir -->
                <div class="flex flex-wrap items-end mb-4 gap-4">
                    <div class="flex-grow min-w-[250px]">
                        <label for="supir" class="block text-sm font-medium text-gray-700 mb-1">Pilih Supir dari Gudang Anda</label>
                        <select id="supir" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200">
                            <option value="">-- Pilih Supir --</option>
                            <?php
                            // 4. KOREKSI QUERY SUPIR
                            $qSupir = mysqli_query($conn, "
                                SELECT k.id, k.nama
                                FROM karyawan k
                                JOIN roles r ON k.role_id = r.id
                                WHERE r.nama_role = 'supir' AND k.gudang_id = '$gudang_id'
                                ORDER BY k.nama ASC
                            ");
                            while ($s = mysqli_fetch_assoc($qSupir)) {
                                echo "<option value='{$s['id']}'>".htmlspecialchars($s['nama'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                     <div class="flex-none">
                        <button type="button" id="btnPilih" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">Pilih</button>
                    </div>
                </div>

                <!-- Input Resi -->
                <div class="flex flex-wrap items-end mb-4 gap-4">
                     <div class="flex-grow">
                        <label for="resiInput" class="block text-sm font-medium text-gray-700 mb-1">Scan / Input No. Resi</label>
                        <input id="resiInput" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200" placeholder="Scan Resi di sini..." disabled autofocus>
                    </div>
                    <div class="flex-none">
                        <button type="button" id="btnScan" class="bg-gray-500 text-white px-4 py-2 rounded w-full sm:w-auto" disabled>Tambah</button>
                    </div>
                </div>

                <ul id="listResi" class="mb-4 border border-gray-200 rounded min-h-[100px] bg-gray-50 p-2"></ul>

                <form method="POST" onsubmit="return submitData()">
                    <input type="hidden" name="supir_id" id="supir_id_input">
                    <input type="hidden" name="resi_list" id="resi_list_input">
                    <div class="text-right">
                        <button type="submit" name="selesai" id="btnSelesai" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold" disabled>Simpan & Serahkan ke Supir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<audio id="beep"><source src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" type="audio/ogg"></audio>

<script>
<?php if(isset($_SESSION['success_msg'])): ?>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($_SESSION['success_msg']) ?>', timer: 3000, showConfirmButton: false });
<?php unset($_SESSION['success_msg']); endif; ?>
</script>

<script src="../../assets/javascript/scanKeluar.js"></script>
