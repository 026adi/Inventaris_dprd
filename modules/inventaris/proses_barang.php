<?php
session_start();
include_once '../../config/koneksi.php';

// ==========================================
// 1. SECURITY CHECK
// ==========================================
// Only Inventory Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// Determine Upload Folder Location (SPECIFIC FOR GOODS)
// Ensure this folder exists: assets/uploads/barang/
$target_dir = "../../assets/uploads/barang/";

// ==========================================
// 2. LOGIC TO ADD GOODS (SAVE)
// ==========================================
if (isset($_POST['simpan'])) {
    
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $jenis  = $_POST['jenis']; // <--- Capture input 'jenis'
    $stok   = (int) $_POST['stok'];
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // Photo Upload Process
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));

    // Image Extension Validation
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (!in_array($foto_ext, $allowed_ext)) {
        // PERUBAHAN: Redirect kembali ke data_barang.php, bukan tambah_barang.php
        header("location:data_barang.php?pesan=gagal_upload");
        exit;
    }

    // Rename to be unique (Format: BARANG_timestamp_random.jpg)
    $foto_baru = "BARANG_" . time() . "_" . rand(100, 999) . "." . $foto_ext;

    // Move file to destination folder
    if (move_uploaded_file($foto_tmp, $target_dir . $foto_baru)) {
        
        // Insert Query
        $query = "INSERT INTO barang (nama_barang, jenis, stok, satuan, foto) 
                  VALUES ('$nama', '$jenis', '$stok', '$satuan', '$foto_baru')";
        
        if (mysqli_query($koneksi, $query)) {
            header("location:data_barang.php?pesan=sukses");
        } else {
            // PERUBAHAN: Redirect kembali ke data_barang.php
            header("location:data_barang.php?pesan=gagal_db");
        }
    } else {
        // PERUBAHAN: Redirect kembali ke data_barang.php
        header("location:data_barang.php?pesan=gagal_upload");
    }
}

// ==========================================
// 3. LOGIC TO UPDATE GOODS (EDIT)
// ==========================================
else if (isset($_POST['update'])) {
    
    $id     = $_POST['id_barang'];
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $jenis  = $_POST['jenis']; 
    $stok   = (int) $_POST['stok']; 
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    // Get old data to check old photo
    $q_lama = mysqli_query($koneksi, "SELECT foto FROM barang WHERE id_barang='$id'");
    $d_lama = mysqli_fetch_assoc($q_lama);

    // Check if user uploaded a new photo?
    if ($_FILES['foto']['name'] != "") {
        // New photo exists
        $foto_name = $_FILES['foto']['name'];
        $foto_tmp  = $_FILES['foto']['tmp_name'];
        $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $foto_baru = "BARANG_" . time() . "_" . rand(100, 999) . "." . $foto_ext;

        // Upload new photo
        move_uploaded_file($foto_tmp, $target_dir . $foto_baru);

        // DELETE OLD PHOTO (Clean up storage)
        if (!empty($d_lama['foto']) && file_exists($target_dir . $d_lama['foto'])) {
            unlink($target_dir . $d_lama['foto']);
        }
    } else {
        // No new photo, use old photo name
        $foto_baru = $d_lama['foto'];
    }

    // Update Database
    $query = "UPDATE barang SET 
              nama_barang='$nama', 
              jenis='$jenis', 
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
// 4. LOGIC TO DELETE GOODS
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    
    $id = $_GET['id'];

    // Get photo filename before deleting data
    $q_cek = mysqli_query($koneksi, "SELECT foto FROM barang WHERE id_barang='$id'");
    $d_cek = mysqli_fetch_assoc($q_cek);

    // Delete physical file in uploads/barang/ folder
    if (!empty($d_cek['foto']) && file_exists($target_dir . $d_cek['foto'])) {
        unlink($target_dir . $d_cek['foto']);
    }

    // Delete data in database
    $hapus = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    
    if ($hapus) {
        header("location:data_barang.php?pesan=hapus");
    } else {
        header("location:data_barang.php?pesan=gagal_hapus");
    }
}
?>