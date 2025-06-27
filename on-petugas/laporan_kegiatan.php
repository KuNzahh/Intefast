<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// --- PHP Logic for Laporan Data Kegiatan Masyarakat ---

// Build WHERE clause based on filters
$where_conditions = [];
$params = [];
$param_types = "";

if (isset($_GET['jenis_kegiatan_filter']) && $_GET['jenis_kegiatan_filter'] != 'semua') {
    $jenis_kegiatan_filter = $_GET['jenis_kegiatan_filter'];
    $where_conditions[] = "jenis_kegiatan_source = ?";
    $params[] = $jenis_kegiatan_filter;
    $param_types .= "s";
}
// Removed jenis_keramaian_filter and kecamatan_filter from PHP logic
// as the columns are not directly available in SIK/STTP based on the error.

if (isset($_GET['bulan_filter']) && $_GET['bulan_filter'] != '') {
    $bulan_filter = $_GET['bulan_filter'];
    $tahun_sekarang = date('Y'); // Asumsi filter bulan untuk tahun ini
    $where_conditions[] = "DATE_FORMAT(tanggal_kegiatan_utama, '%Y-%m') = ?";
    $params[] = "$tahun_sekarang-$bulan_filter";
    $param_types .= "s";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Main query to get activity data from SIK and STTP
// Using 'tgl_kegiatan' for SIK and 'tgl_kampanye' for STTP
// Removed direct references to id_kecamatan and id_keramaian from SELECT list
$sql_kegiatan = "
    SELECT
        'SIK' AS jenis_kegiatan_source,
        s.nama_instansi AS nama_kegiatan,
        s.rangka AS deskripsi_kegiatan,
        s.tgl_kegiatan AS tanggal_kegiatan_utama -- Using tgl_kegiatan from SIK
    FROM sik s
    UNION ALL
    SELECT
        'STTP' AS jenis_kegiatan_source,
        (SELECT k.nama_kampanye FROM kampanye k WHERE k.id_kampanye = st.kampanye_id) AS nama_kegiatan,
        st.nama_paslon AS deskripsi_kegiatan,
        st.tgl_kampanye AS tanggal_kegiatan_utama -- Using tgl_kampanye from STTP
    FROM sttp st
";

// Subquery for filtering without joins to kecamatan/jeniskeramaian
$final_sql = "
    SELECT
        keg.jenis_kegiatan_source,
        keg.nama_kegiatan,
        keg.deskripsi_kegiatan,
        keg.tanggal_kegiatan_utama
    FROM ($sql_kegiatan) AS keg
    $where_clause
    ORDER BY tanggal_kegiatan_utama DESC
";

// Prepare and execute the statement if parameters exist
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $final_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result_kegiatan = mysqli_stmt_get_result($stmt);
    } else {
        // Handle error if statement preparation fails
        error_log("Error preparing statement: " . mysqli_error($conn));
        $result_kegiatan = false; // Indicate failure
    }
} else {
    $result_kegiatan = mysqli_query($conn, $final_sql);
}

$data_kegiatan = [];
if ($result_kegiatan) {
    $data_kegiatan = mysqli_fetch_all($result_kegiatan, MYSQLI_ASSOC);
} else {
    // Log or display an error if query failed
    error_log("Error fetching data: " . mysqli_error($conn));
}


// Function to create CSV for activity data
function createCSVKegiatan($data, $filename = "laporan_kegiatan_masyarakat.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0])); // Header columns
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}

// Handle CSV download action
if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_kegiatan') {
    createCSVKegiatan($data_kegiatan);
}

// Indonesian month names for dropdown and report title
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
    <title>Laporan Data Kegiatan Masyarakat - Sistem Pelayanan</title>
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
                        <h2 class="main-content-title fs-24 mb-1">Laporan Data Kegiatan Masyarakat</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Data Kegiatan Masyarakat</li>
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
                                            <label for="jenis_kegiatan_filter" class="form-label fw-semibold mb-1">Jenis Permohonan</label>
                                            <select select class="form-select" id="jenis_kegiatan_filter" name="jenis_kegiatan_filter">
                                                <option value="semua">Semua Permohonan</option>
                                                <option value="SIK" <?php if (isset($_GET['jenis_kegiatan_filter']) && $_GET['jenis_kegiatan_filter'] == 'SIK') echo 'selected'; ?>>SIK (Surat Izin Keramaian)</option>
                                                <option value="STTP" <?php if (isset($_GET['jenis_kegiatan_filter']) && $_GET['jenis_kegiatan_filter'] == 'STTP') echo 'selected'; ?>>STTP (Surat Tanda Terima Pemberitahuan)</option>
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
                                    document.getElementById('jenis_kegiatan_filter').addEventListener('change', function() {
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
                                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Data Kegiatan Masyarakat</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelKegiatan">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Sumber Kegiatan</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Deskripsi Kegiatan</th>
                                            <th>Tanggal Kegiatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_kegiatan)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_kegiatan as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['jenis_kegiatan_source']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['deskripsi_kegiatan']); ?></td>
                                                    <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tanggal_kegiatan_utama'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data kegiatan masyarakat.</td>
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