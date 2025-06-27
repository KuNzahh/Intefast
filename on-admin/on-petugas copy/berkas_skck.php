<?php
session_start(); // Pastikan ini ada di baris paling atas
include '../include/koneksi.php';

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

// Proses update status bukti
if (isset($_GET['id_update_bukti']) && isset($_GET['status'])) {
    $id_skck_update = $_GET['id_update_bukti'];
    $status_bukti_update = $_GET['status'];

    if ($status_bukti_update === 'bisa_diambil') {
        $update_query = "UPDATE skck SET bukti = 'bisa diambil' WHERE id_skck = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $id_skck_update);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Status bukti pengambilan SKCK berhasil diperbarui menjadi Bisa Diambil.'); window.location.href='berkas_skck.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat memperbarui status bukti.'); window.location.href='berkas_skck.php';</script>";
        }
        mysqli_stmt_close($stmt);
        exit();
    }
    // Anda bisa menambahkan kondisi 'belum bisa diambil' di sini jika diperlukan
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <style>
        .form-control {
            color: #000;
        }

        .form-control::placeholder {
            color: #999;
        }
    </style>

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
                        <h2 class="main-content-title fs-24 mb-1">Proses Berkas</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Proses Berkas / SKCK</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-2 mb-md-0"><i class="fas fa-edit me-2"></i>Berkas Pemohon Terbaru</h5>
                                <div class="mt-5 mt-md-0" style="min-width: 180px;">
                                    <form class="d-flex ms-auto w-100 w-md-auto mt-2 mt-md-0" role="search">
                                        <div class="input-group">
                                            <input class="form-control" type="search" placeholder="Cari..." aria-label="Search" id="searchInput">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPengguna">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Nama Lengkap</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Proses</th>
                                            <th>Status</th>
                                            <th>Cetak dan Kirim Bukti Pengambilan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include '../include/koneksi.php'; // koneksi ke database

                                        $query = "
                                            SELECT skck.*, users.username, users.nama AS nama_pemohon, skck.tanggal_pengajuan
                                            FROM skck
                                            LEFT JOIN users ON skck.user_id = users.id_user
                                            ORDER BY skck.tanggal_pengajuan DESC
                                        ";
                                        $result = mysqli_query($conn, $query);

                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($row['tanggal_pengajuan']); ?></td>
                                                <td class="text-center">
                                                    <a href="detail_skck.php?id=<?php echo $row['id_skck']; ?>" class="btn btn-sm btn-primary">
                                                        <i>Cek detail</i>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo htmlspecialchars($row['progres']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="cetak_dokskck.php?id=<?php echo $row['id_skck']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-print"></i> Cetak Dokumen
                                                    </a>
                                                    <?php if ($row['bukti'] != 'bisa diambil'): ?>
                                                        <a href="?id_update_bukti=<?php echo $row['id_skck']; ?>&status=bisa_diambil" class="btn btn-sm btn-success mt-1">
                                                            <i class="fas fa-paper-plane"></i> Tandai Bisa Diambil
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Sudah Bisa Diambil</span>
                                                    <?php endif; ?>
                                                    <a href="kirim_email_skck.php?user_id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-info mt-1">
                                                        <i class="fas fa-envelope"></i> Kirim Notifikasi Email
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                                    <div class="text-muted small">
                                        <?php
                                        // Hitung total data
                                        $total_rows = mysqli_num_rows($result);
                                        echo "Showing 1 to " . $total_rows . " of " . $total_rows . " entries";
                                        ?>
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
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
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const table = document.getElementById('tabelPengguna');
                const tbody = table.getElementsByTagName('tbody')[0];
                const rows = tbody.getElementsByTagName('tr');

                searchInput.addEventListener('keyup', function() {
                    const searchTerm = searchInput.value.toLowerCase();

                    for (let i = 0; i < rows.length; i++) {
                        const rowData = rows[i].textContent.toLowerCase();
                        if (rowData.includes(searchTerm)) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                });
            });
        </script>

</body>

</html>