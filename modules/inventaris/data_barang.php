<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Data Master Barang"); 

// Ambil semua data barang dari database (urut terbaru)
$query = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang DESC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Master Barang</h1>
    <a href="tambah_barang.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Barang
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Status: <strong><?= $_GET['pesan']; ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                    // Cek jika data kosong
                    if (mysqli_num_rows($query) == 0) {
                        echo '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data barang. Silakan tambah data baru.</td></tr>';
                    }

                    while($row = mysqli_fetch_assoc($query)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <?php if(!empty($row['foto']) && file_exists("../../assets/uploads/barang/" . $row['foto'])): ?>
                                <img src="../../assets/uploads/barang/<?= $row['foto']; ?>" class="img-thumbnail rounded" width="80" style="height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/80?text=No+Img" class="img-thumbnail rounded">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= $row['nama_barang']; ?></strong>
                        </td>
                        <td>
                            <?php 
                                $badge_color = ($row['stok'] < 5) ? 'bg-danger' : 'bg-success'; 
                            ?>
                            <span class="badge <?= $badge_color; ?> fs-6">
                                <?= $row['stok']; ?>
                            </span>
                        </td>
                        <td><?= $row['satuan']; ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="edit_barang.php?id=<?= $row['id_barang']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Data">
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
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer_barang(); ?>