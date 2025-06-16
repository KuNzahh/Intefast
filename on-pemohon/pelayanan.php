<?php
include 'session_start.php'; // Menghubungkan ke database
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
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::row-1 -->
                <div class="row row-sm">
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <img src="../assets/images/ecommerce/jpg/1.jpg" alt="Surat Keterangan" class="img-fluid mb-2">
                            <h6 class="fw-bold">SURAT KETERANGAN CATATAN KEPOLISIAN</h6>
                            <a href="ajuan_skck.php" class="btn btn-dark mt-2">Ajukan Permohonan</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <img src="../assets/images/ecommerce/jpg/17.jpg" alt="Surat Izin Keramaian" class="img-fluid mb-2">
                            <h6 class="fw-bold">SURAT IZIN KERAMAIAN</h6>
                            <a href="ajuan_sik.php" class="btn btn-dark mt-2">Ajukan Permohonan</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <img src="../assets/images/ecommerce/jpg/16.jpg" alt="Surat Tanda Terima" class="img-fluid mb-2">
                            <h6 class="fw-bold">SURAT TANDA TERIMA PEMBERITAHUAN</h6>
                            <a href="ajuan_sttp.php" class="btn btn-dark mt-2">Ajukan Permohonan</a>
                        </div>
                    </div>
                </div>
                <!-- End::row-1 -->

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