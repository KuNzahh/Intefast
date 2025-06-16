<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

if (isset($_GET['jenis']) && isset($_GET['id'])) {
    $jenis = $_GET['jenis'];
    $id = (int)$_GET['id']; // amankan dengan casting integer

    switch ($jenis) {
        case 'skck':
            $sql = "DELETE FROM skck WHERE id_skck = ?";
            break;
        case 'sik':
            $sql = "DELETE FROM sik WHERE id_sik = ?";
            break;
        case 'sttp':
            $sql = "DELETE FROM sttp WHERE id_sttp = ?";
            break;
        default:
            // Jenis tidak dikenal, kembali ke halaman utama
            header("Location: data_pengajuan.php?error=jenis_tidak_valid");
            exit();
    }

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: data_pengajuan.php?hapus=berhasil");
        exit();
    } else {
        header("Location: data_pengajuan.php?error=gagal_query");
        exit();
    }
} else {
    header("Location: data_pengajuan.php?error=parameter_tidak_lengkap");
    exit();
}
