<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Riwayat Transaksi"); 

// =============================
// 1. LOGIKA FILTER (PURE DATA / SEMUA)
// =============================
$search    = $_GET['search'] ?? '';

// REVISI: Default KOSONG agar menampilkan semua data saat Reset
$tgl_awal  = $_GET['tgl_awal'] ?? ''; 
$tgl_akhir = $_GET['tgl_akhir'] ?? '';  

$conditions = [];

// Filter Pencarian Teks
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(barang.nama_barang LIKE '%$search_safe%' 
                      OR riwayat_barang.jenis_transaksi LIKE '%$search_safe%' 
                      OR riwayat_barang.unit_penerima LIKE '%$search_safe%' 
                      OR riwayat_barang.no_surat LIKE '%$search_safe%' 
                      OR riwayat_barang.keterangan LIKE '%$search_safe%')";
}

// Filter Rentang Waktu (Hanya aktif jika user MEMILIH tanggal)
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $awal_safe  = mysqli_real_escape_string($koneksi, $tgl_awal);
    $akhir_safe = mysqli_real_escape_string($koneksi, $tgl_akhir);
    $conditions[] = "(riwayat_barang.tanggal BETWEEN '$awal_safe' AND '$akhir_safe')";
}

$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $conditions);
}

// =============================
// 2. PAGINATION
// =============================
$limit  = 15; 
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total 
              FROM riwayat_barang 
              JOIN barang ON riwayat_barang.id_barang = barang.id_barang 
              $where_sql";
$q_count    = mysqli_query($koneksi, $sql_count);
$data_count = mysqli_fetch_assoc($q_count);
$total_data = $data_count['total'];
$total_page = ceil($total_data / $limit);

// =============================
// 3. QUERY DATA UTAMA
// =============================
$q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");

$query_sql = "SELECT riwayat_barang.*, barang.nama_barang, barang.satuan, barang.jenis 
              FROM riwayat_barang 
              JOIN barang ON riwayat_barang.id_barang = barang.id_barang 
              $where_sql
              ORDER BY riwayat_barang.tanggal DESC, riwayat_barang.id_riwayat DESC
              LIMIT $limit OFFSET $offset";
$q_riwayat = mysqli_query($koneksi, $query_sql);

// Parameter untuk URL Pagination & Cetak
$url_params = "&search=" . urlencode($search) . "&tgl_awal=" . $tgl_awal . "&tgl_akhir=" . $tgl_akhir;

// Cek apakah sedang memfilter (search terisi ATAU tanggal terisi)
$is_filtered = (!empty($search) || !empty($tgl_awal) || !empty($tgl_akhir));
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
        elseif($msg == 'gagal_upload') $text = 'Gagal mengupload file surat.';
        else $text = 'Terjadi kesalahan sistem.';
    ?>
    <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
        <strong>Status:</strong> <?= $text; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row g-2 align-items-center">
            
            <div class="col-md-auto">
                <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCatat">
                    <i class="bi bi-plus-lg me-1"></i> Catat Aktivitas
                </button>
            </div>

            <div class="col-md">
                <form method="GET" class="row g-2 m-0 align-items-center">
                    <div class="col-auto d-flex align-items-center">
                        <span class="fw-bold me-2 small text-muted">Periode:</span>
                        <input type="date" name="tgl_awal" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($tgl_awal); ?>" title="Tanggal Awal">
                        <span class="mx-2">-</span>
                        <input type="date" name="tgl_akhir" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($tgl_akhir); ?>" title="Tanggal Akhir">
                    </div>
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari barang/unit..." 
                               value="<?= htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                    </div>

                    <?php if($is_filtered): ?>
                    <div class="col-auto">
                        <a href="riwayat.php" class="btn btn-sm btn-outline-secondary" title="Hapus Semua Filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-auto ms-auto">
                        <a href="cetak_laporan.php?aksi=print<?= $url_params; ?>" target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Simpan Laporan
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Surat</th>
                        <th class="text-center" width="5%">File</th>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Unit / Asal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_riwayat) == 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            Tidak ada data riwayat.<br>
                            <small>Silakan catat aktivitas baru.</small>
                        </td></tr>
                    <?php endif; ?>

                    <?php while($rw = mysqli_fetch_assoc($q_riwayat)): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($rw['tanggal'])); ?></td>
                        <td>
                            <?php if(!empty($rw['no_surat'])): ?>
                                <span class="fw-bold text-dark"><?= $rw['no_surat']; ?></span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if(!empty($rw['file_surat'])): ?>
                                <a href="../../assets/uploads/surat/barang/<?= $rw['file_surat']; ?>" target="_blank" class="btn btn-sm btn-light border text-primary" title="Lihat Dokumen">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
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
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalDetail"
                                    onclick="showDetail(
                                        '<?= date('d F Y', strtotime($rw['tanggal'])); ?>',
                                        '<?= $rw['no_surat'] ?? '-'; ?>',
                                        '<?= $rw['nama_barang']; ?>',
                                        '<?= $rw['jenis_transaksi']; ?>',
                                        '<?= $rw['jumlah'] . ' ' . $rw['satuan']; ?>',
                                        '<?= $rw['unit_penerima'] ?? '-'; ?>',
                                        '<?= htmlspecialchars($rw['keterangan']); ?>',
                                        '<?= $rw['file_surat'] ?? ''; ?>'
                                    )">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <a href="proses_riwayat.php?aksi=hapus&id=<?= $rw['id_riwayat']; ?>&idb=<?= $rw['id_barang']; ?>&qty=<?= $rw['jumlah']; ?>&tipe=<?= $rw['jenis_transaksi']; ?>" 
                                   class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php if ($total_page > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?><?= $url_params; ?>">&laquo;</a>
                        </li>
                        <?php 
                        $start = max(1, $page - 2); $end = min($total_page, $page + 2);
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?><?= $url_params; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?><?= $url_params; ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%" class="bg-light">Tanggal</th>
                        <td id="det_tanggal"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">No. Surat</th>
                        <td id="det_nosurat" class="fw-bold text-primary"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Barang</th>
                        <td id="det_barang" class="fw-bold"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Jenis Transaksi</th>
                        <td id="det_jenis"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Jumlah</th>
                        <td id="det_jumlah" class="fw-bold"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Unit / Asal</th>
                        <td id="det_unit"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Keterangan</th>
                        <td id="det_ket"></td>
                    </tr>
                    <tr id="row_file">
                        <th class="bg-light">File Pendukung</th>
                        <td><a href="#" id="det_file" target="_blank" class="btn btn-sm btn-outline-primary">Lihat File</a></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
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
            <form action="proses_riwayat.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Aktivitas</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-select" onchange="toggleUnitInput()" required>
                            <option value="keluar">Barang Keluar (Permintaan Unit)</option>
                            <option value="masuk">Barang Masuk (Pengadaan/Beli)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">No. Surat / Bukti (Opsional)</label>
                        <input type="text" name="nomor_urut" class="form-control" placeholder="Contoh: 000.2.3.2/001 atau Surat Jalan No. 123">
                        <div class="form-text small">Masukkan nomor surat lengkap.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Dokumen (Opsional)</label>
                        <input type="file" name="file_surat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <hr class="my-3">
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
                                <option value="Bagian">Bagian</option>
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
// FUNGSI MENAMPILKAN DETAIL DI MODAL
function showDetail(tgl, no, brg, jenis, jml, unit, ket, file) {
    document.getElementById('det_tanggal').innerText = tgl;
    document.getElementById('det_nosurat').innerText = no;
    document.getElementById('det_barang').innerText = brg;
    document.getElementById('det_jumlah').innerText = jml;
    document.getElementById('det_unit').innerText = unit;
    document.getElementById('det_ket').innerText = ket;

    let badge = '';
    if(jenis === 'masuk') {
        badge = '<span class="badge bg-success">Barang Masuk</span>';
    } else {
        badge = '<span class="badge bg-danger">Barang Keluar</span>';
    }
    document.getElementById('det_jenis').innerHTML = badge;

    const rowFile = document.getElementById('row_file');
    const linkFile = document.getElementById('det_file');
    if(file) {
        rowFile.style.display = 'table-row';
        linkFile.href = '../../assets/uploads/surat/barang/' + file;
    } else {
        rowFile.style.display = 'none';
    }
}

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