<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Peminjaman Kendaraan");

// =============================
// 1. SEARCH & FILTER
// =============================
$search    = $_GET['search'] ?? '';

// GANTI VARIABEL AGAR SINKRON DENGAN CETAK_LAPORAN.PHP
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// =============================
// 2. PAGINATION
// =============================
$limit  = 15;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

// =============================
// 3. BUILD QUERY (DENGAN FILTER BARU)
// =============================
$conditions = [];

// Filter Pencarian Teks
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(
        m.nama_mobil LIKE '%$search_safe%' OR
        m.plat_nomor LIKE '%$search_safe%' OR
        p.nama_peminjam LIKE '%$search_safe%' OR
        p.tujuan LIKE '%$search_safe%'
    )";
}

// Filter Rentang Tanggal (Gunakan tgl_awal & tgl_akhir)
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $awal_safe  = mysqli_real_escape_string($koneksi, $tgl_awal);
    $akhir_safe = mysqli_real_escape_string($koneksi, $tgl_akhir);
    $conditions[] = "(p.tgl_pinjam BETWEEN '$awal_safe' AND '$akhir_safe')";
}

// Gabungkan Kondisi WHERE
$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " AND " . implode(' AND ', $conditions);
}

// Params untuk URL (Pagination & Cetak)
$url_params = "&search=" . urlencode($search) . "&tgl_awal=" . $tgl_awal . "&tgl_akhir=" . $tgl_akhir;

// Cek apakah sedang difilter (untuk tombol Reset)
$is_filtered = (!empty($search) || !empty($tgl_awal) || !empty($tgl_akhir));

// =============================
// 4. HITUNG TOTAL DATA
// =============================
$sql_count = "SELECT COUNT(*) as total
              FROM peminjaman p
              JOIN mobil m ON p.id_mobil = m.id_mobil
              WHERE 1=1 $where_sql";
$q_count    = mysqli_query($koneksi, $sql_count);
$total_data = mysqli_fetch_assoc($q_count)['total'];
$total_page = ceil($total_data / $limit);

// =============================
// 5. QUERY DATA UTAMA
// =============================
$sql = "SELECT p.*, m.nama_mobil, m.plat_nomor, m.foto
        FROM peminjaman p
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE 1=1 $where_sql
        ORDER BY FIELD(p.status_kembali, 'Belum', 'Sudah'), p.tgl_pinjam DESC, p.id_pinjam DESC
        LIMIT $limit OFFSET $offset";

$q_pinjam = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Peminjaman</h1>
</div>

<?php if (isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Status:</strong> <?= htmlspecialchars($_GET['pesan']); ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">

        <div class="card mb-3 shadow-sm">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">

                    <div class="col-auto">
                        <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#modalPinjam">
                            <i class="bi bi-plus-lg me-1"></i> Pinjam Mobil
                        </button>
                    </div>

                    <div class="col">
                        <form method="GET" class="row g-2 justify-content-end align-items-center m-0">

                            <div class="col-auto d-flex align-items-center">
                                <span class="fw-bold me-2 small text-muted">Periode:</span>
                                <input type="date" name="tgl_awal" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($tgl_awal); ?>" title="Dari Tanggal">
                                <span class="mx-2">-</span>
                                <input type="date" name="tgl_akhir" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($tgl_akhir); ?>" title="Sampai Tanggal">
                            </div>

                            <div class="col-auto">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Cari mobil / peminjam..."
                                    value="<?= htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-sm btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>

                            <?php if ($is_filtered): ?>
                                <div class="col-auto">
                                    <a href="peminjaman.php" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="col-auto border-start ps-3 ms-2">
                                <a href="cetak_laporan.php?aksi=print<?= $url_params; ?>" target="_blank" class="btn btn-sm btn-success text-nowrap">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Simpan Laporan
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Monitoring Peminjaman</h6>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Mobil</th>
                            <th>Peminjam</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Tgl Rencana Kembali</th>
                            <th class="text-center">No. Surat</th>
                            <th class="text-center">Surat</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($q_pinjam) == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    Data peminjaman tidak ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($q_pinjam)): ?>

                                <?php
                                $tglHariIni = date('Y-m-d');
                                $tglRencana = $row['tgl_rencana_kembali'];
                                ?>

                                <tr class="<?= ($row['status_kembali'] === 'Belum') ? 'table-warning' : ''; ?>">

                                    <td> <strong><?= htmlspecialchars($row['nama_mobil']); ?></strong><br> <small class="text-muted"><?= htmlspecialchars($row['plat_nomor']); ?></small> </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_peminjam']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['tujuan']); ?></small>
                                    </td>

                                    <td class="text-center">
                                        <?= $row['tgl_pinjam'] ? date('d/m/Y', strtotime($row['tgl_pinjam'])) : '-'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $row['tgl_rencana_kembali'] ? date('d/m/Y', strtotime($row['tgl_rencana_kembali'])) : '-'; ?>
                                    </td>

                                    <td class="text-center fw-bold text-dark small">
                                        <?= !empty($row['no_surat']) ? htmlspecialchars($row['no_surat']) : '<span class="text-muted">-</span>'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if (!empty($row['surat_pengajuan'])): ?>
                                            <a href="../../assets/uploads/surat/mobil/<?= $row['surat_pengajuan']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                target="_blank"
                                                title="Unduh Surat Pengajuan">
                                                <i class="bi bi-file-earmark-arrow-down"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center align-middle">
                                        <?php if ($row['status_kembali'] === 'Sudah'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Sudah Kembali
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= date('d/m/Y'); ?></small>
                                        <?php else: ?>
                                            <?php if (!empty($tglRencana) && $tglRencana < $tglHariIni): ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Terlambat
                                                </span>
                                            <?php elseif (!empty($tglRencana) && $tglRencana == $tglHariIni): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock me-1"></i> Deadline Hari Ini
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock-history me-1"></i> Masih Dipinjam
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status_kembali'] === 'Belum'): ?>
                                            <a href="proses_peminjaman.php?aksi=kembali&id=<?= $row['id_pinjam']; ?>&idm=<?= $row['id_mobil']; ?>"
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Mobil sudah dikembalikan?')">
                                                <i class="bi bi-check-lg"></i> Kembali
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">
                                                <i class="bi bi-check-all"></i> Selesai
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>

                    </tbody>
                </table>

                <?php if ($total_page > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">

                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page - 1; ?><?= $url_params; ?>">&laquo;</a>
                            </li>

                            <?php
                            $start = max(1, $page - 2);
                            $end   = min($total_page, $page + 2);
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
</div>

<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="proses_peminjaman.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-key-fill me-2"></i> Pinjam Mobil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Mobil</label>
                            <select name="id_mobil" class="form-select" required>
                                <option value="">-- Pilih Mobil --</option>
                                <?php
                                $q_ready = mysqli_query(
                                    $koneksi,
                                    "SELECT * FROM mobil 
                                     WHERE status_mobil='Tersedia' 
                                     ORDER BY nama_mobil ASC"
                                );
                                while ($m = mysqli_fetch_assoc($q_ready)):
                                ?>
                                    <option value="<?= $m['id_mobil']; ?>">
                                        <?= $m['nama_mobil']; ?> - <?= $m['plat_nomor']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Peminjam</label>
                            <input type="text" name="nama_peminjam" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Pinjam</label>
                            <input type="date"
                                name="tgl_pinjam"
                                class="form-control"
                                value="<?= date('Y-m-d'); ?>"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Tujuan / Keperluan</label>
                            <textarea name="tujuan" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode Peminjaman</label>
                            <select class="form-select" id="modePinjam">
                                <option value="1">1 Hari</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="rentang">Rentang Tanggal</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="tglKembaliBox">
                            <label class="form-label fw-semibold">Tanggal Rencana Kembali</label>
                            <input type="date"
                                name="tgl_rencana_kembali"
                                id="tglRencana"
                                class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">No. Surat / Bukti (Opsional)</label>
                            <input type="text"
                                name="nomor_urut"
                                class="form-control"
                                placeholder="Contoh: 000.1.4/005">
                            <div class="form-text small">
                                Kosongkan jika tidak ada surat.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Surat Pengajuan
                                <span class="text-muted fw-normal">(Opsional)</span>
                            </label>
                            <input type="file"
                                name="surat"
                                class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">
                                Format diperbolehkan: PDF, JPG, PNG
                            </small>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" name="pinjam" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modalFotoMobil" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="judulFotoMobil">Foto Mobil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img
                    id="previewFotoMobil"
                    src=""
                    class="img-fluid rounded"
                    style="max-height:80vh;">
            </div>

        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modePinjam = document.getElementById("modePinjam");
        const tglPinjam = document.querySelector("input[name='tgl_pinjam']");
        const tglBox = document.getElementById("tglKembaliBox");
        const tglRencana = document.getElementById("tglRencana");

        function hitungTanggalKembali() {
            const mode = modePinjam.value;

            // RENTANG TANGGAL (MANUAL)
            if (mode === "rentang") {
                tglBox.classList.remove("d-none");
                tglRencana.readOnly = false;
                tglRencana.value = "";
                return;
            }

            // MODE HARI (1 / 2 / 3)
            tglBox.classList.remove("d-none");
            tglRencana.readOnly = true;

            if (!tglPinjam.value) return;

            const jumlahHari = parseInt(mode);
            const start = new Date(tglPinjam.value);

            // 🔥 CORE FIX: -1 HARI
            start.setDate(start.getDate() + (jumlahHari - 1));

            tglRencana.value = start.toISOString().split("T")[0];
        }

        modePinjam.addEventListener("change", hitungTanggalKembali);
        tglPinjam.addEventListener("change", hitungTanggalKembali);

        // auto hitung saat modal pertama kali dibuka
        hitungTanggalKembali();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const modalFoto = document.getElementById("modalFotoMobil");
        const imgPreview = document.getElementById("previewFotoMobil");
        const judul = document.getElementById("judulFotoMobil");

        modalFoto.addEventListener("show.bs.modal", function(event) {
            const trigger = event.relatedTarget;
            const foto = trigger.getAttribute("data-foto");
            const nama = trigger.getAttribute("data-nama");

            imgPreview.src = foto;
            judul.textContent = nama;
        });
    });
</script>

<?php render_footer_mobil(); ?>