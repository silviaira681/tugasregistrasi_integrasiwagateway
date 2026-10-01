<?php

require_once "config.php";
require_once "auth_check.php";
require_once "fonnte.php";


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

$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header("Location: admin_dashboard.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
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

    header("Location: admin_dashboard.php");

    exit();
}


$user = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| VARIABEL
|--------------------------------------------------------------------------
*/

$error = "";


/*
|--------------------------------------------------------------------------
| PROSES UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');

    $username = trim($_POST['username'] ?? '');

    $noTelepon = trim($_POST['no_telepon'] ?? '');

    $role = trim($_POST['role'] ?? 'User');


    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if (
        $nama === '' ||
        $username === '' ||
        $noTelepon === ''
    ) {

        $error = "Semua data wajib diisi.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Normalisasi nomor
        |--------------------------------------------------------------------------
        */

        $noTelepon = preg_replace(
            '/[\s\-\(\)]/',
            '',
            $noTelepon
        );


        /*
        |--------------------------------------------------------------------------
        | Cek username / nomor
        |--------------------------------------------------------------------------
        */

        $cek = $conn->prepare("
            SELECT id
            FROM auth_users
            WHERE
                (username = ? OR no_telepon = ?)
                AND id <> ?
            LIMIT 1
        ");

        $cek->bind_param(
            "ssi",
            $username,
            $noTelepon,
            $id
        );

        $cek->execute();

        $cekResult = $cek->get_result();


        if ($cekResult->num_rows > 0) {

            $error =
                "Username atau nomor telepon sudah digunakan.";

            $cek->close();

        } else {

            $cek->close();


            /*
            |--------------------------------------------------------------------------
            | Catat perubahan
            |--------------------------------------------------------------------------
            */

            $perubahan = [];


            if ($user['nama'] !== $nama) {

                $perubahan[] = "Nama";
            }


            if ($user['username'] !== $username) {

                $perubahan[] = "Username";
            }


            if ($user['no_telepon'] !== $noTelepon) {

                $perubahan[] = "Nomor Telepon";
            }


            if ($user['role'] !== $role) {

                $perubahan[] = "Role";
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare("
                UPDATE auth_users
                SET
                    nama = ?,
                    username = ?,
                    no_telepon = ?,
                    role = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "ssssi",
                $nama,
                $username,
                $noTelepon,
                $role,
                $id
            );


            if ($update->execute()) {


                /*
                |--------------------------------------------------------------------------
                | NOTIFIKASI DASHBOARD
                |--------------------------------------------------------------------------
                */

                $_SESSION['flash_type'] = "success";


                if (count($perubahan) > 0) {

                    $_SESSION['flash_message'] =
                        "Data user berhasil diubah: " .
                        implode(", ", $perubahan);

                } else {

                    $_SESSION['flash_message'] =
                        "Data user berhasil disimpan.";
                }


                /*
                |--------------------------------------------------------------------------
                | KIRIM WHATSAPP
                |--------------------------------------------------------------------------
                */

                if (
                    $user['no_telepon'] !== $noTelepon
                ) {

                    $pesanWA =
                        "Halo " . $nama . " 👋\n\n" .
                        "Data akun UJIAN ASTS Anda telah diperbarui oleh administrator.\n\n" .
                        "Nama: " . $nama . "\n" .
                        "Username: " . $username . "\n" .
                        "Nomor Telepon: " . $noTelepon . "\n" .
                        "Role: " . $role . "\n\n" .
                        "Jika perubahan ini benar, Anda dapat melanjutkan menggunakan akun seperti biasa.\n\n" .
                        "Terima kasih.";

                    $hasilWA = kirimWhatsApp(
                        $noTelepon,
                        $pesanWA
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Jika WA gagal
                    |--------------------------------------------------------------------------
                    */

                    if (!$hasilWA['success']) {

                        $_SESSION['flash_message'] .=
                            " Data berhasil disimpan, tetapi WhatsApp gagal dikirim.";
                    }
                }


                $update->close();


                header(
                    "Location: admin_dashboard.php"
                );

                exit();


            } else {

                $error =
                    "Gagal mengubah data: " .
                    $conn->error;
            }


            $update->close();
        }
    }
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

<title>Edit User - UJIAN ASTS</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #eff6ff,
            #f8fafc
        );

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 25px;
}


.container {

    width: 650px;

    max-width: 100%;
}


.card {

    background: white;

    padding: 35px;

    border-radius: 22px;

    box-shadow:
        0 15px 45px
        rgba(15, 23, 42, .10);
}


.header {

    margin-bottom: 28px;
}


.header h1 {

    margin: 0 0 8px;

    color: #0f172a;

    font-size: 28px;
}


.header p {

    margin: 0;

    color: #64748b;
}


.group {

    margin-bottom: 18px;
}


label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #334155;
}


input,
select {

    width: 100%;

    padding: 13px 15px;

    border:
        1px solid
        #cbd5e1;

    border-radius: 11px;

    font-size: 15px;

    outline: none;
}


input:focus,
select:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .10);
}


.buttons {

    display: flex;

    gap: 12px;

    margin-top: 25px;
}


button,
a {

    padding: 13px 18px;

    border-radius: 11px;

    border: none;

    text-decoration: none;

    font-weight: bold;

    cursor: pointer;

    font-size: 14px;
}


button {

    background: #2563eb;

    color: white;

    flex: 1;
}


button:hover {

    background: #1d4ed8;
}


.back {

    background: #e2e8f0;

    color: #334155;

    display: flex;

    align-items: center;

    justify-content: center;

    flex: 1;
}


.error {

    background: #fee2e2;

    color: #b91c1c;

    border:
        1px solid
        #fecaca;

    padding: 13px;

    border-radius: 11px;

    margin-bottom: 20px;
}


.info {

    background: #eff6ff;

    color: #1d4ed8;

    border:
        1px solid
        #bfdbfe;

    padding: 13px;

    border-radius: 11px;

    margin-bottom: 20px;

    font-size: 14px;
}


@media(max-width:600px) {

    .card {

        padding: 25px;
    }

    .buttons {

        flex-direction: column;
    }
}

</style>

</head>


<body>


<div class="container">

    <div class="card">


        <div class="header">

            <h1>
                ✏️ Edit User
            </h1>

            <p>
                Ubah data pengguna UJIAN ASTS
            </p>

        </div>


        <?php if ($error !== ''): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <div class="info">

            📱 Jika nomor WhatsApp diubah,
            sistem akan mencoba mengirim
            pemberitahuan ke nomor baru.

        </div>


        <form
            method="POST"
            action=""
        >


            <div class="group">

                <label>
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    value="<?= htmlspecialchars($user['nama']) ?>"
                    required
                >

            </div>


            <div class="group">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    value="<?= htmlspecialchars($user['username']) ?>"
                    required
                >

            </div>


            <div class="group">

                <label>
                    Nomor WhatsApp
                </label>

                <input
                    type="text"
                    name="no_telepon"
                    value="<?= htmlspecialchars($user['no_telepon'] ?? '') ?>"
                    placeholder="081234567890"
                    required
                >

            </div>


            <div class="group">

                <label>
                    Role
                </label>

                <select
                    name="role"
                    required
                >

                    <option
                        value="User"
                        <?= $user['role'] === 'User' ? 'selected' : '' ?>
                    >
                        User
                    </option>

                    <option
                        value="Admin"
                        <?= $user['role'] === 'Admin' ? 'selected' : '' ?>
                    >
                        Admin
                    </option>

                </select>

            </div>


            <div class="buttons">

                <a
                    href="admin_dashboard.php"
                    class="back"
                >
                    ← Kembali
                </a>


                <button type="submit">

                    💾 Simpan Perubahan

                </button>

            </div>


        </form>


    </div>

</div>


</body>

</html>