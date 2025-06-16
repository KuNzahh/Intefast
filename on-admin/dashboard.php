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

// Query jumlah user dengan role 'pemohon'
$query_jumlah_user = "SELECT COUNT(*) AS total FROM users WHERE role = 'pemohon'";
$result_jumlah_user = mysqli_query($conn, $query_jumlah_user);
$data_jumlah_user = mysqli_fetch_assoc($result_jumlah_user);
$jumlah_user = $data_jumlah_user['total'];

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

$berhasil_simpan = false;
$gagal_simpan = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_berita'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $gambar = ''; // Inisialisasi nama file gambar

    // Proses upload gambar jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $nama_file = $_FILES['gambar']['name'];
        $ukuran_file = $_FILES['gambar']['size'];
        $tmp_file = $_FILES['gambar']['tmp_name'];
        $path = "../assets/images/berita/";
        $nama_gambar_baru = uniqid() . '_' . $nama_file;
        $path_file_baru = $path . $nama_gambar_baru;

        $allowed_types = ['image/jpeg', 'image/png'];
        $file_type = $_FILES['gambar']['type'];

        if (in_array($file_type, $allowed_types)) {
            if ($ukuran_file <= 2000000) {
                if (move_uploaded_file($tmp_file, $path_file_baru)) {
                    $gambar = $nama_gambar_baru;
                } else {
                    $gagal_simpan = "Gagal mengupload gambar.";
                }
            } else {
                $gagal_simpan = "Ukuran gambar terlalu besar (maks. 2MB).";
            }
        } else {
            $gagal_simpan = "Jenis file gambar tidak diizinkan (hanya JPEG dan PNG).";
        }
    }

    if (!$gagal_simpan) {
        $query = "INSERT INTO berita (judul, isi, gambar, waktu_ubah) VALUES ('$judul', '$isi', '$gambar', NOW())";
        if (mysqli_query($conn, $query)) {
            $berhasil_simpan = true;

            // Tambahkan notifikasi admin di sini
            $judul_berita_baru = $judul;
            $notifikasi_admin = [
                'pesan' => "Berita baru telah di-share: " . htmlspecialchars($judul_berita_baru),
                'jenis' => 'berita_baru',
                'waktu' => date("Y-m-d H:i:s")
            ];

            if (!isset($_SESSION['notifikasi_admin'])) {
                $_SESSION['notifikasi_admin'] = [];
            }
            array_unshift($_SESSION['notifikasi_admin'], $notifikasi_admin);

            // Batasi jumlah notifikasi (opsional)
            if (count($_SESSION['notifikasi_admin']) > 10) {
                array_pop($_SESSION['notifikasi_admin']);
            }

        } else {
            $gagal_simpan = "Gagal menyimpan berita: " . mysqli_error($conn);
        }
    }
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
                <?php include 'logo.php'; ?>
            </div>
            <?php include 'sidebar.php'; ?>
        </aside>
        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Dashboard Admin</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row row-sm banner-img">
                    <div class="col-sm-12 col-lg-12 col-xl-12">
                        <div class="card custom-card card-box" style="background-color: #e9ecef; border-left: 5px solid #007bff;">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-sm-3 text-center">
                                        <i class="fas fa-user-shield fa-3x text-primary"></i>
                                        <h4 class="fw-bold text-dark mt-2">Halo <a href="#" class="text-primary"><?php echo $_SESSION['nama']; ?></a></h4>
                                        <span class="text-muted">Administrator</span>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0">
                                            Selamat datang di Dashboard Administrator <strong>IntelFast</strong>. Anda memiliki akses penuh untuk mengelola sistem, pengguna, dan data pelayanan.
                                            <br><br>
                                            Gunakan menu di samping kiri untuk navigasi dan pantau semua aktivitas dengan efisien.
                                            <br><br>
                                            Terima kasih atas kontribusi Anda dalam menjaga kelancaran sistem IntelFast.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #6c757d; color: white;">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h6 class="fw-bold">Semua User</h6>
                                <h3><?php
                                    $query_semua_user = "SELECT COUNT(*) AS total FROM users";
                                    $result_semua_user = mysqli_query($conn, $query_semua_user);
                                    $data_semua_user = mysqli_fetch_assoc($result_semua_user);
                                    echo $data_semua_user['total'];
                                    ?></h3>
                                <a href="data_pengguna.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #17a2b8; color: white;">
                            <div class="card-body">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <h6 class="fw-bold">Pemohon</h6>
                                <h3><?php echo $jumlah_user; ?></h3>
                                <a href="data_pengguna.php?role=pemohon" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #fd7e14; color: white;">
                            <div class="card-body">
                                <i class="fas fa-user-tie fa-2x mb-2"></i>
                                <h6 class="fw-bold">Petugas</h6>
                                <h3><?php
                                    $query_petugas = "SELECT COUNT(*) AS total FROM users WHERE role = 'petugas'";
                                    $result_petugas = mysqli_query($conn, $query_petugas);
                                    $data_petugas = mysqli_fetch_assoc($result_petugas);
                                    echo $data_petugas['total'];
                                    ?></h3>
                                <a href="data_pengguna.php?role=petugas" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #dc3545; color: white;">
                            <div class="card-body">
                                <i class="fas fa-user-graduate fa-2x mb-2"></i>
                                <h6 class="fw-bold">Pimpinan</h6>
                                <h3><?php
                                    $query_pimpinan = "SELECT COUNT(*) AS total FROM users WHERE role = 'pimpinan'";
                                    $result_pimpinan = mysqli_query($conn, $query_pimpinan);
                                    $data_pimpinan = mysqli_fetch_assoc($result_pimpinan);
                                    echo $data_pimpinan['total'];
                                    ?></h3>
                                <a href="data_pengguna.php?role=pimpinan" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #343a40; color: white;">
                            <div class="card-body">
                                <i class="fas fa-user-cog fa-2x mb-2"></i>
                                <h6 class="fw-bold">Admin</h6>
                                <h3><?php
                                    $query_admin = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
                                    $result_admin = mysqli_query($conn, $query_admin);
                                    $data_admin = mysqli_fetch_assoc($result_admin);
                                    echo $data_admin['total'];
                                    ?></h3>
                                <a href="data_pengguna.php?role=admin" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #007bff; color: white;">
                            <div class="card-body">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah SKCK</h6>
                                <h3><?php echo $jumlah_skck; ?></h3>
                                <a href="data_pengajuan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #28a745; color: white;">
                            <div class="card-body">
                                <i class="fas fa-id-card fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah SIK</h6>
                                <h3><?php echo $jumlah_sik; ?></h3>
                                <a href="data_pengajuan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center shadow" style="background-color: #ffc107; color: white;">
                            <div class="card-body">
                                <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                <h6 class="fw-bold">Jumlah STTP</h6>
                                <h3><?php echo $jumlah_sttp; ?></h3>
                                <a href="data_pengajuan.php" class="btn btn-light btn-sm mt-2">Detail</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white fw-bold">
                            <i class="fas fa-newspaper me-2"></i> Tambah Berita Baru
                        </div>
                        <div class="card-body">
                            <?php if ($berhasil_simpan): ?>
                                <div class="alert alert-success mt-3" role="alert">
                                    Berita berhasil disimpan!
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($gagal_simpan)): ?>
                                <div class="alert alert-danger mt-3" role="alert">
                                    <?php echo htmlspecialchars($gagal_simpan); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="judul_berita" class="form-label">Judul Berita</label>
                                    <input type="text" class="form-control" id="judul_berita" name="judul" required>
                                </div>
                                <div class="mb-3">
                                    <label for="isi_berita" class="form-label">Isi Berita</label>
                                    <textarea class="form-control" id="isi_berita" name="isi" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="gambar_berita" class="form-label">Gambar</label>
                                    <input type="file" class="form-control" id="gambar_berita" name="gambar">
                                    <small class="text-muted">Pilih gambar untuk berita (opsional).</small>
                                </div>
                                <button type="submit" class="btn btn-primary" name="simpan_berita">Simpan Berita</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <div class="card-title">Statistik Pelayanan Per Bulan</div>
                            </div>
                            <div class="card-body">
                                <canvas id="statistik-berkas" class="chartjs-chart" style="height: 300px;"></canvas>
                                <div class="mt-3 text-end">
                                    <a href="cetak_laporan_statistik.php" class="btn btn-success me-2" target="_blank">
                                        <i class="fas fa-print me-2"></i>
                                        Unduh PDF 
                                    </a>
                                    <button class="btn btn-primary btn-sm" onclick="downloadStatistik()">
                                        <i class="fas fa-download me-2"></i>
                                        Unduh CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card custom-card overflow-hidden">
                            <div class="card-header border-bottom-0">
                                <label class="card-title">Hasil Survey Kepuasan Masyarakat</label>
                                <span class="d-block fs-12 mb-0 text-muted">Data rata-rata hasil survei kepuasan masyarakat.</span>
                            </div>
                            <div class="card-body">
                                <canvas id="surveyChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Statistik Pelayanan
                        const statistikBerkasCanvas = document.getElementById('statistik-berkas');
                        if (statistikBerkasCanvas) {
                            const ctxStatistik = statistikBerkasCanvas.getContext('2d');
                            const allMonths = <?php echo json_encode($all_months); ?>; // Pastikan $all_months didefinisikan di PHP
                            const statistikSKCK = <?php echo json_encode($statistik_skck); ?>; // Pastikan $statistik_skck didefinisikan di PHP
                            const statistikSIK = <?php echo json_encode($statistik_sik); ?>; // Pastikan $statistik_sik didefinisikan di PHP
                            const statistikSTTP = <?php echo json_encode($statistik_sttp); ?>; // Pastikan $statistik_sttp didefinisikan di PHP

                            const skckData = allMonths.map(month => statistikSKCK[month] || 0);
                            const sikData = allMonths.map(month => statistikSIK[month] || 0);
                            const sttpData = allMonths.map(month => statistikSTTP[month] || 0);

                            new Chart(ctxStatistik, {
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
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: 'Jumlah'
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
                        }

                        // Hasil Survey
                        fetch('get_survey_data.php')
                            .then(response => response.json())
                            .then(data => {
                                const labels = data.labels;
                                const averageScores = data.averageScores;

                                const ctxSurvey = document.getElementById('surveyChart').getContext('2d');
                                const surveyChart = new Chart(ctxSurvey, {
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
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 4
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

                    function downloadStatistik() {
                        // ... (fungsi downloadStatistik Anda) ...
                    }

                    function printStatistik() {
                        // ... (fungsi printStatistik Anda) ...
                    }
                </script>

                



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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statistikBerkasCanvas = document.getElementById('statistik-berkas');
            if (statistikBerkasCanvas) {
                const ctx = statistikBerkasCanvas.getContext('2d');
                const allMonths = <?php echo json_encode($all_months); ?>;
                const statistikSKCK = <?php echo json_encode($statistik_skck); ?>;
                const statistikSIK = <?php echo json_encode($statistik_sik); ?>;
                const statistikSTTP = <?php echo json_encode($statistik_sttp); ?>;

                const skckData = allMonths.map(month => statistikSKCK[month] || 0);
                const sikData = allMonths.map(month => statistikSIK[month] || 0);
                const sttpData = allMonths.map(month => statistikSTTP[month] || 0);

                new Chart(ctx, {
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
            }
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
    <?php include 'script.php'; ?>
</body>

</html>