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

// ===============================
// A. PROSES PINJAM MOBIL
// ===============================
if (isset($_POST['pinjam'])) {

    $id_mobil = $_POST['id_mobil'];
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_peminjam']);
    $tujuan   = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    if (isset($_POST['pinjam'])) {

        $id_mobil  = $_POST['id_mobil'];
        $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_peminjam']);
        $tujuan    = mysqli_real_escape_string($koneksi, $_POST['tujuan']);

        $tgl_pinjam = $_POST['tgl_pinjam'];
        $tgl_rencana = $_POST['tgl_rencana_kembali'];


        // fallback safety
        if (empty($tgl_rencana)) {
            $tgl_rencana = $tgl_pinjam;
        }

        $query = "
        INSERT INTO peminjaman 
        (id_mobil, nama_peminjam, tujuan, tgl_pinjam, tgl_rencana_kembali, status_kembali)
        VALUES
        ('$id_mobil', '$nama', '$tujuan', '$tgl_pinjam', '$tgl_rencana', 'Belum')
    ";

        if (mysqli_query($koneksi, $query)) {
            mysqli_query(
                $koneksi,
                "UPDATE mobil SET status_mobil='Dipinjam' WHERE id_mobil='$id_mobil'"
            );

            header("location:peminjaman.php?pesan=berhasil_pinjam");
            exit;
        }
    }


    // 1. CEK STATUS MOBIL (ANTI DOBEL PINJAM)
    $cek = mysqli_query($koneksi, "SELECT status_mobil FROM mobil WHERE id_mobil='$id_mobil'");
    $mobil = mysqli_fetch_assoc($cek);

    if ($mobil['status_mobil'] !== 'Tersedia') {
        header("location:peminjaman.php?pesan=tidak_tersedia");
        exit;
    }

    // 2. SIMPAN DATA PEMINJAMAN
    $query_pinjam = "
        INSERT INTO peminjaman 
(id_mobil, nama_peminjam, tujuan, tgl_pinjam, tgl_rencana_kembali, status_kembali)
VALUES
('$id_mobil','$nama','$tujuan','$tgl_pinjam','$tgl_rencana','Belum')

    ";

    if (mysqli_query($koneksi, $query_pinjam)) {

        // 3. UPDATE STATUS MOBIL → DIPINJAM
        mysqli_query($koneksi, "
            UPDATE mobil 
            SET status_mobil='Dipinjam' 
            WHERE id_mobil='$id_mobil'
        ");

        header("location:peminjaman.php?pesan=berhasil_pinjam");
        exit;
    } else {
        header("location:peminjaman.php?pesan=gagal");
        exit;
    }
}


// ===============================
// B. PROSES PENGEMBALIAN MOBIL
// ===============================
else if (isset($_GET['aksi']) && $_GET['aksi'] === 'kembali') {

    $id_pinjam = $_GET['id'];
    $id_mobil  = $_GET['idm'];
    $tgl_now   = date('Y-m-d');

    // 1. CEK APAKAH SUDAH DIKEMBALIKAN
    $cek = mysqli_query($koneksi, "
        SELECT status_kembali 
        FROM peminjaman 
        WHERE id_pinjam='$id_pinjam'
    ");
    $row = mysqli_fetch_assoc($cek);

    if ($row['status_kembali'] === 'Sudah') {
        header("location:peminjaman.php?pesan=sudah_kembali");
        exit;
    }

    // 2. UPDATE PEMINJAMAN
    $query_kembali = "
        UPDATE peminjaman 
SET status_kembali='Sudah'
WHERE id_pinjam='$id_pinjam';

    ";

    if (mysqli_query($koneksi, $query_kembali)) {

        // 3. UPDATE STATUS MOBIL → TERSEDIA
        mysqli_query($koneksi, "
            UPDATE mobil 
            SET status_mobil='Tersedia'
            WHERE id_mobil='$id_mobil'
        ");

        header("location:peminjaman.php?pesan=mobil_kembali");
        exit;
    }
}


// ===============================
// C. HAPUS RIWAYAT (OPSIONAL)
// ===============================
else if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {

    $id = $_GET['id'];

    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_pinjam='$id'");
    header("location:peminjaman.php?pesan=hapus_riwayat");
    exit;
}

// ===============================
// DEFAULT (AMAN)
// ===============================
else {
    header("location:peminjaman.php");
    exit;
}
