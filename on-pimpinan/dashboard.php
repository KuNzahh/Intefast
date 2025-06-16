<?php
session_start(); // Tambahkan ini di baris paling atas

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php'; // Pastikan koneksi ke database sudah benar

// Query jumlah SKCK
$query_jumlah_skck = "SELECT COUNT(*) AS total FROM skck";
$result_jumlah_skck = mysqli_query($conn, $query_jumlah_skck);
$data_jumlah_skck = mysqli_fetch_assoc($result_jumlah_skck);
$jumlah_skck = $data_jumlah_skck['total'];

// Query jumlah SIK
$query_jumlah_sik = "SELECT COUNT(*) AS total FROM sik";
$result_jumlah_sik = mysqli_query($conn, $query_jumlah_sik);
$data_jumlah_sik = mysqli_fetch_assoc($result_jumlah_sik);
$jumlah_sik = $data_jumlah_sik['total'];

// Query jumlah STTP
$query_jumlah_sttp = "SELECT COUNT(*) AS total FROM sttp";
$result_jumlah_sttp = mysqli_query($conn, $query_jumlah_sttp);
$data_jumlah_sttp = mysqli_fetch_assoc($result_jumlah_sttp);
$jumlah_sttp = $data_jumlah_sttp['total'];

// Query jumlah permohonan (total dari SKCK, SIK, dan STTP)
$jumlah_permohonan = $jumlah_skck + $jumlah_sik + $jumlah_sttp;
$jumlah_user = $jumlah_permohonan;

// Statistik SKCK per bulan
$statistik_skck = [];
$query_skck_bulanan = "SELECT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') AS bulan, COUNT(*) AS jumlah
                        FROM skck
                        GROUP BY bulan
                        ORDER BY bulan";
$result_skck_bulanan = mysqli_query($conn, $query_skck_bulanan);
while ($row = mysqli_fetch_assoc($result_skck_bulanan)) {
    $statistik_skck[$row['bulan']] = $row['jumlah'];
}

// Statistik SIK per bulan
$statistik_sik = [];
$query_sik_bulanan = "SELECT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') AS bulan, COUNT(*) AS jumlah
                       FROM sik
                       GROUP BY bulan
                       ORDER BY bulan";
$result_sik_bulanan = mysqli_query($conn, $query_sik_bulanan);
while ($row = mysqli_fetch_assoc($result_sik_bulanan)) {
    $statistik_sik[$row['bulan']] = $row['jumlah'];
}

// Statistik STTP per bulan
$statistik_sttp = [];
$query_sttp_bulanan = "SELECT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') AS bulan, COUNT(*) AS jumlah
                        FROM sttp
                        GROUP BY bulan
                        ORDER BY bulan";
$result_sttp_bulanan = mysqli_query($conn, $query_sttp_bulanan);
while ($row = mysqli_fetch_assoc($result_sttp_bulanan)) {
    $statistik_sttp[$row['bulan']] = $row['jumlah'];
}

// Mendapatkan semua bulan untuk label grafik
$all_months = array_unique(array_merge(array_keys($statistik_skck), array_keys($statistik_sik), array_keys($statistik_sttp)));
sort($all_months);

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
                <?php include 'logo.php'; ?>
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
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Dashboard Pimpinan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->
            </div>
            <!-- Start::dashboard-content -->

            <!-- Halo User -->
            <div class="row row-sm banner-img">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="card custom-card card-box">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-sm-3 text-center">
                                    <video class="img-fluid rounded" autoplay muted loop>
                                        <source src="../assets/video/2.mp4" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <h4 class="fw-bold text-dark">Halo <a href="#" class="text-primary"><?php echo $_SESSION['nama']; ?></a></h4>
                                </div>
                                <div class="col-sm-8">
                                    <p class="text-m-auto mb-0">
                                        Selamat datang, Bapak/Ibu Pimpinan, di Aplikasi <strong>IntelFast</strong>.
                                        <br><br>
                                        Aplikasi ini dirancang untuk memudahkan pimpinan dalam memantau seluruh aktivitas pelayanan yang sedang berjalan.
                                        ◇ Cek data permohonan yang masuk, status penyelesaian, hingga laporan kendala lapangan secara real-time. <br>
                                        ◇ Pantau kinerja tiap unit pelayanan untuk memastikan standar pelayanan publik tetap terjaga. <br>
                                        ◇ Evaluasi laporan mingguan atau bulanan yang telah disusun secara otomatis berdasarkan aktivitas pelayanan.
                                        <br><br>
                                        Setelah melakukan pemeriksaan, Bapak/Ibu dapat memberikan persetujuan atau verifikasi laporan dengan tanda tangan digital melalui fitur <strong>QR Code</strong> yang tersedia di setiap dokumen.
                                        <br><br>
                                        Terima kasih atas dedikasi Bapak/Ibu dalam memastikan pelayanan publik berjalan cepat, transparan, dan terpercaya melalui IntelFast.
                                    </p>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Kartu Jumlah Data yang Berwarna -->
                <div class="col-md-3 mb-4">
                    <div class="card text-center shadow" style="background-color: #007bff; color: white;">
                        <div class="card-body">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h6 class="fw-bold">Jumlah SKCK</h6>
                            <h3><?php echo $jumlah_skck; ?></h3>
                            <a href="ttd_digital.php" class="btn btn-light btn-sm mt-2">Detail</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center shadow" style="background-color: #28a745; color: white;">
                        <div class="card-body">
                            <i class="fas fa-id-card fa-2x mb-2"></i>
                            <h6 class="fw-bold">Jumlah SIK</h6>
                            <h3><?php echo $jumlah_sik; ?> </h3>
                            <a href="ttd_digital.php" class="btn btn-light btn-sm mt-2">Detail</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center shadow" style="background-color: #ffc107; color: white;">
                        <div class="card-body">
                            <i class="fas fa-bullhorn fa-2x mb-2"></i>
                            <h6 class="fw-bold">Jumlah STTP</h6>
                            <h3><?php echo $jumlah_sttp; ?> </h3>
                            <a href="ttd_digital.php" class="btn btn-light btn-sm mt-2">Detail</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center shadow" style="background-color: #17a2b8; color: white;">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h6 class="fw-bold">Jumlah Permohonan</h6>
                            <h3><?php echo $jumlah_permohonan; ?></h3>
                            <a href="data_pengajuan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="card-title">Statistik Pelayanan Per Bulan</div>
                        </div>
                        <div class="card-body">
                            <canvas id="statistik-berkas" class="chartjs-chart"></canvas>
                            <div class="mt-3 text-end">
                                <a href="cetak_laporan_statistik.php" class="btn btn-success me-2" target="_blank">
                                    <i class="fas fa-print me-2"></i>
                                    Unduh PDF Laporan Statistik Pelayanan
                                </a>
                                <button class="btn btn-primary" onclick="downloadStatistik()">
                                    <i class="fas fa-download me-2"></i>
                                    Unduh CSV Laporan Statistik Pelayanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card overflow-hidden mt-3">
                    <div class="card-header border-bottom-0">
                        <label class="card-title">Hasil Survey Kepuasan Masyarakat</label>
                        <span class="d-block fs-12 mb-0 text-muted">Data rata-rata hasil survei kepuasan masyarakat terkait layanan yang diberikan.</span>
                    </div>
                    <div class="card-body">
                        <canvas id="surveyChart" height="250"></canvas>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Ambil data rata-rata jawaban survei dari PHP
                        fetch('get_survey_data.php')
                            .then(response => response.json())
                            .then(data => {
                                const labels = data.labels;
                                const averageScores = data.averageScores;

                                const ctx = document.getElementById('surveyChart').getContext('2d');
                                const myChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Rata-rata Skor',
                                            data: averageScores,
                                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 4 // Skala maksimum sesuai dengan nilai tertinggi survei (sangat setuju)
                                            }
                                        }
                                    }
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching survey data:', error);
                                document.getElementById('surveyChart').innerHTML = '<p class="text-danger">Gagal memuat data survei.</p>';
                            });
                    });
                </script>

            </div>
        </div>
    </div>
    <!-- End::dashboard-content -->

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statistikBerkasCanvas = document.getElementById('statistik-berkas').getContext('2d');
            const allMonths = <?php echo json_encode($all_months); ?>;
            const statistikSKCK = <?php echo json_encode($statistik_skck); ?>;
            const statistikSIK = <?php echo json_encode($statistik_sik); ?>;
            const statistikSTTP = <?php echo json_encode($statistik_sttp); ?>;

            const skckData = allMonths.map(month => statistikSKCK[month] || 0);
            const sikData = allMonths.map(month => statistikSIK[month] || 0);
            const sttpData = allMonths.map(month => statistikSTTP[month] || 0);

            new Chart(statistikBerkasCanvas, {
                type: 'line',
                data: {
                    labels: allMonths,
                    datasets: [{
                        label: 'SKCK',
                        data: skckData,
                        borderColor: '#007bff',
                        fill: false
                    }, {
                        label: 'SIK',
                        data: sikData,
                        borderColor: '#28a745',
                        fill: false
                    }, {
                        label: 'STTP',
                        data: sttpData,
                        borderColor: '#ffc107',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Pengajuan'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        }
                    }
                }
            });
        });
    </script>


    <?php include 'script.php'; ?>

</body>

</html>