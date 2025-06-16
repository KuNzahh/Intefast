<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

$where = "WHERE 1=1";
if (isset($_GET['role_filter']) && $_GET['role_filter'] != 'semua') {
    $role_filter = mysqli_real_escape_string($conn, $_GET['role_filter']);
    $where .= " AND role = '$role_filter'";
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (nama_pemohon LIKE '%$search%' OR detail LIKE '%$search%')";
}

$sql_pengguna = "SELECT id_user, nama, email, username, role FROM users $where ORDER BY nama ASC";
$result_pengguna = mysqli_query($conn, $sql_pengguna);
$data_pengguna = mysqli_fetch_all($result_pengguna, MYSQLI_ASSOC);

// Fungsi untuk membuat data CSV laporan pengguna
function createCSVPengguna($data, $filename = "laporan_pengguna.csv")
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

$data_tampil = $data_pengguna;
$data_unduh = $data_pengguna;

if (isset($_GET['aksi']) && $_GET['aksi'] == 'unduh_csv_pengguna') {
    createCSVPengguna($data_unduh);
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Laporan Data Pengguna - Sistem Pelayanan</title>
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
                <?php include 'logo.php'; ?>
            </div>
            <?php include 'sidebar.php'; ?>
        </aside>
        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb mb-4">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Laporan Data Pengguna</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Data Pengguna</li>
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
                                            <label for="jenis_filter" class="form-label fw-semibold mb-1">Role Filter</label>
                                            <select class="form-select" id="role_filter" name="role_filter">
                                                <option value="semua">Semua Role</option>
                                                <option value="admin" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == 'admin') echo 'selected'; ?>>Admin</option>
                                                <option value="petugas" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == 'petugas') echo 'selected'; ?>>Petugas</option>
                                                <option value="pemohon" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == 'pemohon') echo 'selected'; ?>>Pemohon</option>
                                                <option value="pimpinan" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == 'pimpinan') echo 'selected'; ?>>Pimpinan</option>
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
                                    document.getElementById('role_filter').addEventListener('change', function() {
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
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Pengguna</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelLaporanPengguna">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data_tampil)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_tampil as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td class="text-capitalize text-center"><?php echo htmlspecialchars($row['role']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data pengguna.</td>
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

    <script>
        $(document).ready(function() {
            $('#tabelLaporanPengguna').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                "order": [
                    [1, "asc"]
                ] // Urutkan berdasarkan nama
            });
        });

        function printDataPengguna() {
            const printContentElement = document.getElementById('tabelLaporanPengguna');
            const printContent = printContentElement.outerHTML;
            const roleFilterElement = document.getElementById('role_filter');
            const roleFilterValue = roleFilterElement.value;
            let judulLaporan = "Laporan Data Pengguna";

            if (roleFilterValue !== 'semua') {
                judulLaporan += " Role: " + roleFilterValue.toUpperCase();
            }

            const printWindowContent = `
                <html>
                <head>
                    <title>Cetak Data Pengguna</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        h2 { text-align: center; margin-bottom: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid black; padding: 5px; text-align: left; }
                        th { text-align: center; }
                        .ttd-container { margin-top: 50px; display: flex; justify-content: space-between; }
                        .ttd-column { text-align: center; }
                        .mt-5 { margin-top: 1.5rem !important; }
                    </style>
                </head>
                <body>
                    <h2>${judulLaporan}</h2>
                    ${printContent}
                    <div class="ttd-container mt-5">
                        <div class="ttd-column">
                            <p>Mengetahui,</p>
                            <p class="mt-5">.........................</p>
                            <p>(Nama Atasan)</p>
                        </div>
                        <div class="ttd-column">
                            <p>Petugas,</p>
                            <p class="mt-5">.........................</p>
                            <p>(Nama Petugas)</p>
                        </div>
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