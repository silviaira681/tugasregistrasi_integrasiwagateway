<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/config.php";


/*
|--------------------------------------------------------------------------
| CEK KONEKSI DATABASE
|--------------------------------------------------------------------------
| Mendukung:
| $conn
| $koneksi
|--------------------------------------------------------------------------
*/

$db = null;

if (isset($conn) && $conn instanceof mysqli) {
    $db = $conn;
}

if ($db === null && isset($koneksi) && $koneksi instanceof mysqli) {
    $db = $koneksi;
}

if ($db === null) {
    die("
        <div style='
            font-family:Arial;
            padding:30px;
            color:#991b1b;
            background:#fee2e2;
            margin:30px;
            border-radius:10px;
        '>
            <h2>Koneksi Database Tidak Ditemukan</h2>
            <p>
                File config.php harus menyediakan koneksi MySQLi
                dengan variabel <b>\$conn</b> atau <b>\$koneksi</b>.
            </p>
        </div>
    ");
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/

$data = [];
$error = "";

$sql = "
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
    ORDER BY id DESC
";

$result = $db->query($sql);

if ($result === false) {

    $error = "Gagal mengambil data: " . $db->error;

} else {

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $result->free();
}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$totalUser = count($data);

$totalLaki = 0;
$totalPerempuan = 0;

foreach ($data as $user) {

    $gender = strtolower(trim($user['gender'] ?? ''));

    if (
        $gender === 'laki-laki' ||
        $gender === 'laki laki' ||
        $gender === 'laki' ||
        $gender === 'male' ||
        $gender === 'pria'
    ) {
        $totalLaki++;
    }

    if (
        $gender === 'perempuan' ||
        $gender === 'wanita' ||
        $gender === 'female'
    ) {
        $totalPerempuan++;
    }
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/

$pesan = $_SESSION['pesan'] ?? "";

$tipePesan = $_SESSION['tipe_pesan'] ?? "success";

unset($_SESSION['pesan']);
unset($_SESSION['tipe_pesan']);


/*
|--------------------------------------------------------------------------
| DATA LOGIN
|--------------------------------------------------------------------------
*/

$namaLogin = $_SESSION['nama'] ?? "Administrator";

$roleLogin = $_SESSION['role'] ?? "Admin";


/*
|--------------------------------------------------------------------------
| FUNGSI ESCAPE
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
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

    <title>Daftar Teman - Ujian ASTS</title>


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


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            position: fixed;

            left: 0;
            top: 0;

            width: 250px;
            height: 100vh;

            background: #111827;

            color: white;

            padding: 25px 15px;

            z-index: 1000;
        }


        .logo {
            padding:
                0 15px 25px;

            border-bottom:
                1px solid
                rgba(255,255,255,.1);

            margin-bottom: 20px;
        }


        .logo h2 {
            font-size: 21px;

            margin-bottom: 5px;
        }


        .logo p {
            color: #9ca3af;

            font-size: 13px;
        }


        .menu-title {
            color: #6b7280;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;

            padding: 10px 15px;

            margin-top: 10px;
        }


        .menu a {
            display: flex;

            align-items: center;

            gap: 12px;

            color: #d1d5db;

            text-decoration: none;

            padding: 12px 15px;

            margin: 4px 0;

            border-radius: 8px;

            font-size: 14px;

            transition: .2s;
        }


        .menu a:hover {
            background: #1f2937;

            color: white;
        }


        .menu a.active {
            background: #2563eb;

            color: white;
        }


        .menu-icon {
            width: 22px;

            text-align: center;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {
            margin-left: 250px;

            min-height: 100vh;
        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {
            min-height: 75px;

            background: white;

            border-bottom:
                1px solid #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 15px 30px;

            gap: 20px;
        }


        .topbar-left h1 {
            font-size: 22px;

            color: #111827;
        }


        .topbar-left p {
            color: #6b7280;

            font-size: 13px;

            margin-top: 4px;
        }


        .profile {
            display: flex;

            align-items: center;

            gap: 12px;
        }


        .profile-icon {
            width: 40px;
            height: 40px;

            background: #2563eb;

            color: white;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: bold;
        }


        .profile-info strong {
            display: block;

            font-size: 14px;
        }


        .profile-info span {
            display: block;

            color: #6b7280;

            font-size: 12px;

            margin-top: 2px;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {
            padding: 30px;
        }


        .page-title {
            margin-bottom: 25px;
        }


        .page-title h2 {
            font-size: 25px;

            color: #111827;

            margin-bottom: 7px;
        }


        .page-title p {
            color: #6b7280;

            font-size: 14px;
        }


        /* =====================================================
           ALERT
        ===================================================== */

        .alert {
            padding: 14px 18px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .alert-success {
            background: #dcfce7;

            color: #166534;

            border:
                1px solid #bbf7d0;
        }


        .alert-error {
            background: #fee2e2;

            color: #991b1b;

            border:
                1px solid #fecaca;
        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }


        .stat-card {
            background: white;

            border-radius: 12px;

            padding: 22px;

            border:
                1px solid #e5e7eb;

            display: flex;

            align-items: center;

            gap: 16px;

            box-shadow:
                0 2px 8px
                rgba(0,0,0,.03);
        }


        .stat-icon {
            width: 50px;
            height: 50px;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;
        }


        .stat-blue {
            background: #dbeafe;
        }


        .stat-pink {
            background: #fce7f3;
        }


        .stat-info span {
            color: #6b7280;

            font-size: 13px;
        }


        .stat-info strong {
            display: block;

            font-size: 25px;

            margin-top: 3px;

            color: #111827;
        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .table-card {
            background: white;

            border-radius: 12px;

            border:
                1px solid #e5e7eb;

            overflow: hidden;

            box-shadow:
                0 2px 8px
                rgba(0,0,0,.03);
        }


        .table-header {
            padding: 20px 22px;

            border-bottom:
                1px solid #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;
        }


        .table-header h3 {
            font-size: 18px;

            color: #111827;
        }


        .table-header p {
            font-size: 13px;

            color: #6b7280;

            margin-top: 4px;
        }


        .btn-tambah {
            display: inline-block;

            background: #2563eb;

            color: white;

            text-decoration: none;

            padding: 10px 16px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: bold;
        }


        .btn-tambah:hover {
            background: #1d4ed8;
        }


        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }


        table {
            width: 100%;

            min-width: 1250px;

            border-collapse: collapse;
        }


        thead {
            background: #f8fafc;
        }


        th {
            padding: 14px 12px;

            text-align: left;

            font-size: 12px;

            color: #475569;

            text-transform: uppercase;

            white-space: nowrap;

            border-bottom:
                1px solid #e5e7eb;
        }


        td {
            padding: 14px 12px;

            border-bottom:
                1px solid #eef2f7;

            font-size: 13px;

            vertical-align: middle;
        }


        tbody tr:hover {
            background: #f8fafc;
        }


        tbody tr:last-child td {
            border-bottom: none;
        }


        .id {
            color: #64748b;

            font-weight: bold;
        }


        .nama {
            font-weight: bold;

            color: #111827;
        }


        .email {
            color: #2563eb;
        }


        .no-hp {
            color: #059669;

            white-space: nowrap;
        }


        .alamat {
            max-width: 220px;

            line-height: 1.5;
        }


        /* =====================================================
           GENDER
        ===================================================== */

        .badge {
            display: inline-block;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            white-space: nowrap;
        }


        .badge-laki {
            background: #dbeafe;

            color: #1d4ed8;
        }


        .badge-perempuan {
            background: #fce7f3;

            color: #be185d;
        }


        .badge-default {
            background: #f1f5f9;

            color: #475569;
        }


        /* =====================================================
           AKSI
        ===================================================== */

        .aksi {
            white-space: nowrap;

            min-width: 165px;
        }


        .btn-edit,
        .btn-delete {
            display: inline-block;

            padding: 7px 10px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

            border: none;

            cursor: pointer;

            transition: .2s;
        }


        .btn-edit {
            background: #2563eb;

            color: white;
        }


        .btn-edit:hover {
            background: #1d4ed8;
        }


        .btn-delete {
            background: #dc2626;

            color: white;
        }


        .btn-delete:hover {
            background: #b91c1c;
        }


        .form-delete {
            display: inline;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {
            text-align: center;

            padding: 50px 20px;

            color: #64748b;
        }


        .empty-icon {
            font-size: 40px;

            margin-bottom: 10px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            text-align: center;

            padding: 25px;

            color: #94a3b8;

            font-size: 12px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 700px) {

            .sidebar {
                position: relative;

                width: 100%;

                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .topbar {
                padding: 15px;

                height: auto;
            }

            .content {
                padding: 15px;
            }

            .table-header {
                align-items: flex-start;

                flex-direction: column;
            }

            .profile-info {
                display: none;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            ASTS System
        </h2>

        <p>
            Manajemen Data Pengguna
        </p>

    </div>


    <div class="menu-title">
        Menu Utama
    </div>


    <nav class="menu">


        <a href="dashboard.php">

            <span class="menu-icon">
                🏠
            </span>

            Dashboard

        </a>


        <a
            href="daftar_teman.php"
            class="active"
        >

            <span class="menu-icon">
                👥
            </span>

            Daftar Teman

        </a>


        <a href="tambah_user.php">

            <span class="menu-icon">
                ➕
            </span>

            Tambah User

        </a>


    </nav>


    <div class="menu-title">
        Akun
    </div>


    <nav class="menu">


        <a
            href="logout.php"
            onclick="
                return confirm(
                    'Yakin ingin keluar dari sistem?'
                );
            "
        >

            <span class="menu-icon">
                🚪
            </span>

            Logout

        </a>


    </nav>


</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="topbar-left">

            <h1>
                Daftar Teman
            </h1>

            <p>
                Kelola dan lihat seluruh data pengguna
            </p>

        </div>


        <div class="profile">


            <div class="profile-icon">

                <?= e(
                    strtoupper(
                        substr(
                            $namaLogin,
                            0,
                            1
                        )
                    )
                ) ?>

            </div>


            <div class="profile-info">

                <strong>
                    <?= e($namaLogin) ?>
                </strong>

                <span>
                    <?= e($roleLogin) ?>
                </span>

            </div>


        </div>


    </header>



    <!-- CONTENT -->

    <section class="content">


        <!-- TITLE -->

        <div class="page-title">

            <h2>
                Daftar Teman
            </h2>

            <p>
                Kelola data pengguna yang
                telah terdaftar di sistem.
            </p>

        </div>



        <!-- NOTIFICATION -->

        <?php if (!empty($pesan)): ?>

            <div
                class="
                    alert
                    <?= $tipePesan === 'error'
                        ? 'alert-error'
                        : 'alert-success'
                    ?>
                "
            >

                <?= e($pesan) ?>

            </div>

        <?php endif; ?>



        <!-- DATABASE ERROR -->

        <?php if (!empty($error)): ?>

            <div class="alert alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>



        <!-- STATISTICS -->

        <div class="stats">


            <!-- TOTAL -->

            <div class="stat-card">

                <div class="stat-icon stat-blue">
                    👥
                </div>

                <div class="stat-info">

                    <span>
                        Total User
                    </span>

                    <strong>
                        <?= $totalUser ?>
                    </strong>

                </div>

            </div>


            <!-- LAKI -->

            <div class="stat-card">

                <div class="stat-icon stat-blue">
                    👨
                </div>

                <div class="stat-info">

                    <span>
                        Laki-laki
                    </span>

                    <strong>
                        <?= $totalLaki ?>
                    </strong>

                </div>

            </div>


            <!-- PEREMPUAN -->

            <div class="stat-card">

                <div class="stat-icon stat-pink">
                    👩
                </div>

                <div class="stat-info">

                    <span>
                        Perempuan
                    </span>

                    <strong>
                        <?= $totalPerempuan ?>
                    </strong>

                </div>

            </div>


        </div>



        <!-- TABLE -->

        <div class="table-card">


            <div class="table-header">


                <div>

                    <h3>
                        Data Seluruh Pengguna
                    </h3>

                    <p>
                        Menampilkan
                        <?= $totalUser ?>
                        data pengguna
                    </p>

                </div>


                <a
                    href="tambah_user.php"
                    class="btn-tambah"
                >

                    + Tambah User

                </a>


            </div>



            <div class="table-wrapper">


                <?php if (count($data) > 0): ?>


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Nama
                                </th>

                                <th>
                                    NISN
                                </th>

                                <th>
                                    TTL
                                </th>

                                <th>
                                    Gender
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    No. WhatsApp
                                </th>

                                <th>
                                    Alamat
                                </th>

                                <th>
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($data as $row): ?>


                            <?php

                            $gender =
                                strtolower(
                                    trim(
                                        $row['gender'] ?? ''
                                    )
                                );


                            if (
                                $gender === 'laki-laki' ||
                                $gender === 'laki laki' ||
                                $gender === 'laki' ||
                                $gender === 'male' ||
                                $gender === 'pria'
                            ) {

                                $genderClass =
                                    "badge-laki";

                            } elseif (
                                $gender === 'perempuan' ||
                                $gender === 'wanita' ||
                                $gender === 'female'
                            ) {

                                $genderClass =
                                    "badge-perempuan";

                            } else {

                                $genderClass =
                                    "badge-default";
                            }

                            ?>


                            <tr>


                                <!-- ID -->

                                <td class="id">

                                    #<?= (int)$row['id'] ?>

                                </td>


                                <!-- NAMA -->

                                <td class="nama">

                                    <?= e(
                                        $row['name'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- NISN -->

                                <td>

                                    <?= e(
                                        $row['nisn'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- TTL -->

                                <td>

                                    <?= e(
                                        $row['ttl'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- GENDER -->

                                <td>

                                    <span
                                        class="
                                            badge
                                            <?= $genderClass ?>
                                        "
                                    >

                                        <?= e(
                                            $row['gender'] ?? '-'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- EMAIL -->

                                <td class="email">

                                    <?= e(
                                        $row['email'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- NO HP -->

                                <td class="no-hp">

                                    <?php
                                    if (
                                        !empty(
                                            $row['no_hp']
                                        )
                                    ):
                                    ?>

                                        <?= e(
                                            $row['no_hp']
                                        ) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <!-- ALAMAT -->

                                <td class="alamat">

                                    <?= e(
                                        $row['address'] ?? '-'
                                    ) ?>

                                </td>


                                <!-- AKSI -->

                                <td class="aksi">


                                    <!-- EDIT -->

                                    <a
                                        href="
                                            edit_user.php?id=
                                            <?= (int)$row['id'] ?>
                                        "
                                        class="btn-edit"
                                    >

                                        ✏️ Edit

                                    </a>


                                    <!-- HAPUS -->

                                    <form
                                        action="hapus_user.php"
                                        method="POST"
                                        class="form-delete"
                                        onsubmit="
                                            return confirm(
                                                'Yakin ingin menghapus user ini? Data yang dihapus tidak dapat dikembalikan.'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row['id'] ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="btn-delete"
                                        >

                                            🗑️ Hapus

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                <?php else: ?>


                    <div class="empty">


                        <div class="empty-icon">
                            👥
                        </div>


                        <h3>
                            Belum Ada Data User
                        </h3>


                        <p>
                            Silakan tambahkan
                            user terlebih dahulu.
                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </div>



        <!-- FOOTER -->

        <div class="footer">

            Sistem Manajemen Data User
            &copy;
            <?= date('Y') ?>

        </div>


    </section>


</main>


</body>

</html>