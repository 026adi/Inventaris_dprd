<?php
session_start();
include_once '../../config/koneksi.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventaris') {
    header("location:../../login.php");
    exit;
}

// DEFINISI FOLDER UPLOAD (Sesuai request folder surat/barang/)
$target_dir = "../../assets/uploads/surat/barang/";

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

    // 2. OLAH NOMOR SURAT (Template: 000.2.3.2/nnn)
    $no_surat = "";
    if (!empty($_POST['nomor_urut'])) {
        $no_surat = "000.2.3.2/" . mysqli_real_escape_string($koneksi, $_POST['nomor_urut']);
    }

    // 3. OLAH UPLOAD FILE (Opsional)
    $file_baru = "";
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = $_FILES['file_surat']['name'];
        $file_tmp  = $_FILES['file_surat']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed)) {
            // Nama file unik: SURAT_BARANG_timestamp.ext
            $file_baru = "SURAT_BARANG_" . time() . "_" . rand(100,999) . "." . $file_ext;
            
            // Pindahkan file
            if (!move_uploaded_file($file_tmp, $target_dir . $file_baru)) {
                $file_baru = ""; // Jika gagal, kosongkan nama file
            }
        }
    }

    // 4. Tangkap Data UNIT PENERIMA
    // Unit hanya diisi jika jenis transaksi 'keluar', jika masuk biarkan kosong
    $unit = '';
    if ($jenis == 'keluar' && isset($_POST['unit_penerima'])) {
        $unit = mysqli_real_escape_string($koneksi, $_POST['unit_penerima']);
    }

    // 5. VALIDASI KHUSUS: Jika barang KELUAR, cek stok
    if ($jenis == 'keluar') {
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id_barang='$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);

        if ($data_stok['stok'] < $jumlah) {
            header("location:riwayat.php?pesan=stok_kurang");
            exit;
        }
    }

    // 6. SIMPAN KE DATABASE (Lengkap dengan no_surat & file_surat)
    $query_riwayat = "INSERT INTO riwayat_barang (id_barang, jenis_transaksi, jumlah, tanggal, keterangan, unit_penerima, no_surat, file_surat) 
                      VALUES ('$id_barang', '$jenis', '$jumlah', '$tanggal', '$keterangan', '$unit', '$no_surat', '$file_baru')";
    
    if (mysqli_query($koneksi, $query_riwayat)) {
        
        // 7. Update Stok Barang Master
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

    // 1. Cek dulu apakah ada file yang harus dihapus?
    $q_cek = mysqli_query($koneksi, "SELECT file_surat FROM riwayat_barang WHERE id_riwayat='$id_riwayat'");
    $d_cek = mysqli_fetch_assoc($q_cek);

    // 2. Hapus file fisik di folder uploads/surat/barang/ jika ada
    if (!empty($d_cek['file_surat']) && file_exists($target_dir . $d_cek['file_surat'])) {
        unlink($target_dir . $d_cek['file_surat']);
    }

    // 3. Hapus data riwayat di database
    $hapus = mysqli_query($koneksi, "DELETE FROM riwayat_barang WHERE id_riwayat='$id_riwayat'");

    if ($hapus) {
        // 4. Balikkan stok (Reverse Logic)
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