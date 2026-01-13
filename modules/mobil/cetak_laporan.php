<?php
require_once '../../config/koneksi.php';

// 1. AMBIL FILTER
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$search    = $_GET['search'] ?? '';

// 2. LOGIKA JUDUL & NAMA FILE
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $ket_periode = date('d F Y', strtotime($tgl_awal)) . " s.d. " . date('d F Y', strtotime($tgl_akhir));
    $nama_file   = "Laporan_Mobil_" . date('dmY', strtotime($tgl_awal)) . "_sd_" . date('dmY', strtotime($tgl_akhir));
} else {
    $ket_periode = "Semua Periode";
    $nama_file   = "Laporan_Mobil_All";
}

// 3. HEADER AGAR OTOMATIS DOWNLOAD WORD
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=\"$nama_file.doc\"");
header("Cache-Control: private, max-age=0, must-revalidate");

// 4. KONVERSI LOGO KE BASE64 (Agar Gambar Muncul di Word Offline)
$path_logo = '../../assets/img/logo.jpg';
$logo_base64 = '';

if (file_exists($path_logo)) {
    $type = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data = file_get_contents($path_logo);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// 5. QUERY DATA
$conditions = [];
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(m.nama_mobil LIKE '%$search_safe%' OR p.nama_peminjam LIKE '%$search_safe%' OR p.no_surat LIKE '%$search_safe%')";
}
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $awal_safe  = mysqli_real_escape_string($koneksi, $tgl_awal);
    $akhir_safe = mysqli_real_escape_string($koneksi, $tgl_akhir);
    $conditions[] = "(p.tgl_pinjam BETWEEN '$awal_safe' AND '$akhir_safe')";
}
$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " AND " . implode(' AND ', $conditions);
}

$sql = "SELECT p.*, m.nama_mobil, m.plat_nomor 
        FROM peminjaman p
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE 1=1 $where_sql
        ORDER BY p.tgl_pinjam ASC";
$query = mysqli_query($koneksi, $sql);
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
    <style>
        /* CSS KHUSUS WORD */
        body { font-family: 'Times New Roman', serif; font-size: 11pt; }
        
        /* Tabel Utama (Data) */
        .table-data { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000;
        }
        .table-data th, .table-data td { 
            border: 1px solid #000; 
            padding: 6px; 
            vertical-align: top; /* Agar teks mulai dari atas */
        }
        .table-data th { 
            background-color: #EFEFEF; /* Abu-abu tipis untuk header */
            text-align: center; 
            font-weight: bold; 
        }

        /* Utilitas */
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-upper { text-transform: uppercase; }
        
        /* Layout Kop & TTD menggunakan Tabel Tanpa Garis */
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-table td { border: none; padding: 2px; }
        
        .garis-bawah {
            border-bottom: 3px double #000; /* Garis ganda tebal */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="garis-bawah">
        <table class="layout-table">
            <tr>
                <td width="15%" align="center" valign="middle">
                    <?php if ($logo_base64): ?>
                        <img src="<?= $logo_base64; ?>" width="80" height="auto">
                    <?php endif; ?>
                </td>
                
                <td width="85%" align="center" valign="middle">
                    <span style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">PEMERINTAH KOTA YOGYAKARTA</span><br>
                    <span style="font-size: 18pt; font-weight: bold; text-transform: uppercase;">SEKRETARIAT DPRD</span><br>
                    <span style="font-size: 10pt;">
                        Jl. Ipda Tut Harsono No. 43 Yogyakarta Kode Pos: 55165 Telp: (0274) 540650<br>
                        EMAIL: dprd@jogjakota.go.id | WEBSITE: www.dprd-jogjakota.go.id
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="text-center">
        <h3 style="text-decoration: underline; margin-bottom: 5px; text-transform: uppercase;">LAPORAN PEMINJAMAN KENDARAAN DINAS</h3>
        <p style="margin-top: 0;">Periode: <?= $ket_periode; ?></p>
    </div>
    <br>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Peminjam</th>
                <th width="20%">Kendaraan</th>
                <th width="12%">Tgl Pinjam</th>
                <th width="12%">Tgl Kembali</th>
                <th width="15%">Tujuan</th>
                <th width="10%">No. Surat</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)): 
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td>
                    <strong><?= $row['nama_peminjam']; ?></strong>
                </td>
                <td>
                    <?= $row['nama_mobil']; ?><br>
                    <i>(<?= $row['plat_nomor']; ?>)</i>
                </td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rencana_kembali'])); ?></td>
                <td><?= $row['tujuan']; ?></td>
                <td class="text-center">
                    <?= !empty($row['no_surat']) ? $row['no_surat'] : '-'; ?>
                </td>
                <td class="text-center">
                    <?= ($row['status_kembali'] == 'Sudah') ? 'Selesai' : 'Dipinjam'; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    Tidak ada data peminjaman pada periode ini.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>

    <table class="layout-table">
        <tr>
            <td width="60%"></td>
            
            <td width="40%" align="center">
                <p>Yogyakarta, <?= date('d F Y'); ?></p>
                <p>________________</p>
                <br><br><br><br> <p class="text-bold" style="text-decoration: underline;">________________</p>
                <p>________________</p>
            </td>
        </tr>
    </table>

</body>
</html>