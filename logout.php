<?php
session_start();

// Hapus semua session
session_destroy();
session_unset();

// Arahkan kembali ke halaman portal depan
header("location:index.php");
exit;
?>