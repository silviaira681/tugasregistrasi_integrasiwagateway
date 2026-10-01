<?php

require_once "config.php";
require_once "auth_check.php";

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
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Profil Saya</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
}

.container {
    width: 700px;
    max-width: 94%;
    margin: 50px auto;
}

.card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.avatar {
    width: 80px;
    height: 80px;
    background: #2563eb;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 20px;
}

h1 {
    margin-bottom: 25px;
}

.row {
    padding: 15px 0;
    border-bottom: 1px solid #e2e8f0;
}

.label {
    color: #64748b;
    font-size: 13px;
}

.value {
    font-weight: bold;
    margin-top: 5px;
}

.buttons {
    margin-top: 25px;
}

.btn {
    display: inline-block;
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    margin-right: 8px;
}

.primary {
    background: #2563eb;
    color: white;
}

.secondary {
    background: #e2e8f0;
    color: #334155;
}

.danger {
    background: #ef4444;
    color: white;
    border: 0;
    cursor: pointer;
}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="avatar">
            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
        </div>

        <h1>Profil Saya</h1>

        <div class="row">

            <div class="label">
                Nama
            </div>

            <div class="value">
                <?= htmlspecialchars($user['nama']) ?>
            </div>

        </div>

        <div class="row">

            <div class="label">
                Username
            </div>

            <div class="value">
                <?= htmlspecialchars($user['username']) ?>
            </div>

        </div>

        <div class="row">

            <div class="label">
                Nomor Telepon
            </div>

            <div class="value">
                <?= htmlspecialchars($user['no_telepon']) ?>
            </div>

        </div>

        <div class="row">

            <div class="label">
                Role
            </div>

            <div class="value">
                <?= htmlspecialchars($user['role']) ?>
            </div>

        </div>

        <div class="buttons">

            <a
                href="edit_profil.php"
                class="btn primary"
            >
                ✏️ Edit Profil
            </a>

            <a
                href="dashboard.php"
                class="btn secondary"
            >
                ← Dashboard
            </a>

            <?php if ($user['role'] !== 'Admin'): ?>

                <form
                    action="hapus_akun.php"
                    method="POST"
                    style="display:inline;"
                    onsubmit="return confirm('Yakin ingin menghapus akun? Data akun akan dihapus.')"
                >

                    <button
                        type="submit"
                        class="btn danger"
                    >
                        🗑️ Hapus Akun
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include "notification.php"; ?>

</body>

</html>