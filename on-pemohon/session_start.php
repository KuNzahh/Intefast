<?php
session_start(); // Tambahkan ini di baris paling atas

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}
?>