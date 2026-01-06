<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    if ($_SESSION['role'] == 'inventaris') {
        header("location:modules/inventaris/index.php");
    } else {
        header("location:modules/mobil/index.php");
    }
    exit;
}

// LOGIKA PROSES LOGIN
$error = false;
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Pastikan enkripsi di database juga MD5

    // Cek Database
    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $cek   = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);

        // Set Session
        $_SESSION['id_admin']     = $data['id_admin'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['username']     = $data['username'];
        $_SESSION['role']         = $data['role'];
        $_SESSION['status']       = "login";

        // Redirect sesuai role
        if ($data['role'] == "inventaris") {
            header("location:modules/inventaris/index.php");
        } else if ($data['role'] == "mobil") {
            header("location:modules/mobil/index.php");
        }
    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Internal</title>
    
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
            
            /* Background Image dengan Overlay Biru seperti di gambar referensi */
            background: linear-gradient(to bottom, rgba(35, 65, 135, 0.85), rgba(20, 40, 90, 0.9)), 
                        url('assets/img/img-dprd.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-card {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-title {
            color: #1a237e; /* Warna biru tua */
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
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
            border-color: #0d6efd;
            background-color: #fff;
        }

        .btn-login {
            background-color: #0d6efd; /* Biru terang seperti referensi */
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
            background-color: #0b5ed7;
        }

        .form-label {
            text-align: left;
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .copyright {
            margin-top: 20px;
            font-size: 0.75rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-title">SISTEM ADMIN DPRD</div>
        <div class="login-subtitle">Silakan login untuk melanjutkan</div>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i> Username atau Password salah!
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

            <button type="submit" name="login" class="btn btn-login mt-4">Login</button>
        </form>

        <div class="copyright">
            &copy; 2026 Sekretariat DPRD Kota Yogyakarta
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>