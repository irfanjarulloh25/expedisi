<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role_nama'] ?? '';
$nama = $_SESSION['nama'] ?? 'User';
?>

<aside class="w-60 min-h-screen p-4 fixed bg-[#1E90FF]
              text-white shadow-lg">
    <div class="mb-6">
        <h2 class="text-xl font-bold mt-10">Expedisi</h2>
        <p class="text-sm mt-1">Halo, <?= htmlspecialchars($nama) ?></p>
    </div>

    <ul>
        <?php if ($role === 'admin'): ?>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/dashboard.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
            </li>
            <div class="mb-2 text-2xl font-bold">- Input -</div>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/karyawan.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-users mr-2"></i> Karyawan
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/gudang.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-warehouse mr-2"></i> Gudang
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/ongkir.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-money-bill mr-2"></i> Ongkir
                </a>
            </li>

            <div class="mb-2 text-2xl font-bold ">- List -</div>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/listKaryawan.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-users mr-2"></i> Karyawan
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/listGudang.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-warehouse mr-2"></i> Gudang
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/listOngkir.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-money-bill mr-2"></i> Ongkir
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/admin/listPaket.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fa-solid fa-box-open mr-2"></i> Paket
                </a>
            </li>

        <?php elseif ($role === 'sorter'): ?>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/sorter/dashboard.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-home mr-2"></i> Dashboard Sorter
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/sorter/paketInput.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-barcode mr-2"></i> Input Paket
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/sorter/scanAntar.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-truck mr-2"></i> Scan Antar
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/sorter/scanKeluar.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-sign-out-alt mr-2"></i> Scan Keluar
                </a>
            </li>
            <li class="mb-2">
                <a href="/cobaExpedisi/pages/sorter/scanMasuk.php" class="flex items-center p-2 hover:bg-white/10 rounded">
                    <i class="fas fa-sign-in-alt mr-2"></i> Scan Masuk
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>
