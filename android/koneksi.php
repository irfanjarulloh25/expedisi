<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbcobaexpedisi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Koneksi Database Gagal"]));
}
?>