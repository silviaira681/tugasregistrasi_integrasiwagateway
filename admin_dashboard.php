<?php

require_once "config.php";
require_once "auth_check.php";


if (($_SESSION['role'] ?? '') !== 'Admin') {

    header("Location: dashboard.php");

    exit();
}

?>

<?php

require_once "config.php";
require_once "auth_check.php";

if (($_SESSION['role'] ?? '') !== 'Admin') {

    header("Location: user_dashboard.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$totalUser = 0;
$totalAdmin = 0;
$totalSemua = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM auth_users
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalSemua = $row['total'];

}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM auth_users
    WHERE role = 'User'
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalUser = $row['total'];

}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM auth_users
    WHERE role = 'Admin'
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalAdmin = $row['total'];

}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/

$resultNotif = $conn->query("
    SELECT COUNT(*) AS total
    FROM admin_notifications
    WHERE is_read = 0
");

$notifBelumDibaca = 0;

if ($resultNotif) {

    $row = $resultNotif->fetch_assoc();

    $notifBelumDibaca = $row['total'];

}


/*
|--------------------------------------------------------------------------
| USER TERBARU
|--------------------------------------------------------------------------
*/

$users = $conn->query("
    SELECT *
    FROM auth_users
    ORDER BY id DESC
");


/*
|--------------------------------------------------------------------------
| NOTIFIKASI TERBARU
|--------------------------------------------------------------------------
*/

$notifications = $conn->query("
    SELECT *
    FROM admin_notifications
    ORDER BY id DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

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
    width: 255px;
    height: 100vh;
    background:
        linear-gradient(
            180deg,
            #111827,
            #1e293b
        );
    color: white;
    padding: 25px 18px;
}

.logo {
    font-size: 21px;
    font-weight: bold;
    padding: 0 10px;
    margin-bottom: 35px;
}

.menu-title {
    color: #94a3b8;
    font-size: 11px;
    margin: 20px 10px 8px;
}

.sidebar a {
    display: block;
    padding: 12px;
    margin-bottom: 5px;
    color: #cbd5e1;
    text-decoration: none;
    border-radius: 10px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #2563eb;
    color: white;
}

.logout {
    color: #fca5a5 !important;
}

.main {
    margin-left: 255px;
    padding: 30px;
}

.topbar {
    background: white;
    border-radius: 17px;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar h1 {
    margin: 0;
}

.admin {
    font-weight: bold;
}

.welcome {
    margin-top: 25px;
    background:
        linear-gradient(
            135deg,
            #1d4ed8,
            #4f46e5
        );
    color: white;
    padding: 30px;
    border-radius: 20px;
}

.stats {
    display: grid;
    grid-template-columns:
        repeat(3, 1fr);
    gap: 20px;
    margin-top: 25px;
}

.stat {
    background: white;
    padding: 25px;
    border-radius: 17px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

.stat-icon {
    font-size: 30px;
}

.stat-number {
    font-size: 30px;
    font-weight: bold;
    margin-top: 10px;
}

.stat-title {
    color: #64748b;
}

.section {
    background: white;
    border-radius: 17px;
    padding: 25px;
    margin-top: 25px;
    overflow-x: auto;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification {
    padding: 14px;
    border-radius: 12px;
    background: #eff6ff;
    margin-top: 10px;
    border-left: 4px solid #2563eb;
}

.notification.unread {
    background: #dbeafe;
}

.notification small {
    color: #64748b;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    min-width: 750px;
}

th {
    background: #f8fafc;
    color: #475569;
}

th,
td {
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.badge {
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.badge-admin {
    background: #ede9fe;
    color: #6d28d9;
}

.badge-user {
    background: #dcfce7;
    color: #166534;
}

.btn {
    padding: 7px 10px;
    border-radius: 7px;
    text-decoration: none;
    font-size: 12px;
}

.edit {
    background: #dbeafe;
    color: #1d4ed8;
}

.delete {
    background: #fee2e2;
    color: #b91c1c;
    border: 0;
    cursor: pointer;
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

    .stats {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo">
        🛡️ ADMIN UJIAN ASTS
    </div>

    <div class="menu-title">
        ADMINISTRATOR
    </div>

    <a
        href="admin_dashboard.php"
        class="active"
    >
        🏠 Dashboard
    </a>

    <a href="profil.php">
        👤 Profil Saya
    </a>

    <a href="daftar_teman.php">
        👥 Data User
    </a>

    <a href="tambah_user.php">
        ➕ Tambah User
    </a>

    <div class="menu-title">
        DATA
    </div>

    <a href="tabel_data.php">
        📊 Tabel Data
    </a>

    <a href="data_json.php">
        🔗 Data JSON
    </a>

    <div class="menu-title">
        AKUN
    </div>

    <a
        href="logout.php"
        class="logout"
        onclick="return confirm('Yakin ingin logout?')"
    >
        🚪 Logout
    </a>

</div>

<div class="main">

    <div class="topbar">

        <h1>Dashboard Admin</h1>

        <div class="admin">
            🛡️ <?= htmlspecialchars($_SESSION['nama']) ?>
        </div>

    </div>

    <div class="welcome">

        <h2>
            Selamat datang, Administrator 👋
        </h2>

        <p>
            Kelola seluruh akun pengguna dari halaman ini.
        </p>

    </div>

    <div class="stats">

        <div class="stat">

            <div class="stat-icon">
                👥
            </div>

            <div class="stat-number">
                <?= $totalSemua ?>
            </div>

            <div class="stat-title">
                Total Semua Akun
            </div>

        </div>

        <div class="stat">

            <div class="stat-icon">
                👨‍🎓
            </div>

            <div class="stat-number">
                <?= $totalUser ?>
            </div>

            <div class="stat-title">
                Total User
            </div>

        </div>

        <div class="stat">

            <div class="stat-icon">
                🔐
            </div>

            <div class="stat-number">
                <?= $totalAdmin ?>
            </div>

            <div class="stat-title">
                Total Admin
            </div>

        </div>

    </div>

    <div class="section">

        <div class="section-header">

            <h2>
                🔔 Notifikasi
            </h2>

            <strong>
                <?= $notifBelumDibaca ?>
                belum dibaca
            </strong>

        </div>

        <?php if ($notifications && $notifications->num_rows > 0): ?>

            <?php while ($notif = $notifications->fetch_assoc()): ?>

                <div class="notification <?= $notif['is_read'] == 0 ? 'unread' : '' ?>">

                    <strong>
                        <?= htmlspecialchars($notif['title']) ?>
                    </strong>

                    <p>
                        <?= htmlspecialchars($notif['message']) ?>
                    </p>

                    <small>
                        <?= htmlspecialchars($notif['created_at']) ?>
                    </small>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>
                Belum ada notifikasi.
            </p>

        <?php endif; ?>

    </div>

    <div class="section">

        <div class="section-header">

            <h2>
                👥 Semua Pengguna
            </h2>

            <a
                href="tambah_user.php"
                class="btn edit"
            >
                + Tambah User
            </a>

        </div>

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>No. Telepon</th>
                    <th>Role</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($users && $users->num_rows > 0): ?>

                <?php while ($user = $users->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $user['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['nama']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['username']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['no_telepon']) ?>
                        </td>

                        <td>

                            <?php if ($user['role'] === 'Admin'): ?>

                                <span class="badge badge-admin">
                                    Admin
                                </span>

                            <?php else: ?>

                                <span class="badge badge-user">
                                    User
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= htmlspecialchars($user['created_at']) ?>
                        </td>

                        <td>

                            <a
                                href="admin_edit_user.php?id=<?= $user['id'] ?>"
                                class="btn edit"
                            >
                                Edit
                            </a>

                            <?php if ($user['id'] != $_SESSION['id']): ?>

                                <form
                                    action="admin_hapus_user.php"
                                    method="POST"
                                    style="display:inline"
                                    onsubmit="return confirm('Yakin ingin menghapus user ini?')"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= $user['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn delete"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7">
                        Belum ada data user.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include "notification.php"; ?>

</body>

</html>