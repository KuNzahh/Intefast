<?php
// proses_tambah_arsip.php

include '../include/koneksi.php'; // Pastikan file koneksi sudah benar

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_arsip = mysqli_real_escape_string($conn, $_POST['judul_arsip']);
    $tanggal_upload = mysqli_real_escape_string($conn, $_POST['tanggal_upload']);

    // File upload handling
    $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    $file = $_FILES['berkas'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $upload_dir = 'uploads/arsip/';

    if (!in_array($file_ext, $allowed_ext)) {
        echo "<script>alert('Format file tidak didukung!');window.history.back();</script>";
        exit;
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_file_name = uniqid('arsip_', true) . '.' . $file_ext;
    $file_path = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        $query = "INSERT INTO arsip (judul_arsip, tanggal_upload, berkas) VALUES ('$judul_arsip', '$tanggal_upload', '$file_path')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Arsip berhasil ditambahkan!');window.location='arsip_digital.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan data!');window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Gagal upload file!');window.history.back();</script>";
    }
} else {
    header('Location: arsip.php');
    exit;
}
?>