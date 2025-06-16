<?php
session_start();
include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sttp = mysqli_real_escape_string($conn, $_POST['id_sttp'] ?? '');
    $user_id = $_SESSION['user_id'];
    $nama_paslon = mysqli_real_escape_string($conn, $_POST['nama_paslon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $penanggung_jawab = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);
    $nama_kampanye = mysqli_real_escape_string($conn, $_POST['nama_kampanye']);
    $tgl_kampanye = mysqli_real_escape_string($conn, $_POST['tgl_kampanye']);
    $tempat = mysqli_real_escape_string($conn, $_POST['tempat']);
    $jumlah_peserta = mysqli_real_escape_string($conn, $_POST['jumlah_peserta']);
    $nama_jurkam = mysqli_real_escape_string($conn, $_POST['nama_jurkam']);
    $memperhatikan = mysqli_real_escape_string($conn, $_POST['memperhatikan']);
    $progres = mysqli_real_escape_string($conn, $_POST['progres'] ?? '');

    $lampiran = '';
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('lampiran_', true) . '.' . $file_ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                $lampiran = $new_filename;
            } else {
                $_SESSION['error'] = 'Gagal mengupload lampiran.';
                header('Location: statushistory.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Format lampiran tidak valid.';
            header('Location: statushistory.php');
            exit();
        }
    }

    if (!empty($id_sttp)) {
        // UPDATE
        $sql = "UPDATE sttp SET 
                user_id='$user_id',
                nama_paslon='$nama_paslon',
                penanggung_jawab='$penanggung_jawab',
                nama_kampanye='$nama_kampanye',
                alamat='$alamat',
                tgl_kampanye='$tgl_kampanye',
                tempat='$tempat',
                jumlah_peserta='$jumlah_peserta',
                nama_jurkam='$nama_jurkam',
                memperhatikan='$memperhatikan',
                progres='$progres'
                    " . (!empty($lampiran) ? ", lampiran='$lampiran'" : "") . "
                WHERE id_sttp='$id_sttp'";
    } else {
        // INSERT
        $sql = "INSERT INTO sttp (
                    user_id, nama_paslon, penanggung_jawab, nama_kampanye,
                alamat, tgl_kampanye, tempat, jumlah_peserta,
                nama_jurkam, memperhatikan, progres, lampiran
                ) VALUES (
                    '$user_id', '$nama_paslon', '$penanggung_jawab', '$nama_kampanye',
                '$alamat', '$tgl_kampanye', '$tempat', '$jumlah_peserta',
                '$nama_jurkam', '$memperhatikan', '$progres', '$lampiran'
                )";
    }

    $run = mysqli_query($conn, $sql);

    $_SESSION[$run ? 'success' : 'error'] = $run
        ? (!empty($id_sttp) ? 'Data berhasil di-update' : 'Data berhasil dikirim')
        : (!empty($id_sttp) ? 'Gagal update data' : 'Gagal tambah data');

    header('Location: statushistory.php'); // <--- ini diganti
    exit();
}

// Handle delete
if (isset($_GET['hapus'])) {
    $id_sttp = mysqli_real_escape_string($conn, $_GET['hapus']);
    $del = mysqli_query($conn, "DELETE FROM sttp WHERE id_sttp='$id_sttp'");
    $_SESSION[$del ? 'success' : 'error'] = $del
        ? 'Data berhasil dihapus' : 'Gagal hapus data';
    header('Location: statushistory.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Dashboard - <?php echo $_SESSION['user_name']; ?></title>
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
                <a href="index.html" class="header-logo">
                    <img src="../assets/images/brand-logos/desktop-white.png" class="desktop-white" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-white.png" class="toggle-white" alt="logo">
                    <img src="../assets/images/brand-logos/desktop-logo.png" class="desktop-logo" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-dark.png" class="toggle-dark" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-logo.png" class="toggle-logo" alt="logo">
                    <img src="../assets/images/brand-logos/desktop-dark.png" class="desktop-dark" alt="logo">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <ul class="main-menu">
                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">Menu Utama</span></li>
                        <!-- End::slide__category -->

                        <!-- Dashboard -->
                        <li class="slide">
                            <a href="dashboard.php" class="side-menu__item">
                                <i class="ti-home side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>

                        <!-- Buat Permohonan -->
                        <li class="slide">
                            <a href="pelayanan.php" class="side-menu__item">
                                <i class="ti-file side-menu__icon"></i>
                                <span class="side-menu__label">Pelayanan</span>
                            </a>
                        </li>

                        <!-- Riwayat Permohonan -->
                        <li class="slide">
                            <a href="statushistory.php" class="side-menu__item">
                                <i class="ti-list side-menu__icon"></i>
                                <span class="side-menu__label">Status dan Histroy</span>
                            </a>
                        </li>

                        <!-- Bantuan -->
                        <li class="slide">
                            <a href="survey.php" class="side-menu__item">
                                <i class="ti-help-alt side-menu__icon"></i>
                                <span class="side-menu__label">Survey Kepuasan</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End::nav -->
            </div>
            <!-- End::main-sidebar -->
        </aside>
        <!-- End::app-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Pelayanan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Ajuan STTP</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::Formulir Pengajuan STTP -->
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Pengajuan STTP</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_sttp" value="<?php echo $_GET['id_sttp'] ?? ''; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                                            <div class="col-md-6">
                                                <label for="nama_paslon" class="form-label">Nama Pasangan Calon</label>
                                                <input type="text" class="form-control" id="nama_paslon" name="nama_paslon" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                                <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                                <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="nama_kampanye" class="form-label">Bentuk Kampanye</label>
                                                <input type="text" class="form-control" id="nama_kampanye" name="nama_kampanye" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="tgl_kampanye" class="form-label">Waktu/Jadwal</label>
                                                <textarea class="form-control" id="tgl_kampanye" name="tgl_kampanye" rows="2" required></textarea>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="tempat" class="form-label"> Tempat</label>
                                                <input type="text" class="form-control" id="tempat" name="tempat" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="jumlah_peserta" class="form-label">Jumlah Peserta</label>
                                                <input type="text" class="form-control" id="jumlah_peserta" name="jumlah_peserta" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="nama_jurkam" class="form-label"> nama Juru Kamera</label>
                                                <input type="text" class="form-control" id="nama_jurkam" name="nama_jurkam" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="progres" class="form-label">Progres</label>
                                                <input type="text" class="form-control" id="progres" name="progres" value="penelitian" readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="memperhatikan" class="form-label">Memperhatikan</label>
                                                <input type="text" class="form-control" id="memperhatikan" name="memperhatikan" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="lampiran" class="form-label">Lampiran</label>
                                                <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                                            </div>

                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-primary">Ajukan STTP</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Formulir -->
            </div>
        </div>
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="fe fe-arrow-up"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

    <!-- Footer Start -->
    <footer class="footer mt-auto py-3 bg-white text-center">
        <div class="container">
            <?php include 'foot.php'; ?>
        </div>
    </footer>
    <!-- Footer End -->

    <?php include 'script.php'; ?>

</body>

</html>