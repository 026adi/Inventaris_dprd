<?php
session_start();
include_once '../../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mobil') {
    header("location:../../login.php");
    exit;
}

// ==========================================
// A. PROSES PINJAM (Keluar Kantor)
// ==========================================
if (isset($_POST['pinjam'])) {
    $id_mobil = $_POST['id_mobil'];
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_peminjam']);
    $tujuan   = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $tgl      = $_POST['tgl_pinjam'];

    // 1. Simpan data peminjaman
    $query_pinjam = "INSERT INTO peminjaman (id_mobil, nama_peminjam, tujuan, tgl_pinjam, status_kembali) 
                     VALUES ('$id_mobil', '$nama', '$tujuan', '$tgl', 'Belum')";
    
    if (mysqli_query($koneksi, $query_pinjam)) {
        
        // 2. OTOMATIS: Update status mobil jadi 'Dipinjam'
        mysqli_query($koneksi, "UPDATE mobil SET status_mobil='Dipinjam' WHERE id_mobil='$id_mobil'");
        
        header("location:peminjaman.php?pesan=berhasil_pinjam");
    } else {
        header("location:peminjaman.php?pesan=gagal");
    }
}

// ==========================================
// B. PROSES KEMBALI (Masuk Kantor)
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "kembali") {
    $id_pinjam = $_GET['id'];
    $id_mobil  = $_GET['idm'];
    $tgl_now   = date('Y-m-d');

    // 1. Update data peminjaman (Set tanggal kembali & status)
    $query_kembali = "UPDATE peminjaman SET tgl_kembali='$tgl_now', status_kembali='Sudah' 
                      WHERE id_pinjam='$id_pinjam'";
    
    if (mysqli_query($koneksi, $query_kembali)) {
        
        // 2. OTOMATIS: Update status mobil jadi 'Tersedia' lagi
        mysqli_query($koneksi, "UPDATE mobil SET status_mobil='Tersedia' WHERE id_mobil='$id_mobil'");
        
        header("location:peminjaman.php?pesan=mobil_kembali");
    }
}

// ==========================================
// C. HAPUS DATA RIWAYAT (Bersih-bersih)
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    // Fitur opsional jika admin ingin menghapus sejarah peminjaman
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_pinjam='$id'");
    header("location:peminjaman.php?pesan=hapus_riwayat");
}
?>