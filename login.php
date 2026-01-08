<?php
session_start();

// ==================================
// BLOK LOGIN JIKA SUDAH LOGIN
// ==================================
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if ($_SESSION['role_nama'] === 'admin') {
        header("Location: pages/admin/dashboard.php");
        exit;
    } elseif ($_SESSION['role_nama'] === 'sorter') {
        header("Location: pages/sorter/dashboard.php");
        exit;
    }
}

require_once __DIR__ . "/config/koneksi.php";

// Inisialisasi variabel error agar tidak muncul "Undefined variable"
$error = '';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT 
            k.id,
            k.nama,
            k.password,
            r.nama_role
        FROM karyawan k
        JOIN roles r ON k.role_id = r.id
        WHERE k.email = '$email'
        LIMIT 1
    ");

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['login']     = true;
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['nama']      = $user['nama'];
            $_SESSION['role_nama'] = strtolower($user['nama_role']); 

            if ($_SESSION['role_nama'] === 'admin') {
                header("Location: pages/admin/dashboard.php");
                exit;
            } elseif ($_SESSION['role_nama'] === 'sorter') {
                header("Location: pages/sorter/dashboard.php");
                exit;
            } else {
                session_destroy();
                header("Location: login.php?error=role_tidak_dikenal");
                exit;
            }
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Email tidak terdaftar di sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Expedisi Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            /* Background Gradient Modern */
            background: radial-gradient(circle at top right, #3b82f6, transparent),
                        radial-gradient(circle at bottom left, #1d4ed8, #0f172a);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md glass-card rounded-[2.5rem] shadow-2xl p-8 md:p-12 transition-all duration-500">
        
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-blue-600 to-blue-400 rounded-3xl mb-6 shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">LOGISTIC<span class="text-blue-600">PRO</span></h1>
            <p class="text-slate-500 mt-2 font-medium">Silakan masuk untuk akses dashboard</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl flex items-center gap-3 text-red-700 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-semibold"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'belum_login'): ?>
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl flex items-center gap-3 text-amber-700">
                <span class="text-sm font-semibold">Sesi berakhir, silakan login kembali.</span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Email Karyawan</label>
                <input type="email" name="email" required
                       class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 outline-none transition-all duration-200 placeholder:text-slate-400"
                       placeholder="contoh@expedisi.com">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Password</label>
                <input type="password" name="password" required
                       class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 outline-none transition-all duration-200 placeholder:text-slate-400"
                       placeholder="••••••••">
            </div>

            <button type="submit" name="login"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-300 active:scale-[0.97] mt-4 flex items-center justify-center gap-2">
                <span>Masuk ke Sistem</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>

        <div class="mt-12 text-center">
            <p class="text-slate-400 text-[10px] tracking-[0.2em] uppercase font-extrabold">
                Management System &bull; &copy; <?= date('Y') ?>
            </p>
        </div>
    </div>

</body>
</html>