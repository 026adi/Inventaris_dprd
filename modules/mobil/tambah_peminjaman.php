<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Form Peminjaman Mobil");

// Mobil ready
$q_ready = mysqli_query(
    $koneksi,
    "SELECT * FROM mobil 
     WHERE status_mobil='Tersedia' 
     ORDER BY nama_mobil ASC"
);
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Form Peminjaman Mobil</h1>
    <a href="peminjaman.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-key-fill me-2"></i>Data Peminjaman</h6>
            </div>
            <div class="card-body">
                <form action="proses_peminjaman.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Mobil Ready</label>
                        <select name="id_mobil" class="form-select" required>
                            <option value="">-- Pilih Mobil --</option>
                            <?php if (mysqli_num_rows($q_ready) > 0): ?>
                                <?php while ($m = mysqli_fetch_assoc($q_ready)): ?>
                                    <option value="<?= $m['id_mobil']; ?>">
                                        <?= $m['nama_mobil']; ?> - <?= $m['plat_nomor']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option disabled>Semua mobil sedang dipakai</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tujuan / Keperluan</label>
                        <textarea name="tujuan" class="form-control" rows="2" required></textarea>
                    </div>

                    <!-- MODE PINJAM -->
                    <!-- MODE PINJAM -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode Peminjaman</label>
                        <select class="form-select" id="modePinjam" name="mode_pinjam">
                            <option value="1hari">1 Hari</option>
                            <option value="rentang">Rentang Tanggal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam"
                            id="tglPinjam"
                            class="form-control"
                            value="<?= date('Y-m-d'); ?>"
                            required>
                    </div>

                    <div class="mb-3 d-none" id="tglKembaliBox">
                        <label class="form-label fw-bold">Tanggal Rencana Kembali</label>
                        <input type="date" name="tgl_rencana_kembali"
                            id="tglRencana"
                            class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Surat Pengajuan (Opsional)
                        </label>
                        <input
                            type="file"
                            name="surat_pengajuan"
                            class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">
                            Format: PDF / JPG / PNG
                        </small>
                    </div>


                    <button type="submit" name="pinjam" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Proses Peminjaman
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const mode = document.getElementById('modePinjam');
    const kembaliBox = document.getElementById('tglKembaliBox');
    const tglPinjam = document.getElementById('tglPinjam');
    const tglRencana = document.getElementById('tglRencana');

    mode.addEventListener('change', () => {
        if (mode.value === '1hari') {
            kembaliBox.classList.add('d-none');
            tglRencana.value = tglPinjam.value; // AUTO SET
        } else {
            kembaliBox.classList.remove('d-none');
            tglRencana.value = '';
        }
    });
</script>


<?php render_footer_mobil(); ?>