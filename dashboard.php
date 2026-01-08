<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php?error=belum_login");
    exit;
}

switch ($_SESSION['role_nama']) {
    case 'admin':
        header("Location: pages/admin/dashboard.php");
        exit;

    case 'sorter':
        header("Location: pages/sorter/dashboard.php");
        exit;

    default:
        session_destroy();
        header("Location: login.php?error=akses_ditolak");
        exit;
}
