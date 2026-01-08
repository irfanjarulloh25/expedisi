<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

include __DIR__ . '/../../config/koneksi.php';

// Ambil Data
$dataOngkir = mysqli_query($conn, "SELECT * FROM harga_pengiriman ORDER BY id DESC");

// Hapus Data Jika ada POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $id = (int)$_POST['hapus_id'];
    $query = mysqli_query($conn, "DELETE FROM harga_pengiriman WHERE id=$id");
    $hapusStatus = $query ? 'success' : 'error';
    $hapusMsg = $query ? 'Harga ongkir berhasil dihapus' : 'Gagal hapus: ' . mysqli_error($conn);
}
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Daftar Harga Ongkir</h1>

        <!-- Input Search -->
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Cari ongkir..." 
                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm overflow-x-auto">
    <table id="ongkirTable" class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b border-gray-300">
            <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">No</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Wilayah</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Kota</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Berat Min (kg)</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Berat Max (kg)</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Harga (Rp)</th>
                <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
    <?php $no = 1; while($o = mysqli_fetch_assoc($dataOngkir)) { ?>
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3"><?= $no++ ?></td>
            <td class="px-4 py-3">
                <?= ucwords(str_replace('_', ' ', htmlspecialchars($o['wilayah']))) ?>
            </td>
            <td class="px-4 py-3"><?= htmlspecialchars($o['kota']) ?></td>
            
            <td class="px-4 py-3">
                <?= (float)$o['berat_min'] ?> 
            </td>
            
            <td class="px-4 py-3">
                <?= ($o['berat_max'] !== null) ? (float)$o['berat_max'] : '>' ?>
            </td>
            
            <td class="px-4 py-3 font-semibold">
                Rp <?= number_format($o['harga'], 0, ',', '.') ?>
            </td>
            <td class="px-4 py-3 flex gap-2">
                <a href="ongkir.php?edit=<?= $o['id'] ?>"
                   class="bg-yellow-400 text-white px-3 py-1 rounded hover:bg-yellow-500 transition">Edit</a>
                <button type="button" onclick="confirmHapus(<?= $o['id'] ?>)"
                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Hapus</button>
            </td>
        </tr>
    <?php } ?>
</tbody>
    </table>

    <!-- Form POST Hapus -->
    <form id="hapusForm" method="POST" style="display:none;">
        <input type="hidden" name="hapus_id" id="hapus_id">
    </form>
</div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmHapus(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah anda yakin ingin menghapus ongkir ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('hapus_id').value = id;
            document.getElementById('hapusForm').submit();
        }
    })
}

// SweetAlert setelah hapus berhasil
<?php if(isset($hapusStatus)) { ?>
Swal.fire({
    icon: '<?= $hapusStatus ?>',
    title: '<?= $hapusStatus==="success"?"Berhasil":"Gagal" ?>',
    text: '<?= $hapusMsg ?>'
}).then(()=>{ window.location='listOngkir.php'; });
<?php } ?>

// Search realtime
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('ongkirTable');
const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

searchInput.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    for (let i=0;i<rows.length;i++){
        const cells = rows[i].getElementsByTagName('td');
        let match=false;
        for (let j=1;j<=5;j++){
            if(cells[j] && cells[j].textContent.toLowerCase().indexOf(filter)>-1){
                match=true; break;
            }
        }
        rows[i].style.display = match?'':'none';
    }
});
</script>
