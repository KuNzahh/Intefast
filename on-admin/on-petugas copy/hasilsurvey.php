<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

$sql_survey = "
    SELECT
        YEAR(tanggal_survey) AS tahun,
        MONTH(tanggal_survey) AS bulan_angka,
        id_survey, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, tanggal_survey
    FROM survey_kepuasan
    ORDER BY tanggal_survey ASC
";
$result_survey = mysqli_query($conn, $sql_survey);

$data_survey_mentah = [];
if ($result_survey) {
    while ($row = mysqli_fetch_assoc($result_survey)) {
        $data_survey_mentah[] = $row;
    }
}

$nilai_opsi = [
    'sangat_setuju' => 4,
    'setuju' => 3,
    'kurang_setuju' => 2,
    'tidak_setuju' => 1,
];

$data_agregat = [];
$labels_grafik = [];
$data_rata_rata_grafik = [];

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Agregasi data per bulan dan tahun
$agregasi_bulanan = [];
foreach ($data_survey_mentah as $row) {
    $tahun = date('Y', strtotime($row['tanggal_survey']));
    $bulan_angka = date('n', strtotime($row['tanggal_survey']));
    $bulan_tahun = $nama_bulan[$bulan_angka] . ' ' . $tahun;

    $total_nilai = 0;
    $valid_jawaban = 0;
    for ($i = 1; $i <= 10; $i++) {
        $jawaban = strtolower($row['q'.$i]);
        if (isset($nilai_opsi[$jawaban])) {
            $total_nilai += $nilai_opsi[$jawaban];
            $valid_jawaban++;
        }
    }

    if ($valid_jawaban > 0) {
        $rata_rata = $total_nilai / $valid_jawaban;
    } else {
        $rata_rata = 0;
    }

    if (!isset($agregasi_bulanan[$bulan_tahun])) {
        $agregasi_bulanan[$bulan_tahun] = [
            'total_nilai' => 0,
            'jumlah_responden' => 0,
            'sum_rata_rata' => 0,
            'bulan_angka' => $bulan_angka,
            'tahun' => $tahun,
            'bulan_nama' => $nama_bulan[$bulan_angka],
        ];
    }
    $agregasi_bulanan[$bulan_tahun]['total_nilai'] += $total_nilai;
    $agregasi_bulanan[$bulan_tahun]['jumlah_responden']++;
    $agregasi_bulanan[$bulan_tahun]['sum_rata_rata'] += $rata_rata;
}

// Siapkan data untuk tabel dan grafik
$data_survey = [];
foreach ($agregasi_bulanan as $bulan_tahun => $data) {
    $rata_rata_bulanan = $data['jumlah_responden'] > 0 ? $data['sum_rata_rata'] / $data['jumlah_responden'] : 0;
    $data_survey[] = [
        'tahun' => $data['tahun'],
        'bulan_nama' => $data['bulan_nama'],
        'jumlah_responden' => $data['jumlah_responden'],
        'rata_rata_hasil' => number_format($rata_rata_bulanan, 2),
    ];
    $labels_grafik[] = $bulan_tahun;
    $data_rata_rata_grafik[] = number_format($rata_rata_bulanan, 2);
}

$json_labels_grafik = json_encode($labels_grafik);
$json_data_rata_rata_grafik = json_encode($data_rata_rata_grafik);

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Hasil Survey Kepuasan Masyarakat - Sistem Pelayanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
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
                        <h2 class="main-content-title fs-24 mb-1">Hasil Survey Kepuasan Masyarakat</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Hasil Survey</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-poll me-2"></i>Data Survey Kepuasan</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>Tahun</th>
                                            <th>Bulan</th>
                                            <th>Jumlah Responden</th>
                                            <th>Rata-rata Kepuasan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_survey)): ?>
                                            <?php foreach ($data_survey as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tahun']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['bulan_nama']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jumlah_responden']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['rata_rata_hasil']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center">Tidak ada data survey kepuasan.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="card custom-card overflow-hidden">
                            <div class="card-header border-bottom-0">
                                <label class="card-title">Grafik Hasil Survey Kepuasan Masyarakat</label>
                                <span class="d-block fs-12 mb-0 text-muted">Data rata-rata hasil survei kepuasan masyarakat terkait layanan yang diberikan per bulan.</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="surveyChart" height="250"></canvas>
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
            $(document).ready(function() {
                const labelsGrafik = <?php echo $json_labels_grafik; ?>;
                const dataRataRataGrafik = <?php echo $json_data_rata_rata_grafik; ?>;

                console.log('Labels Grafik:', labelsGrafik);
                console.log('Data Rata-rata Grafik:', dataRataRataGrafik);

                const ctxSurvey = document.getElementById('surveyChart').getContext('2d');
                const surveyChart = new Chart(ctxSurvey, {
                    type: 'bar', // Menggunakan grafik batang
                    data: {
                        labels: labelsGrafik,
                        datasets: [{
                            label: 'Rata-rata Kepuasan',
                            data: dataRataRataGrafik,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Rata-rata Nilai'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Bulan'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>

</body>

</html>