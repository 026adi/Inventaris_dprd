<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// ==========================================
// 2. LOGIKA TAMBAH BARANG (TANPA KOLOM FOTO)
// ==========================================
if (isset($_POST['simpan'])) {
    
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $jenis  = $_POST['jenis'];
    $stok   = (int) $_POST['stok'];
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // PERUBAHAN: Tidak ada lagi kolom 'foto' di sini
    $query = "INSERT INTO barang (nama_barang, jenis, stok, satuan) 
              VALUES ('$nama', '$jenis', '$stok', '$satuan')";
    
    if (mysqli_query($koneksi, $query)) {
        header("location:data_barang.php?pesan=sukses");
    } else {
        header("location:data_barang.php?pesan=gagal_db");
    }
}

// ==========================================
// 3. LOGIKA UPDATE BARANG (TANPA KOLOM FOTO)
// ==========================================
else if (isset($_POST['update'])) {
    
    $id     = $_POST['id_barang'];
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $jenis  = $_POST['jenis']; 
    $stok   = (int) $_POST['stok']; 
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // PERUBAHAN: Query update lebih pendek
    $query = "UPDATE barang SET 
              nama_barang='$nama', 
              jenis='$jenis', 
              stok='$stok', 
              satuan='$satuan' 
              WHERE id_barang='$id'";

    if (mysqli_query($koneksi, $query)) {
        header("location:data_barang.php?pesan=update");
    } else {
        header("location:data_barang.php?pesan=gagal");
    }
}

// ==========================================
// 4. LOGIKA HAPUS BARANG
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    
    $id = $_GET['id'];

    // Hapus data langsung
    $hapus = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    
    if ($hapus) {
        header("location:data_barang.php?pesan=hapus");
    } else {
        header("location:data_barang.php?pesan=gagal_hapus");
    }
}
?>