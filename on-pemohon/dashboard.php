<?php
session_start(); // Tambahkan ini di baris paling atas

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Dashboard - <?php echo $_SESSION['nama']; ?></title>
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
                    <img src="../assets/images/brand-logos/logopanjang.png" class="desktop-dark" alt="logo">
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
                        <h2 class="main-content-title fs-24 mb-1">Dashboard Pemohon</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::row-1 -->
                <div class="row row-sm">
                    <!-- Kolom Kiri: Halo User, Persyaratan, Hasil Survey -->
                    <div class="col-lg-8">
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
                                            </div>
                                            <div class="col-sm-8">
                                                <h4 class="fw-bold text-dark">Halo <a href="#" class="text-primary"><?php echo $_SESSION['nama']; ?></a></h4>
                                                <p class="text-secondary mb-0">Selamat datang! Kami siap membantu Anda dalam pelayanan Administrasi Sat Intelkam...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Persyaratan -->
                        <div class="row row-sm">
                            <div class="col-md-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <label class="main-content-label fs-13 fw-bold mb-2">Persyaratan SKCK</label>
                                        <ul>
                                            <li>Fotokopi KTP (e-KTP atau SIM) yang masih berlaku dan bawa aslinya untuk verifikasi</li>
                                            <li>Fotokopi Kartu Keluarga (KK)</li>
                                            <li>Fotokopi akta kelahiran, ijazah, surat nikah, atau surat kenal lahir</li>
                                            <li>Pas foto berwarna ukuran 4x6 cm (4–6 lembar), latar merah, berpakaian sopan/formal</li>
                                            <li>Bukti kepesertaan BPJS.............</li>
                                            <li><a href="#" id="detail-skck">Lihat Detail Persyaratan</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="modal fade" id="modal-detail-skck" tabindex="-1" aria-labelledby="modalDetailSKCKLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalDetailSKCKLabel">Detail Persyaratan SKCK</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul>
                                                    <li>Fotokopi KTP (e-KTP atau SIM) yang masih berlaku dan bawa aslinya untuk verifikasi</li>
                                                    <li>Fotokopi Kartu Keluarga (KK)</li>
                                                    <li>Fotokopi akta kelahiran, ijazah, surat nikah, atau surat kenal lahir</li>
                                                    <li>Pas foto berwarna ukuran 4x6 cm (4–6 lembar), latar merah, berpakaian sopan/formal</li>
                                                    <li>Bukti kepesertaan BPJS Kesehatan/JKN aktif (wajib sejak Agustus 2024)</li>
                                                    <li>Mengisi formulir permohonan SKCK (tersedia di kantor polisi atau via aplikasi)</li>
                                                    <li>Surat pengantar dari kelurahan/desa (untuk pengurusan offline)</li>
                                                    <li>Pengambilan sidik jari di Polsek/Polres</li>
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const detailSkckLink = document.getElementById('detail-skck');
                                        const modalDetailSkck = new bootstrap.Modal(document.getElementById('modal-detail-skck'));

                                        detailSkckLink.addEventListener('click', function(event) {
                                            event.preventDefault();
                                            modalDetailSkck.show();
                                        });
                                    });
                                </script>
                            </div>
                            <div class="col-md-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <label class="main-content-label fs-13 fw-bold mb-2">Persyaratan SIK</label>
                                        <ul>
                                            <li>Surat permohonan izin keramaian ditujukan kepada Kapolres setempat</li>
                                            <li>Fotokopi KTP pemohon/panitia penyelenggara</li>
                                            <li>Surat rekomendasi dari kelurahan atau desa setempat</li>
                                            <li>Surat izin dari pemilik tempat kegiatan (jika menggunakan fasilitas umum atau pribadi)</li>
                                            <li>Daftar susunan panitia.................................</li>
                                            <li> <a href="#" id="detail-sik">Lihat Detail Persyaratan</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modal-detail-sik" tabindex="-1" aria-labelledby="modalDetailSIKLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalDetailSIKLabel">Detail Persyaratan SIK</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul>
                                                <li>Surat permohonan izin keramaian ditujukan kepada Kapolres setempat</li>
                                                <li>Fotokopi KTP pemohon/panitia penyelenggara</li>
                                                <li>Surat rekomendasi dari kelurahan atau desa setempat</li>
                                                <li>Surat izin dari pemilik tempat kegiatan (jika menggunakan fasilitas umum atau pribadi)</li>
                                                <li>Daftar susunan panitia penyelenggara acara</li>
                                                <li>Jadwal dan susunan acara secara lengkap</li>
                                                <li>Surat pernyataan bertanggung jawab atas keamanan dan ketertiban acara</li>
                                                <li>Surat pengantar dari Polsek setempat</li>
                                                <li>Fotokopi surat pemberitahuan ke Koramil/Babinsa (jika diperlukan)</li>
                                                <li>Proposal kegiatan (untuk acara besar atau melibatkan massa banyak)</li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const detailSikLink = document.getElementById('detail-sik');
                                    const modalDetailSik = new bootstrap.Modal(document.getElementById('modal-detail-sik'));

                                    detailSikLink.addEventListener('click', function(event) {
                                        event.preventDefault();
                                        modalDetailSik.show();
                                    });
                                });
                            </script>
                            <div class="col-md-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <label class="main-content-label fs-13 fw-bold mb-2">Persyaratan STTP</label>
                                        <ul>
                                            <li>Surat Pemberitahuan Kampanye dari paslon/tim kampanye, memuat: jadwal (sesuai KPU), nama paslon, penanggung jawab, bentuk, waktu & lokasi kampanye......</li>
                                            <li>Fotokopi surat penunjukan resmi tim kampanye</li>
                                            <li>Surat izin/persetujuan lokasi kampanye dari pemilik bangunan atau ..............</li>
                                            <li><a href="#" id="detail-sttp">Lihat Detail Persyaratan</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modal-detail-sttp" tabindex="-1" aria-labelledby="modalDetailSTTPLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalDetailSTTPLabel">Detail Persyaratan STTP</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul>
                                                <li>Surat Pemberitahuan Kampanye dari paslon/tim kampanye, memuat: jadwal (sesuai KPU), nama paslon, penanggung jawab, bentuk, waktu & lokasi kampanye, daftar juru kampanye, susunan acara, jumlah peserta & kendaraan, titik kumpul, rute berangkat & pulang</li>
                                                <li>Fotokopi surat penunjukan resmi tim kampanye</li>
                                                <li>Surat izin/persetujuan lokasi kampanye dari pemilik bangunan atau pemerintah daerah jika di fasilitas umum</li>
                                                <li>Proposal acara dan daftar susunan panitia penyelenggara</li>
                                                <li>Surat pernyataan tertulis dari penyelenggara bahwa acara tidak melanggar norma agama, kesusilaan, kesopanan, dan peraturan perundang-undangan</li>
                                                <li>Surat permohonan tertulis yang ditujukan kepada pejabat Polri berwenang (Kapolres/Polda/Kapolri) sesuai skala dan lokasi kegiatan</li>
                                                <li>Pengajuan paling lambat:</li>
                                                <ul>
                                                    <li>7 hari kerja sebelum kampanye (skala lokal di tingkat Polres)</li>
                                                    <li>21 hari kerja (skala nasional di tingkat Polda)</li>
                                                    <li>30 hari kerja (skala internasional di tingkat Kapolri)</li>
                                                </ul>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const detailSttpLink = document.getElementById('detail-sttp');
                                    const modalDetailSttp = new bootstrap.Modal(document.getElementById('modal-detail-sttp'));

                                    detailSttpLink.addEventListener('click', function(event) {
                                        event.preventDefault();
                                        modalDetailSttp.show();
                                    });
                                });
                            </script>
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

                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

                    <?php
                    // Pastikan koneksi ke database sudah ada di sini (include 'koneksi.php';)
                    include '../include/koneksi.php';
                    $queryBerita = "SELECT * FROM berita ORDER BY id_berita DESC LIMIT 1"; // Ambil berita terbaru
                    $resultBerita = mysqli_query($conn, $queryBerita);

                    if (mysqli_num_rows($resultBerita) > 0) {
                        $berita = mysqli_fetch_assoc($resultBerita);
                    ?>
                        <div class="col-lg-4">
                            <div class="card custom-card">
                                <div class="card-body" style="display: flex; flex-direction: column;">
                                    <div class="card-item-title mb-2">
                                        <label class="main-content-label fs-13 fw-bold mb-1">Berita Terkini</label>
                                    </div>
                                    <?php if (!empty($berita['gambar'])): ?>
                                        <div class="card-item-body">
                                            <img src="../assets/images/berita/<?php echo $berita['gambar']; ?>" alt="berita-img" class="img-fluid rounded mb-2">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-item-body">
                                        <p class="text-dark fw-bold"><?php echo $berita['judul']; ?></p>
                                    </div>
                                    <div class="card-item-body">
                                        <p class="text-secondary mb-2"><?php
                                                                        $isi_berita = $berita['isi'];
                                                                        if (strlen($isi_berita) > 2000) {
                                                                            echo substr($isi_berita, 0, 100) . "...";
                                                                        } else {
                                                                            echo $isi_berita;
                                                                        }
                                                                        ?></p>
                                    </div>
                                    <?php
                                    // Jika ada kolom tanggal di tabel berita, Anda bisa menampilkannya di sini
                                    // if (!empty($berita['tanggal'])): 
                                    ?>
                                    <?php // endif; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    } else {
                        // Tampilkan pesan jika tidak ada berita
                        echo '<div class="col-lg-4"><div class="card custom-card"><div class="card-body"><p class="text-muted">Belum ada berita terkini.</p></div></div></div>';
                    }
                    ?>
                </div>
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