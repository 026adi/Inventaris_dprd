<?php
require_once '../../includes/layout_barang.php';
render_header_barang("Data Master Barang");

// =============================
// 1. FILTER & PENGATURAN
// =============================
$urut   = $_GET['urut'] ?? 'lama';
$search = $_GET['search'] ?? '';
$jenis  = $_GET['jenis'] ?? '';

// Urutan data
$orderQuery = ($urut === 'baru') ? "ORDER BY id_barang DESC" : "ORDER BY id_barang ASC";

// Kondisi Filter
$conditions = [];
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "nama_barang LIKE '%$search_safe%'";
}
if (!empty($jenis)) {
    $jenis_safe = mysqli_real_escape_string($koneksi, $jenis);
    $conditions[] = "jenis = '$jenis_safe'";
}

$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $conditions);
}

// Helper untuk URL Pagination (Agar filter tetap ada saat pindah halaman)
$url_params = "&urut=" . $urut . "&search=" . urlencode($search) . "&jenis=" . urlencode($jenis);

// =============================
// 2. LOGIKA PAGINATION
// =============================
$limit  = 15; // Batas data per halaman
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

// Hitung Total Data (Sesuai Filter)
$sql_count  = "SELECT COUNT(*) as total FROM barang $where_sql";
$q_count    = mysqli_query($koneksi, $sql_count);
$data_count = mysqli_fetch_assoc($q_count);
$total_data = $data_count['total'];
$total_page = ceil($total_data / $limit);

// =============================
// 3. QUERY DATA UTAMA
// =============================
$sql = "SELECT * FROM barang $where_sql $orderQuery LIMIT $limit OFFSET $offset";
$query = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Master Barang</h1>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-2"></i> Tambah Barang
    </button>
</div>

<?php if (isset($_GET['pesan'])): ?>
    <?php
    $msg = $_GET['pesan'];
    $alert_cls = ($msg == 'gagal_db' || $msg == 'gagal_hapus') ? 'danger' : 'success';
    $txt = '';
    if ($msg == 'sukses') $txt = "Data barang berhasil disimpan.";
    elseif ($msg == 'update') $txt = "Data barang berhasil diperbarui.";
    elseif ($msg == 'hapus') $txt = "Data barang berhasil dihapus.";
    else $txt = "Terjadi kesalahan pada sistem.";
    ?>
    <div class="alert alert-<?= $alert_cls; ?> alert-dismissible fade show" role="alert">
        <strong>Status:</strong> <?= $txt; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-3 bg-light border-0">
    <div class="card-body p-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama barang..." value="<?= htmlspecialchars($search); ?>">
            </div>
            <div class="col-auto">
                <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">- Semua Jenis -</option>
                    <option value="Habis Pakai" <?= $jenis == 'Habis Pakai' ? 'selected' : ''; ?>>Habis Pakai</option>
                    <option value="Tetap" <?= $jenis == 'Tetap' ? 'selected' : ''; ?>>Tetap (Aset)</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="urut" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="lama" <?= $urut === 'lama' ? 'selected' : ''; ?>>Terlama</option>
                    <option value="baru" <?= $urut === 'baru' ? 'selected' : ''; ?>>Terbaru</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Cari</button>
                <?php if (!empty($search) || !empty($jenis)): ?>
                    <a href="data_barang.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Barang</th>
                        <th width="15%">Jenis</th>
                        <th width="10%">Stok</th>
                        <th width="10%">Satuan</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1; // Nomor urut mengikuti halaman
                    if (mysqli_num_rows($query) == 0):
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Data tidak ditemukan.</td>
                        </tr>
                        <?php
                    else:
                        while ($row = mysqli_fetch_assoc($query)):
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_barang']); ?></strong></td>
                                <td>
                                    <?php if ($row['jenis'] == 'Tetap'): ?>
                                        <span class="badge bg-indigo text-white" style="background-color: #6610f2;">Aset Tetap</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Habis Pakai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $badge_color = ($row['stok'] < 5) ? 'bg-danger' : 'bg-success'; ?>
                                    <span class="badge <?= $badge_color; ?> fs-6"><?= (int)$row['stok']; ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['satuan']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_barang.php?id=<?= $row['id_barang']; ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i></a>
                                        <a href="proses_barang.php?aksi=hapus&id=<?= $row['id_barang']; ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                    <?php endwhile;
                    endif; ?>
                </tbody>
            </table>

            <?php if ($total_page > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">

                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?><?= $url_params; ?>">
                                &laquo;
                            </a>
                        </li>

                        <?php
                        $start = max(1, $page - 2);
                        $end   = min($total_page, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?><?= $url_params; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?><?= $url_params; ?>">
                                &raquo;
                            </a>
                        </li>

                        <li class="page-item <?= ($page == $total_page) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $total_page; ?><?= $url_params; ?>">Last</a>
                        </li>

                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Input Barang Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="proses_barang.php" method="POST">
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop, Kertas A4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Barang</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Habis Pakai">Habis Pakai (ATK/Bahan)</option>
                            <option value="Tetap">Tetap (Aset Elektronik/Mebel)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok Awal</label>
                            <input type="number" name="stok" class="form-control" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Satuan</label>
                            <select name="satuan" class="form-select" required>
                                <option value="Unit">Unit</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Rim">Rim</option>
                                <option value="Box">Box</option>
                                <option value="Pak">Pak</option>
                                <option value="Buah">Buah</option>
                                <option value="Set">Set</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php render_footer_barang(); ?>