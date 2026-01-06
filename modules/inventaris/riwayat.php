<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Riwayat Barang"); 

// 1. Ambil daftar barang untuk Dropdown Pilihan
$q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");

// 2. Ambil Data Riwayat (Join dengan tabel Barang untuk mendapatkan nama barang & satuan)
$q_riwayat = mysqli_query($koneksi, "SELECT riwayat_barang.*, barang.nama_barang, barang.satuan 
                                     FROM riwayat_barang 
                                     JOIN barang ON riwayat_barang.id_barang = barang.id_barang 
                                     ORDER BY id_riwayat DESC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Riwayat Barang Masuk & Keluar</h1>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <?php 
        $msg = $_GET['pesan'];
        $alert_type = ($msg == 'stok_kurang' || $msg == 'gagal') ? 'danger' : 'success';
        $text = '';
        
        if($msg == 'sukses') $text = 'Data riwayat berhasil disimpan & Stok diperbarui.';
        elseif($msg == 'stok_kurang') $text = 'Gagal! Stok barang tidak mencukupi untuk pengeluaran.';
        elseif($msg == 'dibatalkan') $text = 'Riwayat berhasil dihapus dan Stok dikembalikan.';
    ?>
    <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
        <strong>Status:</strong> <?= $text; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Catat Aktivitas</h6>
            </div>
            <div class="card-body">
                <form action="proses_riwayat.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Barang</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">-- Cari Barang --</option>
                            <?php while($b = mysqli_fetch_assoc($q_barang)): ?>
                                <option value="<?= $b['id_barang']; ?>">
                                    <?= $b['nama_barang']; ?> (Sisa: <?= $b['stok']; ?> <?= $b['satuan']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Aktivitas</label>
                        <select name="jenis_transaksi" class="form-select" required>
                            <option value="masuk">Barang Masuk (Stok Bertambah)</option>
                            <option value="keluar">Barang Keluar (Stok Berkurang)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" min="1" placeholder="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan / Keperluan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pembelian stok baru / Permintaan Divisi Umum" required></textarea>
                    </div>

                    <button type="submit" name="simpan_riwayat" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Simpan & Update Stok
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Daftar Riwayat Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Jenis</th>
                                <th>Jml</th>
                                <th>Ket</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($rw = mysqli_fetch_assoc($q_riwayat)): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($rw['tanggal'])); ?></td>
                                <td class="fw-bold"><?= $rw['nama_barang']; ?></td>
                                <td>
                                    <?php if($rw['jenis_transaksi'] == 'masuk'): ?>
                                        <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Masuk</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up"></i> Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $rw['jumlah'] . ' ' . $rw['satuan']; ?></td>
                                <td class="small text-muted text-truncate" style="max-width: 150px;">
                                    <?= $rw['keterangan']; ?>
                                </td>
                                <td>
                                    <a href="proses_riwayat.php?aksi=hapus&id=<?= $rw['id_riwayat']; ?>&idb=<?= $rw['id_barang']; ?>&qty=<?= $rw['jumlah']; ?>&tipe=<?= $rw['jenis_transaksi']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0" 
                                       title="Batalkan Riwayat"
                                       onclick="return confirm('Yakin ingin membatalkan riwayat ini? Stok barang akan dikembalikan seperti semula.')">
                                        <i class="bi bi-trash"></i>
                                    </a>
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

<?php render_footer_barang(); ?>