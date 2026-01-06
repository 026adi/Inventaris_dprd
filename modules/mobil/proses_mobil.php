<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mobil') {
    header("location:../../login.php");
    exit;
}

// Folder Upload Khusus Mobil
$target_dir = "../../assets/uploads/mobil/";

// ==========================================
// A. SIMPAN MOBIL BARU
// ==========================================
if (isset($_POST['simpan'])) {
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_mobil']);
    $plat   = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $status = $_POST['status_mobil'];

    // Upload Foto
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    
    // Rename Unik: MOBIL_PlatNomor_Acak.jpg
    $plat_clean = str_replace(' ', '', $plat); // Hilangkan spasi di plat
    $foto_baru  = "MOBIL_" . $plat_clean . "_" . rand(100, 999) . "." . $foto_ext;

    if (move_uploaded_file($foto_tmp, $target_dir . $foto_baru)) {
        $query = "INSERT INTO mobil (nama_mobil, plat_nomor, foto, status_mobil) 
                  VALUES ('$nama', '$plat', '$foto_baru', '$status')";
        
        if (mysqli_query($koneksi, $query)) {
            header("location:data_mobil.php?pesan=sukses");
        } else {
            header("location:tambah_mobil.php?pesan=gagal_db");
        }
    } else {
        header("location:tambah_mobil.php?pesan=gagal_upload");
    }
}

// ==========================================
// B. UPDATE MOBIL
// ==========================================
else if (isset($_POST['update'])) {
    $id     = $_POST['id_mobil'];
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_mobil']);
    $plat   = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $status = $_POST['status_mobil'];

    // Cek foto lama
    $q_lama = mysqli_query($koneksi, "SELECT foto FROM mobil WHERE id_mobil='$id'");
    $d_lama = mysqli_fetch_assoc($q_lama);

    // Cek upload foto baru
    if ($_FILES['foto']['name'] != "") {
        $foto_name = $_FILES['foto']['name'];
        $plat_clean = str_replace(' ', '', $plat);
        $foto_ext  = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $foto_baru = "MOBIL_" . $plat_clean . "_" . rand(100, 999) . "." . $foto_ext;

        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_baru);

        // Hapus foto lama
        if (file_exists($target_dir . $d_lama['foto'])) {
            unlink($target_dir . $d_lama['foto']);
        }
    } else {
        $foto_baru = $d_lama['foto'];
    }

    $query = "UPDATE mobil SET 
              nama_mobil='$nama', plat_nomor='$plat', status_mobil='$status', foto='$foto_baru' 
              WHERE id_mobil='$id'";

    if (mysqli_query($koneksi, $query)) {
        header("location:data_mobil.php?pesan=update");
    } else {
        header("location:edit_mobil.php?id=$id&pesan=gagal");
    }
}

// ==========================================
// C. HAPUS MOBIL
// ==========================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus") {
    $id = $_GET['id'];

    // Hapus file foto
    $q_cek = mysqli_query($koneksi, "SELECT foto FROM mobil WHERE id_mobil='$id'");
    $d_cek = mysqli_fetch_assoc($q_cek);

    if (file_exists($target_dir . $d_cek['foto'])) {
        unlink($target_dir . $d_cek['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM mobil WHERE id_mobil='$id'");
    header("location:data_mobil.php?pesan=hapus");
}
?>