<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

$resi    = trim($_POST['no_resi'] ?? '');
$user_id = $_SESSION['user_id'] ?? 0;

$response = [
    'valid' => false,
    'sound' => false
];

try {

    /* ===============================
       VALIDASI DASAR
    ================================ */
    if ($resi === '' || $user_id == 0) {
        $response['sound'] = true;
        echo json_encode($response);
        exit;
    }

    /* ===============================
       GUDANG USER
    ================================ */
    $stmtUser = $conn->prepare("
        SELECT gudang_id 
        FROM karyawan 
        WHERE id = ?
        LIMIT 1
    ");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();

    if ($resUser->num_rows === 0) {
        $response['sound'] = true;
        echo json_encode($response);
        exit;
    }

    $gudang_user = $resUser->fetch_assoc()['gudang_id'];

    /* ===============================
       CEK PAKET
    ================================ */
    $stmt = $conn->prepare("
        SELECT id 
        FROM paket 
        WHERE no_resi = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $resi);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $response['sound'] = true;
        echo json_encode($response);
        exit;
    }

    $paket_id = $res->fetch_assoc()['id'];

    /* ===============================
       STATUS TERAKHIR
    ================================ */
    $stmtStatus = $conn->prepare("
        SELECT status 
        FROM status_paket
        WHERE paket_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmtStatus->bind_param("i", $paket_id);
    $stmtStatus->execute();
    $resStatus = $stmtStatus->get_result();

    if ($resStatus->num_rows > 0) {
        $status = strtolower($resStatus->fetch_assoc()['status']);

        if ($status === 'selesai' || $status === 'diantar') {
            $response['sound'] = true;
            echo json_encode($response);
            exit;
        }
    }

    /* ===============================
       SCAN TERAKHIR
    ================================ */
    $stmtScan = $conn->prepare("
        SELECT jenis_scan, gudang_id
        FROM scan_paket
        WHERE paket_id = ?
        ORDER BY scan_time DESC
        LIMIT 1
    ");
    $stmtScan->bind_param("i", $paket_id);
    $stmtScan->execute();
    $resScan = $stmtScan->get_result();

    if ($resScan->num_rows > 0) {
        $scan = $resScan->fetch_assoc();

        if ($scan['jenis_scan'] === 'keluar') {
            $response['sound'] = true;
            echo json_encode($response);
            exit;
        }

        if ($scan['jenis_scan'] === 'masuk' && $scan['gudang_id'] != $gudang_user) {
            $response['sound'] = true;
            echo json_encode($response);
            exit;
        }

        if ($scan['jenis_scan'] === 'diantar') {
            $response['sound'] = true;
            echo json_encode($response);
            exit;
        }
    }

    /* ===============================
       VALID (BUNYI SUCCESS OPTIONAL)
    ================================ */
    $response['valid'] = true;

} catch (Exception $e) {
    $response['sound'] = true;
}

echo json_encode($response);
exit;
