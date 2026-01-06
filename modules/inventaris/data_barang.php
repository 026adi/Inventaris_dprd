<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Data Master Barang"); 

// =============================
// PARAMETER FILTER
// =============================
$urut   = $_GET['urut'] ?? 'lama';
$search = $_GET['search'] ?? '';

// Urutan data
if ($urut === 'baru') {
    $orderQuery = "ORDER BY id_barang DESC";
} else {
    $orderQuery = "ORDER BY id_barang ASC";
}

// Query dasar
$sql = "SELECT * FROM barang";

// Jika ada keyword search
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= " WHERE nama_barang LIKE '%$search_safe%'";
}

// Gabungkan dengan order
$sql .= " $orderQuery";

// Eksekusi query
$query = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Master Barang</h1>
    <a href="tambah_barang.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Barang
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Status: <strong><?= htmlspecialchars($_GET['pesan']); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTER: SEARCH + URUTAN -->
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

    <!-- SEARCH -->
    <form method="GET" class="d-flex gap-2">
        <input type="hidden" name="urut" value="<?= htmlspecialchars($urut); ?>">
        <input 
            type="text" 
            name="search" 
            class="form-control form-control-sm" 
            placeholder="Cari nama barang..."
            value="<?= htmlspecialchars($search); ?>"
            style="max-width: 250px;"
        >
        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-search"></i>
        </button>
        <?php if(!empty($search)): ?>
            <a href="data_barang.php?urut=<?= htmlspecialchars($urut); ?>" 
               class="btn btn-sm btn-outline-secondary">
                Reset
            </a>
        <?php endif; ?>
    </form>

    <!-- URUTAN -->
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
        <label for="urut" class="fw-semibold mb-0">Urutkan:</label>
        <select name="urut" id="urut" 
                class="form-select form-select-sm w-auto" 
                onchange="this.form.submit()">
            <option value="lama" <?= $urut === 'lama' ? 'selected' : ''; ?>>Terlama</option>
            <option value="baru" <?= $urut === 'baru' ? 'selected' : ''; ?>>Terbaru</option>
        </select>
    </form>

</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Foto</th>
                        <th>Nama Barang</th>
                        <th width="15%">Stok</th>
                        <th width="10%">Satuan</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 

                    if(mysqli_num_rows($query) == 0): 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Data barang tidak ditemukan.
                            </td>
                        </tr>
                    <?php 
                    else:
                        while($row = mysqli_fetch_assoc($query)): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if(!empty($row['foto']) && file_exists("../../assets/uploads/barang/" . $row['foto'])): ?>
                                    <img src="../../assets/uploads/barang/<?= $row['foto']; ?>" 
                                         class="img-thumbnail rounded" 
                                         width="80" 
                                         style="height: 80px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/80?text=No+Img" 
                                         class="img-thumbnail rounded">
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_barang']); ?></strong>
                            </td>
                            <td>
                                <?php 
                                    $badge_color = ($row['stok'] < 5) ? 'bg-danger' : 'bg-success'; 
                                ?>
                                <span class="badge <?= $badge_color; ?> fs-6">
                                    <?= (int)$row['stok']; ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['satuan']); ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="edit_barang.php?id=<?= $row['id_barang']; ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="proses_barang.php?aksi=hapus&id=<?= $row['id_barang']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Hapus Data"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini? Data yang dihapus tidak bisa dikembalikan.')">
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

<?php render_footer_barang(); ?>
