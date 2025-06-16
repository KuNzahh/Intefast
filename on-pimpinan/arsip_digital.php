<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
// Query untuk mengambil data arsip
$query = "SELECT * FROM arsip ORDER BY tanggal_upload DESC";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Arsip Digital- Sistem Pelayanan</title>
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
                        <h2 class="main-content-title fs-24 mb-1">Arsip Digital</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Arsip Digital</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Arsip Digital</h5>
                            </div>
                            <div class="card-body">
                                <form action="proses_tambah_arsip.php" method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="judul_arsip" class="form-label">Judul Arsip</label>
                                        <input type="text" class="form-control" id="judul_arsip" name="judul_arsip" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tanggal_upload" class="form-label">Tanggal Upload</label>
                                        <input type="date" class="form-control" id="tanggal_upload" name="tanggal_upload" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="berkas" class="form-label">Upload Berkas</label>
                                        <input type="file" class="form-control" id="berkas" name="berkas" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                                        <small class="text-muted">Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Data Arsip</h5>
                            </div>

                            <div class="card-body table-responsive">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" id="searchArsip" class="form-control" placeholder="Cari judul arsip atau tanggal...">
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('#searchArsip').on('keyup', function() {
                                            var value = $(this).val().toLowerCase();
                                            $('table tbody tr').filter(function() {
                                                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                                            });
                                        });
                                    });
                                </script>
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Judul Arsip</th>
                                            <th>Tanggal Upload</th>
                                            <th>Berkas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Eksekusi query dan ambil data arsip
                                        $data_arsip = [];
                                        $result = mysqli_query($conn, $query);
                                        if ($result) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $data_arsip[] = $row;
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($data_arsip)): ?>
                                            <?php $no = 1;
                                            foreach ($data_arsip as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['judul_arsip']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($row['tanggal_upload']); ?></td>
                                                    <td class="text-center">
                                                        <a href="<?php echo htmlspecialchars('../'. $row['berkas']); ?>" class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data arsip.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php // endwhile; 
                                        ?>
                                    </tbody>
                                </table>
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
</body>

</html>