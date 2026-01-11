<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Dashboard Mobil"); 

// 1. Hitung Total Semua Mobil
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil");
$d_total = mysqli_fetch_assoc($q_total);

// 2. Hitung Mobil Tersedia (Ready)
$q_ready = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'Tersedia'");
$d_ready = mysqli_fetch_assoc($q_ready);

// 3. Hitung Mobil Dipinjam
$q_loan  = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'Dipinjam'");
$d_loan  = mysqli_fetch_assoc($q_loan);

// 4. Hitung Mobil Sedang Servis
$q_servis = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil WHERE status_mobil = 'Servis'");
$d_servis = mysqli_fetch_assoc($q_servis);
?>

<?php
$today = date('Y-m-d');

// Deadline hari ini
$q_deadline_today = mysqli_query($koneksi, "
    SELECT p.*, m.nama_mobil, m.plat_nomor
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE p.status_kembali = 'Belum'
      AND p.tgl_rencana_kembali = '$today'
");

// Terlambat
$q_terlambat = mysqli_query($koneksi, "
    SELECT p.*, m.nama_mobil, m.plat_nomor
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE p.status_kembali = 'Belum'
      AND p.tgl_rencana_kembali < '$today'
");
?>



<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Transportasi</h1>
</div>



<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Armada</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_total['total']; ?></h2>
                        <p>Unit Mobil</p>
                    </div>
                    <i class="bi bi-car-front-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Siap Pakai</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_ready['total']; ?></h2>
                        <p>Kendaraan Tersedia</p>
                    </div>
                    <i class="bi bi-key-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 fw-bold">Dipinjam</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_loan['total']; ?></h2>
                        <p>Sedang digunakan</p>
                    </div>
                    <i class="bi bi-clock-history fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 fw-bold">Servis</h6>
                        <h2 class="mb-0 fw-bold"><?= $d_servis['total']; ?></h2>
                        <p>Dalam Perbaikan</p>
                    </div>
                    <i class="bi bi-tools fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light border-bottom">
        <strong>🚨 Peminjaman Jatuh Tempo & Terlambat</strong>
    </div>
    <div class="card-body table-responsive">

        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mobil</th>
                    <th>Peminjam</th>
                    <th class="text-center">Tgl Pinjam</th>
                    <th class="text-center">Tgl Rencana Kembali</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>

                <!-- DEADLINE HARI INI -->
                <?php while ($row = mysqli_fetch_assoc($q_deadline_today)): ?>
                    <tr class="table-warning ">
                        <td>
                            <strong><?= $row['nama_mobil']; ?></strong><br>
                            <small class="text-muted"><?= $row['plat_nomor']; ?></small>
                        </td>
                        <td><?= $row['nama_peminjam']; ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rencana_kembali'])); ?></td>
                        <td class="text-center"v>
                            <span class="badge bg-warning text-dark">
                                ⏰ Deadline Hari Ini
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <!-- TERLAMBAT -->
                <?php while ($row = mysqli_fetch_assoc($q_terlambat)): ?>
                    <tr class="table-danger">
                        <td>
                            <strong><?= $row['nama_mobil']; ?></strong><br>
                            <small class="text-muted"><?= $row['plat_nomor']; ?></small>
                        </td>
                        <td><?= $row['nama_peminjam']; ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rencana_kembali'])); ?></td>
                        <td class="text-center">
                            <span class="badge bg-danger">
                                ❌ Terlambat
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (
                    mysqli_num_rows($q_deadline_today) == 0 &&
                    mysqli_num_rows($q_terlambat) == 0
                ): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Tidak ada peminjaman yang jatuh tempo atau terlambat
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

    </div>
</div>


<div class="alert alert-secondary mt-3 shadow-sm">
    <i class="bi bi-info-circle-fill me-2"></i>
    Selamat datang <strong><?= $_SESSION['nama_lengkap']; ?></strong>. Kelola data kendaraan di menu <strong>Data Mobil</strong> dan catat penggunaan di menu <strong>Peminjaman</strong>.
</div>

<?php render_footer_mobil(); ?>