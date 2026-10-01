<?php

require_once "config.php";
require_once "auth_check.php";
require_once "fonnte.php";

$id = $_SESSION['id'];

$stmt = $conn->prepare("
    SELECT nama, no_telepon, role
    FROM auth_users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}

$user = $result->fetch_assoc();

if ($user['role'] === 'Admin') {

    $_SESSION['flash_type'] = "error";

    $_SESSION['flash_message'] =
        "Akun Admin tidak dapat dihapus melalui halaman ini.";

    header("Location: profil.php");
    exit();

}

kirimWhatsApp(
    $user['no_telepon'],
    "Akun Anda telah dihapus dari sistem UJIAN ASTS."
);

$delete = $conn->prepare("
    DELETE FROM auth_users
    WHERE id = ?
");

$delete->bind_param("i", $id);

if ($delete->execute()) {

    session_unset();
    session_destroy();

    header("Location: login.php?deleted=1");
    exit();

}

header("Location: profil.php");
exit();

?>