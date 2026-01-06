<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Edit Data Barang"); 

// 1. Ambil ID dari URL
$id = $_GET['id'];

// 2. Query Data Barang Lama
$query = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = '$id'");
$data  = mysqli_fetch_assoc($query);

// Cek jika data tidak ditemukan (misal ID salah)
if (mysqli_num_rows($query) < 1) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='data_barang.php';</script>";
    exit;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Barang</h1>
    <a href="data_barang.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Form Edit Barang</h6>
            </div>
            <div class="card-body p-4">
                
                <form action="proses_barang.php" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id_barang" value="<?= $data['id_barang']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" value="<?= $data['nama_barang']; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jenis Barang</label>
                            <select name="jenis" class="form-select" required>
                                <option value="Habis Pakai" <?= ($data['jenis'] == 'Habis Pakai') ? 'selected' : ''; ?>>Habis Pakai</option>
                                <option value="Tetap" <?= ($data['jenis'] == 'Tetap') ? 'selected' : ''; ?>>Tetap (Aset)</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stok Saat Ini</label>
                            <input type="number" name="stok" class="form-control" value="<?= $data['stok']; ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Satuan</label>
                            <select name="satuan" class="form-select" required>
                                <option value="Unit" <?= ($data['satuan'] == 'Unit') ? 'selected' : ''; ?>>Unit</option>
                                <option value="Pcs" <?= ($data['satuan'] == 'Pcs') ? 'selected' : ''; ?>>Pcs</option>
                                <option value="Rim" <?= ($data['satuan'] == 'Rim') ? 'selected' : ''; ?>>Rim</option>
                                <option value="Box" <?= ($data['satuan'] == 'Box') ? 'selected' : ''; ?>>Box</option>
                                <option value="Pak" <?= ($data['satuan'] == 'Pak') ? 'selected' : ''; ?>>Pak</option>
                                <option value="Buah" <?= ($data['satuan'] == 'Buah') ? 'selected' : ''; ?>>Buah</option>
                                <option value="Lembar" <?= ($data['satuan'] == 'Lembar') ? 'selected' : ''; ?>>Lembar</option>
                                <option value="Set" <?= ($data['satuan'] == 'Set') ? 'selected' : ''; ?>>Set</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ganti Foto (Opsional)</label>
                        <div class="d-flex align-items-center gap-3">
                            <?php if(!empty($data['foto']) && file_exists("../../assets/uploads/barang/" . $data['foto'])): ?>
                                <img src="../../assets/uploads/barang/<?= $data['foto']; ?>" width="80" class="img-thumbnail rounded">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/80?text=No+Img" class="img-thumbnail rounded">
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="update" class="btn btn-warning fw-bold">
                            <i class="bi bi-save me-2"></i> Update Data Barang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer_barang(); ?>