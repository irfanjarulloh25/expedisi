<?php
session_start();
include '../../config/koneksi.php';

/**
 * ===============================
 * SIMULASI USER LOGIN
 * ===============================
 */
if (!isset($_SESSION['user_id'])) {
    die("<div class='alert alert-danger'>Silakan login terlebih dahulu.</div>");
}
$user = $_SESSION['user_id'];

// Validasi user
$qUser = mysqli_query($conn, "
    SELECT k.id, k.gudang_id, g.nama_gudang
    FROM karyawan k
    LEFT JOIN gudang g ON k.gudang_id = g.id
    WHERE k.id = '$user'
");
$userData = mysqli_fetch_assoc($qUser);
if (!$userData) die("<div class='alert alert-danger'>User tidak valid</div>");
if (empty($userData['gudang_id'])) die("<div class='alert alert-danger'>Karyawan belum memiliki gudang</div>");

$gudang_id   = $userData['gudang_id'];
$nama_gudang = $userData['nama_gudang'] ?? 'Gudang Tidak Diketahui';

// Ambil semua kota dari database untuk autocomplete
$kotaDB = [];
$qKota = mysqli_query($conn, "SELECT kota FROM harga_pengiriman ORDER BY kota ASC");
while ($row = mysqli_fetch_assoc($qKota)) {
    $kotaDB[] = $row['kota'];
}

// Generate No Resi
function generateResi12($conn) {
    do {
        $resi = mt_rand(100000000000, 999999999999);
        $cek  = mysqli_query($conn, "SELECT id FROM paket WHERE no_resi='$resi'");
    } while (mysqli_num_rows($cek) > 0);
    return $resi;
}

// Generate No Resi hanya sekali saat load
$no_resi = generateResi12($conn);

// ===============================
// PROSES SIMPAN PAKET (AJAX)
// ===============================
if (isset($_POST['ajax'])) {
    mysqli_begin_transaction($conn);
    try {
        $no_resi     = $_POST['no_resi'] ?? generateResi12($conn);
        $nama_paket  = $_POST['nama_paket'];
        $qty         = (int) $_POST['qty'];
        $berat       = (float) $_POST['berat'];
        $cod         = $_POST['cod'];
        $harga_cod   = ($cod === 'ya') ? (float) ($_POST['harga_cod'] ?? 0) : 0;

        $nama_pengirim   = $_POST['nama_pengirim'];
        $telp_pengirim   = $_POST['telp_pengirim'];
        $alamat_pengirim = $_POST['alamat_pengirim'];

        $nama_penerima      = $_POST['nama_penerima'];
        $telp_penerima      = $_POST['telp_penerima'];
        $alamat_penerima    = $_POST['alamat_penerima'];
        $provinsi_penerima  = $_POST['provinsi_penerima'];
        $kota_penerima      = $_POST['kota_penerima'];
        $kecamatan_penerima = $_POST['kecamatan_penerima'];
        $kelurahan_penerima = $_POST['kelurahan_penerima'];

        // Validasi kota
        if (!in_array($kota_penerima, $GLOBALS['kotaDB'])) {
            throw new Exception("Kota penerima tidak valid atau tidak tersedia ongkir.");
        }

        // Hitung ongkir
        $harga_ongkir = 0;
        $qHarga = mysqli_query($conn, "
            SELECT harga 
            FROM harga_pengiriman
            WHERE kota = '$kota_penerima'
              AND $berat BETWEEN berat_min AND berat_max
            LIMIT 1
        ");
        if ($row = mysqli_fetch_assoc($qHarga)) $harga_ongkir = $row['harga'];

        // Upload foto
        $foto = null;
        if (!empty($_POST['foto_data'])) {
            $folder = __DIR__ . '/../uploads/paket/';
            if (!is_dir($folder)) mkdir($folder, 0777, true);
            $foto = 'paket_' . time() . '.png';
            $data = str_replace('data:image/png;base64,', '', $_POST['foto_data']);
            file_put_contents($folder . $foto, base64_decode($data));
        }

        // Insert paket
        $sqlPaket = "
            INSERT INTO paket
            (no_resi, nama_paket, qty,
             nama_pengirim, telp_pengirim, alamat_pengirim,
             nama_penerima, telp_penerima, alamat_penerima,
             provinsi_penerima, kota_penerima, kecamatan_penerima, kelurahan_penerima,
             berat, harga_ongkir, cod, harga_cod, foto_paket, created_by)
            VALUES
            ('$no_resi','$nama_paket','$qty',
             '$nama_pengirim','$telp_pengirim','$alamat_pengirim',
             '$nama_penerima','$telp_penerima','$alamat_penerima',
             '$provinsi_penerima','$kota_penerima','$kecamatan_penerima','$kelurahan_penerima',
             '$berat','$harga_ongkir','$cod','$harga_cod','$foto','$user')
        ";
        if (!mysqli_query($conn, $sqlPaket)) throw new Exception(mysqli_error($conn));

        $paket_id = mysqli_insert_id($conn);
        if ($paket_id <= 0) throw new Exception("Paket ID tidak valid");

        // Insert scan paket (MASUK GUDANG)
$sqlScan = "
    INSERT INTO scan_paket
    (paket_id, jenis_scan, gudang_id, scan_by, supir_id, kurir_id, scan_time)
    VALUES
    ('$paket_id', 'masuk', '$gudang_id', '$user', NULL, NULL, NOW())
";
if (!mysqli_query($conn, $sqlScan)) {
    throw new Exception('Gagal menyimpan scan paket: ' . mysqli_error($conn));
}


        // Insert status awal
        $sqlStatus = "
            INSERT INTO status_paket
            (paket_id, status, keterangan, foto_penerima, updated_by, updated_at)
            VALUES
            ('$paket_id', 'Sudah diterima di gudang', '$nama_gudang', NULL, '$user', NOW())
        ";
        if (!mysqli_query($conn, $sqlStatus)) throw new Exception(mysqli_error($conn));

        mysqli_commit($conn);

        echo json_encode([
            'success' => true,
            'no_resi' => $no_resi,
            'harga_ongkir' => $harga_ongkir,
            'nama_gudang' => $nama_gudang
        ]);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Input Paket</h1>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form id="formPaket" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- KIRI: Penerima -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-700">Penerima</h2>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">No Telp Penerima</label>
                            <input type="text" name="telp_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Alamat Penerima</label>
                            <textarea name="alamat_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Provinsi</label>
                            <input type="text" name="provinsi_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div class="relative">
                            <label class="block mb-1 text-sm font-medium text-gray-600">Kota</label>
                            <input type="text" name="kota_penerima" id="kota_penerima" placeholder="Ketik nama kota" autocomplete="off" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <div id="kotaList" class="absolute z-50 w-full bg-white border mt-1 rounded shadow"></div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Kecamatan</label>
                            <input type="text" name="kecamatan_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Kelurahan</label>
                            <input type="text" name="kelurahan_penerima" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <!-- TENGAH: Paket -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-700">Paket</h2>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">No Resi</label>
                            <input type="text" name="no_resi" id="no_resi" readonly value="<?= htmlspecialchars($no_resi) ?>" class="w-full border rounded px-3 py-2 bg-gray-100">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block mb-1 text-sm font-medium text-gray-600">Nama Paket</label>
                                <input type="text" name="nama_paket" id="nama_paket" placeholder="Nama Paket" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-600">Qty</label>
                                <input type="number" name="qty" id="qty" min="1" value="1" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Berat Paket (kg)</label>
                            <input type="number" step="0.01" name="berat" id="berat" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Harga Ongkir (Rp)</label>
                            <input type="text" name="harga_ongkir" id="harga_ongkir" readonly class="w-full border rounded px-3 py-2 bg-gray-100">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Jenis Paket</label>
                            <select name="cod" id="cod" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="toggleCOD()">
                                <option value="tidak">Non COD</option>
                                <option value="ya">COD</option>
                            </select>
                        </div>

                        <div id="hargaCodBox" class="hidden">
                            <label class="block mb-1 text-sm font-medium text-gray-600">Harga COD</label>
                            <input name="harga_cod" type="number" placeholder="Nominal COD" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Foto Paket (Webcam)</label>
                            <video id="video" width="100%" height="200" autoplay muted playsinline class="hidden mb-2 border rounded"></video>
                            <img id="capturedImg" src="" alt="Foto Paket" class="hidden w-full cursor-pointer mb-2 border rounded">
                            <canvas id="canvas" width="320" height="240" class="hidden"></canvas>
                            <input type="hidden" name="foto_data" id="foto_data">
                            <div class="flex gap-2 mt-2">
                                <button type="button" id="startCamera" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Ambil Foto</button>
                                <button type="button" id="snap" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 hidden">Capture Foto</button>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Pengirim -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-700">Pengirim</h2>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Nama Pengirim</label>
                            <input type="text" name="nama_pengirim" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">No Telp Pengirim</label>
                            <input type="text" name="telp_pengirim" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-600">Alamat Pengirim</label>
                            <textarea name="alamat_pengirim" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>

                        <button type="button" id="btnSimpan" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 mt-4">Simpan Paket</button>
                    </div>

                </div>

            </form>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<div id="modalResi" class="fixed inset-0 z-[100] hidden bg-black bg-opacity-70 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm overflow-hidden">
        <div id="printableArea" class="p-4 bg-white border-b-2 border-dashed border-gray-200">
            <div class="flex justify-between items-center mb-2">
                <h2 class="font-black text-xl italic text-blue-700">Express</h2>
                <span class="text-[10px] font-mono" id="res_gudang"></span>
            </div>
            
            <div class="flex flex-col items-center py-2 bg-gray-50 mb-3">
                <svg id="barcodeCanvas"></svg>
                <p class="font-bold text-lg tracking-widest" id="res_no_resi"></p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] mb-3">
                <div class="border-r pr-2">
                    <p class="font-bold text-gray-500 uppercase text-[9px]">Penerima:</p>
                    <p class="font-bold" id="res_penerima"></p>
                    <p id="res_alamat_penerima" class="leading-tight"></p>
                </div>
                <div class="pl-1">
                    <p class="font-bold text-gray-500 uppercase text-[9px]">Pengirim:</p>
                    <p id="res_pengirim"></p>
                </div>
            </div>

            <div class="flex justify-between text-[11px] border-t pt-2">
                <span>Berat: <b id="res_berat"></b> kg</span>
                <span>Qty: <b id="res_qty"></b></span>
                <span>Tipe: <b id="res_tipe"></b></span>
            </div>

            <div id="res_cod_info" class="mt-2 p-2 bg-yellow-100 rounded text-center hidden">
                <p class="text-[10px] font-bold">TAGIHAN COD</p>
                <p class="text-lg font-black" id="res_harga_cod"></p>
            </div>
        </div>

        <div class="p-3 bg-gray-100 flex gap-2">
            <button onclick="printResi()" class="flex-1 bg-green-600 text-white py-2 rounded font-bold hover:bg-green-700">Cetak</button>
            <button onclick="closeAndReload()" class="flex-1 bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Selesai</button>
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div id="modalImg" class="hidden fixed top-0 left-0 w-full h-full bg-black/80 flex justify-center items-center">
    <img id="modalContent" src="" class="max-w-[90%] max-h-[90%]">
</div>

<script src="../../assets/javascript/paketInput.js"></script>