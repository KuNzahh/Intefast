<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
$user_id = $_SESSION['user_id'];
$pesan = ''; // Variabel untuk menyimpan pesan status

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namaLengkap = $_POST['namaLengkap'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $passwordLama = $_POST['password'];
    $passwordBaru = $_POST['passwordBaru'];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = '<div class="alert alert-danger">Format email tidak valid.</div>';
    } else {
        $update_profil_sql = "UPDATE users SET nama=?, username=?, email=? WHERE id_user=?";
        $stmt_profil = mysqli_prepare($conn, $update_profil_sql);
        mysqli_stmt_bind_param($stmt_profil, "sssi", $namaLengkap, $username, $email, $user_id);

        if (mysqli_stmt_execute($stmt_profil)) {
            // Update password jika diisi
            if (!empty($passwordBaru)) {
                // Verifikasi password lama (jika perlu)
                $query_cek_password = "SELECT password FROM users WHERE id_user = '$user_id'";
                $result_cek_password = mysqli_query($conn, $query_cek_password);
                $row_cek_password = mysqli_fetch_assoc($result_cek_password);

                if (empty($passwordLama) || password_verify($passwordLama, $row_cek_password['password'])) {
                    $hashed_password_baru = password_hash($passwordBaru, PASSWORD_DEFAULT);
                    $update_password_sql = "UPDATE users SET password=? WHERE id_user=?";
                    $stmt_password = mysqli_prepare($conn, $update_password_sql);
                    mysqli_stmt_bind_param($stmt_password, "si", $hashed_password_baru, $user_id);
                    mysqli_stmt_execute($stmt_password);
                    mysqli_stmt_close($stmt_password);
                    $pesan = '<div class="alert alert-success">Profil dan password berhasil diperbarui.</div>';
                } elseif (!empty($passwordLama)) {
                    $pesan = '<div class="alert alert-danger">Password lama tidak sesuai. Profil berhasil diperbarui tanpa mengubah password.</div>';
                } else {
                    $pesan = '<div class="alert alert-success">Profil berhasil diperbarui.</div>';
                }
            } else {
                $pesan = '<div class="alert alert-success">Profil berhasil diperbarui.</div>';
            }
        } else {
            $pesan = '<div class="alert alert-danger">Gagal memperbarui profil.</div>';
        }
        mysqli_stmt_close($stmt_profil);

        // Refresh data profil dari database setelah update
        $query_profil = "SELECT nama, username, email FROM users WHERE id_user = '$user_id'";
        $result_profil = mysqli_query($conn, $query_profil);
        $data_profil = mysqli_fetch_assoc($result_profil);
        $_SESSION['nama'] = $data_profil['nama']; // Update nama di session
    }
} else {
    // Ambil data profil saat halaman pertama kali diakses
    $query_profil = "SELECT nama, username, email FROM users WHERE id_user = '$user_id'";
    $result_profil = mysqli_query($conn, $query_profil);
    $data_profil = mysqli_fetch_assoc($result_profil);
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <!-- Loader -->
    <div id="loader">
        <img src="../assets/images/media/media-79.svg" alt="">
    </div>
    <!-- Loader -->

    <div class="page">
        <!-- app-header -->
        <header class="app-header">
            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">
                <?php include 'container.php'; ?>
            </div>
            <!-- End::main-header-container -->
        </header>
        <!-- /app-header -->
        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">
            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="index.php" class="header-logo">
                    <img src="../assets/images/brand-logos/desktop-white.png" class="desktop-white" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-white.png" class="toggle-white" alt="logo">
                    <img src="../assets/images/brand-logos/desktop-logo.png" class="desktop-logo" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-dark.png" class="toggle-dark" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-logo.png" class="toggle-logo" alt="logo">
                    <img src="../assets/images/brand-logos/logopanjangbiru.jpg" class="desktop-dark" alt="logo">
                </a>
            </div>
            <!-- End::main-sidebar-header -->
            <!-- Start::main-sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- End::main-sidebar -->
        </aside>
        <!-- End::app-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">

                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Profil Akun</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profil Akun</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="">
                        <div class="card shadow-lg p-4">
                            <h3 class="mb-4">Informasi Akun</h3>
                            <?php echo $pesan; ?>
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="namaLengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="namaLengkap"
                                        placeholder="Masukkan nama lengkap" value="<?php echo $data_profil['nama'] ?? ''; ?>" name="namaLengkap" required>
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username"
                                        placeholder="Masukkan username" value="<?php echo $data_profil['username'] ?? ''; ?>" name="username" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email"
                                        placeholder="Masukkan email" value="<?php echo $data_profil['email'] ?? ''; ?>" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="password"
                                        placeholder="Masukkan password lama" name="password">
                                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                                </div>

                                <div class="mb-4">
                                    <label for="passwordBaru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="passwordBaru"
                                        placeholder="Masukkan password baru" name="passwordBaru">
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scrollToTop">
        <span class="arrow"><i class="fe fe-arrow-up"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <footer class="footer mt-auto py-3 bg-white text-center">
        <div class="container">
            <?php include 'foot.php'; ?>
        </div>
    </footer>
    <?php include 'script.php'; ?>

</body>

</html>