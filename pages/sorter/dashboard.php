<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

/* ===============================
   VALIDASI LOGIN & ROLE
================================ */
if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'sorter') {
    header('Location: ../../login.php');
    exit;
}

include '../../config/koneksi.php';
mysqli_query($conn, "SET time_zone = '+07:00'");

/* ===============================
   USER LOGIN & GUDANG
================================ */
$user_id = $_SESSION['user_id'];

$qUser = mysqli_query($conn, "
    SELECT k.id, k.gudang_id, g.nama_gudang
    FROM karyawan k
    JOIN gudang g ON g.id = k.gudang_id
    WHERE k.id = '$user_id'
");
$user = mysqli_fetch_assoc($qUser);

$gudang_id  = $user['gudang_id'];
$namaGudang = $user['nama_gudang'];

/* ===============================
   TANGGAL
================================ */
$hari_ini_full = date('Y-m-d');
$bulan_tahun   = date('Y-m-');
$jumlah_hari   = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

$tanggal_labels = [];
for ($i = 1; $i <= $jumlah_hari; $i++) {
    $tanggal_labels[] = $bulan_tahun . str_pad($i, 2, '0', STR_PAD_LEFT);
}

/* ===============================
   FUNCTION RESI
================================ */
function getResiList($conn, $query) {
    $data = [];
    $res = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row['no_resi'];
    }
    return $data;
}

/* ===============================
   STATUS TERAKHIR GLOBAL
================================ */
$lastScanQuery = "
    SELECT paket_id, MAX(scan_time) AS last_time
    FROM scan_paket
    GROUP BY paket_id
";

/* === MASUK === */
$resi_masuk = getResiList($conn, "
    SELECT p.no_resi
    FROM ($lastScanQuery) last
    JOIN scan_paket s
        ON s.paket_id = last.paket_id
       AND s.scan_time = last.last_time
    JOIN paket p ON p.id = s.paket_id
    WHERE s.jenis_scan = 'masuk'
      AND s.gudang_id = '$gudang_id'
      AND DATE(s.scan_time) = '$hari_ini_full'
");

/* === KELUAR === */
$resi_keluar = getResiList($conn, "
    SELECT p.no_resi
    FROM ($lastScanQuery) last
    JOIN scan_paket s
        ON s.paket_id = last.paket_id
       AND s.scan_time = last.last_time
    JOIN paket p ON p.id = s.paket_id
    WHERE s.jenis_scan = 'keluar'
      AND s.gudang_id = '$gudang_id'
      AND DATE(s.scan_time) = '$hari_ini_full'
");

/* === DIANTAR === */
$resi_diantar = getResiList($conn, "
    SELECT p.no_resi
    FROM ($lastScanQuery) last
    JOIN scan_paket s
        ON s.paket_id = last.paket_id
       AND s.scan_time = last.last_time
    JOIN paket p ON p.id = s.paket_id
    WHERE s.jenis_scan = 'diantar'
      AND s.gudang_id = '$gudang_id'
      AND DATE(s.scan_time) = '$hari_ini_full'
");

/* ===============================
   GRAFIK BULANAN (STATUS TERAKHIR)
================================ */
function getDailyData($conn, $tgl_list, $tgl_awal, $gudang_id, $jenis_scan) {
    $map = [];

    $query = "
        SELECT DATE(s.scan_time) AS tgl, COUNT(*) AS jml
        FROM (
            SELECT paket_id, MAX(scan_time) last_time
            FROM scan_paket
            GROUP BY paket_id
        ) last
        JOIN scan_paket s
            ON s.paket_id = last.paket_id
           AND s.scan_time = last.last_time
        WHERE s.jenis_scan = '$jenis_scan'
          AND s.gudang_id = '$gudang_id'
          AND s.scan_time >= '$tgl_awal 00:00:00'
        GROUP BY DATE(s.scan_time)
    ";

    $res = mysqli_query($conn, $query);
    while ($r = mysqli_fetch_assoc($res)) {
        $map[$r['tgl']] = $r['jml'];
    }

    return array_map(fn($tgl) => $map[$tgl] ?? 0, $tgl_list);
}

$val_masuk   = getDailyData($conn, $tanggal_labels, $bulan_tahun.'01', $gudang_id, 'masuk');
$val_keluar  = getDailyData($conn, $tanggal_labels, $bulan_tahun.'01', $gudang_id, 'keluar');
$val_diantar = getDailyData($conn, $tanggal_labels, $bulan_tahun.'01', $gudang_id, 'diantar');
?>

<?php include '../common/header.php'; ?>

<div class="flex bg-gray-50 min-h-screen">
<?php include '../common/sidebar.php'; ?>

<div class="flex-1 ml-64">
<?php include '../common/navbar.php'; ?>

<main class="p-6 pt-20">

<h1 class="text-2xl font-bold mb-6">
    Dashboard Sorter – <?= $namaGudang ?>
</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

<!-- MASUK -->
<div class="relative bg-emerald-500 text-white p-6 rounded-xl">
    <button onclick='openModal("Masuk", <?= json_encode($resi_masuk) ?>)'
        class="absolute top-3 right-3 bg-white/20 hover:bg-white/30 p-2 rounded-full">
        <i class="fa-solid fa-eye"></i>
    </button>
    <p>Masuk (Status Terakhir)</p>
    <p class="text-4xl font-bold"><?= count($resi_masuk) ?></p>
</div>

<!-- KELUAR -->
<div class="relative bg-orange-500 text-white p-6 rounded-xl">
    <button onclick='openModal("Keluar", <?= json_encode($resi_keluar) ?>)'
        class="absolute top-3 right-3 bg-white/20 hover:bg-white/30 p-2 rounded-full">
        <i class="fa-solid fa-eye"></i>
    </button>
    <p>Keluar / Transit</p>
    <p class="text-4xl font-bold"><?= count($resi_keluar) ?></p>
</div>

<!-- DIANTAR -->
<div class="relative bg-blue-500 text-white p-6 rounded-xl">
    <button onclick='openModal("Diantar", <?= json_encode($resi_diantar) ?>)'
        class="absolute top-3 right-3 bg-white/20 hover:bg-white/30 p-2 rounded-full">
        <i class="fa-solid fa-eye"></i>
    </button>
    <p>Diantar</p>
    <p class="text-4xl font-bold"><?= count($resi_diantar) ?></p>
</div>

</div>

<div class="bg-white p-6 rounded-xl h-96">
    <canvas id="chartSorter"></canvas>
</div>

</main>
</div>
</div>

<!-- MODAL -->
<div id="resiModal" class="fixed inset-0 hidden bg-black/60 flex items-center justify-center">
<div class="bg-white rounded-xl w-full max-w-md p-6">
<h3 id="modalTitle" class="font-bold mb-4"></h3>
<div id="modalContent" class="space-y-2 max-h-80 overflow-y-auto"></div>
<button onclick="closeModal()" class="mt-4 w-full bg-gray-800 text-white py-2 rounded">Tutup</button>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openModal(title, data) {
    document.getElementById('modalTitle').innerText = 'Daftar Resi ' + title;
    const c = document.getElementById('modalContent');
    c.innerHTML = data.length
        ? data.map(r => `<div class="p-2 bg-gray-100 rounded font-mono">${r}</div>`).join('')
        : '<p class="text-center text-gray-400">Tidak ada data</p>';
    document.getElementById('resiModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('resiModal').classList.add('hidden');
}

/* ===============================
   CHART.JS (FIX ERROR)
================================ */
new Chart(document.getElementById('chartSorter'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($tanggal_labels) ?>,
        datasets: [
            { label: 'Masuk',   data: <?= json_encode($val_masuk) ?>,   backgroundColor: '#10B981' },
            { label: 'Keluar',  data: <?= json_encode($val_keluar) ?>,  backgroundColor: '#F97316' },
            { label: 'Diantar', data: <?= json_encode($val_diantar) ?>, backgroundColor: '#3B82F6' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: {
                    callback: function(value) {
                        const label = this.getLabelForValue(value);
                        return label.split('-')[2]; // tanggal saja
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
