<?php
session_start();
include 'koneksi.php'; // Menghubungkan ke database (path disesuaikan)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Gunakan prepared statements untuk keamanan yang lebih baik
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk memeriksa pengguna menggunakan prepared statements
    $query = "SELECT id_user, nama, role, password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // ---- LOGIN SUKSES ----
            // Set sesi berdasarkan role dan id_user
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['loggedin'] = true; // Tambahkan penanda login

            // Arahkan kembali ke halaman login dengan status sukses
            // Halaman login nanti yang akan mengarahkan ke dashboard
            header('Location: ../index.php?status=success');
            exit();

        } else {
            // ---- PASSWORD SALAH ----
            // Arahkan kembali ke halaman login dengan status error
            header('Location: ../index.php?status=error');
            exit();
        }
    } else {
        // ---- USERNAME TIDAK DITEMUKAN ----
        // Arahkan kembali ke halaman login dengan status error
        header('Location: ../index.php?status=error');
        exit();
    }
} else {
    // Jika file diakses langsung, kembalikan ke halaman login
    header('Location: ../index.php');
    exit();
}
?>