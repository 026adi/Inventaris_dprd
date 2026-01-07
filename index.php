<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Sistem Informasi DPRD</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
        }
        .hero-section {
            /* Layer 1: Gradient Biru Gelap (Transparan) untuk efek siluet
               Layer 2: Gambar Gedung DPRD (img-dprd.jpeg)
            */
            background: linear-gradient(to bottom, rgba(13, 110, 253, 0.85) 0%, rgba(10, 88, 202, 0.95) 100%), 
                        url('assets/img/img-dprd.jpeg'); 
            
            background-size: cover;       /* Agar gambar memenuhi area */
            background-position: center;  /* Agar gambar rata tengah */
            background-repeat: no-repeat;
            
            color: white;
            padding: 120px 0 150px;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            margin-bottom: -80px; /* Menarik kartu ke atas agar menumpuk background */
            position: relative;
            z-index: 1;
        }

        .app-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
            background-color: #fff;
            position: relative;
            z-index: 2; /* Kartu tampil di atas background */
        }

        .app-card:hover {
            transform: translateY(-15px); /* Efek naik saat di-hover */
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }

        .icon-box {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 25px;
        }
        
        /* Merapikan jarak tulisan di dalam kartu */
        .card-body {
            padding: 3rem !important;
        }
    </style>
</head>
<body>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold display-4 mb-3">Sistem Informasi Internal</h1>
            <p class="lead fs-4 opacity-75">Sekretariat DPRD Kota Yogyakarta</p>
        </div>
    </div>

    <div class="container mb-5" style="margin-top: 40px;">
        <div class="row justify-content-center">
            
            <div class="col-md-6 col-lg-5 mb-4">
                <div class="card app-card shadow-lg h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary mx-auto">
                            <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold mb-3">Inventory</h3>
                        <p class="text-muted mb-4 lead fs-6">
                            Sistem pengelolaan stok barang gudang, pencatatan barang masuk (pengadaan), dan barang keluar.
                        </p>
                        <a href="login.php" class="btn btn-outline-primary w-100 rounded-pill fw-bold py-3">
                            Login Admin Gudang <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-5 mb-4">
                <div class="card app-card shadow-lg h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success mx-auto">
                            <i class="bi bi-car-front-fill" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold mb-3">Kendaraan</h3>
                        <p class="text-muted mb-4 lead fs-6">
                            Sistem manajemen armada mobil dinas, monitoring status kendaraan, dan jadwal peminjaman.
                        </p>
                        <a href="login.php" class="btn btn-outline-success w-100 rounded-pill fw-bold py-3">
                            Login Admin Mobil <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="text-center text-muted py-4 small">
        &copy; 2026 Sekretariat DPRD Kota Yogyakarta. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>