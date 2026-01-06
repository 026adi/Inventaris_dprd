<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Peminjaman Kendaraan"); 

// =============================
// SEARCH PEMINJAMAN
// =============================
$search = $_GET['search'] ?? '';

// 1. Mobil yang TERSEDIA untuk form
$q_ready = mysqli_query(
    $koneksi,
    "SELECT * FROM mobil 
     WHERE status_mobil='Tersedia' 
     ORDER BY nama_mobil ASC"
);

// 2. Data Peminjaman + Search
$sql = "
    SELECT p.*, m.nama_mobil, m.plat_nomor
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= "
        WHERE 
            m.nama_mobil LIKE '%$search_safe%' OR
            m.plat_nomor LIKE '%$search_safe%' OR
            p.nama_peminjam LIKE '%$search_safe%' OR
            p.tujuan LIKE '%$search_safe%'
    ";
}

// Belum kembali di atas
$sql .= " ORDER BY FIELD(p.status_kembali, 'Belum', 'Sudah'), p.id_pinjam DESC";

$q_pinjam = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Peminjaman</h1>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Status:</strong> <?= $_GET['pesan']; ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">

    <!-- KIRI : FORM PINJAM -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-key-fill me-2"></i>Form Pinjam Mobil
                </h6>
            </div>
            <div class="card-body">
                <form action="proses_peminjaman.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Pilih Mobil Ready</label>
                        <select name="id_mobil" class="form-select" required>
                            <option value="">-- Pilih Mobil --</option>
                            <?php if(mysqli_num_rows($q_ready) > 0): ?>
                                <?php while($m = mysqli_fetch_assoc($q_ready)): ?>
                                    <option value="<?= $m['id_mobil']; ?>">
                                        <?= $m['nama_mobil']; ?> - [<?= $m['plat_nomor']; ?>]
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option disabled>Semua mobil sedang dipakai</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Tujuan / Keperluan</label>
                        <textarea name="tujuan" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" name="pinjam" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Proses Peminjaman
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KANAN : SEARCH + MONITORING -->
    <div class="col-md-8">

        <!-- SEARCH -->
        <div class="mb-3 d-flex justify-content-end">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari mobil / plat / peminjam / tujuan..."
                    value="<?= htmlspecialchars($search); ?>"
                    style="width:450px;"
                >
                <button class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>

                <?php if (!empty($search)): ?>
                    <a href="peminjaman.php" class="btn btn-outline-secondary">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL MONITORING -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Monitoring Peminjaman</h6>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Mobil</th>
                            <th>Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_pinjam) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Data peminjaman tidak ditemukan
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while($row = mysqli_fetch_assoc($q_pinjam)): ?>
                            <tr class="<?= ($row['status_kembali']=='Belum') ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?= $row['nama_mobil']; ?></strong><br>
                                    <small class="text-muted"><?= $row['plat_nomor']; ?></small>
                                </td>
                                <td>
                                    <?= $row['nama_peminjam']; ?><br>
                                    <small class="text-muted"><?= $row['tujuan']; ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td>
                                    <?php if($row['status_kembali']=='Belum'): ?>
                                        <span class="badge bg-danger">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Kembali</span><br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($row['tgl_kembali'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status_kembali']=='Belum'): ?>
                                        <a href="proses_peminjaman.php?aksi=kembali&id=<?= $row['id_pinjam']; ?>&idm=<?= $row['id_mobil']; ?>"
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Mobil sudah dikembalikan?')">
                                            <i class="bi bi-check-lg"></i> Kembali
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="bi bi-check-all"></i> Selesai
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php render_footer_mobil(); ?>

