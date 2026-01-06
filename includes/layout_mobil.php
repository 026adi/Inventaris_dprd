<?php
session_start();
include_once __DIR__ . '/../config/koneksi.php';

function render_header_mobil($judul = "Mobil DPRD") {
    // 1. Cek Login
    if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
        header("location:" . base_url('login.php'));
        exit;
    }

    // 2. Proteksi Role: Hanya boleh MOBIL
    if ($_SESSION['role'] != 'mobil') {
        echo "<script>alert('Anda bukan Admin Mobil!'); window.location='" . base_url('logout.php') . "';</script>";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold ms-2" href="#">
            <i class="bi bi-car-front-fill"></i> MOBIL DPRD
        </a>
        <div class="text-white me-3 d-none d-md-block small">
            <i class="bi bi-person-circle"></i> <?= $_SESSION['nama_lengkap']; ?> (Admin Transport)
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse shadow-sm">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('modules/mobil/index.php'); ?>">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('modules/mobil/data_mobil.php'); ?>">
                            <i class="bi bi-car-front me-2"></i> Data Mobil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('modules/mobil/peminjaman.php'); ?>">
                            <i class="bi bi-calendar-check me-2"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger fw-bold" href="<?= base_url('logout.php'); ?>" onclick="return confirm('Keluar dari Sistem Mobil?')">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 min-vh-100 d-flex flex-column">
            <div class="flex-grow-1 pt-4">
<?php
}

function render_footer_mobil() {
?>
            </div>
            <footer class="py-3 bg-white border-top text-center mt-auto">
                <div class="container"><span class="text-muted small">&copy; 2026 Transportasi DPRD Kota Yogyakarta</span></div>
            </footer>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/script.js'); ?>"></script>
</body>
</html>
<?php
}
?>