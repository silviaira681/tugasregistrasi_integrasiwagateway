<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/fonnte.php";


/*
|--------------------------------------------------------------------------
| ID USER
|--------------------------------------------------------------------------
*/

$id = (int)(
    $_GET["id"]
    ?? $_POST["id"]
    ?? 0
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
| DATA
|--------------------------------------------------------------------------
*/

$name = "";
$nisn = "";
$ttl = "";
$gender = "";
$email = "";
$no_hp = "";
$address = "";

$error = "";


/*
|--------------------------------------------------------------------------
| AMBIL DATA LAMA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        nisn,
        ttl,
        gender,
        email,
        no_hp,
        address
    FROM users
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Query gagal: " .
        htmlspecialchars(
            $conn->error
        )
    );
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


/*
|--------------------------------------------------------------------------
| ISI FORM
|--------------------------------------------------------------------------
*/

$name =
    $user["name"] ?? "";

$nisn =
    $user["nisn"] ?? "";

$ttl =
    $user["ttl"] ?? "";

$gender =
    $user["gender"] ?? "";

$email =
    $user["email"] ?? "";

$no_hp =
    $user["no_hp"] ?? "";

$address =
    $user["address"] ?? "";


/*
|--------------------------------------------------------------------------
| PROSES UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $name =
        trim(
            $_POST["name"] ?? ""
        );


    $nisn =
        trim(
            $_POST["nisn"] ?? ""
        );


    $ttl =
        trim(
            $_POST["ttl"] ?? ""
        );


    $gender =
        trim(
            $_POST["gender"] ?? ""
        );


    $email =
        trim(
            $_POST["email"] ?? ""
        );


    $no_hp =
        trim(
            $_POST["no_hp"] ?? ""
        );


    $address =
        trim(
            $_POST["address"] ?? ""
        );


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if (
        $name === "" ||
        $nisn === "" ||
        $ttl === "" ||
        $gender === "" ||
        $email === "" ||
        $no_hp === "" ||
        $address === ""
    ) {

        $error =
            "Semua data wajib diisi.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Format email tidak valid.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI NOMOR
        |--------------------------------------------------------------------------
        */

        $no_hp =
            preg_replace(
                "/[^0-9]/",
                "",
                $no_hp
            );


        if (
            substr(
                $no_hp,
                0,
                1
            ) === "0"
        ) {

            $no_hp =
                "62" .
                substr(
                    $no_hp,
                    1
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT
        |--------------------------------------------------------------------------
        */

        $stmt =
            $conn->prepare("
                SELECT id
                FROM users
                WHERE
                    (
                        nisn = ?
                        OR email = ?
                        OR no_hp = ?
                    )
                    AND id != ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Query pengecekan gagal: " .
                $conn->error;

        } else {


            $stmt->bind_param(
                "sssi",
                $nisn,
                $email,
                $no_hp,
                $id
            );


            $stmt->execute();


            $duplicate =
                $stmt
                ->get_result()
                ->fetch_assoc();


            $stmt->close();


            if ($duplicate) {

                $error =
                    "NISN, email, atau nomor WhatsApp sudah digunakan user lain.";

            } else {


                /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                */

                $stmt =
                    $conn->prepare("
                        UPDATE users
                        SET
                            name = ?,
                            nisn = ?,
                            ttl = ?,
                            gender = ?,
                            email = ?,
                            no_hp = ?,
                            address = ?
                        WHERE id = ?
                    ");


                if (!$stmt) {

                    $error =
                        "Query update gagal: " .
                        $conn->error;

                } else {


                    $stmt->bind_param(
                        "sssssssi",
                        $name,
                        $nisn,
                        $ttl,
                        $gender,
                        $email,
                        $no_hp,
                        $address,
                        $id
                    );


                    if ($stmt->execute()) {


                        $stmt->close();


                        /*
                        |--------------------------------------------------------------------------
                        | NOTIFIKASI WEBSITE
                        |--------------------------------------------------------------------------
                        */

                        $_SESSION["pesan"] =
                            "Data Anda berhasil diubah: " .
                            $name;

                        $_SESSION["tipe_pesan"] =
                            "success";


                        /*
                        |--------------------------------------------------------------------------
                        | WHATSAPP ADMIN
                        |--------------------------------------------------------------------------
                        */

                        $nomorAdmin =
                            "6281234567890";


                        $pesanAdmin =
                            "✏️ DATA USER DIUBAH\n\n" .

                            "Nama: " .
                            $name .
                            "\n" .

                            "NISN: " .
                            $nisn .
                            "\n" .

                            "Email: " .
                            $email .
                            "\n" .

                            "WhatsApp: " .
                            $no_hp .
                            "\n" .

                            "Waktu: " .
                            date("d-m-Y H:i:s") .
                            "\n\n" .

                            "Data user berhasil diperbarui.";


                        kirimWhatsApp(
                            $nomorAdmin,
                            $pesanAdmin
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | WHATSAPP USER
                        |--------------------------------------------------------------------------
                        */

                        $pesanUser =
                            "✏️ DATA ANDA DIUBAH\n\n" .

                            "Halo " .
                            $name .
                            ",\n\n" .

                            "Data Anda berhasil diperbarui oleh administrator.\n\n" .

                            "Waktu: " .
                            date("d-m-Y H:i:s") .
                            "\n\n" .

                            "Terima kasih.";


                        kirimWhatsApp(
                            $no_hp,
                            $pesanUser
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | KEMBALI
                        |--------------------------------------------------------------------------
                        */

                        header(
                            "Location: daftar_teman.php"
                        );

                        exit;


                    } else {

                        $error =
                            "Data gagal diubah: " .
                            $stmt->error;

                        $stmt->close();
                    }
                }
            }
        }
    }
}


function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit User - ASTS System
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;

            color: #1f2937;
        }


        .container {

            max-width: 850px;

            margin: 40px auto;

            padding: 20px;
        }


        .card {

            background: white;

            border-radius: 14px;

            padding: 30px;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,.06);

            border:
                1px solid #e5e7eb;
        }


        h1 {

            font-size: 25px;

            margin-bottom: 7px;
        }


        .subtitle {

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 25px;
        }


        .error {

            background: #fee2e2;

            color: #991b1b;

            border:
                1px solid #fecaca;

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;
        }


        .grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;
        }


        .full {

            grid-column:
                1 / -1;
        }


        label {

            display: block;

            font-size: 13px;

            font-weight: bold;

            margin-bottom: 7px;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 11px 13px;

            border:
                1px solid #d1d5db;

            border-radius: 8px;

            font-size: 14px;

            font-family: inherit;

            outline: none;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #2563eb;
        }


        textarea {

            min-height: 100px;

            resize: vertical;
        }


        .actions {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        button,
        .back {

            padding:
                11px 18px;

            border-radius: 8px;

            border: none;

            text-decoration: none;

            font-weight: bold;

            cursor: pointer;

            font-size: 14px;
        }


        button {

            background: #2563eb;

            color: white;
        }


        .back {

            background: #e5e7eb;

            color: #374151;
        }


        @media (max-width: 650px) {

            .grid {

                grid-template-columns: 1fr;
            }

            .full {

                grid-column: auto;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="card">


        <h1>
            ✏️ Edit Data User
        </h1>


        <p class="subtitle">
            Ubah data pengguna.
        </p>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
        >


            <input
                type="hidden"
                name="id"
                value="<?= (int)$id ?>"
            >


            <div class="grid">


                <div>

                    <label>
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= e($name) ?>"
                        required
                    >

                </div>


                <div>

                    <label>
                        NISN
                    </label>

                    <input
                        type="text"
                        name="nisn"
                        value="<?= e($nisn) ?>"
                        required
                    >

                </div>


                <div>

                    <label>
                        TTL
                    </label>

                    <input
                        type="text"
                        name="ttl"
                        value="<?= e($ttl) ?>"
                        required
                    >

                </div>


                <div>

                    <label>
                        Gender
                    </label>

                    <select
                        name="gender"
                        required
                    >

                        <option value="">
                            -- Pilih Gender --
                        </option>

                        <option
                            value="Laki-laki"
                            <?= $gender === "Laki-laki"
                                ? "selected"
                                : "" ?>
                        >
                            Laki-laki
                        </option>

                        <option
                            value="Perempuan"
                            <?= $gender === "Perempuan"
                                ? "selected"
                                : "" ?>
                        >
                            Perempuan
                        </option>

                    </select>

                </div>


                <div>

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= e($email) ?>"
                        required
                    >

                </div>


                <div>

                    <label>
                        No. WhatsApp
                    </label>

                    <input
                        type="text"
                        name="no_hp"
                        value="<?= e($no_hp) ?>"
                        required
                    >

                </div>


                <div class="full">

                    <label>
                        Alamat
                    </label>

                    <textarea
                        name="address"
                        required
                    ><?= e($address) ?></textarea>

                </div>


            </div>


            <div class="actions">


                <button
                    type="submit"
                >

                    💾 Simpan Perubahan

                </button>


                <a
                    href="daftar_teman.php"
                    class="back"
                >

                    ← Kembali

                </a>


            </div>


        </form>


    </div>


</div>


</body>

</html>