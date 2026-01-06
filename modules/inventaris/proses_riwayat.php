<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
// Hanya Admin Inventaris yang boleh akses file ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// ==========================================
// A. LOGIKA SIMPAN RIWAYAT (BARANG MASUK/KELUAR)
// ==========================================
if (isset($_POST['simpan_riwayat'])) {
    
    // Tangkap data dari form riwayat.php
    $id_barang  = $_POST['id_barang'];
    $jenis      = $_POST['jenis_transaksi']; // isinya 'masuk' atau 'keluar'
    $jumlah     = (int) $_POST['jumlah'];
    $tanggal    = $_POST['tanggal'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // VALIDASI KHUSUS: Jika barang KELUAR, cek apakah stok cukup?
    if ($jenis == 'keluar') {
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id_barang='$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);

        if ($data_stok['stok'] < $jumlah) {
            // Jika stok kurang, batalkan dan kembali
            header("location:riwayat.php?pesan=stok_kurang");
            exit;
        }
    }

    // 1. Simpan data ke tabel riwayat (Sejarah)
    $query_riwayat = "INSERT INTO riwayat_barang (id_barang, jenis_transaksi, jumlah, tanggal, keterangan) 
                      VALUES ('$id_barang', '$jenis', '$jumlah', '$tanggal', '$keterangan')";
    
    if (mysqli_query($koneksi, $query_riwayat)) {
        
        // 2. Update angka stok di tabel master barang (Matematika)
        if ($jenis == 'masuk') {
            // Jika masuk, stok bertambah
            $query_update = "UPDATE barang SET stok = stok + $jumlah WHERE id_barang='$id_barang'";
        } else {
            // Jika keluar, stok berkurang
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
    
    // Ambil parameter dari link hapus
    $id_riwayat = $_GET['id'];
    $id_barang  = $_GET['idb'];
    $jumlah     = $_GET['qty'];
    $tipe       = $_GET['tipe']; // Kita butuh tahu tipe aslinya (masuk/keluar) untuk membalikkan stok

    // Hapus baris sejarahnya
    $hapus = mysqli_query($koneksi, "DELETE FROM riwayat_barang WHERE id_riwayat='$id_riwayat'");

    if ($hapus) {
        // Balikkan stok ke kondisi semula (Reverse Logic)
        
        if ($tipe == 'masuk') {
            // Dulu 'masuk' (nambah), sekarang dihapus -> berarti stok harus DIKURANGI
            $q_reverse = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang='$id_barang'";
        } else { 
            // Dulu 'keluar' (kurang), sekarang dihapus -> berarti stok harus DITAMBAH (dikembalikan)
            $q_reverse = "UPDATE barang SET stok = stok + $jumlah WHERE id_barang='$id_barang'";
        }
        mysqli_query($koneksi, $q_reverse);
        
        header("location:riwayat.php?pesan=dibatalkan");
    } else {
        header("location:riwayat.php?pesan=gagal_hapus");
    }
}
?>