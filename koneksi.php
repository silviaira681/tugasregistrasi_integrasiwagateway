<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ujian_asts";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    exit();
}
?>