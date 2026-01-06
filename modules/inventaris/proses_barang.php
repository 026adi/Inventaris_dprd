<?php
session_start();
include_once '../../config/koneksi.php';

// ==========================================
// 1. CEK KEAMANAN
// ==========================================
// Hanya Admin Inventaris yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// Tentukan Lokasi Folder Upload (KHUSUS BARANG)
// Pastikan folder ini sudah dibuat: assets/uploads/barang/
$target_dir = "../../assets/uploads/barang/";

// ==========================================
// 2. LOGIKA TAMBAH BARANG (SIMPAN)
// ==========================================
if (isset($_POST['simpan'])) {
    
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $stok   = (int) $_POST['stok'];
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // Proses Upload Foto
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));

    // Validasi Ekstensi Gambar
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (!in_array($foto_ext, $allowed_ext)) {
        header("location:tambah_barang.php?pesan=gagal_upload");
        exit;
    }

    // Rename agar unik (Format: BARANG_timestamp_acak.jpg)
    $foto_baru = "BARANG_" . time() . "_" . rand(100, 999) . "." . $foto_ext;

    // Pindahkan file ke folder tujuan
    if (move_uploaded_file($foto_tmp, $target_dir . $foto_baru)) {
        
        $query = "INSERT INTO barang (nama_barang, stok, satuan, foto) 
                  VALUES ('$nama', '$stok', '$satuan', '$foto_baru')";
        
        if (mysqli_query($koneksi, $query)) {
            header("location:data_barang.php?pesan=sukses");
        } else {
            header("location:tambah_barang.php?pesan=gagal_db");
        }
    } else {
        header("location:tambah_barang.php?pesan=gagal_upload");
    }
}

// ==========================================
// 3. LOGIKA UPDATE BARANG (EDIT)
// ==========================================
else if (isset($_POST['update'])) {
    
    $id     = $_POST['id_barang'];
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $stok   = (int) $_POST['stok']; // Stok bisa diedit manual (Opname)
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // Ambil data lama untuk mengecek foto lama
    $q_lama = mysqli_query($koneksi, "SELECT foto FROM barang WHERE id_barang='$id'");
    $d_lama = mysqli_fetch_assoc($q_lama);

    // Cek apakah user mengupload foto baru?
    if ($_FILES['foto']['name'] != "") {
        // Ada foto baru
        $foto_name = $_FILES['foto']['name'];
        $foto_tmp  = $_FILES['foto']['tmp_name'];
        $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $foto_baru = "BARANG_" . time() . "_" . rand(100, 999) . "." . $foto_ext;

        // Upload foto baru
        move_uploaded_file($foto_tmp, $target_dir . $foto_baru);

        // HAPUS FOTO LAMA (Clean up storage)
        if (!empty($d_lama['foto']) && file_exists($target_dir . $d_lama['foto'])) {
            unlink($target_dir . $d_lama['foto']);
        }
    } else {
        // Tidak ada foto baru, pakai nama foto lama
        $foto_baru = $d_lama['foto'];
    }

    // Update Database
    $query = "UPDATE barang SET 
              nama_barang='$nama', 
              stok='$stok', 
              satuan='$satuan', 
              foto='$foto_baru' 
              WHERE id_barang='$id'";

    if (mysqli_query($koneksi, $query)) {
        header("location:data_barang.php?pesan=update");
    } else {
        header("location:edit_barang.php?id=$id&pesan=gagal");
    }
}

// ==========================================
// 4. LOGIKA HAPUS BARANG
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    
    $id = $_GET['id'];

    // Ambil nama file foto sebelum data dihapus
    $q_cek = mysqli_query($koneksi, "SELECT foto FROM barang WHERE id_barang='$id'");
    $d_cek = mysqli_fetch_assoc($q_cek);

    // Hapus file fisik di folder uploads/barang/
    if (!empty($d_cek['foto']) && file_exists($target_dir . $d_cek['foto'])) {
        unlink($target_dir . $d_cek['foto']);
    }

    // Hapus data di database
    // (Note: Data di riwayat_barang juga akan hilang otomatis karena FOREIGN KEY ON DELETE CASCADE)
    $hapus = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    
    if ($hapus) {
        header("location:data_barang.php?pesan=hapus");
    } else {
        header("location:data_barang.php?pesan=gagal_hapus");
    }
}
?>