<?php

require_once "auth_check.php";
require_once "config.php";

header("Content-Type: application/json; charset=UTF-8");

$result = $conn->query("
    SELECT
        id,
        name,
        nisn,
        ttl,
        gender,
        email,
        address
    FROM users
    ORDER BY id DESC
");

$data = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $data[] = $row;

    }

}

echo json_encode(
    $data,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE
);

$conn->close();

?>