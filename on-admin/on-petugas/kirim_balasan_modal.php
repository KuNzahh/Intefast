<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak.";
    exit();
}

include '../include/koneksi.php';
$id_petugas = $_SESSION['user_id'];
$id_penerima = isset($_POST['id_penerima']) ? intval($_POST['id_penerima']) : 0;
$balasan = isset($_POST['balasan']) ? trim($_POST['balasan']) : '';

if ($id_penerima > 0 && !empty($balasan)) {
    $query_kirim = "INSERT INTO chat (id_pengirim, id_penerima, pesan, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt_kirim = mysqli_prepare($conn, $query_kirim);
    mysqli_stmt_bind_param($stmt_kirim, "iis", $id_petugas, $id_penerima, $balasan);
    if (mysqli_stmt_execute($stmt_kirim)) {
        echo "success";
    } else {
        echo "Gagal mengirim pesan.";
    }
} else {
    echo "Data tidak lengkap.";
}
?>