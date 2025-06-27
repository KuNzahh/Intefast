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
                                <a href="cetak_laporan_statistik.php" class="btn btn-danger me-2" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Unduh PDF Laporan Statistik Pelayanan
                                </a>
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
                    <div class="card-footer text-end">
                        <a href="cetak_laporan_HasilSurveyLayanan.php" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i>
                            Unduh PDF Hasil Survey Layanan
                        </a>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        fetch('get_survey_data.php')
                            .then(response => response.json())
                            .then(data => {
                                const labels = data.labels;
                                const averageScores = data.averageScores;

                                const ctx = document.getElementById('surveyChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Rata-rata Skor Survei',
                                            data: averageScores,
                                            backgroundColor: [
                                                '#007bff', '#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14'
                                            ],
                                            borderColor: [
                                                '#0056b3', '#1e7e34', '#d39e00', '#117a8b', '#bd2130', '#4e267a', '#c05600'
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            title: {
                                                display: true,
                                                text: 'Rata-rata Hasil Survey Kepuasan Masyarakat',
                                                font: {
                                                    size: 18
                                                }
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        let value = context.parsed.y;
                                                        let label = context.dataset.label || '';
                                                        return `${label}: ${value.toFixed(2)} dari 4`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 4,
                                                title: {
                                                    display: true,
                                                    text: 'Skor (1 = Tidak Setuju, 4 = Sangat Setuju)'
                                                },
                                                ticks: {
                                                    stepSize: 1
                                                },
                                                grid: {
                                                    color: '#e9ecef'
                                                }
                                            },
                                            x: {
                                                title: {
                                                    display: true,
                                                    text: 'Pertanyaan Survei'
                                                },
                                                grid: {
                                                    display: false
                                                }
                                            }
                                        }
                                    }
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching survey data:', error);
                                document.getElementById('surveyChart').insertAdjacentHTML('beforebegin', '<p class="text-danger">Gagal memuat data survei.</p>');
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
            const ctx = document.getElementById('statistik-berkas').getContext('2d');
            const allMonths = <?php echo json_encode($all_months); ?>;
            const statistikSKCK = <?php echo json_encode($statistik_skck); ?>;
            const statistikSIK = <?php echo json_encode($statistik_sik); ?>;
            const statistikSTTP = <?php echo json_encode($statistik_sttp); ?>;

            // Ambil data per bulan, jika tidak ada isi 0
            const skckData = allMonths.map(month => statistikSKCK[month] !== undefined ? statistikSKCK[month] : 0);
            const sikData = allMonths.map(month => statistikSIK[month] !== undefined ? statistikSIK[month] : 0);
            const sttpData = allMonths.map(month => statistikSTTP[month] !== undefined ? statistikSTTP[month] : 0);

            // Jika semua data 0, tampilkan pesan
            const totalData = skckData.reduce((a, b) => a + b, 0) + sikData.reduce((a, b) => a + b, 0) + sttpData.reduce((a, b) => a + b, 0);
            if (totalData === 0) {
                ctx.font = "16px Arial";
                ctx.fillStyle = "#888";
                ctx.textAlign = "center";
                ctx.fillText("Belum ada data statistik pelayanan.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allMonths,
                    datasets: [
                        {
                            label: 'SKCK',
                            data: skckData,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0,123,255,0.2)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#007bff',
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'SIK',
                            data: sikData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40,167,69,0.2)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#28a745',
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'STTP',
                            data: sttpData,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255,193,7,0.2)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#ffc107',
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Statistik Pengajuan SKCK, SIK, STTP per Bulan'
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Pengajuan'
                            },
                            ticks: {
                                stepSize: 1
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