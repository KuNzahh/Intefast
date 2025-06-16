<?php
session_start();
require_once '../include/koneksi.php';

if (!isset($_GET['jenis']) || !isset($_GET['id'])) {
    header("Location: data_pengajuan.php?msg=invalid");
    exit();
}

$jenis = $_GET['jenis'];
$id    = intval($_GET['id']);

switch ($jenis) {
    case 'skck':
        $query = "DELETE FROM skck WHERE id_skck = $id";
        break;
    case 'sik':
        $query = "DELETE FROM sik WHERE id_sik = $id";
        break;
    case 'sttp':
        $query = "DELETE FROM sttp WHERE id_sttp = $id";
        break;
    default:
        header("Location: data_pengajuan.php?msg=invalid_jenis");
        exit();
}

if (mysqli_query($conn, $query)) {
    header("Location: data_pengajuan.php?msg=deleted");
} else {
    header("Location: data_pengajuan.php?msg=error");
}
exit();
