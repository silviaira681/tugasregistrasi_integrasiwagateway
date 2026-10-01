<?php

require_once "config.php";
require_once "auth_check.php";
require_once "fonnte.php";

// kode berikutnya...
/*
|--------------------------------------------------------------------------
| CEK ADMIN
|--------------------------------------------------------------------------
*/

if (($_SESSION['role'] ?? '') !== 'Admin') {

    header("Location: dashboard.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| AMBIL ID
|--------------------------------------------------------------------------
*/

$id = (int)($_POST['id'] ?? 0);


if ($id <= 0) {

    $_SESSION['flash_type'] = "error";

    $_SESSION['flash_message'] =
        "ID user tidak valid.";

    header("Location: admin_dashboard.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| ADMIN TIDAK BOLEH HAPUS DIRI SENDIRI
|--------------------------------------------------------------------------
*/

if ($id == ($_SESSION['id'] ?? 0)) {

    $_SESSION['flash_type'] = "error";

    $_SESSION['flash_message'] =
        "Admin tidak dapat menghapus akun sendiri.";

    header("Location: admin_dashboard.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        nama,
        no_telepon,
        username
    FROM auth_users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION['flash_type'] = "error";

    $_SESSION['flash_message'] =
        "User tidak ditemukan.";

    header("Location: admin_dashboard.php");

    exit();
}


$user = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| HAPUS USER
|--------------------------------------------------------------------------
*/

$delete = $conn->prepare("
    DELETE FROM auth_users
    WHERE id = ?
");


$delete->bind_param(
    "i",
    $id
);


if ($delete->execute()) {


    /*
    |--------------------------------------------------------------------------
    | Kirim WhatsApp setelah data berhasil dihapus
    |--------------------------------------------------------------------------
    */

    if (!empty($user['no_telepon'])) {

        $pesanWA =
            "Halo " .
            $user['nama'] .
            " 👋\n\n" .

            "Kami informasikan bahwa akun Anda " .
            "telah dihapus dari sistem UJIAN ASTS " .
            "oleh administrator.\n\n" .

            "Username: " .
            $user['username'] .
            "\n\n" .

            "Jika Anda merasa tidak melakukan " .
            "permintaan ini, silakan hubungi administrator.";

        $hasilWA = kirimWhatsApp(
            $user['no_telepon'],
            $pesanWA
        );


        if ($hasilWA['success']) {

            $_SESSION['flash_type'] =
                "success";

            $_SESSION['flash_message'] =
                "Akun berhasil dihapus dan notifikasi WhatsApp berhasil dikirim.";

        } else {

            $_SESSION['flash_type'] =
                "success";

            $_SESSION['flash_message'] =
                "Akun berhasil dihapus, tetapi notifikasi WhatsApp gagal dikirim.";
        }

    } else {

        $_SESSION['flash_type'] =
            "success";

        $_SESSION['flash_message'] =
            "Akun berhasil dihapus. User tidak memiliki nomor WhatsApp.";
    }


} else {

    $_SESSION['flash_type'] =
        "error";

    $_SESSION['flash_message'] =
        "Gagal menghapus akun: " .
        $conn->error;
}


$delete->close();


/*
|--------------------------------------------------------------------------
| KEMBALI
|--------------------------------------------------------------------------
*/

header("Location: admin_dashboard.php");

exit();

?>