<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Data Mobil"); 

// =============================
// OPSI URUTAN DATA
// =============================
$urut = $_GET['urut'] ?? 'lama';

if ($urut === 'baru') {
    $orderQuery = "ORDER BY id_mobil DESC";
} else {
    $orderQuery = "ORDER BY id_mobil ASC";
}

// Ambil data mobil dari database
$query = mysqli_query($koneksi, "SELECT * FROM mobil $orderQuery");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Armada Mobil</h1>
    <a href="tambah_mobil.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Mobil Baru
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Status: <strong><?= htmlspecialchars($_GET['pesan']); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTER URUTAN -->
<div class="mb-3 d-flex justify-content-end">
    <form method="GET" class="d-flex align-items-center gap-2">
        <label for="urut" class="fw-semibold mb-0">Urutkan:</label>
        <select name="urut" id="urut" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
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

                    if(mysqli_num_rows($query) == 0): 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Belum ada data mobil. Silakan tambah data baru.
                            </td>
                        </tr>
                    <?php 
                    else:
                        while($row = mysqli_fetch_assoc($query)): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if(!empty($row['foto']) && file_exists("../../assets/uploads/mobil/" . $row['foto'])): ?>
                                    <img src="../../assets/uploads/mobil/<?= $row['foto']; ?>" 
                                         class="img-thumbnail rounded" 
                                         width="100" 
                                         style="height: 60px; object-fit: cover;">
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
                                if($row['status_mobil'] === 'Tersedia') {
                                    echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Tersedia</span>';
                                } elseif($row['status_mobil'] === 'Dipinjam') {
                                    echo '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Dipinjam</span>';
                                } else {
                                    echo '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Servis</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="edit_mobil.php?id=<?= $row['id_mobil']; ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="proses_mobil.php?aksi=hapus&id=<?= $row['id_mobil']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Hapus Mobil"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus mobil ini? Data peminjaman terkait juga akan hilang.')">
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
