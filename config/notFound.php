<?php
session_start();

$baseUrl = '/cobaExpedisi'; // Define the base URL of the project

if (isset($_SESSION['login']) && isset($_SESSION['role_nama'])) {
    $role = $_SESSION['role_nama'];
    if ($role === 'admin') {
        $dashboardUrl = $baseUrl . '/pages/admin/dashboard.php';
    } elseif ($role === 'sorter') {
        $dashboardUrl = $baseUrl . '/pages/sorter/dashboard.php';
    } else {
        // Fallback for unknown role, redirect to login
        $dashboardUrl = $baseUrl . '/login.php';
    }
} else {
    // If not logged in, redirect to login page
    $dashboardUrl = $baseUrl . '/login.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; }
        h1 { font-size: 48px; }
        a { text-decoration: none; color: #007bff; font-size: 18px; }
    </style>
</head>
<body>
    <h1>404 - Halaman Tidak Ditemukan</h1>
    <p>Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    <a href="<?php echo htmlspecialchars($dashboardUrl); ?>">Kembali ke Halaman Utama</a>
</body>
</html>
