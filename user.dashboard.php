<?php

require_once "config.php";
require_once "auth_check.php";

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$noTelepon = $_SESSION['no_telepon'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Dashboard User</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #0f172a;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background:
        linear-gradient(
            180deg,
            #0f172a,
            #1e293b
        );
    color: white;
    padding: 25px 18px;
}

.logo {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 35px;
    padding: 0 10px;
}

.menu-title {
    font-size: 11px;
    color: #94a3b8;
    margin: 20px 10px 8px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 5px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #2563eb;
    color: white;
}

.main {
    margin-left: 250px;
    padding: 30px;
}

.topbar {
    background: white;
    border-radius: 16px;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 20px rgba(15,23,42,.06);
}

.topbar h1 {
    margin: 0;
    font-size: 23px;
}

.user {
    font-weight: bold;
}

.hero {
    margin-top: 25px;
    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );
    color: white;
    padding: 30px;
    border-radius: 20px;
}

.hero h2 {
    margin-top: 0;
}

.cards {
    display: grid;
    grid-template-columns:
        repeat(3, 1fr);
    gap: 20px;
    margin-top: 25px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 17px;
    box-shadow: 0 5px 20px rgba(15,23,42,.06);
}

.card-icon {
    font-size: 30px;
}

.card h3 {
    margin-bottom: 8px;
}

.card p {
    color: #64748b;
}

.btn {
    display: inline-block;
    background: #2563eb;
    color: white;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 9px;
}

.logout {
    color: #fca5a5 !important;
}

@media(max-width:900px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
    }

    .cards {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo">
        📚 UJIAN ASTS
    </div>

    <div class="menu-title">
        MENU UTAMA
    </div>

    <a href="user_dashboard.php"
       class="active">
        🏠 Dashboard
    </a>

    <a href="profil.php">
        👤 Profil Saya
    </a>

    <a href="daftar_teman.php">
        👥 Daftar Teman
    </a>

    <a href="tambah_user.php">
        ➕ Tambah User
    </a>

    <a href="tabel_data.php">
        📊 Tabel Data
    </a>

    <a href="data_json.php">
        🔗 Data JSON
    </a>

    <div class="menu-title">
        AKUN
    </div>

    <a href="logout.php"
       class="logout"
       onclick="return confirm('Yakin ingin logout?')">
        🚪 Logout
    </a>

</div>

<div class="main">

    <div class="topbar">

        <h1>Dashboard</h1>

        <div class="user">
            👤 <?= htmlspecialchars($nama) ?>
        </div>

    </div>

    <div class="hero">

        <h2>
            Selamat datang, <?= htmlspecialchars($nama) ?>! 👋
        </h2>

        <p>
            Anda berhasil login ke sistem UJIAN ASTS.
        </p>

        <p>
            Username:
            <strong><?= htmlspecialchars($username) ?></strong>
            <br>

            Nomor:
            <strong><?= htmlspecialchars($noTelepon) ?></strong>
        </p>

    </div>

    <div class="cards">

        <div class="card">

            <div class="card-icon">
                👤
            </div>

            <h3>Profil Saya</h3>

            <p>
                Lihat dan ubah data pribadi akun.
            </p>

            <a
                href="profil.php"
                class="btn"
            >
                Buka Profil
            </a>

        </div>

        <div class="card">

            <div class="card-icon">
                👥
            </div>

            <h3>Daftar Teman</h3>

            <p>
                Melihat data teman yang tersimpan.
            </p>

            <a
                href="daftar_teman.php"
                class="btn"
            >
                Lihat Data
            </a>

        </div>

        <div class="card">

            <div class="card-icon">
                📊
            </div>

            <h3>Tabel Data</h3>

            <p>
                Melihat data dalam bentuk tabel.
            </p>

            <a
                href="tabel_data.php"
                class="btn"
            >
                Buka Tabel
            </a>

        </div>

    </div>

</div>

<?php include "notification.php"; ?>

</body>

</html>