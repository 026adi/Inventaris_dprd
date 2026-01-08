<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// ==========================================
// A. LOGIKA SIMPAN RIWAYAT (BARANG MASUK/KELUAR)
// ==========================================
if (isset($_POST['simpan_riwayat'])) {
    
    // 1. Tangkap data dasar
    $id_barang  = $_POST['id_barang'];
    $jenis      = $_POST['jenis_transaksi']; // 'masuk' atau 'keluar'
    $jumlah     = (int) $_POST['jumlah'];
    $tanggal    = $_POST['tanggal'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // 2. Tangkap Data UNIT PENERIMA (Revisi Baru)
    // Unit hanya diisi jika jenis transaksi 'keluar', jika masuk biarkan kosong
    $unit = '';
    if ($jenis == 'keluar' && isset($_POST['unit_penerima'])) {
        $unit = mysqli_real_escape_string($koneksi, $_POST['unit_penerima']);
    }

    // 3. VALIDASI KHUSUS: Jika barang KELUAR, cek stok
    if ($jenis == 'keluar') {
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id_barang='$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);

        if ($data_stok['stok'] < $jumlah) {
            header("location:riwayat.php?pesan=stok_kurang");
            exit;
        }
    }

    // 4. SIMPAN KE DATABASE (Update Query dengan kolom unit_penerima)
    $query_riwayat = "INSERT INTO riwayat_barang (id_barang, jenis_transaksi, jumlah, tanggal, keterangan, unit_penerima) 
                      VALUES ('$id_barang', '$jenis', '$jumlah', '$tanggal', '$keterangan', '$unit')";
    
    if (mysqli_query($koneksi, $query_riwayat)) {
        
        // 5. Update Stok Barang Master
        if ($jenis == 'masuk') {
            $query_update = "UPDATE barang SET stok = stok + $jumlah WHERE id_barang='$id_barang'";
        } else {
            $query_update = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang='$id_barang'";
        }
        mysqli_query($koneksi, $query_update);

        header("location:riwayat.php?pesan=sukses");
    } else {
        header("location:riwayat.php?pesan=gagal");
    }
}

// ==========================================
// B. LOGIKA HAPUS / BATALKAN RIWAYAT
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    
    $id_riwayat = $_GET['id'];
    $id_barang  = $_GET['idb'];
    $jumlah     = (int) $_GET['qty'];
    $tipe       = $_GET['tipe']; 

    // Hapus data riwayat
    $hapus = mysqli_query($koneksi, "DELETE FROM riwayat_barang WHERE id_riwayat='$id_riwayat'");

    if ($hapus) {
        // Balikkan stok (Reverse Logic)
        if ($tipe == 'masuk') {
            // Dulu masuk, sekarang dihapus -> Stok KURANG
            $q_reverse = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang='$id_barang'";
        } else { 
            // Dulu keluar, sekarang dihapus -> Stok TAMBAH (Kembali)
            $q_reverse = "UPDATE barang SET stok = stok + $jumlah WHERE id_barang='$id_barang'";
        }
        mysqli_query($koneksi, $q_reverse);
        
        header("location:riwayat.php?pesan=dibatalkan");
    } else {
        header("location:riwayat.php?pesan=gagal_hapus");
    }
}
?>