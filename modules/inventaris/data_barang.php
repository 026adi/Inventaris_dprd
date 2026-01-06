<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Data Master Barang"); 

// =============================
// PARAMETER FILTER
// =============================
$urut   = $_GET['urut'] ?? 'lama';
$search = $_GET['search'] ?? '';
$jenis  = $_GET['jenis'] ?? ''; // <--- Tangkap parameter jenis dari URL

// Urutan data
if ($urut === 'baru') {
    $orderQuery = "ORDER BY id_barang DESC";
} else {
    $orderQuery = "ORDER BY id_barang ASC";
}

// === MEMBANGUN QUERY DENGAN MULTI KONDISI (WHERE) ===
$conditions = [];

// 1. Jika ada filter Search
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "nama_barang LIKE '%$search_safe%'";
}

// 2. Jika ada filter Jenis
if (!empty($jenis)) {
    $jenis_safe = mysqli_real_escape_string($koneksi, $jenis);
    $conditions[] = "jenis = '$jenis_safe'";
}

// Gabungkan semua kondisi dengan 'AND'
$sql = "SELECT * FROM barang";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

// Tambahkan Order
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
                <?php if(!empty($search) || !empty($jenis)): ?>
                    <a href="data_barang.php" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
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
                        <th width="10%">Foto</th>
                        <th>Nama Barang</th>
                        <th width="15%">Jenis</th>
                        <th width="10%">Stok</th>
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
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-search display-6 d-block mb-2 text-secondary"></i>
                                Data tidak ditemukan dengan filter tersebut.
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
                                    <img src="../../assets/uploads/barang/<?= $row['foto']; ?>" class="img-thumbnail rounded" width="60" style="height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/60?text=No+Img" class="img-thumbnail rounded">
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($row['nama_barang']); ?></strong></td>
                            
                            <td>
                                <?php if($row['jenis'] == 'Tetap'): ?>
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
                                    <a href="proses_barang.php?aksi=hapus&id=<?= $row['id_barang']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus barang ini?')"><i class="bi bi-trash"></i></a>
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