<?php
require_once '../../includes/layout_mobil.php';
render_header_mobil("Peminjaman Kendaraan");

// =============================
// SEARCH & FILTER
// =============================
$search = $_GET['search'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

// =============================
// PAGINATION
// =============================
$limit = 15;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;


// =============================
// QUERY DATA PEMINJAMAN
// =============================
$sql = "
    SELECT p.*, m.nama_mobil, m.plat_nomor, m.foto
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE 1=1
";

// SEARCH TEXT
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $sql .= "
        AND (
            m.nama_mobil LIKE '%$search_safe%' OR
            m.plat_nomor LIKE '%$search_safe%' OR
            p.nama_peminjam LIKE '%$search_safe%' OR
            p.tujuan LIKE '%$search_safe%'
        )
    ";
}

// FILTER TANGGAL (KALENDER)
if (!empty($from) && !empty($to)) {
    $sql .= "
        AND (
            p.tgl_pinjam <= '$to'
            AND p.tgl_rencana_kembali >= '$from'
        )
    ";
}

// =============================
// HITUNG TOTAL DATA (PAGINATION)
// =============================
$sql_count = "
    SELECT COUNT(*) as total
    FROM peminjaman p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE 1=1
";

if (!empty($search)) {
    $sql_count .= "
        AND (
            m.nama_mobil LIKE '%$search_safe%' OR
            m.plat_nomor LIKE '%$search_safe%' OR
            p.nama_peminjam LIKE '%$search_safe%' OR
            p.tujuan LIKE '%$search_safe%'
        )
    ";
}

if (!empty($from) && !empty($to)) {
    $sql_count .= "
        AND (
            p.tgl_pinjam <= '$to'
            AND p.tgl_rencana_kembali >= '$from'
        )
    ";
}

$q_count     = mysqli_query($koneksi, $sql_count);
$total_data  = mysqli_fetch_assoc($q_count)['total'];
$total_page  = ceil($total_data / $limit);


// URUTKAN: BELUM → SUDAH
$sql .= "
    ORDER BY FIELD(p.status_kembali, 'Belum', 'Sudah'), p.id_pinjam DESC
    LIMIT $limit OFFSET $offset
";


$q_pinjam = mysqli_query($koneksi, $sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Peminjaman</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPinjam">
        <i class="bi bi-plus-lg me-1"></i> Pinjam Mobil
    </button>

</div>

<?php if (isset($_GET['pesan'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Status:</strong> <?= htmlspecialchars($_GET['pesan']); ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">

        <!-- SEARCH + FILTER -->
        <div class="mb-3">
            <form method="GET" class="row g-2 align-items-end">

                <!-- SEARCH KIRI -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari mobil / plat / peminjam / tujuan..."
                        value="<?= htmlspecialchars($search); ?>">
                </div>

                <!-- TGL MASUK -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tgl Masuk</label>
                    <input
                        type="date"
                        name="from"
                        class="form-control"
                        value="<?= htmlspecialchars($from); ?>">
                </div>

                <!-- TGL KELUAR -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tgl Keluar</label>
                    <input
                        type="date"
                        name="to"
                        class="form-control"
                        value="<?= htmlspecialchars($to); ?>">
                </div>

                <!-- BUTTON -->
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>

                    <?php if ($search || $from || $to): ?>
                        <a href="peminjaman.php" class="btn btn-outline-secondary w-100">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>


        <!-- TABEL -->
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
                                <td colspan="6" class="text-center text-muted">
                                    Data peminjaman tidak ditemukan
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($q_pinjam)): ?>

                                <?php
                                $tglHariIni = date('Y-m-d');
                                $tglRencana = $row['tgl_rencana_kembali'];
                                ?>

                                <tr class="<?= ($row['status_kembali'] === 'Belum') ? 'table-warning' : ''; ?>">

                                    <!-- MOBIL -->
                                    <td class="text-center">
                                        <?php if (!empty($row['foto']) && file_exists("../../assets/uploads/mobil/" . $row['foto'])): ?>
                                            <img
                                                src="../../assets/uploads/mobil/<?= $row['foto']; ?>"
                                                class="img-thumbnail rounded foto-mobil"
                                                width="100"
                                                style="height:60px;object-fit:cover;cursor:pointer;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalFotoMobil"
                                                data-foto="../../assets/uploads/mobil/<?= $row['foto']; ?>"
                                                data-nama="<?= htmlspecialchars($row['nama_mobil']); ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/100x60?text=No+Image"
                                                class="img-thumbnail rounded">
                                        <?php endif; ?>
                                    </td>


                                    <!-- PEMINJAM -->
                                    <td>
                                        <?= htmlspecialchars($row['nama_peminjam']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['tujuan']); ?></small>
                                    </td>

                                    <!-- TGL PINJAM -->
                                    <td class="text-center">
                                        <?= $row['tgl_pinjam']
                                            ? date('d/m/Y', strtotime($row['tgl_pinjam']))
                                            : '<span class="text-muted">—</span>'; ?>
                                    </td>

                                    <!-- TGL RENCANA KEMBALI -->
                                    <td class="text-center">
                                        <?= $row['tgl_rencana_kembali']
                                            ? date('d/m/Y', strtotime($row['tgl_rencana_kembali']))
                                            : '<span class="text-muted">—</span>'; ?>
                                    </td>

                                    <!-- NO. SURAT -->
                                    <td class="text-center fw-bold text-dark">
                                        <?= !empty($row['no_surat'])
                                            ? htmlspecialchars($row['no_surat'])
                                            : '<span class="text-muted">—</span>'; ?>
                                    </td>


                                    <!-- SURAT -->
                                    <td class="text-center">
                                        <?php if (!empty($row['surat_pengajuan'])): ?>
                                            <a href="../../assets/uploads/surat/mobil<?= $row['surat_pengajuan']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                download
                                                title="Unduh Surat Pengajuan">
                                                <i class="bi bi-file-earmark-arrow-down"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>


                                    <!-- STATUS -->
                                    <td class="text-center align-middle">

                                        <?php if ($row['status_kembali'] === 'Sudah'): ?>

                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Sudah Kembali
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('d/m/Y'); ?>
                                            </small>

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


                                    <!-- AKSI -->
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

                            <!-- PREV -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>&from=<?= $from; ?>&to=<?= $to; ?>">
                                    &laquo;
                                </a>
                            </li>

                            <?php
                            $start = max(1, $page - 2);
                            $end   = min($total_page, $page + 2);
                            ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&from=<?= $from; ?>&to=<?= $to; ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- NEXT -->
                            <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>&from=<?= $from; ?>&to=<?= $to; ?>">
                                    &raquo;
                                </a>
                            </li>

                            <!-- LAST -->
                            <li class="page-item <?= ($page == $total_page) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?= $total_page; ?>&search=<?= urlencode($search); ?>&from=<?= $from; ?>&to=<?= $to; ?>">
                                    Last
                                </a>
                            </li>

                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- ================= MODAL PINJAM MOBIL ================= -->
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="proses_peminjaman.php" method="POST" enctype="multipart/form-data">

                <!-- HEADER -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-key-fill me-2"></i> Pinjam Mobil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- PILIH MOBIL -->
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

                        <!-- NAMA PEMINJAM -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Peminjam</label>
                            <input type="text" name="nama_peminjam" class="form-control" required>
                        </div>

                        <!-- TANGGAL PINJAM -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Pinjam</label>
                            <input type="date"
                                name="tgl_pinjam"
                                class="form-control"
                                value="<?= date('Y-m-d'); ?>"
                                required>
                        </div>

                        <!-- TUJUAN -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tujuan / Keperluan</label>
                            <textarea name="tujuan" class="form-control" rows="2" required></textarea>
                        </div>

                        <!-- MODE -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode Peminjaman</label>
                            <select class="form-select" id="modePinjam">
                                <option value="1">1 Hari</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="rentang">Rentang Tanggal</option>
                            </select>
                        </div>

                        <!-- TGL RENCANA KEMBALI -->
                        <div class="col-md-6 d-none" id="tglKembaliBox">
                            <label class="form-label fw-semibold">Tanggal Rencana Kembali</label>
                            <input type="date"
                                name="tgl_rencana_kembali"
                                id="tglRencana"
                                class="form-control">

                        </div>

                        <!-- NO. SURAT -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. Surat / Bukti (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">
                                    000.1.4/
                                </span>
                                <input
                                    type="text"
                                    name="nomor_urut"
                                    class="form-control"
                                    placeholder="(Isi Nomor)">
                            </div>
                            <div class="form-text small">
                                Kosongkan jika tidak ada surat.
                            </div>
                        </div>



                        <!-- SURAT -->
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

                <!-- FOOTER -->
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

<!-- MODAL PREVIEW FOTO MOBIL -->
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