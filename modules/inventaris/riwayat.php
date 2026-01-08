<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Riwayat Transaksi"); 

// =============================
// LOGIKA FILTER (SEARCH & TANGGAL)
// =============================
$search   = $_GET['search'] ?? '';
$tgl_cari = $_GET['tgl_cari'] ?? '';

$conditions = [];

// 1. Filter Pencarian Teks
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(barang.nama_barang LIKE '%$search_safe%' 
                      OR riwayat_barang.jenis_transaksi LIKE '%$search_safe%' 
                      OR riwayat_barang.unit_penerima LIKE '%$search_safe%' 
                      OR riwayat_barang.keterangan LIKE '%$search_safe%')";
}

// 2. Filter Tanggal
if (!empty($tgl_cari)) {
    $tgl_safe = mysqli_real_escape_string($koneksi, $tgl_cari);
    $conditions[] = "riwayat_barang.tanggal = '$tgl_safe'";
}

$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $conditions);
}

// Ambil daftar barang untuk Dropdown Modal
$q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");

// Query Utama Riwayat (Tidak mengambil kolom foto)
$query_sql = "SELECT riwayat_barang.*, barang.nama_barang, barang.satuan 
              FROM riwayat_barang 
              JOIN barang ON riwayat_barang.id_barang = barang.id_barang 
              $where_sql
              ORDER BY id_riwayat DESC";
$q_riwayat = mysqli_query($koneksi, $query_sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Riwayat Barang Masuk & Keluar</h1>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <?php 
        $msg = $_GET['pesan'];
        $alert_type = ($msg == 'stok_kurang' || $msg == 'gagal') ? 'danger' : 'success';
        $text = '';
        if($msg == 'sukses') $text = 'Data berhasil disimpan & Stok diperbarui.';
        elseif($msg == 'stok_kurang') $text = 'Gagal! Stok tidak cukup.';
        elseif($msg == 'dibatalkan') $text = 'Riwayat dihapus, stok dikembalikan.';
    ?>
    <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
        <strong>Status:</strong> <?= $text; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCatat">
                <i class="bi bi-plus-lg me-1"></i> Catat Aktivitas
            </button>

            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="date" name="tgl_cari" class="form-control" value="<?= htmlspecialchars($tgl_cari); ?>" title="Filter berdasarkan tanggal">
                <input type="text" name="search" class="form-control" placeholder="Cari barang / unit..." value="<?= htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                <?php if(!empty($search) || !empty($tgl_cari)): ?>
                    <a href="riwayat.php" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Unit / Asal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_riwayat) == 0): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada data riwayat.</td></tr>
                    <?php endif; ?>

                    <?php while($rw = mysqli_fetch_assoc($q_riwayat)): ?>
                    <tr>
                        <td>
                            <?= date('d/m/Y', strtotime($rw['tanggal'])); ?>
                        </td>
                        <td>
                            <strong><?= $rw['nama_barang']; ?></strong><br>
                            <small class="text-muted fst-italic"><?= $rw['keterangan']; ?></small>
                        </td>
                        <td>
                            <?php if($rw['jenis_transaksi'] == 'masuk'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-arrow-down"></i> Masuk</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-arrow-up"></i> Keluar</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= $rw['jumlah'] . ' ' . $rw['satuan']; ?></td>
                        <td>
                            <?php if(!empty($rw['unit_penerima'])): ?>
                                <span class="badge bg-secondary text-dark bg-opacity-10 border"><?= $rw['unit_penerima']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="proses_riwayat.php?aksi=hapus&id=<?= $rw['id_riwayat']; ?>&idb=<?= $rw['id_barang']; ?>&qty=<?= $rw['jumlah']; ?>&tipe=<?= $rw['jenis_transaksi']; ?>" 
                               class="btn btn-sm btn-outline-danger border-0" 
                               onclick="return confirm('Batalkan transaksi ini? Stok akan dikembalikan.')">
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

<div class="modal fade" id="modalCatat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Catat Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_riwayat.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Aktivitas</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-select" onchange="toggleUnitInput()" required>
                            <option value="keluar">Barang Keluar (Permintaan Unit)</option>
                            <option value="masuk">Barang Masuk (Pengadaan/Beli)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Barang</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">-- Cari Barang --</option>
                            <?php mysqli_data_seek($q_barang, 0); while($b = mysqli_fetch_assoc($q_barang)): ?>
                                <option value="<?= $b['id_barang']; ?>"><?= $b['nama_barang']; ?> (Stok: <?= $b['stok']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3 p-3 bg-light border rounded" id="area_unit">
                        <label class="form-label fw-bold small text-muted text-uppercase">Unit Peminta / Penerima</label>
                        <div class="mb-2">
                            <select id="kategori_unit" class="form-select form-select-sm" onchange="updateUnitOptions()">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Bagian">Bagian (Sekretariat)</option>
                                <option value="Komisi">Komisi</option>
                                <option value="Fraksi">Fraksi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <select id="detail_unit" name="unit_penerima" class="form-select form-select-sm" disabled>
                            <option value="">-- Pilih Detail Unit --</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" min="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_riwayat" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateUnitOptions() {
    const dataUnit = {
        'Bagian': ['Bagian Persidangan & Perundang-undangan', 'Bagian Admin Keuangan', 'Bagian Admin Umum & Humas'],
        'Komisi': ['Komisi A', 'Komisi B', 'Komisi C', 'Komisi D'],
        'Fraksi': ['Fraksi PDIP', 'Fraksi PAN', 'Fraksi Golkar', 'Fraksi PKS', 'Fraksi Gerindra', 'Fraksi PPP', 'Fraksi NasDem'],
        'Lainnya': ['Pimpinan DPRD', 'Sekretaris DPRD', 'Staf Ahli', 'Umum/Tamu']
    };
    const kategori = document.getElementById('kategori_unit').value;
    const detail = document.getElementById('detail_unit');
    detail.innerHTML = '<option value="">-- Pilih Detail Unit --</option>';
    if (kategori && dataUnit[kategori]) {
        detail.disabled = false;
        dataUnit[kategori].forEach(item => { detail.add(new Option(item, item)); });
    } else {
        detail.disabled = true;
    }
}
function toggleUnitInput() {
    const jenis = document.getElementById('jenis_transaksi').value;
    const area = document.getElementById('area_unit');
    const inputUnit = document.getElementById('detail_unit');
    if (jenis === 'masuk') {
        area.style.display = 'none';
        inputUnit.disabled = true; inputUnit.value = ''; 
    } else {
        area.style.display = 'block';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    toggleUnitInput();
});
</script>

<?php render_footer_barang(); ?>