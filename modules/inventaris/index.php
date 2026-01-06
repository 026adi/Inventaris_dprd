<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Dashboard Inventaris"); 

// 1. Hitung Total Jenis Barang
$q_item = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
$d_item = mysqli_fetch_assoc($q_item);

// 2. Hitung Total Stok Keseluruhan
$q_stok = mysqli_query($koneksi, "SELECT SUM(stok) as total_stok FROM barang");
$d_stok = mysqli_fetch_assoc($q_stok);

// 3. Cek Barang yang Stoknya Menipis (< 5)
$q_tipis = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE stok < 5");
$d_tipis = mysqli_fetch_assoc($q_tipis);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Gudang</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Jenis Barang</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_item['total']; ?></h2>
                        <small>Item Terdaftar</small>
                    </div>
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Stok Fisik</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_stok['total_stok'] ?? 0; ?></h2>
                        <small>Unit/Pcs di Gudang</small>
                    </div>
                    <i class="bi bi-boxes fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 fw-bold">Stok Menipis</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_tipis['total']; ?></h2>
                        <small>Kurang dari 5 unit</small>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3 shadow-sm">
    <i class="bi bi-info-circle-fill me-2"></i>
    Selamat datang di Sistem Inventaris DPRD. Silakan kelola data barang melalui menu <strong>Data Barang</strong>.
</div>

<?php render_footer_barang(); ?>