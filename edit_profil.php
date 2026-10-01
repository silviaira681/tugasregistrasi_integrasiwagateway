<?php

require_once "config.php";
require_once "auth_check.php";
require_once "fonnte.php";

$id = $_SESSION['id'];

$stmt = $conn->prepare("
    SELECT *
    FROM auth_users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaBaru = trim($_POST['nama'] ?? '');
    $usernameBaru = trim($_POST['username'] ?? '');
    $noTeleponBaru = trim($_POST['no_telepon'] ?? '');
    $passwordBaru = $_POST['password'] ?? '';

    if (
        $namaBaru === '' ||
        $usernameBaru === '' ||
        $noTeleponBaru === ''
    ) {

        $error = "Nama, username, dan nomor telepon wajib diisi.";

    } else {

        $cek = $conn->prepare("
            SELECT id
            FROM auth_users
            WHERE (username = ? OR no_telepon = ?)
            AND id <> ?
            LIMIT 1
        ");

        $cek->bind_param(
            "ssi",
            $usernameBaru,
            $noTeleponBaru,
            $id
        );

        $cek->execute();

        $hasil = $cek->get_result();

        if ($hasil->num_rows > 0) {

            $error =
                "Username atau nomor telepon sudah digunakan.";

        } else {

            $perubahan = [];

            if ($user['nama'] !== $namaBaru) {

                $perubahan[] =
                    "Nama: " .
                    $user['nama'] .
                    " → " .
                    $namaBaru;

            }

            if ($user['username'] !== $usernameBaru) {

                $perubahan[] =
                    "Username: " .
                    $user['username'] .
                    " → " .
                    $usernameBaru;

            }

            if ($user['no_telepon'] !== $noTeleponBaru) {

                $perubahan[] =
                    "Nomor Telepon: " .
                    $user['no_telepon'] .
                    " → " .
                    $noTeleponBaru;

            }

            if ($passwordBaru !== '') {

                if (strlen($passwordBaru) < 6) {

                    $error =
                        "Password baru minimal 6 karakter.";

                } else {

                    $perubahan[] =
                        "Password berhasil diubah.";

                }

            }

            if ($error === '') {

                if ($passwordBaru !== '') {

                    $passwordHash =
                        password_hash(
                            $passwordBaru,
                            PASSWORD_DEFAULT
                        );

                    $update = $conn->prepare("
                        UPDATE auth_users
                        SET nama = ?,
                            username = ?,
                            no_telepon = ?,
                            password = ?
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        "ssssi",
                        $namaBaru,
                        $usernameBaru,
                        $noTeleponBaru,
                        $passwordHash,
                        $id
                    );

                } else {

                    $update = $conn->prepare("
                        UPDATE auth_users
                        SET nama = ?,
                            username = ?,
                            no_telepon = ?
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        "sssi",
                        $namaBaru,
                        $usernameBaru,
                        $noTeleponBaru,
                        $id
                    );

                }

                if ($update->execute()) {

                    if (count($perubahan) === 0) {

                        $pesan =
                            "Data anda berhasil disimpan. Tidak ada perubahan data.";

                    } else {

                        $pesan =
                            "Data anda berhasil diubah: " .
                            implode(", ", $perubahan);

                    }

                    $_SESSION['nama'] = $namaBaru;
                    $_SESSION['username'] = $usernameBaru;
                    $_SESSION['no_telepon'] = $noTeleponBaru;

                    $_SESSION['flash_type'] = "success";

                    $_SESSION['flash_message'] = $pesan;

                    kirimWhatsApp(
                        $noTeleponBaru,
                        "Data akun Anda berhasil diubah.\n\n" .
                        implode("\n", $perubahan)
                    );

                    header("Location: profil.php");
                    exit();

                } else {

                    $error =
                        "Gagal mengubah data: " .
                        $conn->error;

                }

                $update->close();

            }

        }

        $cek->close();

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Profil</title>

<style>

body {
    margin: 0;
    background: #f1f5f9;
    font-family: Arial, sans-serif;
}

.container {
    width: 650px;
    max-width: 94%;
    margin: 40px auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.group {
    margin-bottom: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
}

button,
a {
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    border: 0;
}

button {
    background: #2563eb;
    color: white;
    cursor: pointer;
}

.back {
    background: #e2e8f0;
    color: #334155;
}

.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 15px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>✏️ Edit Profil</h1>

<p>
Ubah data akun Anda di bawah ini.
</p>

<?php if ($error !== ''): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST">

<div class="group">

<label>Nama</label>

<input
    type="text"
    name="nama"
    value="<?= htmlspecialchars($user['nama']) ?>"
    required
>

</div>

<div class="group">

<label>Username</label>

<input
    type="text"
    name="username"
    value="<?= htmlspecialchars($user['username']) ?>"
    required
>

</div>

<div class="group">

<label>Nomor Telepon</label>

<input
    type="text"
    name="no_telepon"
    value="<?= htmlspecialchars($user['no_telepon']) ?>"
    required
>

</div>

<div class="group">

<label>Password Baru</label>

<input
    type="password"
    name="password"
    placeholder="Kosongkan jika tidak ingin mengubah"
>

</div>

<button type="submit">
💾 Simpan Perubahan
</button>

<a
    href="profil.php"
    class="back"
>
Batal
</a>

</form>

</div>

</div>

<?php include "notification.php"; ?>

</body>

</html>