<?php
// 1. KONEKSI DATABASE
require_once '../../config/koneksi.php';

// 2. AMBIL DATA FILTER DARI URL
$tgl_awal  = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$search    = $_GET['search'] ?? '';

// 3. BUAT NAMA FILE AGAR RAPI SAAT DISIMPAN
$nama_file = "Laporan_Peminjaman_Mobil_" . date('d-m-Y', strtotime($tgl_awal)) . "_sd_" . date('d-m-Y', strtotime($tgl_akhir));

// 4. BANGUN QUERY
$conditions = [];

// a. Filter Pencarian
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(
        m.nama_mobil LIKE '%$search_safe%' OR
        m.plat_nomor LIKE '%$search_safe%' OR
        p.nama_peminjam LIKE '%$search_safe%' OR
        p.tujuan LIKE '%$search_safe%' OR
        p.no_surat LIKE '%$search_safe%'
    )";
}

// b. Filter Tanggal
$awal_safe  = mysqli_real_escape_string($koneksi, $tgl_awal);
$akhir_safe = mysqli_real_escape_string($koneksi, $tgl_akhir);
$conditions[] = "(p.tgl_pinjam BETWEEN '$awal_safe' AND '$akhir_safe')";

$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $conditions);
}

// Query Utama (Join Peminjaman dengan Mobil)
$sql = "SELECT p.*, m.nama_mobil, m.plat_nomor 
        FROM peminjaman p
        JOIN mobil m ON p.id_mobil = m.id_mobil
        $where_sql
        ORDER BY p.tgl_pinjam ASC, p.id_pinjam ASC";

$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $nama_file; ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 2cm; color: #000; }
        
        /* KOP SURAT */
        .kop-surat { width: 100%; border-bottom: 4px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .kop-surat td { border: none; vertical-align: middle; }
        .kop-img { width: 90px; text-align: center; }
        .kop-text { text-align: center; }
        
        .kop-text h3 { margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-text h2 { margin: 0; font-size: 18pt; font-weight: bold; text-transform: uppercase; }
        .kop-text p { margin: 2px 0; font-size: 9pt; }
        .alamat-lengkap { font-size: 9pt; font-style: normal; }
        
        /* TABEL DATA */
        .tabel-data { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10pt; }
        .tabel-data th, .tabel-data td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .tabel-data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .signature { margin-top: 40px; float: right; text-align: center; width: 250px; }
        .font-bold { font-weight: bold; }

        /* NAVIGASI TOMBOL */
        .no-print { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .btn { padding: 8px 15px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; margin: 0 5px; }
        .btn-print { background: #198754; color: white; border-color: #198754; }
        .btn-close { background: #6c757d; color: white; border-color: #6c757d; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            @page { size: A4 landscape; margin: 2cm; } /* Landscape agar muat banyak kolom */
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
        <button class="btn btn-print" onclick="window.print()">Simpan PDF / Cetak</button>
    </div>

    <table class="kop-surat">
        <tr>
            <td class="kop-img">
                <img src="../../assets/img/logo.jpg" alt="Logo" style="width: 80px; height: auto;">
            </td>
            <td class="kop-text">
                <h3>PEMERINTAH KOTA YOGYAKARTA</h3>
                <h2>SEKRETARIAT DPRD</h2>
                <p class="alamat-lengkap">
                    Jl. Ipda Tut Harsono No. 43 Yogyakarta Kode Pos: 55165 Telp: (0274) 540650 Fax (0274) 540651<br>
                    EMAIL: dprd@jogjakota.go.id<br>
                    HOTLINE SMS: 08122780001 HOTLINE EMAIL: upik@jogjakota.go.id<br>
                    WEBSITE: www.dprd-jogjakota.go.id
                </p>
            </td>
        </tr>
    </table>

    <h4 style="text-align: center; text-decoration: underline; margin-bottom: 5px; font-weight: bold;">LAPORAN PEMINJAMAN KENDARAAN DINAS</h4>
    <p style="text-align: center; margin-top: 0;">Periode: <?= date('d F Y', strtotime($tgl_awal)); ?> s.d. <?= date('d F Y', strtotime($tgl_akhir)); ?></p>

    <table class="tabel-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tgl Pinjam</th>
                <th width="15%">Peminjam</th>
                <th>Kendaraan</th>
                <th width="12%">Tgl Kembali</th>
                <th>Tujuan</th>
                <th width="12%">No. Surat</th>
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
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                <td><?= $row['nama_peminjam']; ?></td>
                <td>
                    <?= $row['nama_mobil']; ?><br>
                    <small style="font-style:italic;"><?= $row['plat_nomor']; ?></small>
                </td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rencana_kembali'])); ?></td>
                <td><?= $row['tujuan']; ?></td>
                <td class="text-center"><?= !empty($row['no_surat']) ? $row['no_surat'] : '-'; ?></td>
                <td class="text-center">
                    <?= ($row['status_kembali'] == 'Sudah') ? 'Selesai' : 'Dipinjam'; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data peminjaman pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature">
        <p>Yogyakarta, <?= date('d F Y'); ?></p>
        <p>Kepala Bagian Administrasi Umum</p>
        <br><br><br><br>
        <p class="font-bold">________________________</p>
        <p>NIP. __________________</p>
    </div>

</body>
</html>