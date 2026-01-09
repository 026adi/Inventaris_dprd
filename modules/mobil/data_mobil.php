<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Data Mobil");

// =============================
// SEARCH, FILTER & SORT
// =============================
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort   = $_GET['sort'] ?? 'terbaru';

// =============================
// PAGINATION
// =============================
$limit = 15;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

// =============================
// HITUNG TOTAL DATA
// =============================
$sql_count = "SELECT COUNT(*) as total FROM mobil WHERE 1=1";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql_count .= "
        AND (
            nama_mobil LIKE '%$search_safe%' OR
            plat_nomor LIKE '%$search_safe%'
        )
    ";
}

if (!empty($status) && $status !== 'semua') {
    $sql_count .= " AND status_mobil='$status'";
}

$q_count    = mysqli_query($koneksi, $sql_count);
$total_data = mysqli_fetch_assoc($q_count)['total'];
$total_page = ceil($total_data / $limit);


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
    $sql .= " ORDER BY id_mobil DESC LIMIT $limit OFFSET $offset";
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
            <option value="Tersedia" <?= ($status == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
            <option value="Dipinjam" <?= ($status == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
            <option value="Servis" <?= ($status == 'Servis') ? 'selected' : ''; ?>>Servis</option>
        </select>

        <!-- SORT -->
        <select name="sort" class="form-select" style="width: 150px;">
            <option value="terbaru" <?= ($sort == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
            <option value="terlama" <?= ($sort == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
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

    <button class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalTambahMobil">
        <i class="bi bi-plus-lg"></i> Tambah Mobil Baru
    </button>

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
                                    <?php if (!empty($row['foto']) && file_exists("../../assets/uploads/mobil/" . $row['foto'])): ?>
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
<?php if ($total_page > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">

            <!-- FIRST -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="?page=1&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>">
                    «
                </a>
            </li>

            <!-- PREV -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>">
                    ‹
                </a>
            </li>

            <?php for ($i = max(1, $page - 2); $i <= min($total_page, $page + 2); $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- NEXT -->
            <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>">
                    ›
                </a>
            </li>

            <!-- LAST -->
            <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="?page=<?= $total_page; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>">
                    »
                </a>
            </li>

        </ul>
    </nav>
<?php endif; ?>


<!-- MODAL TAMBAH MOBIL -->
<div class="modal fade" id="modalTambahMobil" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="proses_mobil.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-car-front-fill me-2"></i> Tambah Mobil Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- NAMA MOBIL -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Mobil</label>
                        <input type="text" name="nama_mobil" class="form-control" required>
                    </div>

                    <!-- PLAT NOMOR -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Plat Nomor</label>
                        <input type="text" name="plat_nomor" class="form-control" required>
                    </div>

                    <!-- FOTO MOBIL -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Mobil</label>
                        <input type="file" name="foto" class="form-control">
                    </div>

                    <!-- STATUS -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Mobil</label>
                        <select name="status_mobil" class="form-select">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Servis">Servis</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Data
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<?php render_footer_mobil(); ?>