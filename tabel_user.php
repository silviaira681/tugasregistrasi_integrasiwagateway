<?php

require_once "auth_check.php";
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $nisn = trim($_POST["nisn"] ?? "");
    $ttl = trim($_POST["ttl"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if (
        $name === "" ||
        $nisn === "" ||
        $ttl === "" ||
        $gender === "" ||
        $email === "" ||
        $address === ""
    ) {

        $error = "Semua data harus diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Email tidak valid.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO users
            (name, nisn, ttl, gender, email, address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssss",
            $name,
            $nisn,
            $ttl,
            $gender,
            $email,
            $address
        );

        if ($stmt->execute()) {

            header("Location: daftar_teman.php");
            exit;

        } else {

            $error = "Gagal menyimpan data.";

        }

        $stmt->close();
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

<title>Tambah User</title>

<style>

body {
    margin: 0;
    background: #f4f7fb;
    font-family: Arial, sans-serif;
}

.sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    background: #111827;
    padding: 25px 15px;
    color: white;
}

.logo {
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #d1d5db;
    text-decoration: none;
    padding: 13px;
    border-radius: 8px;
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

.box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 800px;
}

.group {
    margin-bottom: 17px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-sizing: border-box;
}

textarea {
    min-height: 100px;
}

button {
    background: #2563eb;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">
UJIAN ASTS
</div>

<a href="dashboard.php">
🏠 Dashboard
</a>

<a href="daftar_teman.php">
👥 Daftar Teman
</a>

<a
href="tambah_user.php"
class="active"
>
➕ Tambah User
</a>

<a href="tabel_data.php">
📋 Tabel Data
</a>

<a href="data_json.php" target="_blank">
🔗 Data JSON
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>


<div class="main">

<div class="box">

<h1>Tambah User</h1>

<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<form method="POST">

<div class="group">

<label>Nama</label>

<input
type="text"
name="name"
required
>

</div>


<div class="group">

<label>NISN</label>

<input
type="text"
name="nisn"
required
>

</div>


<div class="group">

<label>TTL</label>

<input
type="text"
name="ttl"
placeholder="PONOROGO, 12 Agustus 2010"
required
>

</div>


<div class="group">

<label>Gender</label>

<select name="gender" required>

<option value="">
-- Pilih Gender --
</option>

<option value="MALE">
MALE
</option>

<option value="FEMALE">
FEMALE
</option>

</select>

</div>


<div class="group">

<label>Email</label>

<input
type="email"
name="email"
required
>

</div>


<div class="group">

<label>Alamat</label>

<textarea
name="address"
required
></textarea>

</div>


<button type="submit">
Simpan User
</button>

</form>

</div>

</div>

</body>

</html>