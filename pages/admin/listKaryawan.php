<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// 1. Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// ==========================================
// 2. LOGIKA HAPUS (Dijalankan jika ada POST)
// ==========================================
if (isset($_POST['hapus_id'])) {
    $id_hapus = (int)$_POST['hapus_id'];
    
    // A. Ambil nama foto untuk dihapus dari folder uploads
    $query_foto = mysqli_query($conn, "SELECT foto FROM karyawan WHERE id = $id_hapus");
    $data_foto = mysqli_fetch_assoc($query_foto);
    
    if (!empty($data_foto['foto'])) {
        $path_foto = __DIR__ . '/../uploads/karyawan/' . $data_foto['foto'];
        if (file_exists($path_foto)) {
            unlink($path_foto); // Hapus file fisik
        }
    }

    // B. Hapus data dari database
    $delete = mysqli_query($conn, "DELETE FROM karyawan WHERE id = $id_hapus");

    if ($delete) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'title' => 'Berhasil',
            'text'  => 'Data karyawan berhasil dihapus.'
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'title' => 'Gagal',
            'text'  => 'Gagal menghapus data: ' . mysqli_error($conn)
        ];
    }
    header("Location: karyawan.php");
    exit;
}

// ==========================================
// 3. AMBIL DATA KARYAWAN
// ==========================================
$karyawan = mysqli_query($conn, "
    SELECT k.*, r.nama_role, g.nama_gudang
    FROM karyawan k
    LEFT JOIN roles r ON k.role_id = r.id
    LEFT JOIN gudang g ON k.gudang_id = g.id
    ORDER BY k.id DESC
");
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Karyawan</h1>
            <a href="input_karyawan.php" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                + Tambah Karyawan
            </a>
        </div>

        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Cari berdasarkan NIK, Nama, Email, atau Role..."
                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm overflow-x-auto border border-gray-200">
            <table id="karyawanTable" class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">No</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 uppercase">Foto</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">NIK</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">Role</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase">Gudang</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $no=1; while ($k = mysqli_fetch_assoc($karyawan)) { ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4"><?= $no++ ?></td>
                        <td class="px-4 py-4 text-center">
                            <img src="<?= $k['foto'] ? '../uploads/karyawan/'.$k['foto'] : 'https://via.placeholder.com/50' ?>"
                                class="w-10 h-10 object-cover rounded-full border border-gray-200 mx-auto">
                        </td>
                        <td class="px-4 py-4 font-medium text-gray-700"><?= htmlspecialchars($k['nik']) ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($k['nama']) ?></td>
                        <td class="px-4 py-4 text-gray-500"><?= htmlspecialchars($k['email']) ?></td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs uppercase">
                                <?= htmlspecialchars($k['nama_role']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($k['nama_gudang'] ?? '-') ?></td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="karyawan.php?edit=<?= $k['id'] ?>"
                                   class="bg-yellow-400 text-white px-3 py-1.5 rounded-lg hover:bg-yellow-500 transition text-xs">
                                   Edit
                                </a>
                                <button type="button" onclick="confirmHapus(<?= $k['id'] ?>)"
                                   class="bg-red-500 text-white px-3 py-1.5 rounded-lg hover:bg-red-600 transition text-xs">
                                   Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <form id="hapusForm" method="POST" style="display:none;">
                <input type="hidden" name="hapus_id" id="hapus_id">
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. Fungsi Konfirmasi Hapus
function confirmHapus(id) {
    Swal.fire({
        title: 'Hapus Karyawan?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Masukkan ID ke form hidden dan submit
            document.getElementById('hapus_id').value = id;
            document.getElementById('hapusForm').submit();
        }
    });
}

// 2. Tampilkan Flash Message (Success/Error)
<?php if(isset($_SESSION['flash'])): ?>
    const flash = <?= json_encode($_SESSION['flash']) ?>;
    Swal.fire({
        icon: flash.type,
        title: flash.title,
        text: flash.text,
        timer: 3000,
        showConfirmButton: false
    });
<?php unset($_SESSION['flash']); endif; ?>

// 3. Script Search Realtime
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('karyawanTable');
const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

searchInput.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;

        // Cek kolom NIK (2), Nama (3), Email (4), Role (5), Gudang (6)
        for (let j = 2; j <= 6; j++) {
            if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
});
</script>