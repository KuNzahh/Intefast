<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// Ambil semua data permohonan dengan filter
$where = "WHERE 1=1";
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (nama_pemohon LIKE '%$search%' OR detail LIKE '%$search%')";
}
if (isset($_GET['jenis_filter']) && $_GET['jenis_filter'] == 'semua') {
    $where .= ""; // Tidak ada filter jenis
} elseif (isset($_GET['jenis_filter']) && $_GET['jenis_filter'] != '') {
    $jenis_filter = mysqli_real_escape_string($conn, $_GET['jenis_filter']);
    $where .= " AND jenis = '$jenis_filter'";
}
if (isset($_GET['bulan_filter']) && $_GET['bulan_filter'] != '') {
    $bulan_filter = $_GET['bulan_filter'];
    $tahun_sekarang = date('Y'); // Asumsi filter bulan untuk tahun ini
    $where .= " AND DATE_FORMAT(tanggal_pengajuan, '%Y-%m') = '$tahun_sekarang-$bulan_filter'";
}

$sql_all = "SELECT nama_pemohon, tanggal_pengajuan, jenis, detail, progres FROM (
    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SKCK' AS jenis, s.keperluan AS detail, s.progres
    FROM skck s JOIN users u ON s.user_id = u.id_user
    UNION ALL
    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SIK' AS jenis, CONCAT('Instansi: ', s.nama_instansi, '<br>Penanggung Jawab: ', s.penanggung_jawab) AS detail, s.progres
    FROM sik s JOIN users u ON s.user_id = u.id_user
    UNION ALL
    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'STTP' AS jenis, CONCAT('Paslon: ', s.nama_paslon, '<br>Kampanye: ', s.kampanye_id) AS detail, s.progres
    FROM sttp s JOIN users u ON s.user_id = u.id_user
) AS semua $where ORDER BY tanggal_pengajuan DESC";

$result_all = mysqli_query($conn, $sql_all);
$semua_data = mysqli_fetch_all($result_all, MYSQLI_ASSOC);

// Ambil data personil dari session user_id
$ttd_nama = $ttd_jabatan = $ttd_nrp = "";
if (isset($_SESSION['user_id'])) {
    $id_user = $_SESSION['user_id'];
    $sql_personil = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE user_id = $id_user";
    $result_personil = mysqli_query($conn, $sql_personil);
    if ($row = mysqli_fetch_assoc($result_personil)) {
        $ttd_nama = $row['nama'];
        $ttd_jabatan = $row['jabatan'];
        $ttd_nrp = $row['nrp'];
    }
}

// Fungsi untuk membuat data CSV laporan permohonan
function createCSVPermohonan($data, $filename = "laporan_permohonan.csv")
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

$data_tampil = $semua_data;
$data_unduh = $semua_data;

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_permohonan') {
    createCSVPermohonan($data_unduh);
}

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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Laporan Data Permohonan - Sistem Pelayanan</title>
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
                        <h2 class="main-content-title fs-24 mb-1">Laporan Data Permohonan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Data Permohonan</li>
                        </ol>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Pencarian Otomatis</h5>
                            </div>
                            <div class="card-body">
                                <form id="filterForm" method="GET" autocomplete="off">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label for="jenis_filter" class="form-label fw-semibold mb-1">Jenis Permohonan</label>
                                            <select class="form-select shadow-sm" id="jenis_filter" name="jenis_filter">
                                                <option value="semua">Semua Permohonan</option>
                                                <option value="SKCK" <?php if (isset($_GET['jenis_filter']) && $_GET['jenis_filter'] == 'SKCK') echo 'selected'; ?>>SKCK</option>
                                                <option value="SIK" <?php if (isset($_GET['jenis_filter']) && $_GET['jenis_filter'] == 'SIK') echo 'selected'; ?>>SIK</option>
                                                <option value="STTP" <?php if (isset($_GET['jenis_filter']) && $_GET['jenis_filter'] == 'STTP') echo 'selected'; ?>>STTP</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bulan_filter" class="form-label fw-semibold mb-1">Filter Bulan</label>
                                            <select class="form-select shadow-sm" id="bulan_filter" name="bulan_filter">
                                                <option value="">Semua Bulan</option>
                                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                                    <option value="<?php echo sprintf("%02d", $i); ?>" <?php if (isset($_GET['bulan_filter']) && $_GET['bulan_filter'] == sprintf("%02d", $i)) echo 'selected'; ?>>
                                                        <?php echo $nama_bulan[$i]; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-6 mt-3">
                                            <label for="search" class="form-label fw-semibold mb-1">Cari Nama/Detail</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="search" name="search" placeholder="Cari..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <script>
                                    // Submit form otomatis saat filter berubah
                                    document.getElementById('jenis_filter').addEventListener('change', function() {
                                        document.getElementById('filterForm').submit();
                                    });
                                    document.getElementById('bulan_filter').addEventListener('change', function() {
                                        document.getElementById('filterForm').submit();
                                    });
                                    document.getElementById('search').addEventListener('input', function() {
                                        // Debounce pencarian agar tidak submit terlalu sering
                                        clearTimeout(window.searchTimeout);
                                        window.searchTimeout = setTimeout(function() {
                                            document.getElementById('filterForm').submit();
                                        }, 500);
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Data Permohonan</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelLaporan">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pemohon</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Detail</th>
                                            <th>Progres</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_tampil)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_tampil as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                                    <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jenis']); ?></td>
                                                    <td><?php echo $row['detail']; ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars(ucfirst($row['progres'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada data permohonan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <footer class="footer mt-auto py-3 bg-white text-center">
            <div class="container">
                <?php include 'foot.php'; ?>
            </div>
        </footer>
    </div>
    <?php include 'script.php'; ?>

</body>

</html>