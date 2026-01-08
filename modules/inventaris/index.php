<?php 
require_once '../../includes/layout_barang.php'; 
render_header_barang("Dashboard Gudang"); 

// ==========================================
// 1. HITUNG KARTU ATAS (STATISTIK JENIS)
// ==========================================
$q_habis = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE jenis = 'Habis Pakai'");
$d_habis = mysqli_fetch_assoc($q_habis);

$q_tetap = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE jenis = 'Tetap'");
$d_tetap = mysqli_fetch_assoc($q_tetap);

// ==========================================
// 2. QUERY UTAMA: KELOMPOKKAN PER UNIT
// ==========================================
// Kita ambil nama unit, total barang yang diambil (SUM), dan berapa kali minta (COUNT)
$q_distribusi = mysqli_query($koneksi, "
    SELECT unit_penerima, 
           SUM(jumlah) as total_qty, 
           COUNT(id_riwayat) as frekuensi 
    FROM riwayat_barang 
    WHERE jenis_transaksi = 'keluar' AND unit_penerima != '' 
    GROUP BY unit_penerima 
    ORDER BY total_qty DESC
");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Gudang</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Barang Habis Pakai</h6>
                        <h1 class="display-4 fw-bold mb-0"><?= $d_habis['total']; ?></h1>
                        <p class="mb-0 small mt-2">Item Terdaftar (ATK/Bahan)</p>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="data_barang.php?jenis=Habis Pakai" class="text-white text-decoration-none small stretched-link">
                    Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-white bg-success h-100 shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Barang Tetap (Aset)</h6>
                        <h1 class="display-4 fw-bold mb-0"><?= $d_tetap['total']; ?></h1>
                        <p class="mb-0 small mt-2">Item Aset (Elektronik/Mebel)</p>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-laptop fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="data_barang.php?jenis=Tetap" class="text-white text-decoration-none small stretched-link">
                    Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Statistik Distribusi per Unit</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Unit / Bagian</th>
                        <th class="text-center">Total Item Diambil</th>
                        <th class="text-center">Frekuensi Permintaan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($q_distribusi) == 0) {
                        echo '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data pengambilan barang.</td></tr>';
                    }
                    
                    // Kita simpan data modal dalam array agar bisa di-render di luar tabel (supaya HTML rapi)
                    $modalData = []; 

                    while($row = mysqli_fetch_assoc($q_distribusi)): 
                        // Buat ID unik untuk modal berdasarkan nama unit (hapus spasi agar valid)
                        $modalID = "modalUnit_" . md5($row['unit_penerima']);
                        $modalData[] = ['id' => $modalID, 'unit' => $row['unit_penerima']];
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-bold text-primary"><?= $row['unit_penerima']; ?></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark fs-6"><?= $row['total_qty']; ?> Pcs/Unit</span>
                        </td>
                        <td class="text-center"><?= $row['frekuensi']; ?>x Permintaan</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#<?= $modalID; ?>">
                                <i class="bi bi-eye me-1"></i> Lihat Rincian
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach($modalData as $md): ?>
<div class="modal fade" id="<?= $md['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-data me-2"></i>Rincian Pengambilan: <strong><?= $md['unit']; ?></strong>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Query KHUSUS untuk barang yang diambil unit ini
                            $unit_safe = mysqli_real_escape_string($koneksi, $md['unit']);
                            $q_detail = mysqli_query($koneksi, "
                                SELECT r.*, b.nama_barang, b.satuan 
                                FROM riwayat_barang r
                                JOIN barang b ON r.id_barang = b.id_barang
                                WHERE r.jenis_transaksi = 'keluar' 
                                AND r.unit_penerima = '$unit_safe'
                                ORDER BY r.tanggal DESC
                            ");

                            while($det = mysqli_fetch_assoc($q_detail)):
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($det['tanggal'])); ?></td>
                                <td class="fw-bold"><?= $det['nama_barang']; ?></td>
                                <td class="text-center text-danger fw-bold">
                                    -<?= $det['jumlah']; ?> <?= $det['satuan']; ?>
                                </td>
                                <td class="small text-muted"><?= $det['keterangan']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php render_footer_barang(); ?>