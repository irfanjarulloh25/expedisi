<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$id        = null;
$edit      = false;
$wilayah   = '';
$kota      = '';
$berat_min = '';
$berat_max = '';
$harga     = '';
$swal      = null;

// ======================
// MODE EDIT (Ambil Data)
// ======================
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = true;

    $stmt = $conn->prepare("SELECT * FROM harga_pengiriman WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $wilayah   = $data['wilayah'];
        $kota      = $data['kota'];
        $berat_min = $data['berat_min']; // Akan berisi desimal, misal 1.50
        $berat_max = $data['berat_max'];
        $harga     = $data['harga'];
    } else {
        $swal = [
            'icon' => 'error',
            'title' => 'Oops...',
            'text' => 'Data tidak ditemukan',
            'redirect' => 'ongkir.php'
        ];
    }
}

// ======================
// PROSES SIMPAN / UPDATE
// ======================
if (isset($_POST['simpan'])) {
    // Sanitasi input teks
    $wilayah = mysqli_real_escape_string($conn, $_POST['wilayah']);
    $kota    = mysqli_real_escape_string($conn, $_POST['kota']);
    
    // Gunakan floatval agar mendukung desimal (1.5)
    $berat_min = floatval($_POST['berat_min']);
    $berat_max = ($_POST['berat_max'] !== '') ? floatval($_POST['berat_max']) : null;
    $harga     = floatval($_POST['harga']);

    if ($edit) {
        // Mode Update
        $sql = "UPDATE harga_pengiriman SET 
                wilayah = ?, kota = ?, berat_min = ?, berat_max = ?, harga = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdddi", $wilayah, $kota, $berat_min, $berat_max, $harga, $id);
        $msg = 'diupdate';
    } else {
        // Mode Simpan Baru
        $sql = "INSERT INTO harga_pengiriman (wilayah, kota, berat_min, berat_max, harga) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddd", $wilayah, $kota, $berat_min, $berat_max, $harga);
        $msg = 'disimpan';
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => "Harga ongkir berhasil $msg",
            'redirect' => 'ongkir.php'
        ];
        header("Location: ongkir.php");
        exit;
    } else {
        $swal = [
            'icon' => 'error',
            'title' => 'Gagal',
            'text' => "Kesalahan Database: " . $conn->error
        ];
    }
}
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">
            <?= $edit ? 'Edit Harga Ongkir' : 'Input Harga Ongkir' ?>
        </h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Wilayah</label>
                    <select name="wilayah" class="border border-gray-300 rounded-lg p-2 w-full" required>
                        <option value="">-- Pilih Wilayah --</option>
                        <option value="jabodetabek" <?= $wilayah == 'jabodetabek' ? 'selected' : '' ?>>Jabodetabek</option>
                        <option value="luar_jabodetabek" <?= $wilayah == 'luar_jabodetabek' ? 'selected' : '' ?>>Luar Jabodetabek</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Kota</label>
                    <input type="text" name="kota" placeholder="Contoh: Jakarta Selatan" 
                           class="border border-gray-300 rounded-lg p-2 w-full" 
                           value="<?= htmlspecialchars($kota) ?>" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Berat Minimum (kg)</label>
                        <input type="number" name="berat_min" step="0.01" 
                               class="border border-gray-300 rounded-lg p-2 w-full" 
                               value="<?= $berat_min ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Berat Maksimum (kg)</label>
                        <input type="number" name="berat_max" step="0.01"
                               class="border border-gray-300 rounded-lg p-2 w-full" 
                               placeholder="Kosongkan jika tidak ada batas" 
                               value="<?= $berat_max ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" step="1"
                           class="border border-gray-300 rounded-lg p-2 w-full" 
                           value="<?= $harga ?>" required>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" name="simpan" 
                            class="bg-blue-600 text-white px-8 py-2 rounded-xl hover:bg-blue-700 transition font-semibold">
                        <?= $edit ? 'Update Data' : 'Simpan Data' ?>
                    </button>

                    <a href="ongkir.php" class="bg-gray-200 text-gray-700 px-8 py-2 rounded-xl hover:bg-gray-300 transition font-semibold">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php 
    // Ambil dari session flash jika ada
    $flash = $_SESSION['flash'] ?? $swal;
    if ($flash): 
    ?>
    Swal.fire({
        icon: '<?= $flash['icon'] ?>',
        title: '<?= $flash['title'] ?>',
        text: '<?= $flash['text'] ?>'
    }).then((result) => {
        <?php if(isset($flash['redirect'])): ?>
            window.location = '<?= $flash['redirect'] ?>';
        <?php endif; ?>
    });
    <?php unset($_SESSION['flash']); endif; ?>
});
</script>