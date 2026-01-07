<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Peminjaman Kendaraan");

// =============================
// SEARCH & FILTER
// =============================
$search = $_GET['search'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

// =============================
// QUERY DATA PEMINJAMAN
// =============================
$sql = "
    SELECT p.*, m.nama_mobil, m.plat_nomor
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE 1=1
";

// SEARCH TEXT
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= "
        AND (
            m.nama_mobil LIKE '%$search_safe%' OR
            m.plat_nomor LIKE '%$search_safe%' OR
            p.nama_peminjam LIKE '%$search_safe%' OR
            p.tujuan LIKE '%$search_safe%'
        )
    ";
}

// FILTER TANGGAL (KALENDER)
if (!empty($from) && !empty($to)) {
    $sql .= "
        AND (
            p.tgl_pinjam <= '$to'
            AND p.tgl_rencana_kembali >= '$from'
        )
    ";
}

// URUTKAN: BELUM → SUDAH
$sql .= " ORDER BY FIELD(p.status_kembali, 'Belum', 'Sudah'), p.id_pinjam DESC";

$q_pinjam = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Peminjaman</h1>
    <a href="tambah_peminjaman.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Pinjam Mobil
    </a>
</div>

<?php if (isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Status:</strong> <?= htmlspecialchars($_GET['pesan']); ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">

        <!-- SEARCH + FILTER -->
        <div class="mb-3">
            <form method="GET" class="row g-2 align-items-end">

                <!-- SEARCH KIRI -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari mobil / plat / peminjam / tujuan..."
                        value="<?= htmlspecialchars($search); ?>">
                </div>

                <!-- TGL MASUK -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tgl Masuk</label>
                    <input
                        type="date"
                        name="from"
                        class="form-control"
                        value="<?= htmlspecialchars($from); ?>">
                </div>

                <!-- TGL KELUAR -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tgl Keluar</label>
                    <input
                        type="date"
                        name="to"
                        class="form-control"
                        value="<?= htmlspecialchars($to); ?>">
                </div>

                <!-- BUTTON -->
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>

                    <?php if ($search || $from || $to): ?>
                        <a href="peminjaman.php" class="btn btn-outline-secondary w-100">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>


        <!-- TABEL -->
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
                            <th>Tgl Rencana Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($q_pinjam) == 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Data peminjaman tidak ditemukan
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($q_pinjam)): ?>
                                <tr class="<?= ($row['status_kembali'] === 'Belum') ? 'table-warning' : ''; ?>">

                                    <!-- MOBIL -->
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_mobil']); ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['plat_nomor']); ?></small>
                                    </td>

                                    <!-- PEMINJAM -->
                                    <td>
                                        <?= htmlspecialchars($row['nama_peminjam']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['tujuan']); ?></small>
                                    </td>

                                    <!-- TGL PINJAM -->
                                    <td>
                                        <?= $row['tgl_pinjam']
                                            ? date('d/m/Y', strtotime($row['tgl_pinjam']))
                                            : '<span class="text-muted">—</span>'; ?>
                                    </td>

                                    <!-- TGL RENCANA KEMBALI -->
                                    <td class="text-center">
                                        <?= $row['tgl_rencana_kembali']
                                            ? date('d/m/Y', strtotime($row['tgl_rencana_kembali']))
                                            : '<span class="text-muted">—</span>'; ?>
                                    </td>

                                    <!-- STATUS -->
                                    <td>
                                        <?php if ($row['status_kembali'] == 'Belum'): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock-history me-1"></i> Masih Dipinjam
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Sudah Kembali<br>
                                                <small class="text-white-50">
                                                    <?= date('d/m/Y'); ?>
                                                </small>
                                            </span>
                                        <?php endif; ?>

                                    </td>

                                    <!-- AKSI -->
                                    <td>
                                        <?php if ($row['status_kembali'] === 'Belum'): ?>
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