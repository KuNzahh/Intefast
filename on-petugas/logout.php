<?php
session_start();

// Hapus semua sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Arahkan pengguna ke halaman login
header('Location: ../index.php');
exit();
?>
