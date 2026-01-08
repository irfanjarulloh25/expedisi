<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include __DIR__ . '/../../config/koneksi.php';

// HAPUS DATA via GET hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Set semua karyawan yang pakai gudang ini jadi NULL
    mysqli_query($conn, "UPDATE karyawan SET gudang_id=NULL WHERE gudang_id=$id");

    // Hapus gudang
    $query = mysqli_query($conn, "DELETE FROM gudang WHERE id=$id");

    if ($query) {
        $swal = "Swal.fire({icon:'success', title:'Sukses', text:'Gudang berhasil dihapus'})";
    } else {
        $swal = "Swal.fire({icon:'error', title:'Gagal', text:'Gagal hapus gudang: ".mysqli_error($conn)."'} )";
    }
}
$dataGudang = mysqli_query($conn, "SELECT * FROM gudang ORDER BY id DESC");
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Daftar Gudang</h1>

        <!-- Input Search Realtime -->
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Cari gudang..." 
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div class="bg-white rounded-2xl shadow p-6 border border-gray-200 overflow-x-auto">
            <table id="gudangTable" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Nama Gudang</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Alamat Lengkap</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Kota</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Provinsi</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $no = 1; while($g = mysqli_fetch_assoc($dataGudang)) { ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $no++ ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($g['nama_gudang']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($g['alamat_lengkap']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($g['kota']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($g['provinsi']) ?></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="gudang.php?edit=<?= $g['id'] ?>" 
                                   class="bg-yellow-400 text-white px-3 py-1 rounded hover:bg-yellow-500 transition">Edit</a>
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition btn-hapus" 
                                        data-id="<?= $g['id'] ?>">Hapus</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('gudangTable');
const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

// Realtime search
searchInput.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 1; j <= 4; j++) {
            if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
});

// SweetAlert Hapus
document.querySelectorAll('.btn-hapus').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Hapus gudang ini?',
            text: "Data karyawan yang pakai gudang ini akan diubah menjadi NULL.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'listGudang.php?hapus=' + id;
            }
        })
    });
});
</script>

<?php if(isset($swal) && $swal): ?>
<script>
    <?= $swal ?>;
</script>
<?php endif; ?>
