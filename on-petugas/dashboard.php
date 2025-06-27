<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div id="loader">
        <img src="../assets/images/media/media-79.svg" alt="">
    </div>
    <div class="page">
        <header class="app-header">
            <div class="main-header-container container-fluid">
                <?php include 'container.php'; ?>
            </div>
        </header>
        <aside class="app-sidebar sticky" id="sidebar">
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
            <?php include 'sidebar.php'; ?>
        </aside>
        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Dashboard Petugas Pelayanan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
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
                                            Terima kasih telah berdedikasi dalam memberikan pelayanan terbaik kepada masyarakat.<br><br>
                                            ◇ Pastikan semua permohonan diperiksa dengan teliti. <br>
                                            ◇ Berikan informasi yang jelas dan akurat kepada pemohon. <br>
                                            ◇ Bantu masyarakat dengan ramah dan profesional.<br><br>
                                            Jika ada kendala dalam sistem atau pertanyaan terkait tugas Anda, silakan hubungi administrator.<br>
                                            Mari bersama mewujudkan pelayanan yang cepat, transparan, dan terpercaya!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-sm">
                    <div class="col-md-6 col-lg-4">
                        <div class="card text-center shadow" style="background-color: #007bff; color: white;">
                            <div class="card-body">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah SKCK</h6>
                                <h3><?php echo $jumlah_skck; ?></h3>
                                <a href="laporan_permohonan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card text-center shadow" style="background-color: #28a745; color: white;">
                            <div class="card-body">
                                <i class="fas fa-id-card fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah SIK</h6>
                                <h3><?php echo $jumlah_sik; ?></h3>
                                <a href="laporan_permohonan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card text-center shadow" style="background-color: #ffc107; color: white;">
                            <div class="card-body">
                                <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah STTP</h6>
                                <h3><?php echo $jumlah_sttp; ?></h3>
                                <a href="laporan_permohonan.php" class="btn btn-light btn-sm mt-2">Detail</a>
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

            function downloadStatistik() {
                const allMonths = <?php echo json_encode($all_months); ?>;
                const statistikSKCK = <?php echo json_encode($statistik_skck); ?>;
                const statistikSIK = <?php echo json_encode($statistik_sik); ?>;
                const statistikSTTP = <?php echo json_encode($statistik_sttp); ?>;

                let csvContent = "Bulan,SKCK,SIK,STTP\n";
                allMonths.forEach(month => {
                    const skck = statistikSKCK[month] || 0;
                    const sik = statistikSIK[month] || 0;
                    const sttp = statistikSTTP[month] || 0;
                    csvContent += `${month},${skck},${sik},${sttp}\n`;
                });

                const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "statistik_pelayanan.csv");
                document.body.appendChild(link);

                link.click();
                document.body.removeChild(link);
            }
        </script>

        

</body>

</html>