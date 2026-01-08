<?php
session_start();
require_once '../../config/koneksi.php';

// ===============================
// CEK LOGIN & ROLE
// ===============================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mobil') {
    header("location:../../login.php");
    exit;
}

// ==================================================
// A. PROSES PINJAM MOBIL
// ==================================================
if (isset($_POST['pinjam'])) {

    $id_mobil   = $_POST['id_mobil'];
    $nama       = mysqli_real_escape_string($koneksi, $_POST['nama_peminjam']);
    $tujuan     = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_rencana = $_POST['tgl_rencana_kembali'];

    // fallback: kalau kosong → 1 hari
    if (empty($tgl_rencana)) {
        $tgl_rencana = $tgl_pinjam;
    }

    // ===============================
    // 1. CEK STATUS MOBIL
    // ===============================
    $cek = mysqli_query($koneksi,
        "SELECT status_mobil FROM mobil WHERE id_mobil='$id_mobil'"
    );
    $mobil = mysqli_fetch_assoc($cek);

    if ($mobil['status_mobil'] !== 'Tersedia') {
        header("location:peminjaman.php?pesan=tidak_tersedia");
        exit;
    }

    // ===============================
    // 2. UPLOAD SURAT (OPSIONAL)
    // ===============================
    $surat_nama = null;

    if (!empty($_FILES['surat']['name'])) {
        $ext = strtolower(pathinfo($_FILES['surat']['name'], PATHINFO_EXTENSION));
        $allow = ['pdf','jpg','jpeg','png'];

        if (in_array($ext, $allow)) {
            $surat_nama = 'SURAT_' . time() . '_' . rand(100,999) . '.' . $ext;
            move_uploaded_file(
                $_FILES['surat']['tmp_name'],
                '../../assets/uploads/surat/' . $surat_nama
            );
        }
    }

    // ===============================
    // 3. SIMPAN PEMINJAMAN
    // ===============================
    $query = "
        INSERT INTO peminjaman
        (id_mobil, nama_peminjam, tujuan, tgl_pinjam, tgl_rencana_kembali, surat_pengajuan, status_kembali)
        VALUES
        ('$id_mobil', '$nama', '$tujuan', '$tgl_pinjam', '$tgl_rencana', '$surat_nama', 'Belum')
    ";

    if (mysqli_query($koneksi, $query)) {

        // Update status mobil
        mysqli_query($koneksi,
            "UPDATE mobil SET status_mobil='Dipinjam' WHERE id_mobil='$id_mobil'"
        );

        header("location:peminjaman.php?pesan=berhasil_pinjam");
        exit;
    } else {
        header("location:peminjaman.php?pesan=gagal");
        exit;
    }
}

// ==================================================
// B. PROSES PENGEMBALIAN MOBIL
// ==================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] === 'kembali') {

    $id_pinjam = $_GET['id'];
    $id_mobil  = $_GET['idm'];

    // Cek status
    $cek = mysqli_query($koneksi,
        "SELECT status_kembali FROM peminjaman WHERE id_pinjam='$id_pinjam'"
    );
    $row = mysqli_fetch_assoc($cek);

    if ($row['status_kembali'] === 'Sudah') {
        header("location:peminjaman.php?pesan=sudah_kembali");
        exit;
    }

    // Update peminjaman
    mysqli_query($koneksi,
        "UPDATE peminjaman SET status_kembali='Sudah' WHERE id_pinjam='$id_pinjam'"
    );

    // Update mobil
    mysqli_query($koneksi,
        "UPDATE mobil SET status_mobil='Tersedia' WHERE id_mobil='$id_mobil'"
    );

    header("location:peminjaman.php?pesan=mobil_kembali");
    exit;
}

// ==================================================
// C. HAPUS RIWAYAT (OPSIONAL)
// ==================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {

    $id = $_GET['id'];

    // ambil surat dulu (kalau ada)
    $q = mysqli_query($koneksi,
        "SELECT surat_pengajuan FROM peminjaman WHERE id_pinjam='$id'"
    );
    $d = mysqli_fetch_assoc($q);

    if (!empty($d['surat_pengajuan'])) {
        $file = '../../assets/uploads/surat/' . $d['surat_pengajuan'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_pinjam='$id'");
    header("location:peminjaman.php?pesan=hapus_riwayat");
    exit;
}

// ==================================================
// DEFAULT
// ==================================================
else {
    header("location:peminjaman.php");
    exit;
}
