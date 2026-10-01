<?php

session_start();

require_once "config.php";
require_once "fonnte.php";


if (isset($_SESSION['id'])) {

    header("Location: dashboard.php");

    exit();
}


$error = "";

$nama = "";

$username = "";

$noTelepon = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama =
        trim($_POST['nama'] ?? '');

    $username =
        trim($_POST['username'] ?? '');

    $noTelepon =
        trim($_POST['no_telepon'] ?? '');

    $password =
        $_POST['password'] ?? '';

    $passwordConfirm =
        $_POST['password_confirm'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if (
        $nama === '' ||
        $username === '' ||
        $noTelepon === '' ||
        $password === '' ||
        $passwordConfirm === ''
    ) {

        $error =
            "Semua data wajib diisi.";

    } elseif (strlen($username) < 3) {

        $error =
            "Username minimal 3 karakter.";

    } elseif (strlen($password) < 6) {

        $error =
            "Password minimal 6 karakter.";

    } elseif ($password !== $passwordConfirm) {

        $error =
            "Konfirmasi password tidak sama.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Format nomor
        |--------------------------------------------------------------------------
        */

        $noTelepon =
            preg_replace(
                '/[\s\-\(\)]/',
                '',
                $noTelepon
            );


        if (
            substr(
                $noTelepon,
                0,
                1
            ) === '+'
        ) {

            $noTelepon =
                substr(
                    $noTelepon,
                    1
                );
        }


        if (
            substr(
                $noTelepon,
                0,
                1
            ) === '0'
        ) {

            $noTelepon =
                '62' .
                substr(
                    $noTelepon,
                    1
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validasi nomor
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^62[0-9]{8,15}$/',
                $noTelepon
            )
        ) {

            $error =
                "Nomor WhatsApp tidak valid.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Cek username
            |--------------------------------------------------------------------------
            */

            $stmt =
                $conn->prepare("
                    SELECT id
                    FROM auth_users
                    WHERE username = ?
                    LIMIT 1
                ");

            $stmt->bind_param(
                "s",
                $username
            );

            $stmt->execute();

            $result =
                $stmt->get_result();


            if ($result->num_rows > 0) {

                $error =
                    "Username sudah digunakan.";

                $stmt->close();

            } else {

                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | Cek nomor
                |--------------------------------------------------------------------------
                */

                $stmt =
                    $conn->prepare("
                        SELECT id
                        FROM auth_users
                        WHERE no_telepon = ?
                        LIMIT 1
                    ");

                $stmt->bind_param(
                    "s",
                    $noTelepon
                );

                $stmt->execute();

                $result =
                    $stmt->get_result();


                if ($result->num_rows > 0) {

                    $error =
                        "Nomor WhatsApp sudah digunakan.";

                    $stmt->close();

                } else {

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | Hash password
                    |--------------------------------------------------------------------------
                    */

                    $passwordHash =
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Role default USER
                    |--------------------------------------------------------------------------
                    */

                    $role = "User";


                    /*
                    |--------------------------------------------------------------------------
                    | INSERT
                    |--------------------------------------------------------------------------
                    */

                    $stmt =
                        $conn->prepare("
                            INSERT INTO auth_users
                            (
                                nama,
                                username,
                                no_telepon,
                                password,
                                role
                            )
                            VALUES
                            (?, ?, ?, ?, ?)
                        ");

                    $stmt->bind_param(
                        "sssss",
                        $nama,
                        $username,
                        $noTelepon,
                        $passwordHash,
                        $role
                    );


                    if ($stmt->execute()) {


                        /*
                        |--------------------------------------------------------------------------
                        | KIRIM WHATSAPP
                        |--------------------------------------------------------------------------
                        */

                        $pesanWA =
                            "Halo " .
                            $nama .
                            " 👋\n\n" .

                            "Registrasi akun UJIAN ASTS berhasil.\n\n" .

                            "Username: " .
                            $username .
                            "\n\n" .

                            "Akun Anda sudah dapat digunakan " .
                            "untuk login ke sistem UJIAN ASTS.\n\n" .

                            "Terima kasih.";


                        $hasilWA =
                            kirimWhatsApp(
                                $noTelepon,
                                $pesanWA
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Simpan status pesan
                        |--------------------------------------------------------------------------
                        */

                        if ($hasilWA['success']) {

                            $_SESSION['register_message'] =
                                "Registrasi berhasil. Notifikasi WhatsApp berhasil dikirim.";

                        } else {

                            $_SESSION['register_message'] =
                                "Registrasi berhasil, tetapi WhatsApp gagal dikirim.";
                        }


                        $stmt->close();


                        header(
                            "Location: login.php"
                        );

                        exit();


                    } else {

                        $error =
                            "Registrasi gagal: " .
                            $conn->error;

                        $stmt->close();
                    }
                }
            }
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

<title>
    Register - UJIAN ASTS
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family: Arial, sans-serif;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1e3a8a
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;
}


.box {

    width: 500px;

    max-width: 100%;

    background: white;

    padding: 35px;

    border-radius: 22px;

    box-shadow:
        0 25px 60px
        rgba(0,0,0,.20);
}


h1 {

    text-align: center;

    color: #0f172a;

    margin-bottom: 8px;
}


.subtitle {

    text-align: center;

    color: #64748b;

    margin-bottom: 25px;
}


.group {

    margin-bottom: 16px;
}


label {

    display: block;

    font-weight: bold;

    margin-bottom: 7px;

    color: #334155;
}


input {

    width: 100%;

    padding: 13px;

    border:
        1px solid
        #cbd5e1;

    border-radius: 10px;

    font-size: 15px;
}


button {

    width: 100%;

    padding: 14px;

    background: #2563eb;

    border: none;

    color: white;

    border-radius: 10px;

    font-weight: bold;

    cursor: pointer;

    margin-top: 8px;
}


.error {

    background: #fee2e2;

    color: #b91c1c;

    padding: 12px;

    border-radius: 10px;

    margin-bottom: 18px;
}


.login {

    text-align: center;

    margin-top: 20px;

    color: #64748b;
}


.login a {

    color: #2563eb;

    font-weight: bold;

    text-decoration: none;
}

</style>

</head>


<body>


<div class="box">


    <h1>
        📝 Registrasi
    </h1>


    <div class="subtitle">
        Buat akun UJIAN ASTS
    </div>


    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


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
                value="<?= htmlspecialchars($nama) ?>"
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
                value="<?= htmlspecialchars($username) ?>"
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
                value="<?= htmlspecialchars($noTelepon) ?>"
                placeholder="081234567890"
                required
            >

        </div>


        <div class="group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                required
            >

        </div>


        <div class="group">

            <label>
                Konfirmasi Password
            </label>

            <input
                type="password"
                name="password_confirm"
                required
            >

        </div>


        <button type="submit">

            📝 Daftar

        </button>


    </form>


    <div class="login">

        Sudah punya akun?

        <a href="login.php">
            Login
        </a>

    </div>


</div>


</body>

</html>