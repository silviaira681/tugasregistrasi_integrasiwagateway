<?php

require_once __DIR__ . "/fonnte.php";

$nomor = "085731637000";

$pesan = "TEST WHATSAPP

Ini adalah pesan pengujian dari project UJIAN ASTS.

Jika pesan ini masuk ke WhatsApp, berarti koneksi Fonnte sudah berhasil.";

$hasil = kirimWhatsApp($nomor, $pesan);

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Test Fonnte</title>";
echo "</head>";
echo "<body style='font-family:Arial; padding:30px;'>";

echo "<h2>Hasil Test Fonnte</h2>";

echo "<pre style='background:#f5f5f5; padding:20px; border-radius:8px;'>";
print_r($hasil);
echo "</pre>";

echo "</body>";
echo "</html>";