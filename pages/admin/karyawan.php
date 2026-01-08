<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// ======================
// CEK EDIT MODE
// ======================
$edit = false;
$karyawanData = [
    'id' => '',
    'nik' => '',
    'nama' => '',
    'email' => '',
    'alamat' => '',
    'role_id' => '',
    'gudang_id' => '',
    'foto' => ''
];

if (isset($_GET['edit'])) {
    $edit = true;
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM karyawan WHERE id = $id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $karyawanData = mysqli_fetch_assoc($res);
    }
}

/* ======================
   SIMPAN KARYAWAN
====================== */
if (isset($_POST['simpan'])) {
    $id        = $_POST['id'] ?? null;
    $nik       = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
    $role_id   = $_POST['role_id'];
    $gudang_id = $_POST['gudang_id'];

    // --- VALIDASI NIK DUPLIKAT ---
    if ($edit) {
        // Jika edit, cari NIK yang sama tapi bukan milik ID ini
        $cekNik = mysqli_query($conn, "SELECT id FROM karyawan WHERE nik = '$nik' AND id != '$id'");
    } else {
        // Jika tambah baru, cari NIK yang sama
        $cekNik = mysqli_query($conn, "SELECT id FROM karyawan WHERE nik = '$nik'");
    }

    if (mysqli_num_rows($cekNik) > 0) {
        // Jika NIK ditemukan, set flash error dan stop proses
        $_SESSION['flash'] = [
            'type' => 'error',
            'title' => 'NIK Sudah Terdaftar',
            'text'  => 'Gagal! NIK ' . $nik . ' sudah digunakan oleh karyawan lain.'
        ];
        header("Location: " . $_SERVER['PHP_SELF'] . ($edit ? "?edit=$id" : ""));
        exit;
    }
    // --- AKHIR VALIDASI NIK ---

    $password = null;
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $foto = $karyawanData['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $folder = __DIR__ . '/../uploads/karyawan/';
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        if ($edit && $karyawanData['foto']) {
            $oldFile = $folder . $karyawanData['foto'];
            if (file_exists($oldFile)) unlink($oldFile);
        }

        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = time() . '_' . rand(100,999) . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $foto);
    }

    if ($edit) {
        $sql = "UPDATE karyawan SET 
            nik='$nik', nama='$nama', email='$email', alamat='$alamat',
            role_id='$role_id', gudang_id='$gudang_id', foto='$foto'";
        if ($password) $sql .= ", password='$password'";
        $sql .= " WHERE id='$id'";
    } else {
        $sql = "INSERT INTO karyawan (nik,nama,email,password,foto,alamat,role_id,gudang_id)
                VALUES ('$nik','$nama','$email','$password','$foto','$alamat','$role_id','$gudang_id')";
    }

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'title' => $edit ? 'Update Berhasil' : 'Simpan Berhasil',
            'text'  => 'Data karyawan berhasil disimpan.'
        ];
        header("Location: karyawan.php");
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'title' => 'Gagal',
            'text'  => 'Database error: ' . mysqli_error($conn)
        ];
        header("Location: " . $_SERVER['PHP_SELF'] . ($edit ? "?edit=$id" : ""));
    }
    exit;
}

/* ======================
   DATA ROLE & GUDANG
====================== */
$roles  = mysqli_query($conn, "SELECT * FROM roles");
$gudang = mysqli_query($conn, "SELECT * FROM gudang");
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800"><?= $edit ? 'Edit Karyawan' : 'Input Karyawan' ?></h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $karyawanData['id'] ?>">

                <div class="grid grid-cols-3 gap-6">
                    <!-- KIRI -->
                    <div class="col-span-2">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">NIK</label>
                            <input type="text" name="nik" class="border border-gray-300 rounded-lg p-2 w-full" required
                                value="<?= htmlspecialchars($karyawanData['nik']) ?>">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Nama</label>
                            <input type="text" name="nama" class="border border-gray-300 rounded-lg p-2 w-full" required
                                value="<?= htmlspecialchars($karyawanData['nama']) ?>">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" class="border border-gray-300 rounded-lg p-2 w-full"
                                value="<?= htmlspecialchars($karyawanData['email']) ?>">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Password</label>

                            <?php if ($edit): ?>
                                <button type="button" id="btnEditPassword"
                                    class="bg-gray-300 px-3 py-1 rounded">Edit Password</button>
                                <input type="password" name="password" id="password"
                                    class="border border-gray-300 rounded-lg p-2 w-full hidden mt-2"
                                    placeholder="Kosongkan jika tidak ingin diubah">
                            <?php else: ?>
                                <input type="password" name="password" id="password"
                                    class="border border-gray-300 rounded-lg p-2 w-full"
                                    placeholder="Masukkan password">
                                <p class="text-xs text-gray-500 mt-1">*Password wajib diisi</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Alamat</label>
                            <textarea name="alamat" class="border border-gray-300 rounded-lg p-2 w-full"><?= htmlspecialchars($karyawanData['alamat']) ?></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Role</label>
                                <select name="role_id" id="role_id" class="border border-gray-300 rounded-lg p-2 w-full" required>
                                    <option value="">-- Pilih Role --</option>
                                    <?php while ($r = mysqli_fetch_assoc($roles)) { ?>
                                        <option value="<?= $r['id'] ?>" <?= $karyawanData['role_id']==$r['id'] ? 'selected':'' ?>>
                                            <?= ucfirst($r['nama_role']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Gudang</label>
                                <select name="gudang_id" class="border border-gray-300 rounded-lg p-2 w-full" required>
                                    <option value="">-- Pilih Gudang --</option>
                                    <?php while ($g = mysqli_fetch_assoc($gudang)) { ?>
                                        <option value="<?= $g['id'] ?>" <?= $karyawanData['gudang_id']==$g['id'] ? 'selected':'' ?>>
                                            <?= $g['nama_gudang'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN -->
                    <div class="col-span-1 text-center">
                        <label class="block text-sm font-medium mb-2">Foto Karyawan</label>
                        <img id="previewFoto"
                            src="<?= $karyawanData['foto'] ? '../uploads/karyawan/'.$karyawanData['foto'] : 'https://via.placeholder.com/200' ?>"
                            class="rounded-lg mb-4 w-48 h-48 object-cover">
                        <input type="file" name="foto" class="w-full" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <button type="submit" name="simpan"
                        class="bg-blue-500 text-white px-6 py-2 rounded-xl hover:bg-blue-600 transition">
                        <?= $edit ? 'Update Karyawan' : 'Simpan Karyawan' ?>
                    </button>

                    <?php if ($edit): ?>
                        <a href="karyawan.php"
                           class="bg-gray-300 text-gray-800 px-6 py-2 rounded-xl hover:bg-gray-400 transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="../../assets/javascript/karyawan.js"></script>

<?php if(isset($_SESSION['flash'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const flash = <?= json_encode($_SESSION['flash']) ?>;
Swal.fire({
    icon: flash.type,
    title: flash.title,
    text: flash.text
});
</script>
<?php unset($_SESSION['flash']); endif; ?>
