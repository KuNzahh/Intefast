<?php
session_start(); // Pastikan ini ada di baris paling atas
include '../include/koneksi.php';

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

// Proses update status bukti SIK (tetap ada)
if (isset($_GET['id_update_bukti_sik']) && isset($_GET['status'])) {
    // ... (kode Anda untuk update status bukti) ...
}

// Proses upload berkas online dari modal
if (isset($_POST['id_sik_upload'])) {
    $id_sik_upload_modal = intval($_POST['id_sik_upload']);

    if ($id_sik_upload_modal > 0 && isset($_FILES['berkas_online']) && $_FILES['berkas_online']['error'] === 0) {
        $allowed_types = ['application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['berkas_online']['type'], $allowed_types) && $_FILES['berkas_online']['size'] <= $max_size) {
            $file_name = 'berkas_online_sik_' . $id_sik_upload_modal . '_' . date('YmdHis') . '.pdf';
            $upload_dir = '../uploads/berkasOnline/';
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['berkas_online']['tmp_name'], $target_file)) {
                $update_query = "UPDATE sik SET berkasOnline = ? WHERE id_sik = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "si", $file_name, $id_sik_upload_modal);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>alert('Berkas online berhasil diunggah.'); window.location.href='berkas_sik.php';</script>";
                } else {
                    echo "<script>alert('Gagal menyimpan nama berkas ke database.'); window.location.href='berkas_sik.php';</script>";
                    unlink($target_file);
                }
                mysqli_stmt_close($stmt);
                exit();
            } else {
                echo "<script>alert('Gagal mengunggah berkas ke server.'); window.location.href='berkas_sik.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Jenis file tidak valid atau ukuran file melebihi batas (2MB). Hanya file PDF yang diperbolehkan.'); window.location.href='berkas_sik.php';</script>";
            exit();
        }
    } else if (isset($_POST['id_sik_upload'])) {
        echo "<script>alert('Tidak ada file yang diunggah atau terjadi kesalahan.'); window.location.href='berkas_sik.php';</script>";
        exit();
    }
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
                            <li class="breadcrumb-item active" aria-current="page">Proses Berkas / SIK</li>
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
                                            <th>Nama Instansi</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Proses</th>
                                            <th>Status</th>
                                            <th>Cetak dan Kirim Bukti Pengambilan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include '../include/koneksi.php'; // koneksi ke database

                                        $query_sik = "
                                            SELECT sik.*, users.username, sik.tanggal_pengajuan
                                            FROM sik
                                            LEFT JOIN users ON sik.user_id = users.id_user
                                            ORDER BY sik.tanggal_pengajuan DESC
                                        ";
                                        $result_sik = mysqli_query($conn, $query_sik);

                                        $no = 1;
                                        while ($row_sik = mysqli_fetch_assoc($result_sik)) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row_sik['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row_sik['nama_instansi']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($row_sik['tanggal_pengajuan']); ?></td>
                                                <td class="text-center">
                                                    <a href="detail_sik.php?id=<?php echo $row_sik['id_sik']; ?>" class="btn btn-sm btn-primary">
                                                        <i>Cek detail</i>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo htmlspecialchars($row_sik['progres']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="cetak_doksik.php?id=<?php echo $row_sik['id_sik']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-print"></i> Cetak Dokumen
                                                    </a>
                                                    <?php
                                                    if (isset($row_sik['berkasOnline']) && !empty($row_sik['berkasOnline'])) {
                                                        echo '<a href="../uploads/berkasOnline/' . htmlspecialchars($row_sik['berkasOnline']) . '" class="btn btn-sm btn-info mt-1" target="_blank">';
                                                        echo '<i class="fas fa-eye"></i> Lihat Berkas Online';
                                                        echo '</a>';
                                                    } else {
                                                        echo '<button type="button" class="btn btn-sm btn-success mt-1 btn-upload-berkas" data-bs-toggle="modal" data-bs-target="#uploadBerkasModal" data-id="' . $row_sik['id_sik'] . '">';
                                                        echo '<i class="fas fa-upload"></i> Kirim Berkas Online';
                                                        echo '</button>';
                                                    }
                                                    ?>
                                                    <a href="kirim_email_sik.php?user_id=<?php echo $row_sik['user_id']; ?>" class="btn btn-sm btn-info mt-1">
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
                                        $total_rows_sik = mysqli_num_rows($result_sik);
                                        echo "Showing 1 to " . $total_rows_sik . " of " . $total_rows_sik . " entries";
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

        <div class="modal fade" id="uploadBerkasModal" tabindex="-1" aria-labelledby="uploadBerkasModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadBerkasModalLabel">Unggah Berkas Online</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_sik_upload" id="id_sik_upload">
                            <div class="mb-3">
                                <label for="berkas_online" class="form-label">Pilih Berkas (PDF, Max 2MB):</label>
                                <input type="file" class="form-control" id="berkas_online" name="berkas_online" accept="application/pdf">
                            </div>
                            <button type="submit" class="btn btn-primary">Unggah</button>
                        </form>
                        <div id="upload_response" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'script.php'; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const table = document.getElementById('tabelPengguna');
                const tbody = table.getElementsByTagName('tbody')[0];
                const rows = tbody.getElementsByTagName('tr');
                const uploadBerkasButtons = document.querySelectorAll('.btn-upload-berkas');
                const idSikUploadInput = document.getElementById('id_sik_upload');

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

                uploadBerkasButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const idSik = this.dataset.id;
                        idSikUploadInput.value = idSik;
                    });
                });

                const uploadForm = document.querySelector('#uploadBerkasModal form');
                if (uploadForm) {
                    uploadForm.addEventListener('submit', function(e) {
                        // Tidak perlu preventDefault lagi karena kita ingin submit form biasa
                    });
                }
            });
        </script>

</body>

</html>