<?php

echo "<h2>Test PHP cURL</h2>";

if (function_exists("curl_init")) {
    echo "<p style='color:green; font-weight:bold;'>✅ cURL AKTIF</p>";
    echo "<p>PHP sudah bisa menggunakan cURL untuk menghubungi Fonnte.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ cURL TIDAK AKTIF</p>";
    echo "<p>cURL PHP belum aktif.</p>";
}