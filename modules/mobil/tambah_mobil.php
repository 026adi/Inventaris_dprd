<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Tambah Mobil Baru"); 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Input Mobil Baru</h1>
    <a href="data_mobil.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_upload'): ?>
            <div class="alert alert-danger" role="alert">
                Gagal mengupload foto. Pastikan format gambar benar (JPG/PNG).
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-car-front-fill me-2"></i>Form Data Mobil</h6>
            </div>
            <div class="card-body p-4">
                
                <form action="proses_mobil.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama & Tipe Mobil</label>
                        <input type="text" name="nama_mobil" class="form-control" placeholder="Contoh: Toyota Avanza Veloz 2022" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Plat Nomor</label>
                            <input type="text" name="plat_nomor" class="form-control" placeholder="Contoh: AB 1234 XY" required>
                            <div class="form-text">Pastikan plat nomor belum terdaftar sebelumnya.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status Awal</label>
                            <select name="status_mobil" class="form-select" required>
                                <option value="Tersedia">Tersedia (Siap Pakai)</option>
                                <option value="Servis">Servis (Bengkel)</option>
                                </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto Mobil</label>
                        <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        <div class="form-text text-muted">Format: JPG, JPEG, PNG. Wajib diisi agar mudah dikenali.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i> Simpan Data Mobil
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer_mobil(); ?>