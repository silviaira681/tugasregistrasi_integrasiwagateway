<?php

require_once "auth_check.php";
require_once "config.php";


/* =========================================================
   DATA LOGIN
========================================================= */

$namaAdmin = $_SESSION['nama'] ?? 'Administrator';
$roleAdmin = $_SESSION['role'] ?? 'Admin';

$namaBersih = trim($namaAdmin);

$inisial = strtoupper(
    substr($namaBersih, 0, 1)
);


/* =========================================================
   PENCARIAN
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


/* =========================================================
   PAGINATION
========================================================= */

$limit = 10;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;


/* =========================================================
   TOTAL DATA
========================================================= */

if ($search !== '') {

    $searchSql = "%" . $conn->real_escape_string($search) . "%";

    $countQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM users
        WHERE
            name LIKE '$searchSql'
            OR nisn LIKE '$searchSql'
            OR ttl LIKE '$searchSql'
            OR gender LIKE '$searchSql'
            OR email LIKE '$searchSql'
            OR address LIKE '$searchSql'
    ");

} else {

    $countQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM users
    ");

}


$totalData = 0;

if ($countQuery) {

    $countRow = $countQuery->fetch_assoc();

    $totalData = (int) $countRow['total'];

}


$totalPage = $totalData > 0
    ? ceil($totalData / $limit)
    : 1;


/* =========================================================
   DATA USER
========================================================= */

$users = [];


if ($search !== '') {

    $searchSql = "%" . $conn->real_escape_string($search) . "%";

    $query = $conn->query("
        SELECT
            id,
            name,
            nisn,
            ttl,
            gender,
            email,
            address
        FROM users
        WHERE
            name LIKE '$searchSql'
            OR nisn LIKE '$searchSql'
            OR ttl LIKE '$searchSql'
            OR gender LIKE '$searchSql'
            OR email LIKE '$searchSql'
            OR address LIKE '$searchSql'
        ORDER BY id DESC
        LIMIT $limit OFFSET $offset
    ");

} else {

    $query = $conn->query("
        SELECT
            id,
            name,
            nisn,
            ttl,
            gender,
            email,
            address
        FROM users
        ORDER BY id DESC
        LIMIT $limit OFFSET $offset
    ");

}


if ($query) {

    while ($row = $query->fetch_assoc()) {

        $users[] = $row;

    }

}


/* =========================================================
   NOMOR DATA
========================================================= */

$nomorAwal = $offset + 1;

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
        Tabel Data - UJIAN ASTS
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;

            color: #1e293b;

            min-height: 100vh;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;

            width: 255px;

            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #0f172a 0%,
                    #1e293b 100%
                );

            color: white;

            padding: 24px 17px;

            z-index: 1000;

            overflow-y: auto;
        }


        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                5px 10px 28px;
        }


        .brand-icon {

            width: 45px;
            height: 45px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #3b82f6,
                    #6366f1
                );

            font-size: 21px;

            flex-shrink: 0;
        }


        .brand-text h2 {

            font-size: 17px;

            margin-bottom: 4px;
        }


        .brand-text span {

            color: #94a3b8;

            font-size: 10px;
        }


        .menu-title {

            color: #64748b;

            font-size: 10px;

            font-weight: bold;

            letter-spacing: 1px;

            padding:
                15px 12px 8px;
        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                12px 13px;

            margin-bottom: 5px;

            border-radius: 10px;

            color: #cbd5e1;

            text-decoration: none;

            font-size: 14px;

            transition: .2s;
        }


        .menu a:hover {

            background:
                rgba(255,255,255,.08);

            color: white;

            transform:
                translateX(3px);
        }


        .menu a.active {

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5
                );

            color: white;

            box-shadow:
                0 5px 15px
                rgba(37,99,235,.25);
        }


        .menu-icon {

            width: 25px;

            text-align: center;

            font-size: 17px;

            flex-shrink: 0;
        }


        .logout {

            margin-top: 30px;
        }


        .logout a {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                12px 13px;

            border-radius: 10px;

            color: #fca5a5;

            background:
                rgba(239,68,68,.08);

            text-decoration: none;

            font-size: 14px;

            transition: .2s;
        }


        .logout a:hover {

            background:
                rgba(239,68,68,.16);

            color: #fecaca;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 255px;

            min-height: 100vh;

            padding:
                28px 32px;
        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;
        }


        .page-title h1 {

            font-size: 27px;

            color: #0f172a;

            margin-bottom: 5px;
        }


        .page-title p {

            font-size: 13px;

            color: #64748b;
        }


        .profile {

            display: flex;

            align-items: center;

            gap: 10px;

            background: white;

            padding:
                7px 13px 7px 7px;

            border-radius: 12px;

            box-shadow:
                0 3px 15px
                rgba(15,23,42,.06);
        }


        .profile-avatar {

            width: 40px;
            height: 40px;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #3b82f6,
                    #6366f1
                );

            color: white;

            display: flex;

            align-items: center;
            justify-content: center;

            font-weight: bold;
        }


        .profile-info strong {

            display: block;

            font-size: 13px;
        }


        .profile-info span {

            display: block;

            margin-top: 3px;

            color: #64748b;

            font-size: 11px;
        }


        /* =====================================================
           STAT CARD
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 16px;

            margin-bottom: 22px;
        }


        .stat-card {

            background: white;

            padding: 18px;

            border-radius: 14px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.05);

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .stat-card span {

            display: block;

            color: #64748b;

            font-size: 12px;

            margin-bottom: 7px;
        }


        .stat-card strong {

            font-size: 24px;

            color: #0f172a;
        }


        .stat-icon {

            width: 45px;
            height: 45px;

            border-radius: 11px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 20px;
        }


        .blue {
            background: #dbeafe;
        }


        .purple {
            background: #ede9fe;
        }


        .green {
            background: #dcfce7;
        }


        /* =====================================================
           CONTENT CARD
        ===================================================== */

        .content-card {

            background: white;

            border-radius: 16px;

            padding: 22px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.05);
        }


        /* =====================================================
           HEADER CONTENT
        ===================================================== */

        .content-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 20px;
        }


        .content-title h2 {

            font-size: 18px;

            color: #0f172a;

            margin-bottom: 5px;
        }


        .content-title p {

            color: #94a3b8;

            font-size: 12px;
        }


        /* =====================================================
           BUTTON TAMBAH
        ===================================================== */

        .btn-tambah {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            height: 38px;

            padding:
                0 15px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5
                );

            color: white;

            border-radius: 8px;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

            white-space: nowrap;

            transition: .2s;
        }


        .btn-tambah:hover {

            transform:
                translateY(-1px);

            box-shadow:
                0 5px 15px
                rgba(37,99,235,.25);
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .toolbar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 18px;

            padding: 13px;

            background: #f8fafc;

            border-radius: 10px;

            border:
                1px solid #e2e8f0;
        }


        .search-form {

            display: flex;

            align-items: center;

            gap: 8px;

            flex: 1;

            max-width: 500px;
        }


        .search-box {

            position: relative;

            flex: 1;
        }


        .search-box input {

            width: 100%;

            height: 38px;

            padding:
                0 13px 0 38px;

            border:
                1px solid #cbd5e1;

            border-radius: 8px;

            outline: none;

            background: white;

            color: #334155;

            font-size: 12px;
        }


        .search-box input:focus {

            border-color: #3b82f6;

            box-shadow:
                0 0 0 3px
                rgba(59,130,246,.1);
        }


        .search-icon {

            position: absolute;

            left: 13px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #94a3b8;

            font-size: 14px;
        }


        .btn-search {

            height: 38px;

            padding:
                0 15px;

            border: none;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-size: 12px;

            font-weight: bold;

            cursor: pointer;
        }


        .btn-search:hover {

            background: #1d4ed8;
        }


        .btn-reset {

            height: 38px;

            padding:
                0 13px;

            border:
                1px solid #cbd5e1;

            border-radius: 8px;

            background: white;

            color: #475569;

            font-size: 12px;

            text-decoration: none;

            display: inline-flex;

            align-items: center;
        }


        .btn-reset:hover {

            background: #f1f5f9;
        }


        .data-info {

            color: #64748b;

            font-size: 12px;

            white-space: nowrap;
        }


        /* =====================================================
           TABLE WRAPPER
        ===================================================== */

        .table-wrapper {

            width: 100%;

            overflow-x: auto;

            border:
                1px solid #e2e8f0;

            border-radius: 12px;

            scrollbar-width: thin;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .data-table {

            width: 100%;

            min-width: 1100px;

            border-collapse: collapse;

            table-layout: auto;
        }


        .data-table th {

            background: #2563eb;

            color: white;

            padding:
                14px 12px;

            text-align: left;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        .data-table th:first-child {

            border-radius:
                10px 0 0 0;
        }


        .data-table th:last-child {

            border-radius:
                0 10px 0 0;
        }


        .data-table td {

            padding:
                12px;

            border-bottom:
                1px solid #e5e7eb;

            font-size: 12px;

            color: #334155;

            vertical-align: middle;
        }


        .data-table tbody tr {

            transition: .15s;
        }


        .data-table tbody tr:hover {

            background: #f8fafc;
        }


        .data-table tbody tr:last-child td {

            border-bottom: none;
        }


        /* =====================================================
           ID
        ===================================================== */

        .id-number {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 30px;

            height: 28px;

            padding:
                0 7px;

            background: #eff6ff;

            color: #2563eb;

            border-radius: 6px;

            font-weight: bold;

            font-size: 11px;
        }


        /* =====================================================
           NAMA
        ===================================================== */

        .name-cell {

            display: flex;

            align-items: center;

            gap: 9px;

            min-width: 180px;
        }


        .avatar {

            width: 34px;
            height: 34px;

            border-radius: 9px;

            background:
                linear-gradient(
                    135deg,
                    #dbeafe,
                    #e0e7ff
                );

            color: #2563eb;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 12px;

            font-weight: bold;

            flex-shrink: 0;
        }


        .name-text {

            font-weight: 600;

            color: #0f172a;

            white-space: nowrap;
        }


        /* =====================================================
           NISN
        ===================================================== */

        .nisn {

            color: #475569;

            white-space: nowrap;
        }


        /* =====================================================
           GENDER
        ===================================================== */

        .gender-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding:
                5px 9px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 600;

            white-space: nowrap;
        }


        .gender-laki {

            background: #dbeafe;

            color: #1d4ed8;
        }


        .gender-perempuan {

            background: #fce7f3;

            color: #be185d;
        }


        .gender-lain {

            background: #f1f5f9;

            color: #64748b;
        }


        /* =====================================================
           EMAIL
        ===================================================== */

        .email {

            color: #2563eb;

            white-space: nowrap;
        }


        /* =====================================================
           ALAMAT
        ===================================================== */

        .address {

            max-width: 230px;

            color: #64748b;

            line-height: 1.5;
        }


        /* =====================================================
           KOLOM AKSI
        ===================================================== */

        .data-table th.aksi-column {

            width: 170px;

            min-width: 170px;

            text-align: center;

            position: sticky;

            right: 0;

            z-index: 2;
        }


        .data-table td.aksi-column {

            width: 170px;

            min-width: 170px;

            text-align: center;

            background: white;

            position: sticky;

            right: 0;

            z-index: 1;
        }


        .data-table tbody tr:hover td.aksi-column {

            background: #f8fafc;
        }


        /* =====================================================
           TOMBOL AKSI
        ===================================================== */

        .aksi-buttons {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            flex-wrap: nowrap;

            white-space: nowrap;
        }


        .btn-edit,
        .btn-hapus {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            height: 32px;

            min-width: 62px;

            padding:
                0 10px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 11px;

            font-weight: 600;

            cursor: pointer;

            transition: .2s;

            white-space: nowrap;

            border: none;
        }


        .btn-edit {

            background: #f59e0b;

            color: white;
        }


        .btn-edit:hover {

            background: #d97706;

            transform:
                translateY(-1px);
        }


        .btn-hapus {

            background: #ef4444;

            color: white;
        }


        .btn-hapus:hover {

            background: #dc2626;

            transform:
                translateY(-1px);
        }


        /* =====================================================
           DATA KOSONG
        ===================================================== */

        .empty-row {

            text-align: center;

            padding:
                50px 20px !important;

            color: #94a3b8 !important;
        }


        .empty-icon {

            font-size: 35px;

            margin-bottom: 10px;
        }


        .empty-row strong {

            display: block;

            color: #475569;

            margin-bottom: 5px;
        }


        /* =====================================================
           PAGINATION
        ===================================================== */

        .table-footer {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-top: 18px;
        }


        .showing {

            color: #64748b;

            font-size: 12px;
        }


        .pagination {

            display: flex;

            align-items: center;

            gap: 5px;

            list-style: none;
        }


        .pagination a {

            display: flex;

            align-items: center;

            justify-content: center;

            min-width: 32px;

            height: 32px;

            padding:
                0 8px;

            border:
                1px solid #e2e8f0;

            border-radius: 7px;

            color: #475569;

            text-decoration: none;

            font-size: 11px;

            background: white;
        }


        .pagination a:hover {

            background: #eff6ff;

            color: #2563eb;

            border-color: #93c5fd;
        }


        .pagination a.active {

            background: #2563eb;

            color: white;

            border-color: #2563eb;
        }


        .pagination a.disabled {

            color: #cbd5e1;

            pointer-events: none;

            background: #f8fafc;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1000px) {

            .stats {

                grid-template-columns:
                    repeat(3, 1fr);
            }

        }


        @media (max-width: 800px) {

            .sidebar {

                width: 70px;

                padding:
                    20px 10px;
            }


            .brand {

                justify-content: center;
            }


            .brand-text,
            .menu-title {

                display: none;
            }


            .menu a {

                justify-content: center;

                padding: 13px;
            }


            .menu a span:not(.menu-icon) {

                display: none;
            }


            .logout a {

                justify-content: center;
            }


            .logout a span:not(.menu-icon) {

                display: none;
            }


            .main {

                margin-left: 70px;

                padding: 20px;
            }


            .stats {

                grid-template-columns:
                    1fr;
            }


            .content-header {

                align-items: flex-start;

                flex-direction: column;
            }


            .toolbar {

                align-items: stretch;

                flex-direction: column;
            }


            .search-form {

                max-width: none;

                width: 100%;
            }


            .data-info {

                white-space: normal;
            }

        }


        @media (max-width: 550px) {

            .main {

                padding: 15px;
            }


            .topbar {

                align-items: flex-start;

                gap: 10px;
            }


            .profile-info {

                display: none;
            }


            .page-title h1 {

                font-size: 22px;
            }


            .search-form {

                flex-wrap: wrap;
            }


            .search-box {

                width: 100%;

                flex-basis: 100%;
            }


            .btn-search,
            .btn-reset {

                flex: 1;
            }


            .table-footer {

                flex-direction: column;

                align-items: flex-start;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-icon">
            📚
        </div>


        <div class="brand-text">

            <h2>
                UJIAN ASTS
            </h2>

            <span>
                Student Management
            </span>

        </div>

    </div>


    <div class="menu-title">
        MENU UTAMA
    </div>


    <nav class="menu">


        <!-- DASHBOARD -->

        <a href="dashboard.php">

            <span class="menu-icon">
                🏠
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- DAFTAR TEMAN -->

        <a href="daftar_teman.php">

            <span class="menu-icon">
                👥
            </span>

            <span>
                Daftar Teman
            </span>

        </a>


        <!-- TAMBAH USER -->

        <a href="tambah_user.php">

            <span class="menu-icon">
                ➕
            </span>

            <span>
                Tambah User
            </span>

        </a>


        <!-- TABEL DATA -->

        <a
            href="tabel_data.php"
            class="active"
        >

            <span class="menu-icon">
                📋
            </span>

            <span>
                Tabel Data
            </span>

        </a>


        <!-- DATA JSON -->

        <a
            href="dashboard.php"
            onclick="
                sessionStorage.setItem(
                    'buka_json',
                    '1'
                );
            "
        >

            <span class="menu-icon">
                🔗
            </span>

            <span>
                Data JSON
            </span>

        </a>


    </nav>


    <div class="menu-title">
        AKUN
    </div>


    <div class="logout">

        <a href="logout.php">

            <span class="menu-icon">
                🚪
            </span>

            <span>
                Keluar dari Sistem
            </span>

        </a>

    </div>


</aside>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="page-title">

            <h1>
                Tabel Data
            </h1>

            <p>
                Kelola seluruh data pengguna UJIAN ASTS
            </p>

        </div>


        <div class="profile">


            <div class="profile-avatar">

                <?= htmlspecialchars($inisial) ?>

            </div>


            <div class="profile-info">

                <strong>
                    <?= htmlspecialchars($namaAdmin) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($roleAdmin) ?>
                </span>

            </div>


        </div>


    </div>



    <!-- =================================================
         STATISTIK
    ================================================== -->

    <div class="stats">


        <div class="stat-card">


            <div>

                <span>
                    Total Data
                </span>

                <strong>
                    <?= $totalData ?>
                </strong>

            </div>


            <div class="stat-icon blue">
                👥
            </div>


        </div>


        <div class="stat-card">


            <div>

                <span>
                    Halaman
                </span>

                <strong>
                    <?= $page ?>
                </strong>

            </div>


            <div class="stat-icon purple">
                📄
            </div>


        </div>


        <div class="stat-card">


            <div>

                <span>
                    Data Per Halaman
                </span>

                <strong>
                    <?= $limit ?>
                </strong>

            </div>


            <div class="stat-icon green">
                📋
            </div>


        </div>


    </div>



    <!-- =================================================
         CONTENT
    ================================================== -->

    <div class="content-card">


        <!-- HEADER -->

        <div class="content-header">


            <div class="content-title">

                <h2>
                    Data Pengguna
                </h2>

                <p>
                    Tambah, edit, dan hapus data pengguna
                </p>

            </div>


            <a
                href="tambah_user.php"
                class="btn-tambah"
            >

                ➕ Tambah User

            </a>


        </div>



        <!-- =================================================
             SEARCH TOOLBAR
        ================================================== -->

        <div class="toolbar">


            <form
                action="tabel_data.php"
                method="GET"
                class="search-form"
            >


                <div class="search-box">


                    <span class="search-icon">
                        🔍
                    </span>


                    <input
                        type="text"
                        name="search"
                        placeholder="Cari nama, NISN, email, alamat..."
                        value="<?= htmlspecialchars($search) ?>"
                    >


                </div>


                <button
                    type="submit"
                    class="btn-search"
                >
                    Cari
                </button>


                <?php if ($search !== ''): ?>

                    <a
                        href="tabel_data.php"
                        class="btn-reset"
                    >
                        Reset
                    </a>

                <?php endif; ?>


            </form>


            <div class="data-info">

                <?php if ($search !== ''): ?>

                    Hasil pencarian:
                    <strong>
                        <?= htmlspecialchars($search) ?>
                    </strong>

                <?php else: ?>

                    Menampilkan seluruh data

                <?php endif; ?>

            </div>


        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="table-wrapper">


            <table class="data-table">


                <thead>

                    <tr>

                        <th>
                            No
                        </th>

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
                            Alamat
                        </th>

                        <th class="aksi-column">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (count($users) > 0): ?>


                        <?php

                        $nomor = $nomorAwal;

                        foreach ($users as $user):


                            $namaUser =
                                trim(
                                    $user['name'] ?? ''
                                );


                            $inisialUser =
                                strtoupper(
                                    substr(
                                        $namaUser,
                                        0,
                                        1
                                    )
                                );


                            $gender =
                                trim(
                                    $user['gender'] ?? ''
                                );


                            $genderUpper =
                                strtoupper(
                                    $gender
                                );


                            if (
                                $genderUpper === 'MALE' ||
                                $genderUpper === 'LAKI-LAKI'
                            ) {

                                $genderClass =
                                    'gender-laki';

                                $genderIcon =
                                    '👨';

                            } elseif (
                                $genderUpper === 'FEMALE' ||
                                $genderUpper === 'PEREMPUAN'
                            ) {

                                $genderClass =
                                    'gender-perempuan';

                                $genderIcon =
                                    '👩';

                            } else {

                                $genderClass =
                                    'gender-lain';

                                $genderIcon =
                                    '👤';

                            }

                        ?>


                            <tr>


                                <!-- NOMOR -->

                                <td>

                                    <?= $nomor++ ?>

                                </td>



                                <!-- ID -->

                                <td>

                                    <span class="id-number">

                                        <?= htmlspecialchars(
                                            $user['id']
                                        ) ?>

                                    </span>

                                </td>



                                <!-- NAMA -->

                                <td>


                                    <div class="name-cell">


                                        <div class="avatar">

                                            <?= htmlspecialchars(
                                                $inisialUser
                                            ) ?>

                                        </div>


                                        <div class="name-text">

                                            <?= htmlspecialchars(
                                                $namaUser
                                            ) ?>

                                        </div>


                                    </div>


                                </td>



                                <!-- NISN -->

                                <td>

                                    <span class="nisn">

                                        <?= htmlspecialchars(
                                            $user['nisn'] ?? '-'
                                        ) ?>

                                    </span>

                                </td>



                                <!-- TTL -->

                                <td>

                                    <?= htmlspecialchars(
                                        $user['ttl'] ?? '-'
                                    ) ?>

                                </td>



                                <!-- GENDER -->

                                <td>


                                    <span
                                        class="
                                            gender-badge
                                            <?= $genderClass ?>
                                        "
                                    >

                                        <?= $genderIcon ?>

                                        <?= htmlspecialchars(
                                            $gender ?: '-'
                                        ) ?>

                                    </span>


                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <span class="email">

                                        <?= htmlspecialchars(
                                            $user['email'] ?? '-'
                                        ) ?>

                                    </span>

                                </td>



                                <!-- ALAMAT -->

                                <td>

                                    <div class="address">

                                        <?= htmlspecialchars(
                                            $user['address'] ?? '-'
                                        ) ?>

                                    </div>

                                </td>



                                <!-- =================================================
                                     AKSI
                                ================================================== -->

                                <td class="aksi-column">


                                    <div class="aksi-buttons">


                                        <!-- EDIT -->

                                        <a
                                            href="edit.php?id=<?= urlencode($user['id']) ?>"
                                            class="btn-edit"
                                        >

                                            ✏️ Edit

                                        </a>


                                        <!-- HAPUS -->

                                        <a
                                            href="hapus.php?id=<?= urlencode($user['id']) ?>"
                                            class="btn-hapus"
                                            onclick="
                                                return confirm(
                                                    'Yakin ingin menghapus data <?= htmlspecialchars(addslashes($namaUser)) ?>?'
                                                );
                                            "
                                        >

                                            🗑️ Hapus

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
                                class="empty-row"
                            >

                                <div class="empty-icon">
                                    📭
                                </div>


                                <strong>
                                    Data tidak ditemukan
                                </strong>


                                <span>
                                    <?php if ($search !== ''): ?>

                                        Tidak ada data yang cocok
                                        dengan pencarian
                                        "<?= htmlspecialchars($search) ?>".

                                    <?php else: ?>

                                        Belum ada data pengguna.

                                    <?php endif; ?>
                                </span>


                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>



        <!-- =================================================
             FOOTER TABLE
        ================================================== -->

        <div class="table-footer">


            <div class="showing">


                <?php if ($totalData > 0): ?>


                    <?php

                    $dari =
                        $offset + 1;

                    $sampai =
                        min(
                            $offset + $limit,
                            $totalData
                        );

                    ?>


                    Menampilkan
                    <strong>
                        <?= $dari ?>
                    </strong>
                    -
                    <strong>
                        <?= $sampai ?>
                    </strong>
                    dari
                    <strong>
                        <?= $totalData ?>
                    </strong>
                    data


                <?php else: ?>


                    Tidak ada data


                <?php endif; ?>


            </div>



            <!-- PAGINATION -->

            <?php if ($totalPage > 1): ?>


                <ul class="pagination">


                    <!-- PREVIOUS -->

                    <?php if ($page > 1): ?>


                        <li>

                            <a
                                href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>"
                            >
                                ‹
                            </a>

                        </li>


                    <?php else: ?>


                        <li>

                            <a class="disabled">
                                ‹
                            </a>

                        </li>


                    <?php endif; ?>



                    <!-- NOMOR HALAMAN -->

                    <?php


                    $startPage =
                        max(
                            1,
                            $page - 2
                        );


                    $endPage =
                        min(
                            $totalPage,
                            $page + 2
                        );


                    for (
                        $i = $startPage;
                        $i <= $endPage;
                        $i++
                    ):

                    ?>


                        <li>

                            <a
                                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                                class="<?= $i == $page ? 'active' : '' ?>"
                            >

                                <?= $i ?>

                            </a>

                        </li>


                    <?php endfor; ?>



                    <!-- NEXT -->

                    <?php if ($page < $totalPage): ?>


                        <li>

                            <a
                                href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>"
                            >
                                ›
                            </a>

                        </li>


                    <?php else: ?>


                        <li>

                            <a class="disabled">
                                ›
                            </a>

                        </li>


                    <?php endif; ?>


                </ul>


            <?php endif; ?>


        </div>


    </div>


</main>


</body>

</html>