<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Edit Data Mobil"); 

// 1. Ambil ID dari URL
$id = $_GET['id'];

// 2. Query Data Mobil Lama
$query = mysqli_query($koneksi, "SELECT * FROM mobil WHERE id_mobil = '$id'");
$data  = mysqli_fetch_assoc($query);

// Cek jika data tidak ditemukan
if (mysqli_num_rows($query) < 1) {
    echo "<script>alert('Data mobil tidak ditemukan!'); window.location='data_mobil.php';</script>";
    exit;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Data Mobil</h1>
    <a href="data_mobil.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Form Edit Mobil</h6>
            </div>
            <div class="card-body p-4">
                
                <form action="proses_mobil.php" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id_mobil" value="<?= $data['id_mobil']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama & Tipe Mobil</label>
                        <input type="text" name="nama_mobil" class="form-control" value="<?= $data['nama_mobil']; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Plat Nomor</label>
                            <input type="text" name="plat_nomor" class="form-control" value="<?= $data['plat_nomor']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status Mobil</label>
                            <select name="status_mobil" class="form-select" required>
                                <option value="Tersedia" <?= ($data['status_mobil'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="Servis" <?= ($data['status_mobil'] == 'Servis') ? 'selected' : ''; ?>>Servis / Bengkel</option>
                            </select>
                            <div class="form-text text-muted">Hati-hati mengubah status 'Dipinjam' secara manual.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ganti Foto (Opsional)</label>
                        <div class="d-flex align-items-center gap-3">
                            <?php if(!empty($data['foto']) && file_exists("../../assets/uploads/mobil/" . $data['foto'])): ?>
                                <img src="../../assets/uploads/mobil/<?= $data['foto']; ?>" width="100" class="img-thumbnail rounded">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/100?text=No+Img" class="img-thumbnail rounded">
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto mobil.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="update" class="btn btn-warning fw-bold">
                            <i class="bi bi-save me-2"></i> Update Data Mobil
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer_mobil(); ?>