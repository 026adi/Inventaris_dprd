<?php
require_once '../../includes/layout_barang.php';
render_header_barang("Riwayat Barang");

// =============================
// SEARCH RIWAYAT
// =============================
$search = $_GET['search'] ?? '';

$sql = "
    SELECT r.*, b.nama_barang, b.satuan
    FROM riwayat_barang r
    JOIN barang b ON r.id_barang = b.id_barang
";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= "
        WHERE
            b.nama_barang LIKE '%$search_safe%'
            OR r.jenis_transaksi LIKE '%$search_safe%'
            OR r.keterangan LIKE '%$search_safe%'
    ";
}

$sql .= " ORDER BY r.tanggal DESC";
$query = mysqli_query($koneksi, $sql);

// Dropdown barang
$q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Riwayat Barang Masuk & Keluar</h1>
</div>

<?php if (isset($_GET['pesan'])): ?>
    <?php
    $msg = $_GET['pesan'];
    $alert = ($msg == 'stok_kurang' || $msg == 'gagal') ? 'danger' : 'success';

    $text = match ($msg) {
        'sukses' => 'Data riwayat berhasil disimpan & stok diperbarui.',
        'stok_kurang' => 'Gagal! Stok barang tidak mencukupi.',
        'dibatalkan' => 'Riwayat dihapus & stok dikembalikan.',
        default => ''
    };
    ?>
    <div class="alert alert-<?= $alert ?> alert-dismissible fade show">
        <strong>Status:</strong> <?= $text ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">

    <!-- KIRI : CATAT AKTIVITAS -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Catat Aktivitas</h6>
            </div>
            <div class="card-body">
                <form action="proses_riwayat.php" method="POST">

                    <div class="mb-3">
                        <label class="fw-bold">Pilih Barang</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php while ($b = mysqli_fetch_assoc($q_barang)): ?>
                                <option value="<?= $b['id_barang'] ?>">
                                    <?= $b['nama_barang'] ?> (<?= $b['stok'] ?> <?= $b['satuan'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Jenis Aktivitas</label>
                        <select name="jenis_transaksi" class="form-select" required>
                            <option value="masuk">Barang Masuk</option>
                            <option value="keluar">Barang Keluar</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" required></textarea>
                    </div>

                    <button class="btn btn-primary w-100">
                        Simpan & Update Stok
                    </button>

                </form>
            </div>
        </div>
    </div>

    <!-- KANAN : SEARCH + TABEL -->
    <div class="col-md-8">

        <!-- SEARCH -->
        <div class="mb-3 d-flex justify-content-end">
            <form method="GET" class="d-flex gap-2">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari barang / jenis / keterangan..."
                    value="<?= htmlspecialchars($search) ?>"
                    style="width:450px"
                >
                <button class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>

                <?php if ($search): ?>
                    <a href="riwayat.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL RIWAYAT -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Daftar Riwayat Terakhir</h6>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Jenis</th>
                            <th>Jml</th>
                            <th>Ket</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) == 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Data tidak ditemukan</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($r = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                                    <td><strong><?= $r['nama_barang'] ?></strong></td>
                                    <td>
                                        <span class="badge <?= $r['jenis_transaksi']=='keluar'?'bg-danger':'bg-success' ?>">
                                            <?= ucfirst($r['jenis_transaksi']) ?>
                                        </span>
                                    </td>
                                    <td><?= $r['jumlah'] ?></td>
                                    <td><?= $r['keterangan'] ?></td>
                                    <td class="text-center">
                                        <a href="proses_riwayat.php?aksi=hapus&id=<?= $r['id_riwayat'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin hapus?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<?php render_footer_barang(); ?>
