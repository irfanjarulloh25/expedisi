<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role_nama'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
include '../../config/koneksi.php';

// 1. Inisialisasi Nama Bulan
$bulan_labels = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tahun_ini = date('Y');

// --- Statistik Total Karyawan & Gudang ---
$total_karyawan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM karyawan"))['total'];
$total_gudang   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM gudang"))['total'];

// 2. Ambil Data Chart
$val_total_paket = array_fill(0, 12, 0);
$q_total_paket = mysqli_query($conn, "SELECT MONTH(created_at) as bulan, COUNT(*) as jml FROM paket WHERE YEAR(created_at)='$tahun_ini' GROUP BY MONTH(created_at)");
while($r = mysqli_fetch_assoc($q_total_paket)) { $val_total_paket[$r['bulan']-1] = (int)$r['jml']; }

$val_berhasil = array_fill(0, 12, 0);
$q_berhasil = mysqli_query($conn, "SELECT MONTH(updated_at) as bulan, COUNT(*) as jml FROM status_paket WHERE status='berhasil' AND YEAR(updated_at)='$tahun_ini' GROUP BY MONTH(updated_at)");
while($r = mysqli_fetch_assoc($q_berhasil)) { $val_berhasil[$r['bulan']-1] = (int)$r['jml']; }

$val_masalah = array_fill(0, 12, 0);
$q_masalah = mysqli_query($conn, "SELECT MONTH(updated_at) as bulan, COUNT(*) as jml FROM status_paket WHERE status='bermasalah' AND YEAR(updated_at)='$tahun_ini' GROUP BY MONTH(updated_at)");
while($r = mysqli_fetch_assoc($q_masalah)) { $val_masalah[$r['bulan']-1] = (int)$r['jml']; }
?>

<?php include '../common/header.php'; ?>
<?php include '../common/sidebar.php'; ?>
<?php include '../common/navbar.php'; ?>

<main class="ml-64 pt-16 min-h-screen bg-gray-100">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Tahunan Admin</h1>

        <!-- Kotak Total Karyawan & Total Gudang -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full text-blue-600 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Karyawan</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_karyawan) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full text-indigo-600 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Gudang</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_gudang) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Performa Bulanan -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Analisis Performa Paket Tahun <?= $tahun_ini ?></h2>
                <p class="text-gray-500 text-sm">Visualisasi jumlah paket berdasarkan status per bulan</p>
            </div>
            <div class="w-full h-96">
                <canvas id="yearlyTrendChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('yearlyTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($bulan_labels) ?>,
        datasets: [
            {
                label: 'Total Paket',
                data: <?= json_encode($val_total_paket) ?>,
                backgroundColor: '#3B82F6',
                borderRadius: 5
            },
            {
                label: 'Berhasil',
                data: <?= json_encode($val_berhasil) ?>,
                backgroundColor: '#10B981',
                borderRadius: 5
            },
            {
                label: 'Bermasalah',
                data: <?= json_encode($val_masalah) ?>,
                backgroundColor: '#EF4444',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>
