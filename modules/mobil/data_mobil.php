<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Data Mobil");

// =============================
// SEARCH, FILTER & SORT
// =============================
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort   = $_GET['sort'] ?? 'terbaru';

$sql = "SELECT * FROM mobil WHERE 1=1";

// SEARCH
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= " AND (
        nama_mobil LIKE '%$search_safe%' OR
        plat_nomor LIKE '%$search_safe%' OR
        status_mobil LIKE '%$search_safe%'
    )";
}

// FILTER STATUS
if (!empty($status)) {
    $status_safe = mysqli_real_escape_string($koneksi, $status);
    $sql .= " AND status_mobil = '$status_safe'";
}

// SORT
if ($sort === 'terlama') {
    $sql .= " ORDER BY id_mobil ASC";
} else {
    $sql .= " ORDER BY id_mobil DESC";
}

$q_mobil = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Armada Mobil</h1>
</div>

<!-- SEARCH + FILTER -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

    <form method="GET" class="d-flex gap-2 align-items-center">

        <!-- SEARCH -->
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Cari mobil / plat / status..."
            value="<?= htmlspecialchars($search); ?>"
            style="width: 260px;">

        <!-- FILTER STATUS -->
        <select name="status" class="form-select" style="width: 160px;">
            <option value="">Semua Status</option>
            <option value="Tersedia" <?= ($status=='Tersedia')?'selected':''; ?>>Tersedia</option>
            <option value="Dipinjam" <?= ($status=='Dipinjam')?'selected':''; ?>>Dipinjam</option>
            <option value="Servis" <?= ($status=='Servis')?'selected':''; ?>>Servis</option>
        </select>

        <!-- SORT -->
        <select name="sort" class="form-select" style="width: 150px;">
            <option value="terbaru" <?= ($sort=='terbaru')?'selected':''; ?>>Terbaru</option>
            <option value="terlama" <?= ($sort=='terlama')?'selected':''; ?>>Terlama</option>
        </select>

        <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-search"></i>
        </button>

        <?php if ($search || $status || $sort !== 'terbaru'): ?>
            <a href="data_mobil.php" class="btn btn-outline-secondary">
                Reset
            </a>
        <?php endif; ?>

    </form>

    <a href="tambah_mobil.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Mobil Baru
    </a>
</div>

<?php if (isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Status: <strong><?= htmlspecialchars($_GET['pesan']); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- TABLE -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Foto Mobil</th>
                        <th>Nama & Tipe</th>
                        <th>Plat Nomor</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($q_mobil) == 0):
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Data mobil tidak ditemukan.
                            </td>
                        </tr>
                    <?php
                    else:
                        while ($row = mysqli_fetch_assoc($q_mobil)):
                    ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <?php if (!empty($row['foto']) && file_exists("../../assets/uploads/mobil/".$row['foto'])): ?>
                                        <img src="../../assets/uploads/mobil/<?= $row['foto']; ?>"
                                             class="img-thumbnail rounded"
                                             width="100"
                                             style="height:60px;object-fit:cover;">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/100x60?text=No+Image"
                                             class="img-thumbnail rounded">
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_mobil']); ?></td>
                                <td>
                                    <span class="badge bg-dark font-monospace">
                                        <?= htmlspecialchars($row['plat_nomor']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    if ($row['status_mobil'] === 'Tersedia') {
                                        echo '<span class="badge bg-success">Tersedia</span>';
                                    } elseif ($row['status_mobil'] === 'Dipinjam') {
                                        echo '<span class="badge bg-warning text-dark">Dipinjam</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Servis</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_mobil.php?id=<?= $row['id_mobil']; ?>"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="proses_mobil.php?aksi=hapus&id=<?= $row['id_mobil']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin hapus mobil ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        endwhile;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer_mobil(); ?>
