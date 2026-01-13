<?php
// Mencegah error reporting merusak output JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIG PATH KONEKSI ---
// Gunakan path absolut agar lebih aman jika file di-include di berbagai tempat
$root = $_SERVER['DOCUMENT_ROOT'] . '/cobaExpedisi';
$path_koneksi = $root . '/config/koneksi.php';

if (file_exists($path_koneksi)) {
    include $path_koneksi;
}

/*
|--------------------------------------------------------------------------
| API AJAX CEK RESI
|--------------------------------------------------------------------------
*/
if (isset($_GET['ajax_resi'])) {
    header('Content-Type: application/json');

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
        exit;
    }

    $resi = trim($_GET['ajax_resi']);
    if ($resi === '') {
        echo json_encode(['success' => false, 'message' => 'No resi kosong']);
        exit;
    }

    // Ambil data paket
    $stmt = mysqli_prepare($conn, "SELECT * FROM paket WHERE no_resi = ?");
    mysqli_stmt_bind_param($stmt, 's', $resi);
    mysqli_stmt_execute($stmt);
    $paket = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$paket) {
        echo json_encode(['success' => false, 'message' => 'Resi tidak ditemukan']);
        exit;
    }

    // Ambil riwayat status
    $stmt2 = mysqli_prepare(
        $conn,
        "SELECT status, keterangan, foto_penerima, updated_at
         FROM status_paket
         WHERE paket_id = ?
         ORDER BY updated_at DESC"
    );
    mysqli_stmt_bind_param($stmt2, 'i', $paket['id']);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);

    $history = [];
    $lastFotoPenerima = null;

    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
        if (!$lastFotoPenerima && !empty($row['foto_penerima'])) {
            $lastFotoPenerima = $row['foto_penerima'];
        }
    }

    echo json_encode([
        'success' => true,
        'paket' => $paket,
        'status_history' => $history,
        'last_status_foto' => $lastFotoPenerima
    ]);
    exit;
}
?>

<nav class="fixed top-0 left-60 right-0 h-16 bg-[#1E90FF] flex items-center justify-between px-6 z-40 shadow-md">
    <div class="w-full max-w-md">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input id="trackingInput"
                type="text"
                placeholder="Scan No Resi ..."
                class="w-full rounded-full pl-10 pr-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-white/50 bg-white">
        </div>
    </div>

    <div class="flex items-center gap-4">
        <a href="/cobaExpedisi/logout.php" id="btnLogout"
            class="text-white border border-white/30 bg-white/10 px-4 py-1.5 rounded-lg hover:bg-white/20 transition text-sm font-medium">
            Logout
        </a>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('trackingInput');

    input.addEventListener('keypress', async (e) => {
        if (e.key !== 'Enter') return;

        const resi = input.value.trim();
        if (!resi) return;

        Swal.fire({
            title: 'Mencari data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            // Gunakan path absolut agar fetch tidak salah alamat
            const response = await fetch(`/cobaExpedisi/pages/common/navbar.php?ajax_resi=${encodeURIComponent(resi)}`);
            
            // Cek jika response bukan JSON (misal error PHP 500)
            if (!response.ok) throw new Error('Server Error');
            
            const data = await response.json();

            if (!data.success) {
                Swal.fire('Gagal', data.message, 'error');
                return;
            }

            const historyHtml = data.status_history.length
                ? data.status_history.map(s => `
                    <div class="border-l-4 border-blue-500 pl-3 py-2 mb-3 bg-blue-50 text-left">
                        <div class="text-[10px] text-gray-500">${s.updated_at}</div>
                        <div class="font-bold text-blue-700 text-sm">${s.status}</div>
                        <div class="text-xs text-gray-600">${s.keterangan || '-'}</div>
                    </div>
                `).join('')
                : '<p class="italic text-gray-400 py-4">Belum ada riwayat status</p>';

            Swal.fire({
                title: `<span class="text-blue-600">Resi: ${data.paket.no_resi}</span>`,
                width: 650,
                showConfirmButton: false,
                showCloseButton: true,
                html: `
                    <div class="bg-gray-100 p-4 rounded-xl mb-4 text-left text-sm grid grid-cols-2 gap-2 border border-gray-200">
                        <p class="col-span-2 border-b pb-1 mb-1 font-bold text-gray-700 uppercase text-[10px]">Informasi Pengiriman</p>
                        <p><b>📦 Isi Paket:</b> ${data.paket.nama_paket}</p>
                        <p><b>⚖️ Berat:</b> ${data.paket.berat} Kg</p>
                        <p><b>📥 Penerima:</b> ${data.paket.nama_penerima}</p>
                        <p><b>📤 Pengirim:</b> ${data.paket.nama_pengirim}</p>
                        <p class="col-span-2"><b>📍 Alamat:</b> ${data.paket.alamat_penerima}</p>
                    </div>

                    <h4 class="font-bold mb-2 text-left text-gray-700 flex items-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         Riwayat Status
                    </h4>
                    <div class="max-h-56 overflow-y-auto pr-2 custom-scrollbar">${historyHtml}</div>

                    <div class="flex gap-3 mt-6 border-t pt-4">
                        <button onclick="viewImage('${data.paket.foto_paket}', 'paket')"
                            class="flex-1 py-3 text-xs font-bold rounded-xl transition-all
                            ${data.paket.foto_paket ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700' : 'bg-gray-200 text-gray-400 cursor-not-allowed'}"
                            ${data.paket.foto_paket ? '' : 'disabled'}>
                            📦 FOTO PAKET
                        </button>

                        <button onclick="viewImage('${data.last_status_foto}', 'penerima')"
                            class="flex-1 py-3 text-xs font-bold rounded-xl transition-all
                            ${data.last_status_foto ? 'bg-green-600 text-white shadow-lg shadow-green-200 hover:bg-green-700' : 'bg-gray-200 text-gray-400 cursor-not-allowed'}"
                            ${data.last_status_foto ? '' : 'disabled'}>
                            👤 FOTO PENERIMA
                        </button>
                    </div>
                `
            });

        } catch (err) {
            Swal.fire('Error', 'Gagal memproses data. Periksa koneksi database atau path file.', 'error');
            console.error(err);
        }

        input.value = '';
    });

    // Logout confirmation
    document.getElementById('btnLogout').addEventListener('click', e => {
        e.preventDefault();
        const logoutUrl = e.currentTarget.href;
        Swal.fire({
            title: 'Logout?',
            text: 'Sesi Anda akan berakhir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1E90FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) window.location.href = logoutUrl;
        });
    });
});

/**
 * Fungsi untuk menampilkan gambar
 * @param {string} file Nama file gambar
 * @param {string} type 'paket' atau 'penerima'
 */
function viewImage(file, type) {
    if (!file || file === 'null') return;
    
    // Sesuaikan path folder foto Anda di sini
    // Foto paket ada di folder 'paket', Foto penerima ada di folder 'fotoPenerima'
    const folder = type === 'paket' ? 'paket' : 'fotoPenerima'; 
    const fullPath = `/cobaExpedisi/pages/uploads/${folder}/${file}`;

    Swal.fire({
        title: `Bukti Foto ${type === 'paket' ? 'Paket' : 'Penerima'}`,
        imageUrl: fullPath,
        imageAlt: 'Gambar tidak ditemukan',
        imageWidth: 400, // Ukuran lebar preview gambar
        imageHeight: 'auto',
        showConfirmButton: false,
        showCloseButton: true,
        footer: `<a href="${fullPath}" target="_blank" class="text-blue-500 text-xs">Buka gambar di tab baru</a>`
    });
}
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>