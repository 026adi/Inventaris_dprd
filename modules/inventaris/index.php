<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Dashboard Gudang"); 

// Hitung Statistik
$q_habis = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE jenis = 'Habis Pakai'");
$d_habis = mysqli_fetch_assoc($q_habis);

$q_tetap = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE jenis = 'Tetap'");
$d_tetap = mysqli_fetch_assoc($q_tetap);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Gudang</h1>
</div>

<div class="row">
    
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-primary h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Barang Habis Pakai</h6>
                        <h1 class="display-4 fw-bold mb-0"><?= $d_habis['total']; ?></h1>
                        <p class="mb-0 small mt-2">Item Terdaftar (ATK/Bahan)</p>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="data_barang.php?jenis=Habis Pakai" class="text-white text-decoration-none small stretched-link">
                    Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card text-white bg-success h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Barang Tetap (Aset)</h6>
                        <h1 class="display-4 fw-bold mb-0"><?= $d_tetap['total']; ?></h1>
                        <p class="mb-0 small mt-2">Item Aset (Elektronik/Mebel)</p>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-laptop fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="data_barang.php?jenis=Tetap" class="text-white text-decoration-none small stretched-link">
                    Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<div class="alert alert-light border shadow-sm mt-2">
    <div class="d-flex gap-3 align-items-center">
        <div class="fs-1 text-primary"><i class="bi bi-info-circle-fill"></i></div>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Informasi Pengelolaan</h5>
            <p class="mb-0 text-muted">
                Gunakan menu <strong>Data Barang</strong> untuk menambah item baru. Stok barang akan otomatis berubah saat Anda mencatat transaksi di menu <strong>Riwayat Masuk/Keluar</strong>.
            </p>
        </div>
    </div>
</div>

<?php render_footer_barang(); ?>