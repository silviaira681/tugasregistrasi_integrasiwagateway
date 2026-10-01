<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/fonnte.php";


/*
|--------------------------------------------------------------------------
| HANYA POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"]
    !== "POST"
) {

    $_SESSION["pesan"] =
        "Permintaan hapus tidak valid.";

    $_SESSION["tipe_pesan"] =
        "error";

    header(
        "Location: daftar_teman.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

$id =
    (int)(
        $_POST["id"] ?? 0
    );


if ($id <= 0) {

    $_SESSION["pesan"] =
        "ID user tidak valid.";

    $_SESSION["tipe_pesan"] =
        "error";

    header(
        "Location: daftar_teman.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA USER SEBELUM DIHAPUS
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare("
        SELECT
            id,
            name,
            no_hp
        FROM users
        WHERE id = ?
        LIMIT 1
    ");


if (!$stmt) {

    $_SESSION["pesan"] =
        "Gagal mengambil data user.";

    $_SESSION["tipe_pesan"] =
        "error";

    header(
        "Location: daftar_teman.php"
    );

    exit;
}


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$result =
    $stmt->get_result();


$user =
    $result->fetch_assoc();


$stmt->close();


if (!$user) {

    $_SESSION["pesan"] =
        "Data user tidak ditemukan.";

    $_SESSION["tipe_pesan"] =
        "error";

    header(
        "Location: daftar_teman.php"
    );

    exit;
}


$namaUser =
    $user["name"];


$nomorUser =
    $user["no_hp"];


/*
|--------------------------------------------------------------------------
| HAPUS
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare("
        DELETE FROM users
        WHERE id = ?
    ");


if (!$stmt) {

    $_SESSION["pesan"] =
        "Query hapus gagal: " .
        $conn->error;

    $_SESSION["tipe_pesan"] =
        "error";

    header(
        "Location: daftar_teman.php"
    );

    exit;
}


$stmt->bind_param(
    "i",
    $id
);


if ($stmt->execute()) {


    /*
    |--------------------------------------------------------------------------
    | NOTIFIKASI WEBSITE
    |--------------------------------------------------------------------------
    */

    $_SESSION["pesan"] =
        "Akun telah dihapus: " .
        $namaUser;

    $_SESSION["tipe_pesan"] =
        "success";


    /*
    |--------------------------------------------------------------------------
    | NOTIFIKASI WHATSAPP ADMIN
    |--------------------------------------------------------------------------
    */

    $nomorAdmin =
        "6281234567890";


    $pesanAdmin =
        "🗑️ DATA USER DIHAPUS\n\n" .

        "Nama: " .
        $namaUser .
        "\n" .

        "ID: " .
        $id .
        "\n" .

        "Waktu: " .
        date("d-m-Y H:i:s") .
        "\n\n" .

        "Data user telah dihapus dari sistem.";


    kirimWhatsApp(
        $nomorAdmin,
        $pesanAdmin
    );


    /*
    |--------------------------------------------------------------------------
    | NOTIFIKASI USER
    |--------------------------------------------------------------------------
    |
    | Karena data sudah dihapus, kirim WhatsApp
    | menggunakan nomor yang sudah diambil
    | sebelum DELETE.
    |
    */

    if (!empty($nomorUser)) {


        $pesanUser =
            "🗑️ DATA ANDA DIHAPUS\n\n" .

            "Halo " .
            $namaUser .
            ",\n\n" .

            "Data Anda telah dihapus dari sistem oleh administrator.\n\n" .

            "Waktu: " .
            date("d-m-Y H:i:s") .
            "\n\n" .

            "Terima kasih.";


        kirimWhatsApp(
            $nomorUser,
            $pesanUser
        );
    }


} else {


    $_SESSION["pesan"] =
        "Akun gagal dihapus: " .
        $stmt->error;


    $_SESSION["tipe_pesan"] =
        "error";
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| KEMBALI
|--------------------------------------------------------------------------
*/

header(
    "Location: daftar_teman.php"
);

exit;