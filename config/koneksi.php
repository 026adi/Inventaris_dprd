<?php
// 1. Konfigurasi Database
$host = "localhost";
$user = "root";      // Default XAMPP
$pass = "";          // Default XAMPP kosong
$db   = "db_kantor_dprd"; // Nama database yang baru kita buat

// 2. Membuat Koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// 3. Cek Koneksi
if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}

/**
 * 4. Fungsi Helper Base URL
 * Fungsi ini agar pemanggilan file CSS/JS/Gambar konsisten.
 * GANTI 'web-inventaris-dprd' sesuai nama folder asli kamu di htdocs.
 */
if (!function_exists('base_url')) {
    function base_url($path = "") {
        // Contoh: http://localhost/web-inventaris-dprd/assets/css/style.css
        return "http://localhost/inventaris dprd/" . $path;
    }
}

// 5. Set timezone Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

/**
 * Catatan:
 * Berbeda dengan project magang, di sini kita tidak pakai logika auto-update status dulu.
 * Kita fokus agar koneksi dan login multi-admin jalan lancar.
 */
?>