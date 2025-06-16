<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// Daftar nama bulan dalam bahasa Indonesia
$nama_bulan = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];

$filter_jenis = isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : 'semua';
$filter_bulan = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : '';
$tahun_sekarang = date('Y');
$where_clause = "WHERE 1=1"; // Kondisi awal selalu true

if ($filter_jenis != 'semua') {
    $where_clause .= " AND jenis = '$filter_jenis'";
}
if (!empty($filter_bulan)) {
    $where_clause .= " AND DATE_FORMAT(tanggal_pengajuan, '%Y-%m') = '$tahun_sekarang-$filter_bulan'";
}

// Query untuk mengambil semua data permohonan dengan filter
$sql_all_ttd = "SELECT nama_pemohon, tanggal_pengajuan, jenis, detail, id
                FROM (
                    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SKCK' AS jenis, s.keperluan AS detail, s.id_skck AS id
                    FROM skck s JOIN users u ON s.user_id = u.id_user
                    UNION ALL
                    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SIK' AS jenis, CONCAT('Instansi: ', s.nama_instansi, '<br>Penanggung Jawab: ', s.penanggung_jawab) AS detail, s.id_sik AS id
                    FROM sik s JOIN users u ON s.user_id = u.id_user
                    UNION ALL
                    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'STTP' AS jenis, CONCAT('Paslon: ', s.nama_paslon, '<br>Kampanye: ', s.nama_kampanye) AS detail, s.id_sttp AS id
                    FROM sttp s JOIN users u ON s.user_id = u.id_user
                ) AS semua $where_clause ORDER BY tanggal_pengajuan DESC";

$result_all_ttd = mysqli_query($conn, $sql_all_ttd);
$data_permohonan_ttd = mysqli_fetch_all($result_all_ttd, MYSQLI_ASSOC);

// Query untuk mengambil data kinerja petugas
$sql_kinerja = "SELECT kp.*, u.nama AS nama_petugas
                FROM kinerja_petugas kp
                JOIN users u ON kp.id_user = u.id_user
                WHERE u.role = 'petugas'
                ORDER BY kp.tanggal_penilaian DESC";
$result_kinerja = mysqli_query($conn, $sql_kinerja);
$data_kinerja = mysqli_fetch_all($result_kinerja, MYSQLI_ASSOC);

// Fungsi untuk membuat data CSV laporan kinerja petugas
function createCSVKinerjaPetugas($data, $filename = "laporan_kinerja_petugas.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_kinerja') {
    createCSVKinerjaPetugas($data_kinerja);
}

// Query untuk mengambil data progres pengajuan
$sql_progres_pengajuan = "SELECT nama_pemohon, tanggal_update, jenis, progres
                           FROM (
                               SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan AS tanggal_update, 'SKCK' AS jenis, s.progres
                               FROM skck s JOIN users u ON s.user_id = u.id_user
                               UNION ALL
                               SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan AS tanggal_update, 'SIK' AS jenis, s.progres
                               FROM sik s JOIN users u ON s.user_id = u.id_user
                               UNION ALL
                               SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan AS tanggal_update, 'STTP' AS jenis, s.progres
                               FROM sttp s JOIN users u ON s.user_id = u.id_user
                           ) AS semua_progres
                           ORDER BY tanggal_update DESC";
$result_progres = mysqli_query($conn, $sql_progres_pengajuan);
$data_progres = mysqli_fetch_all($result_progres, MYSQLI_ASSOC);

// Fungsi untuk membuat data CSV laporan progres pengajuan
function createCSVProgresPengajuan($data, $filename = "laporan_progres_pengajuan.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_progres') {
    createCSVProgresPengajuan($data_progres);
}

// Fungsi untuk membuat data CSV laporan permohonan
function createCSVPermohonanTTD($data, $filename = "laporan_permohonan.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_permohonan_ttd') {
    createCSVPermohonanTTD($data_permohonan_ttd);
}

// Query untuk mengambil data Personil Satintelkam
$sql_personil = "SELECT nama, jabatan, nrp FROM personil_satintel ORDER BY nama ASC";
$result_personil = mysqli_query($conn, $sql_personil);
$data_personil = mysqli_fetch_all($result_personil, MYSQLI_ASSOC);

// Fungsi untuk membuat data CSV Personil Satintelkam
function createCSVPersonil($data, $filename = "laporan_personil_satintelkam.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_personil') {
    createCSVPersonil($data_personil);
}


//Query untuk mengambil data Kegiatan Masyarakat
$sql_kegiatan_masyarakat = "SELECT nama_pemohon, tanggal_kegiatan, jenis, tempat, dasar
                           FROM (
                               SELECT u.nama AS nama_pemohon, s.tgl_kegiatan AS tanggal_kegiatan, 'SIK' AS jenis, s.tempat, s.dasar as dasar
                               FROM sik s JOIN users u ON s.user_id = u.id_user
                               UNION ALL
                               SELECT u.nama AS nama_pemohon, s.tgl_kampanye AS tanggal_kegiatan, 'STTP' AS jenis, s.tempat, s.memperhatikan as dasar
                               FROM sttp s JOIN users u ON s.user_id = u.id_user
                           ) AS semua_progres
                           ORDER BY tanggal_kegiatan DESC";
$result_kegiatan = mysqli_query($conn, $sql_kegiatan_masyarakat);
$data_kegiatan = mysqli_fetch_all($result_kegiatan, MYSQLI_ASSOC);

// Fungsi untuk membuat data CSV laporan kegiatan Masyarakat
function createCSVKegiatanMasyarakat($data, $filename = "laporan_kegiatan_masyarakat.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_kegiatan_masyarakat') {
    createCSVProgresPengajuan($data_kegiatan);
}

// Query untuk mengambil data dari tabel kriminal dengan nama kecamatan dan situasi keamanan
$sql_intelijen_keamanan = "SELECT
                                kec.nama_kecamatan,
                                COUNT(k.id_kriminal) AS jumlah_kasus,
                                CASE
                                    WHEN COUNT(k.id_kriminal) > 10 THEN 'Tinggi'
                                    WHEN COUNT(k.id_kriminal) > 5 THEN 'Sedang'
                                    ELSE 'Rendah'
                                END AS situasi_keamanan
                            FROM kriminal k
                            JOIN kecamatan kec ON k.kecamatan_id = kec.id_kecamatan
                            GROUP BY kec.nama_kecamatan
                            ORDER BY kec.nama_kecamatan;
";

$result_intel = mysqli_query($conn, $sql_intelijen_keamanan);
$data_intel = mysqli_fetch_all($result_intel, MYSQLI_ASSOC);


// Fungsi untuk membuat data CSV Intelijen Keamanan
function createCSVKIntelijenKeamanan($data, $filename = "laporan_intelijen_keamanan.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_intelijen_keamanan') {
    createCSVKIntelijenKeamanan($data_intel);
}

// Query untuk mengambil data berita dengan waktu ubah
$sql_notifikasi = "SELECT
                                    b.id_berita,
                                    b.judul,
                                    b.isi,
                                    b.waktu_ubah
                                FROM berita b
";

$result_notif = mysqli_query($conn, $sql_notifikasi);
$data_notif = mysqli_fetch_all($result_notif, MYSQLI_ASSOC);


// Fungsi untuk membuat data CSV Notifikasi dan Peringatan Dini
function createCSVnotif($data, $filename = "laporan_notif.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_notif') {
    createCSVnotif($data_notif);
}

// Query untuk mengambil data user role petugas
$sql_petugas = "SELECT
                    u.id_user,
                    u.nama,
                    u.email
                    FROM users u
                    WHERE u.role = 'petugas'
                    ORDER BY u.nama ASC"; // Menambahkan ORDER BY agar data terurut
$result_datpetugas = mysqli_query($conn, $sql_petugas);
$data_datpetugas = mysqli_fetch_all($result_datpetugas, MYSQLI_ASSOC);


// Fungsi untuk membuat data CSV Data Petugas
function createCSVDatpetugas($data, $filename = "laporan_Data_petugas.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header kolom
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_DatPetugas') {
    createCSVDatpetugas($data_datpetugas);
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>TTD Digital - Sistem Pelayanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb mb-4">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Laporan </h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Pimpinan</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Pilih Laporan</h5>
                            </div>
                            <div class="card-body d-flex flex-wrap gap-2">
                                <button class="btn btn-primary mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanPermohonanTTD" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #007bff; border-color: #007bff;">
                                    <i class="fas fa-file-alt me-2"></i>Laporan Data Permohonan
                                </button>
                                <button class="btn btn-success mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanDataStatusPengajuan" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #28a745; border-color: #28a745;">
                                    <i class="fas fa-tasks me-2"></i>Laporan Data Status Pengajuan
                                </button>
                                <button class="btn btn-info mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanKinerjaPetugas" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #17a2b8; border-color: #17a2b8;">
                                    <i class="fas fa-chart-bar me-2"></i>Laporan Kinerja Petugas
                                </button>
                                <button class="btn btn-warning mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanDataKegiatanMasyarakat" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #ffc107; border-color: #ffc107; color: #212529;">
                                    <i class="fas fa-users me-2"></i>Laporan Data Kegiatan Masyarakat
                                </button>
                                <button class="btn btn-danger mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanIntelijenSituasiKeamanan" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #dc3545; border-color: #dc3545;">
                                    <i class="fas fa-shield-alt me-2"></i>Laporan Intelijen Situasi Keamanan
                                </button>
                                <button class="btn btn-secondary mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanNotifiksasidanPeringatan" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #6c757d; border-color: #6c757d;">
                                    <i class="fas fa-bell me-2"></i>Laporan Berita Pemberitahuan dan Peringatan Dini
                                </button>
                                <button class="btn btn-outline-primary mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanPersonilSatintelkam" role="button" aria-expanded="false" aria-controls="laporanPersonilSatintelkam" style="background-color: #4e73df; border-color: #4e73df; color: #fff;">
                                    <i class="fas fa-user-shield me-2"></i>Laporan Personil Satintelkam
                                </button>
                                <button class="btn btn-dark mb-2 flex-grow-1" data-bs-toggle="collapse" href="#laporanDataPetugas" role="button" aria-expanded="false" aria-controls="laporanPermohonanTTD" style="background-color: #343a40; border-color: #343a40;">
                                    <i class="fas fa-user-cog me-2"></i>Laporan Data Petugas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="collapse mt-4 " id="laporanPermohonanTTD">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2" style="color: #007bff;"></i>Data Permohonan</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="mb-3" id="filterForm">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_jenis" class="form-label">Jenis Permohonan:</label>
                                        <select class="form-select" id="filter_jenis" name="filter_jenis" onchange="this.form.submit()">
                                            <option value="semua">Semua Permohonan</option>
                                            <option value="SKCK" <?php if ($filter_jenis == 'SKCK') echo 'selected'; ?>>SKCK</option>
                                            <option value="SIK" <?php if ($filter_jenis == 'SIK') echo 'selected'; ?>>SIK</option>
                                            <option value="STTP" <?php if ($filter_jenis == 'STTP') echo 'selected'; ?>>STTP</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_bulan" class="form-label">Filter Bulan:</label>
                                        <select class="form-select" id="filter_bulan" name="filter_bulan" onchange="this.form.submit()">
                                            <option value="">Semua Bulan</option>
                                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                                <option value="<?php echo sprintf("%02d", $i); ?>" <?php if ($filter_bulan == sprintf("%02d", $i)) echo 'selected'; ?>>
                                                    <?php echo $nama_bulan[$i]; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="ttd_digital.php#laporanPermohonanTTD" class="btn btn-secondary"><i class="fas fa-undo me-2"></i>Reset Filter</a>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPermohonanTTD">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_permohonan_ttd)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_permohonan_ttd as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jenis']); ?></td>
                                                    <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                    <td><?php echo $row['detail']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data permohonan sesuai filter.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <form method="GET" action="cetak_laporanQR_permohonan.php" target="_blank">
                                        <input type="hidden" name="filter_jenis" value="<?php echo htmlspecialchars(isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : 'semua'); ?>">
                                        <input type="hidden" name="filter_bulan" value="<?php echo htmlspecialchars(isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : ''); ?>">
                                        <button type="submit" class="btn btn-primary btn-sm me-2">
                                            <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                        </button>
                                    </form>
                                    <form method="GET">
                                        <input type="hidden" name="aksi" value="unduh_csv_permohonan_ttd">
                                        <input type="hidden" name="filter_jenis" value="<?php echo htmlspecialchars(isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : 'semua'); ?>">
                                        <input type="hidden" name="filter_bulan" value="<?php echo htmlspecialchars(isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : ''); ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>



                <div class="collapse mt-4" id="laporanDataStatusPengajuan">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2" style="color: #28a745;"></i>Data Status Pengajuan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelProgresPengajuan">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pemohon</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Tanggal Update</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_progres)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_progres as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jenis']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tanggal_update']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['progres']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data progres pengajuan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_progres.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_progres">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse mt-4" id="laporanKinerjaPetugas">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Laporan Kinerja Petugas</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelKinerjaPetugas">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Petugas</th>
                                            <th>Tanggal Penilaian</th>
                                            <th>Kedisiplinan</th>
                                            <th>Tanggung Jawab</th>
                                            <th>Kerjasama</th>
                                            <th>Inisiatif</th>
                                            <th>Kualitas Kerja</th>
                                            <th>Catatan</th>
                                            <th>Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_kinerja)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_kinerja as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tanggal_penilaian']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['kedisiplinan']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tanggung_jawab']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['kerjasama']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['inisiatif']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['kualitas_kerja']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['catatan']); ?></td>
                                                    <td class="text-center">
                                                        <?php
                                                        $nilai_sb = 0;
                                                        if ($row['kedisiplinan'] == 'Sangat Baik') $nilai_sb++;
                                                        if ($row['tanggung_jawab'] == 'Sangat Baik') $nilai_sb++;
                                                        if ($row['kerjasama'] == 'Sangat Baik') $nilai_sb++;
                                                        if ($row['inisiatif'] == 'Sangat Baik') $nilai_sb++;
                                                        if ($row['kualitas_kerja'] == 'Sangat Baik') $nilai_sb++;

                                                        if ($nilai_sb == 5) {
                                                            echo 'Petugas Terbaik (5 Kriteria)';
                                                        } elseif ($nilai_sb == 4) {
                                                            echo 'Petugas Terbaik (4 Kriteria)';
                                                        } elseif ($nilai_sb == 3) {
                                                            echo 'Petugas Terbaik (3 Kriteria)';
                                                        } elseif ($nilai_sb == 2) {
                                                            echo 'Petugas Terbaik (2 Kriteria)';
                                                        } else {
                                                            echo 'Baik'; // Atau nilai lain sesuai keinginan Anda
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada data kinerja petugas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_kinerja.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_kinerja">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse mt-4" id="laporanDataKegiatanMasyarakat">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Laporan Data Kegiatan Masyarakat</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelLaporanKegiatanMasyarakat">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pemohon</th>
                                            <th>Tanggal Kegiatan</th>
                                            <th>Jenis</th>
                                            <th>Tempat</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_kegiatan)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_kegiatan as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tanggal_kegiatan']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jenis']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tempat']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['dasar']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada data Kegiatan Masyarakat.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_kegiatan.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_kegiatan_masyarakat">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse mt-4" id="laporanIntelijenSituasiKeamanan">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Laporan Intelijen Situasi Keamanan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelLaporanIntelijenKeamanan">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama_kecamatan</th>
                                            <th>Jumlah Kasus</th>
                                            <th>Situasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_intel)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_intel as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['nama_kecamatan']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jumlah_kasus']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['situasi_keamanan']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data Intelijen Keamanan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_keamanan.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_keamanan_masyarakat">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse mt-4" id="laporanNotifiksasidanPeringatan">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Laporan Berita Pemberitahuan dan Peringatan Dini</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabellaporanNotifiksasidanPeringatan">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Judul Berita</th>
                                            <th>Isi Berita</th>
                                            <th>Waktu Posting</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_notif)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_notif as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['isi']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['waktu_ubah']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data berita.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_berita.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_kegiatan_masyarakat">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse mt-4" id="laporanPersonilSatintelkam">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Laporan Personil Satintelkam</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelLaporanPersonil">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>NRP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_personil)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_personil as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['nrp']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data personil.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_personil.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_personil">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="collapse mt-4" id="laporanDataPetugas">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Laporan laporan Data Petugas</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabellaporanDataPetugas">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_datpetugas)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_datpetugas as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data petugas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <form method="GET" action="cetak_laporanQR_petugas.php" target="_blank">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-file-pdf me-2"></i>TTD Digital dan Unduh PDF
                                    </button>
                                </form>
                                <form method="GET">
                                    <input type="hidden" name="aksi" value="unduh_csv_kegiatan_masyarakat">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-2"></i>Unduh CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>




                <script>
                    $(document).ready(function() {
                        $('#tabelKinerjaPetugas').DataTable({
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                            },
                            "order": [
                                [2, "desc"]
                            ] // Urutkan berdasarkan tanggal penilaian
                        });
                    });
                </script>
            </div>

        </div>
        <footer class="footer mt-auto py-3 bg-white text-center">
            <div class="container">
                <?php include 'foot.php'; ?>
            </div>
        </footer>
        <?php include 'script.php'; ?>

        <script>
            $(document).ready(function() {
                $('#tabelPermohonanTTD').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                    },
                    "order": [
                        [2, "desc"]
                    ] // Urutkan berdasarkan tanggal
                });
            });

            function printDataPermohonanTTD() {
                const printContentElement = document.getElementById('tabelPermohonanTTD');
                const printContent = printContentElement.outerHTML;
                let judulLaporan = "Laporan Data Permohonan";

                const jenisFilterElement = document.getElementById('filter_jenis');
                const jenisFilterValue = jenisFilterElement.value;
                const bulanFilterElement = document.getElementById('filter_bulan');
                const bulanFilterValue = bulanFilterElement.value;
                const namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const tahunSekarang = new Date().getFullYear();

                let filterText = [];
                if (jenisFilterValue !== 'semua') {
                    filterText.push(`Jenis: ${jenisFilterValue}`);
                }
                if (bulanFilterValue !== '') {
                    filterText.push(`Bulan: ${namaBulan[parseInt(bulanFilterValue)]}`);
                }
                if (filterText.length > 0) {
                    judulLaporan += " (" + filterText.join(", ") + ")";
                } else {
                    judulLaporan += " Tahun " + tahunSekarang;
                }

                const printWindowContent = `
                <html>
                <head>
                    <title>${judulLaporan}</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        h2 { text-align: center; margin-bottom: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid black; padding: 5px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .text-center { text-align: center; }
                        .ttd-container { position: fixed; right: 50px; bottom: 50px; text-align: center; }
                        .mt-5 { margin-top: 1.5rem !important; }
                    </style>
                </head>
                <body>
                    <h2>${judulLaporan}</h2>
                    ${printContent}
                    <div class="ttd-container">
                        <p>Pimpinan,</p>
                        <p class="mt-5">.........................</p>
                        <p>(Nama Pimpinan)</p>
                    </div>
                </body>
                </html>
            `;

                const printWindow = window.open('', '_blank');
                printWindow.document.write(printWindowContent);
                printWindow.document.close();
                printWindow.print();
            }
        </script>

</body>

</html>