<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/fonnte.php";

$nama    = "";
$nisn    = "";
$ttl     = "";
$gender  = "";
$email   = "";
$no_hp   = "";
$address = "";

$pesan = "";
$tipe_pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama    = trim($_POST["nama"] ?? "");
    $nisn    = trim($_POST["nisn"] ?? "");
    $ttl     = trim($_POST["ttl"] ?? "");
    $gender  = trim($_POST["gender"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $no_hp   = trim($_POST["no_hp"] ?? "");
    $address = trim($_POST["address"] ?? "");

    // ==============================
    // VALIDASI
    // ==============================

    if (
        $nama === "" ||
        $nisn === "" ||
        $ttl === "" ||
        $gender === "" ||
        $email === "" ||
        $no_hp === "" ||
        $address === ""
    ) {
        $pesan = "Semua field wajib diisi.";
        $tipe_pesan = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $pesan = "Format email tidak valid.";
        $tipe_pesan = "error";

    } else {

        // ==============================
        // NORMALISASI NOMOR WHATSAPP
        // ==============================

        $no_hp = preg_replace('/[^0-9]/', '', $no_hp);

        if (substr($no_hp, 0, 1) === "0") {
            $no_hp = "62" . substr($no_hp, 1);
        }

        if (!preg_match('/^62[0-9]{9,15}$/', $no_hp)) {

            $pesan = "Nomor WhatsApp tidak valid.";
            $tipe_pesan = "error";

        } else {

            // ==============================
            // CEK DATA DUPLIKAT
            // ==============================

            $cek = $conn->prepare("
                SELECT id
                FROM users
                WHERE nisn = ?
                   OR email = ?
                   OR no_hp = ?
                LIMIT 1
            ");

            $cek->bind_param("sss", $nisn, $email, $no_hp);
            $cek->execute();

            $hasil_cek = $cek->get_result();

            if ($hasil_cek->num_rows > 0) {

                $pesan = "NISN, email, atau nomor WhatsApp sudah terdaftar.";
                $tipe_pesan = "error";

            } else {

                // ==============================
                // SIMPAN KE DATABASE
                // ==============================

                $stmt = $conn->prepare("
                    INSERT INTO users
                    (
                        name,
                        nisn,
                        ttl,
                        gender,
                        email,
                        no_hp,
                        address
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "sssssss",
                    $nama,
                    $nisn,
                    $ttl,
                    $gender,
                    $email,
                    $no_hp,
                    $address
                );

                if ($stmt->execute()) {

                    // ==============================
                    // KIRIM WHATSAPP
                    // ==============================

                    $pesanWA =
                        "Halo *" . $nama . "* 👋\n\n" .
                        "Data kamu telah berhasil didaftarkan ke sistem *UJIAN ASTS*.\n\n" .
                        "📋 *Data Peserta*\n" .
                        "Nama: " . $nama . "\n" .
                        "NISN: " . $nisn . "\n" .
                        "TTL: " . $ttl . "\n" .
                        "Email: " . $email . "\n\n" .
                        "Terima kasih.";

                    $hasilWA = kirimWhatsApp($no_hp, $pesanWA);

                    if ($hasilWA["success"]) {

                        $pesan =
                            "Data berhasil disimpan dan WhatsApp berhasil dikirim.";

                        $tipe_pesan = "success";

                    } else {

                        $alasan = $hasilWA["message"] ?? "Tidak diketahui";

                        $pesan =
                            "Data berhasil disimpan, tetapi WhatsApp gagal dikirim. " .
                            "Penyebab: " . $alasan;

                        $tipe_pesan = "warning";
                    }

                    // Kosongkan form setelah berhasil
                    $nama    = "";
                    $nisn    = "";
                    $ttl     = "";
                    $gender  = "";
                    $email   = "";
                    $no_hp   = "";
                    $address = "";

                } else {

                    $pesan = "Data gagal disimpan ke database.";
                    $tipe_pesan = "error";
                }

                $stmt->close();
            }

            $cek->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tambah User Baru - UJIAN ASTS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .container {
            width: 100%;
            max-width: 850px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2563eb;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            margin-top: 10px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .radio-item input {
            width: auto;
        }

        .help {
            display: block;
            margin-top: 7px;
            color: #6b7280;
            font-size: 13px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .warning {
            background: #fef3c7;
            color: #92400e;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 9px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
            font-weight: bold;
        }

        @media (max-width: 600px) {

            .container {
                margin: 15px auto;
                padding: 12px;
            }

            .card {
                padding: 20px;
            }

            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <a href="admin_dashboard.php" class="back">
        ← Kembali ke Dashboard
    </a>

    <div class="card">

        <div class="header">

            <h1>Tambah User Baru</h1>

            <p>
                Isi formulir di bawah ini untuk menambahkan
                data peserta baru ke dalam sistem.
            </p>

        </div>

        <?php if ($pesan !== ""): ?>

            <div class="alert <?= htmlspecialchars($tipe_pesan) ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="nama">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= htmlspecialchars($nama) ?>"
                    placeholder="Masukkan nama lengkap"
                    required
                >

            </div>

            <div class="form-group">

                <label for="nisn">
                    NISN
                </label>

                <input
                    type="text"
                    id="nisn"
                    name="nisn"
                    value="<?= htmlspecialchars($nisn) ?>"
                    placeholder="Masukkan NISN"
                    required
                >

            </div>

            <div class="form-group">

                <label for="ttl">
                    Tempat, Tanggal Lahir (TTL)
                </label>

                <input
                    type="text"
                    id="ttl"
                    name="ttl"
                    value="<?= htmlspecialchars($ttl) ?>"
                    placeholder="Contoh: Sumenep, 12 Januari 2008"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Jenis Kelamin
                </label>

                <div class="radio-group">

                    <label class="radio-item">

                        <input
                            type="radio"
                            name="gender"
                            value="MALE"
                            <?= $gender === "MALE" ? "checked" : "" ?>
                            required
                        >

                        Laki-Laki (MALE)

                    </label>

                    <label class="radio-item">

                        <input
                            type="radio"
                            name="gender"
                            value="FEMALE"
                            <?= $gender === "FEMALE" ? "checked" : "" ?>
                        >

                        Perempuan (FEMALE)

                    </label>

                </div>

            </div>

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="contoh@email.com"
                    required
                >

            </div>

            <div class="form-group">

                <label for="no_hp">
                    Nomor WhatsApp 📱
                </label>

                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    value="<?= htmlspecialchars($no_hp) ?>"
                    placeholder="081234567890"
                    required
                >

                <span class="help">
                    📱 Nomor ini digunakan untuk mengirim notifikasi WhatsApp.
                    Contoh: <strong>081234567890</strong>
                </span>

            </div>

            <div class="form-group">

                <label for="address">
                    Alamat Lengkap
                </label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Masukkan alamat lengkap"
                    required
                ><?= htmlspecialchars($address) ?></textarea>

            </div>

            <button type="submit" class="btn">
                💾 Simpan Data & Kirim WhatsApp
            </button>

        </form>

    </div>

</div>

</body>

</html>