<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    $name    = isset($data['name']) ? trim($data['name']) : '';
    $nisn    = isset($data['nisn']) ? trim($data['nisn']) : '';
    $ttl     = isset($data['ttl']) ? trim($data['ttl']) : '';
    $gender  = isset($data['gender']) ? trim($data['gender']) : '';
    $email   = isset($data['email']) ? trim($data['email']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';

    $stmt = $conn->prepare("INSERT INTO users (name, nisn, ttl, gender, email, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $nisn, $ttl, $gender, $email, $address);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menambahkan data"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak boleh kosong"]);
}
?>