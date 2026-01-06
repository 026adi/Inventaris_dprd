<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Tambah Barang Baru"); 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Input Barang Baru</h1>
    <a href="data_barang.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_upload'): ?>
            <div class="alert alert-danger" role="alert">
                Gagal mengupload gambar. Pastikan format JPG/PNG dan ukuran wajar.
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Form Barang</h6>
            </div>
            <div class="card-body p-4">
                
                <form action="proses_barang.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Kertas HVS A4, Spidol Boardmarker" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok Awal</label>
                            <input type="number" name="stok" class="form-control" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Satuan</label>
                            <select name="satuan" class="form-select" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="Unit">Unit</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Rim">Rim</option>
                                <option value="Box">Box</option>
                                <option value="Pak">Pak</option>
                                <option value="Buah">Buah</option>
                                <option value="Lembar">Lembar</option>
                                <option value="Set">Set</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto Barang</label>
                        <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        <div class="form-text text-muted">
                            Format yang diperbolehkan: JPG, JPEG, PNG. Pastikan gambar jelas.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i> Simpan Data Barang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer_barang(); ?>