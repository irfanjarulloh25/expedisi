<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

/* ===============================
   FILTER TANGGAL
================================ */
$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-d');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

/* ===============================
   PROSES HAPUS
================================ */
if (isset($_POST['hapus']) && !empty($_POST['paket_id'])) {

    foreach ($_POST['paket_id'] as $id) {

        // Ambil foto paket
        $qFoto = mysqli_query($conn, "
            SELECT foto_paket 
            FROM paket 
            WHERE id='$id'
        ");
        $foto = mysqli_fetch_assoc($qFoto);

        if ($foto && !empty($foto['foto_paket'])) {
            $path = __DIR__ . "/../uploads/paket/" . $foto['foto_paket'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // HAPUS DATA TERKAIT (CHILD)
        mysqli_query($conn, "DELETE FROM scan_paket WHERE paket_id='$id'");
        mysqli_query($conn, "DELETE FROM status_paket WHERE paket_id='$id'");

        // HAPUS DATA PAKET
        mysqli_query($conn, "DELETE FROM paket WHERE id='$id'");
    }

    header("Location: listPaket.php?tgl_awal=$tgl_awal&tgl_akhir=$tgl_akhir");
    exit;
}

/* ===============================
   AMBIL DATA PAKET
================================ */
$qPaket = mysqli_query($conn, "
    SELECT *
    FROM paket
    WHERE DATE(created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    ORDER BY created_at DESC
");
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
<div class="p-6">

<h1 class="text-2xl font-bold mb-4">📦 Daftar Paket</h1>

<!-- FILTER TANGGAL -->
<form method="GET" class="flex gap-3 mb-4">
    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="border px-3 py-2 rounded">
    <div> _ </div>
    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="border px-3 py-2 rounded">

    <button class="bg-blue-600 text-white px-4 py-2 rounded">
        Tampilkan
    </button>

    <?php if ($tgl_awal !== date('Y-m-d') || $tgl_akhir !== date('Y-m-d')): ?>
        <a href="listPaket.php" class="bg-gray-500 text-white px-4 py-2 rounded">
            Hari Ini
        </a>
    <?php endif; ?>
</form>

<!-- FORM HAPUS -->
<form method="POST" id="formHapus">

<button name="hapus" class="bg-red-600 text-white px-4 py-2 rounded mb-3">
    🗑 Hapus Terpilih
</button>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="min-w-full text-sm">
<thead class="bg-gray-200">
<tr>
    <th class="px-3 py-2 text-center">
        <input type="checkbox" id="checkAll">
    </th>
    <th class="px-3 py-2">No Resi</th>
    <th class="px-3 py-2">Nama Paket</th>
    <th class="px-3 py-2">Pengirim</th>
    <th class="px-3 py-2">Penerima</th>
    <th class="px-3 py-2">Tanggal</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($qPaket) > 0): ?>
<?php while ($p = mysqli_fetch_assoc($qPaket)): ?>
<tr class="border-b hover:bg-gray-50">
    <td class="text-center">
        <input type="checkbox" name="paket_id[]" value="<?= $p['id'] ?>" class="cek">
    </td>
    <td class="font-semibold"><?= htmlspecialchars($p['no_resi']) ?></td>
    <td><?= htmlspecialchars($p['nama_paket']) ?></td>
    <td><?= htmlspecialchars($p['nama_pengirim']) ?></td>
    <td><?= htmlspecialchars($p['nama_penerima']) ?></td>
    <td><?= date('d-m-Y H:i', strtotime($p['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="6" class="text-center py-4 text-gray-500">
    Tidak ada data paket
</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</form>
</div>
</main>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// CHECK ALL
document.getElementById('checkAll').addEventListener('click', function () {
    document.querySelectorAll('.cek').forEach(c => c.checked = this.checked);
});

// SWEETALERT KONFIRMASI HAPUS
document.getElementById('formHapus').addEventListener('submit', function (e) {
    e.preventDefault();

    const checked = document.querySelectorAll('.cek:checked');

    if (checked.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak ada data',
            text: 'Pilih paket terlebih dahulu'
        });
        return;
    }

    Swal.fire({
        title: 'Yakin hapus paket?',
        text: 'Semua data paket, scan, status, dan foto akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            e.target.submit();
        }
    });
});
</script>
