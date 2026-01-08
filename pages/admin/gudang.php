<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include __DIR__ . '/../../config/koneksi.php';

$edit = false;
$gudangData = [
    'id' => '',
    'nama_gudang' => '',
    'alamat_lengkap' => '',
    'kecamatan' => '',
    'kelurahan' => '',
    'kota' => '',
    'provinsi' => ''
];

// ======================
// CEK EDIT MODE DARI GET
// ======================
if (isset($_GET['edit'])) {
    $edit = true;
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM gudang WHERE id = $id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $gudangData = mysqli_fetch_assoc($res);
    }
}

// ======================
// SIMPAN / UPDATE
// ======================
$swal = ''; // variable untuk simpan script Swal
if (isset($_POST['simpan'])) {
    $id              = $_POST['id'] ?? null;
    $nama_gudang     = mysqli_real_escape_string($conn, $_POST['nama_gudang']);
    $alamat_lengkap  = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $kecamatan       = mysqli_real_escape_string($conn, $_POST['kecamatan']);
    $kelurahan       = mysqli_real_escape_string($conn, $_POST['kelurahan']);
    $kota            = mysqli_real_escape_string($conn, $_POST['kota']);
    $provinsi        = mysqli_real_escape_string($conn, $_POST['provinsi']);

    if ($id) {
        // UPDATE
        $query = mysqli_query($conn, "
            UPDATE gudang SET
                nama_gudang='$nama_gudang',
                alamat_lengkap='$alamat_lengkap',
                kecamatan='$kecamatan',
                kelurahan='$kelurahan',
                kota='$kota',
                provinsi='$provinsi'
            WHERE id='$id'
        ");

        if ($query) {
            $swal = "Swal.fire({icon:'success', title:'Sukses', text:'Data gudang berhasil diperbarui'})";
        } else {
            $swal = "Swal.fire({icon:'error', title:'Gagal', text:'Gagal update data: ".mysqli_error($conn)."'} )";
        }

        // Setelah update, reset form ke mode input baru
        $edit = false;
        $gudangData = [
            'id' => '',
            'nama_gudang' => '',
            'alamat_lengkap' => '',
            'kecamatan' => '',
            'kelurahan' => '',
            'kota' => '',
            'provinsi' => ''
        ];

    } else {
        // INSERT
        $query = mysqli_query($conn, "
            INSERT INTO gudang (nama_gudang, alamat_lengkap, kecamatan, kelurahan, kota, provinsi)
            VALUES ('$nama_gudang','$alamat_lengkap','$kecamatan','$kelurahan','$kota','$provinsi')
        ");

        if ($query) {
            $swal = "Swal.fire({icon:'success', title:'Sukses', text:'Data gudang berhasil disimpan'})";
        } else {
            $swal = "Swal.fire({icon:'error', title:'Gagal', text:'Gagal menyimpan data: ".mysqli_error($conn)."'} )";
        }
    }
}
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800"><?= $edit ? 'Edit Gudang' : 'Input Gudang' ?></h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $gudangData['id'] ?>">

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Nama Gudang</label>
                    <input type="text" name="nama_gudang"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        value="<?= htmlspecialchars($gudangData['nama_gudang']) ?>" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Alamat Lengkap</label>
                    <textarea name="alamat_lengkap"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required><?= htmlspecialchars($gudangData['alamat_lengkap']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Kecamatan</label>
                        <input type="text" name="kecamatan"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            value="<?= htmlspecialchars($gudangData['kecamatan']) ?>">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Kelurahan</label>
                        <input type="text" name="kelurahan"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            value="<?= htmlspecialchars($gudangData['kelurahan']) ?>">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Kota</label>
                        <input type="text" name="kota"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            value="<?= htmlspecialchars($gudangData['kota']) ?>">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Provinsi</label>
                        <input type="text" name="provinsi"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            value="<?= htmlspecialchars($gudangData['provinsi']) ?>">
                    </div>
                </div>

                <div class="mt-4 flex gap-4">
                    <button type="submit" name="simpan"
                        class="bg-blue-500 text-white px-6 py-2 rounded-xl hover:bg-blue-600 transition">
                        <?= $edit ? 'Update Gudang' : 'Simpan Gudang' ?>
                    </button>

                    <?php if ($edit): ?>
                        <a href="gudang.php"
                            class="bg-gray-300 text-gray-800 px-6 py-2 rounded-xl hover:bg-gray-400 transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</main>

<?php if($swal): ?>
<script>
    <?= $swal ?>;
</script>
<?php endif; ?>
