<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// --- PHP Logic for Laporan Intelijen Situasi Keamanan ---

// Fetch all kecamatan for filter dropdown
$query_kecamatan = "SELECT id_kecamatan, nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan ASC";
$result_kecamatan = mysqli_query($conn, $query_kecamatan);
$list_kecamatan = mysqli_fetch_all($result_kecamatan, MYSQLI_ASSOC);

// Build WHERE clause for criminal data based on filters
$where_kriminal_conditions = [];
$params = [];
$param_types = "";

if (isset($_GET['kecamatan_filter']) && $_GET['kecamatan_filter'] != 'semua') {
    $kecamatan_filter = $_GET['kecamatan_filter'];
    $where_kriminal_conditions[] = "kr.kecamatan_id = ?";
    $params[] = $kecamatan_filter;
    $param_types .= "i";
}

// ASUMSI: Tabel 'kriminal' memiliki kolom 'tanggal_kejadian' untuk filter bulan.
if (isset($_GET['bulan_filter']) && $_GET['bulan_filter'] != '') {
    $bulan_filter = $_GET['bulan_filter'];
    $tahun_sekarang = date('Y'); // Asumsi filter bulan untuk tahun ini
    $where_kriminal_conditions[] = "DATE_FORMAT(kr.tanggal_kejadian, '%Y-%m') = ?";
    $params[] = "$tahun_sekarang-$bulan_filter";
    $param_types .= "s";
}

$where_kriminal_clause = '';
if (!empty($where_kriminal_conditions)) {
    $where_kriminal_clause = "WHERE " . implode(" AND ", $where_kriminal_conditions);
}

// Query untuk menghitung jumlah kriminal per kecamatan
$sql_situasi_keamanan = "
    SELECT
        k.id_kecamatan,
        k.nama_kecamatan,
        COUNT(kr.id_kriminal) AS jumlah_kriminal
    FROM kecamatan k
    LEFT JOIN kriminal kr ON k.id_kecamatan = kr.kecamatan_id
    $where_kriminal_clause
    GROUP BY k.id_kecamatan, k.nama_kecamatan
    ORDER BY k.nama_kecamatan ASC
";

// Prepare and execute the statement if parameters exist
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql_situasi_keamanan);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result_situasi_keamanan = mysqli_stmt_get_result($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($conn));
        $result_situasi_keamanan = false;
    }
} else {
    $result_situasi_keamanan = mysqli_query($conn, $sql_situasi_keamanan);
}

$data_situasi_keamanan = [];

if ($result_situasi_keamanan) {
    while ($row = mysqli_fetch_assoc($result_situasi_keamanan)) {
        $jumlah_kriminal = (int)$row['jumlah_kriminal'];
        $status_keamanan = '';
        $warna_status = '';

        // Definisi threshold keamanan
        if ($jumlah_kriminal <= 5) {
            $status_keamanan = 'Aman';
            $warna_status = 'text-success'; // Hijau
        } elseif ($jumlah_kriminal <= 15) {
            $status_keamanan = 'Hati-hati';
            $warna_status = 'text-warning'; // Kuning
        } else {
            $status_keamanan = 'Merah';
            $warna_status = 'text-danger'; // Merah
        }

        $row['status_keamanan'] = $status_keamanan;
        $row['warna_status'] = $warna_status;

        // Filter berdasarkan situasi jika ada inputan
        $filter_situasi = isset($_GET['situasi_filter']) ? strtolower($_GET['situasi_filter']) : '';
        $status_filter_lolos = true;

        if ($filter_situasi === 'aman' && $status_keamanan !== 'Aman') {
            $status_filter_lolos = false;
        } elseif ($filter_situasi === 'hati-hati' && $status_keamanan !== 'Hati-hati') {
            $status_filter_lolos = false;
        } elseif ($filter_situasi === 'merah' && $status_keamanan !== 'Merah') {
            $status_filter_lolos = false;
        }

        if ($status_filter_lolos) {
            $data_situasi_keamanan[] = $row;
        }
    }
} else {
    error_log("Error fetching data: " . mysqli_error($conn));
}

// Function to create CSV for security intelligence data
function createCSVIntelijen($data, $filename = "laporan_intelijen_keamanan.csv")
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        // Remove 'warna_status' before writing to CSV
        $headers = array_keys($data[0]);
        $headers = array_diff($headers, ['warna_status']);
        fputcsv($output, $headers); // Header columns

        foreach ($data as $row) {
            unset($row['warna_status']); // Remove this column for CSV
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Laporan Situasi Keamanan - Sistem Pelayanan</title>
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
                        <h2 class="main-content-title fs-24 mb-1">Laporan Situasi Keamanan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Situasi Keamanan</li>
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
                                            <label for="kecamatan_filter" class="form-label fw-semibold mb-1">Kecamatan</label>
                                            <select class="form-select" id="kecamatan_filter" name="kecamatan_filter">
                                                <option value="semua">Semua Kecamatan</option>
                                                <?php foreach ($list_kecamatan as $kc) : ?>
                                                    <option value="<?php echo $kc['id_kecamatan']; ?>" <?php if (isset($_GET['kecamatan_filter']) && $_GET['kecamatan_filter'] == $kc['id_kecamatan']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($kc['nama_kecamatan']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="situasi_filter" class="form-label fw-semibold mb-1">Filter Situasi</label>
                                            <select class="form-select shadow-sm" id="situasi_filter" name="situasi_filter">
                                                <option value="">Semua Situasi</option>
                                                <option value="aman" <?php if (isset($_GET['situasi_filter']) && $_GET['situasi_filter'] == 'aman') echo 'selected'; ?>>Aman</option>
                                                <option value="hati-hati" <?php if (isset($_GET['situasi_filter']) && $_GET['situasi_filter'] == 'hati-hati') echo 'selected'; ?>>Hati-hati</option>
                                                <option value="merah" <?php if (isset($_GET['situasi_filter']) && $_GET['situasi_filter'] == 'merah') echo 'selected'; ?>>Merah</option>
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
                                    document.getElementById('kecamatan_filter').addEventListener('change', function() {
                                        document.getElementById('filterForm').submit();
                                    });
                                    document.getElementById('situasi_filter').addEventListener('change', function() {
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
                                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Situasi Keamanan Per Kecamatan</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelIntelijen">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Kecamatan</th>
                                            <th>Jumlah Insiden Kriminal</th>
                                            <th>Status Keamanan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_situasi_keamanan)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_situasi_keamanan as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_kecamatan']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['jumlah_kriminal']); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge <?php echo $row['warna_status']; ?>">
                                                            <?php echo htmlspecialchars($row['status_keamanan']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data situasi keamanan.</td>
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
