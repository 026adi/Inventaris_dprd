<?php
session_start();
require_once 'config/koneksi.php';

// 1. TANGKAP PARAMETER DARI URL (inventaris / mobil)
// Default ke 'inventaris' jika tidak ada, atau bisa juga redirect ke index
$app_mode = $_GET['app'] ?? '';

// Jika user langsung akses login.php tanpa parameter yang benar, lempar balik ke index
if ($app_mode != 'inventaris' && $app_mode != 'mobil') {
    header("location:index.php");
    exit;
}

// 2. SETUP TAMPILAN BERDASARKAN MODE APLIKASI
if ($app_mode == 'inventaris') {
    $page_title  = "LOGIN GUDANG";
    $theme_color = "#0d6efd"; // Biru Bootstrap Primary
    $bg_overlay  = "rgba(13, 110, 253, 0.85)"; // Biru Transparan
    $icon_class  = "bi-box-seam";
    $btn_class   = "btn-primary";
} else {
    $page_title  = "LOGIN KENDARAAN";
    $theme_color = "#198754"; // Hijau Bootstrap Success
    $bg_overlay  = "rgba(25, 135, 84, 0.85)"; // Hijau Transparan
    $icon_class  = "bi-car-front-fill";
    $btn_class   = "btn-success";
}

// 3. JIKA SUDAH LOGIN, CEK SESI & REDIRECT
if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    if ($_SESSION['role'] == 'inventaris') {
        header("location:modules/inventaris/index.php");
    } else {
        header("location:modules/mobil/index.php");
    }
    exit;
}

// 4. LOGIKA PROSES LOGIN
$error = "";
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); 

    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $cek   = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);

        // --- VALIDASI ROLE KETAT ---
        // Pastikan user yang login sesuai dengan pintu masuknya (app_mode)
        if ($data['role'] != $app_mode) {
            $error = "Akun Anda tidak terdaftar di sistem " . strtoupper($app_mode) . ".";
        } else {
            // Login Sukses
            $_SESSION['id_admin']     = $data['id_admin'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['username']     = $data['username'];
            $_SESSION['role']         = $data['role'];
            $_SESSION['status']       = "login";

            // Redirect sesuai role
            if ($data['role'] == "inventaris") {
                header("location:modules/inventaris/index.php");
            } else {
                header("location:modules/mobil/index.php");
            }
            exit;
        }
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?> - Sistem Informasi Internal</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            
            /* Background Dinamis sesuai aplikasi yang dipilih */
            background: linear-gradient(to bottom, <?= $bg_overlay; ?>, rgba(0,0,0, 0.8)), 
                        url('assets/img/img-dprd.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-card {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 5px solid <?= $theme_color; ?>; /* Garis warna di atas kartu */
        }

        .login-header-icon {
            font-size: 3rem;
            color: <?= $theme_color; ?>;
            margin-bottom: 10px;
        }

        .login-title {
            color: <?= $theme_color; ?>;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: <?= $theme_color; ?>;
            background-color: #fff;
        }

        .btn-login {
            /* Warna tombol mengikuti tema aplikasi */
            background-color: <?= $theme_color; ?>; 
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            color: white;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            opacity: 0.9;
            color: white;
        }

        .form-label {
            text-align: left;
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .back-link {
            margin-top: 20px;
            display: block;
            font-size: 0.85rem;
            color: #adb5bd;
            text-decoration: none;
        }
        .back-link:hover {
            color: <?= $theme_color; ?>;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header-icon">
            <i class="bi <?= $icon_class; ?>"></i>
        </div>

        <div class="login-title"><?= $page_title; ?></div>
        <div class="login-subtitle">Silakan login untuk melanjutkan</div>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="text-start">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="text-start mt-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" name="login" class="btn btn-login mt-4">MASUK SISTEM</button>
        </form>

        <a href="index.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
        </a>
        
        <div class="mt-4 small text-muted">
            &copy; 2026 Sekretariat DPRD Kota Yogyakarta
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>