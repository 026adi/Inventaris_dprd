<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    die("Akses Ditolak.");
}

$id = $_GET['id'] ?? 0;

// 2. AMBIL DATA TRANSAKSI
$query = "SELECT r.*, b.nama_barang, b.satuan, b.jenis 
          FROM riwayat_barang r 
          JOIN barang b ON r.id_barang = b.id_barang 
          WHERE r.id_riwayat = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan.");
}

// 3. HEADER AGAR DIBACA SEBAGAI FILE WORD
$filename = "Bukti_Transaksi_" . str_replace(['/', '\\'], '-', $data['no_surat']) . ".doc";

header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=$filename");

// 4. STYLE & KONTEN SURAT
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 12pt; 
            line-height: 1.5;
        }
        /* KOP SURAT */
        .header { 
            text-align: center; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h2 { margin: 0; font-size: 16pt; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 0; font-size: 11pt; }

        /* JUDUL SURAT */
        .judul { text-align: center; margin-bottom: 20px; }
        .judul h3 { text-decoration: underline; margin: 0; font-size: 14pt; font-weight: bold; }
        .judul p { margin: 5px 0 0 0; }

        /* KOTAK STATUS */
        .box-status {
            border: 2px solid #000;
            padding: 5px 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* TABEL DATA (TANPA GARIS/BORDER) */
        .tabel-info { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .tabel-info td { 
            padding: 5px; 
            vertical-align: top; 
            border: none; /* Hilangkan garis */
        }
        .label { width: 150px; font-weight: bold; }
        .titik { width: 20px; text-align: center; }
        .isi { font-weight: normal; }

        /* TANDA TANGAN */
        .ttd-table { width: 100%; margin-top: 50px; }
        .ttd-table td { text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    <div class="header">
        <h2>SEKRETARIAT DPRD KOTA YOGYAKARTA</h2>
        <p>Jl. Ipda Tut Harsono No.43, Muja Muju, Kec. Umbulharjo, Kota Yogyakarta</p>
        <p>Telp: (0274) 123456 | Email: setwan@jogjakota.go.id</p>
    </div>

    <div class="judul">
        <h3>BUKTI TRANSAKSI BARANG</h3>
        <p>Nomor: <?= !empty($data['no_surat']) ? $data['no_surat'] : '-'; ?></p>
    </div>

    <div class="box-status">
        STATUS: <?= strtoupper($data['jenis_transaksi'] == 'masuk' ? 'BARANG MASUK (PENGADAAN)' : 'BARANG KELUAR (DISTRIBUSI)'); ?>
    </div>

    <table class="tabel-info">
        <tr>
            <td class="label">Hari, Tanggal</td>
            <td class="titik">:</td>
            <td class="isi"><?= date('l, d F Y', strtotime($data['tanggal'])); ?></td>
        </tr>
        <tr>
            <td class="label">Nama Barang</td>
            <td class="titik">:</td>
            <td class="isi"><strong><?= $data['nama_barang']; ?></strong></td>
        </tr>
        <tr>
            <td class="label">Jenis Barang</td>
            <td class="titik">:</td>
            <td class="isi"><?= $data['jenis']; ?></td>
        </tr>
        <tr>
            <td class="label">Jumlah</td>
            <td class="titik">:</td>
            <td class="isi"><?= $data['jumlah'] . ' ' . $data['satuan']; ?></td>
        </tr>
        <tr>
            <td class="label">Unit / Tujuan</td>
            <td class="titik">:</td>
            <td class="isi"><?= !empty($data['unit_penerima']) ? $data['unit_penerima'] : '-'; ?></td>
        </tr>
        <tr>
            <td class="label">Keterangan</td>
            <td class="titik">:</td>
            <td class="isi"><?= $data['keterangan']; ?></td>
        </tr>
    </table>

    <table class="ttd-table">
        <tr>
            <td width="50%">
                Mengetahui,<br>
                Pengurus Barang
                <br><br><br><br><br>
                <b><u><?= $_SESSION['nama_lengkap'] ?? 'Admin Inventaris'; ?></u></b>
            </td>
            <td width="50%">
                Yogyakarta, <?= date('d F Y'); ?><br>
                Penerima / Pemohon
                <br><br><br><br><br>
                <b>( ........................................ )</b>
            </td>
        </tr>
    </table>

</body>
</html>