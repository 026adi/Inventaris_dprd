<?php 
require_once '../../includes/layout_mobil.php'; 
render_header_mobil("Peminjaman Kendaraan"); 

// 1. Ambil Mobil yang TERSEDIA saja untuk form
$q_ready = mysqli_query($koneksi, "SELECT * FROM mobil WHERE status_mobil='Tersedia' ORDER BY nama_mobil ASC");

// 2. Ambil Daftar Peminjaman (Status 'Belum' kembali ada di atas)
$q_pinjam = mysqli_query($koneksi, "SELECT peminjaman.*, mobil.nama_mobil, mobil.plat_nomor 
                                    FROM peminjaman 
                                    JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil 
                                    ORDER BY FIELD(status_kembali, 'Belum', 'Sudah'), id_pinjam DESC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Peminjaman</h1>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Status: <strong><?= $_GET['pesan']; ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-key-fill me-2"></i>Form Pinjam Mobil</h6>
            </div>
            <div class="card-body">
                <form action="proses_peminjaman.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Mobil Ready</label>
                        <select name="id_mobil" class="form-select" required>
                            <option value="">-- Pilih Mobil --</option>
                            <?php 
                            if(mysqli_num_rows($q_ready) > 0) {
                                while($m = mysqli_fetch_assoc($q_ready)): ?>
                                    <option value="<?= $m['id_mobil']; ?>">
                                        <?= $m['nama_mobil']; ?> - [<?= $m['plat_nomor']; ?>]
                                    </option>
                                <?php endwhile; 
                            } else {
                                echo '<option value="" disabled>Semua mobil sedang dipakai!</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" class="form-control" placeholder="Nama Pegawai / Anggota Dewan" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tujuan / Keperluan</label>
                        <textarea name="tujuan" class="form-control" rows="2" placeholder="Contoh: Kunjungan Kerja ke Bantul" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" name="pinjam" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Proses Peminjaman
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Monitoring Peminjaman</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Mobil</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($q_pinjam)): ?>
                            <tr class="<?= ($row['status_kembali'] == 'Belum') ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?= $row['nama_mobil']; ?></strong><br>
                                    <small class="text-muted"><?= $row['plat_nomor']; ?></small>
                                </td>
                                <td>
                                    <?= $row['nama_peminjam']; ?><br>
                                    <small class="text-muted"><?= $row['tujuan']; ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td>
                                    <?php if($row['status_kembali'] == 'Belum'): ?>
                                        <span class="badge bg-danger">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Kembali</span><br>
                                        <small class="text-muted"><?= date('d/m/y', strtotime($row['tgl_kembali'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status_kembali'] == 'Belum'): ?>
                                        <a href="proses_peminjaman.php?aksi=kembali&id=<?= $row['id_pinjam']; ?>&idm=<?= $row['id_mobil']; ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Proses Pengembalian"
                                           onclick="return confirm('Mobil sudah dikembalikan ke kantor?')">
                                            <i class="bi bi-check-lg"></i> Kembali
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="bi bi-check-all"></i> Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer_mobil(); ?>