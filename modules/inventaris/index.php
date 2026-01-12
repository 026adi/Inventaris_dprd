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
    <h1 class="h2">Dashboard Inventory</h1>
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
                    
                    $modalData = []; 

                    while($row = mysqli_fetch_assoc($q_distribusi)): 
                        $modalID = "modalUnit_" . md5($row['unit_penerima']);
                        $modalData[] = ['id' => $modalID, 'unit' => $row['unit_penerima']];
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-bold text-primary"><?= $row['unit_penerima']; ?></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark fs-6"><?= $row['total_qty']; ?> item </span>
                        </td>
                        <td class="text-center"><?= $row['frekuensi']; ?>x Transaksi</td>
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
                    <i class="bi bi-clipboard-data me-2"></i>Rincian: <strong><?= $md['unit']; ?></strong>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                
                <div class="bg-light p-3 rounded mb-3 border">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted mb-1"><i class="bi bi-calendar-event"></i> Tgl Spesifik</label>
                            <input type="date" id="date_<?= $md['id']; ?>" class="form-control form-control-sm" 
                                   onchange="applyFilter('<?= $md['id']; ?>', 'exact')">
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted mb-1"><i class="bi bi-calendar-month"></i> Per Bulan</label>
                            <input type="month" id="month_<?= $md['id']; ?>" class="form-control form-control-sm" 
                                   onchange="applyFilter('<?= $md['id']; ?>', 'month')">
                        </div>
                        <div class="col-md-2">
                             <button class="btn btn-sm btn-secondary w-100" onclick="resetFilter('<?= $md['id']; ?>')">Reset</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0" id="tbl_<?= $md['id']; ?>">
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
                            $unit_safe = mysqli_real_escape_string($koneksi, $md['unit']);
                            $q_detail = mysqli_query($koneksi, "
                                SELECT r.*, b.nama_barang, b.satuan 
                                FROM riwayat_barang r
                                JOIN barang b ON r.id_barang = b.id_barang
                                WHERE r.jenis_transaksi = 'keluar' 
                                AND r.unit_penerima = '$unit_safe'
                                ORDER BY r.tanggal DESC
                            ");
                            
                            $hasData = false;
                            while($det = mysqli_fetch_assoc($q_detail)):
                                $hasData = true;
                                $tgl_asli = $det['tanggal']; 
                            ?>
                            <tr data-tgl="<?= $tgl_asli; ?>">
                                <td><?= date('d/m/Y', strtotime($det['tanggal'])); ?></td>
                                <td class="fw-bold"><?= $det['nama_barang']; ?></td>
                                <td class="text-center text-danger fw-bold">
                                    -<?= $det['jumlah']; ?> <?= $det['satuan']; ?>
                                </td>
                                <td class="small text-muted"><?= $det['keterangan']; ?></td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if(!$hasData): ?>
                                <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div id="msg_<?= $md['id']; ?>" class="text-center text-muted py-4 d-none">
                        <i class="bi bi-search display-6 d-block mb-2"></i> 
                        Tidak ada transaksi pada filter tanggal tersebut.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
function applyFilter(modalID, type) {
    // 1. Ambil elemen input
    var inputDate  = document.getElementById("date_" + modalID);
    var inputMonth = document.getElementById("month_" + modalID);
    
    // 2. Logika Saling Hapus (Mutual Exclusive)
    // Kalau user pilih Tanggal, kosongkan Bulan. Kalau pilih Bulan, kosongkan Tanggal.
    if (type === 'exact') {
        inputMonth.value = ""; 
    } else if (type === 'month') {
        inputDate.value = "";
    }

    var valDate  = inputDate.value;
    var valMonth = inputMonth.value;

    // 3. Ambil tabel
    var table = document.getElementById("tbl_" + modalID);
    var tr = table.getElementsByTagName("tr");
    var found = false;

    // 4. Loop Filter
    for (var i = 1; i < tr.length; i++) {
        var rowDate = tr[i].getAttribute("data-tgl");
        
        if (rowDate) {
            var show = true;

            // Jika filter Tanggal Spesifik aktif
            if (valDate !== "") {
                if (rowDate !== valDate) show = false;
            }
            // Jika filter Bulan aktif (startsWith)
            else if (valMonth !== "") {
                if (!rowDate.startsWith(valMonth)) show = false;
            }

            // Terapkan display
            if (show) {
                tr[i].style.display = "";
                found = true;
            } else {
                tr[i].style.display = "none";
            }
        }
    }

    // 5. Tampilkan Pesan Kosong jika tidak ada hasil
    var msgBox = document.getElementById("msg_" + modalID);
    
    // Cek apakah ada filter yang aktif?
    var isFiltering = (valDate !== "" || valMonth !== "");

    if (!found && isFiltering) {
        msgBox.classList.remove("d-none");
        table.style.display = "none"; 
    } else {
        msgBox.classList.add("d-none");
        table.style.display = "";
    }
}

function resetFilter(modalID) {
    document.getElementById("date_" + modalID).value = "";
    document.getElementById("month_" + modalID).value = "";
    // Jalankan filter kosong (reset)
    applyFilter(modalID, 'reset'); 
}
</script>

<?php render_footer_barang(); ?>