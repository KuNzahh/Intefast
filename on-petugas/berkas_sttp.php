<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

// Proses upload berkas online STTP dari modal
if (isset($_POST['id_sttp_upload'])) {
    $id_sttp_upload_modal = intval($_POST['id_sttp_upload']);

    if ($id_sttp_upload_modal > 0 && isset($_FILES['berkas_online_sttp']) && $_FILES['berkas_online_sttp']['error'] === 0) {
        $allowed_types = ['application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['berkas_online_sttp']['type'], $allowed_types) && $_FILES['berkas_online_sttp']['size'] <= $max_size) {
            $file_name = 'berkas_online_sttp_' . $id_sttp_upload_modal . '_' . date('YmdHis') . '.pdf';
            $upload_dir = '../uploads/berkasOnline/';
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['berkas_online_sttp']['tmp_name'], $target_file)) {
                $update_query = "UPDATE sttp SET berkasOnline = ? WHERE id_sttp = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "si", $file_name, $id_sttp_upload_modal);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>alert('Berkas online STTP berhasil diunggah.'); window.location.href='berkas_sttp.php';</script>";
                } else {
                    echo "<script>alert('Gagal menyimpan nama berkas STTP ke database.'); window.location.href='berkas_sttp.php';</script>";
                    unlink($target_file);
                }
                mysqli_stmt_close($stmt);
                exit();
            } else {
                echo "<script>alert('Gagal mengunggah berkas STTP ke server.'); window.location.href='berkas_sttp.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Jenis file STTP tidak valid atau ukuran file melebihi batas (2MB). Hanya file PDF yang diperbolehkan.'); window.location.href='berkas_sttp.php';</script>";
            exit();
        }
    } else if (isset($_POST['id_sttp_upload'])) {
        echo "<script>alert('Tidak ada file STTP yang diunggah atau terjadi kesalahan.'); window.location.href='berkas_sttp.php';</script>";
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
            /* warna teks utama di input */
        }

        .form-control::placeholder {
            color: #999;
            /* warna placeholder */
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
                <a href="index.html" class="header-logo">
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
                            <li class="breadcrumb-item active" aria-current="page">Proses Berkas / STTP</li>
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
                                        <input class="form-control" type="search" placeholder="Search..." aria-label="Search" id="searchInput">
                                    </form>
                                </div>
                            </div>

                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPengguna">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Nama Paslon</th>
                                            <th>Bentuk Kampanye </th>
                                            <th>Proses</th>
                                            <th>Status</th>
                                            <th>Cetak dan Kirim Bukti Pengambilan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include '../include/koneksi.php'; // koneksi ke database

                                        $query_sttp = "
                                            SELECT sttp.*, users.username, sttp.tanggal_pengajuan, sttp.nama_paslon, sttp.kampanye_id, sttp.progres, sttp.berkasOnline
                                            FROM sttp
                                            LEFT JOIN users ON sttp.user_id = users.id_user
                                            ORDER BY sttp.tanggal_pengajuan DESC
                                        ";
                                        $result_sttp = mysqli_query($conn, $query_sttp);

                                        $no = 1;
                                        while ($row_sttp = mysqli_fetch_assoc($result_sttp)) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row_sttp['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row_sttp['nama_paslon']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($row_sttp['kampanye_id']); ?></td>
                                                <td class="text-center">
                                                    <a href="detail_sttp.php?id=<?php echo $row_sttp['id_sttp']; ?>" class="btn btn-sm btn-primary">
                                                        <i>Cek detail</i>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo htmlspecialchars($row_sttp['progres']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="cetak_doksttp.php?id=<?php echo $row_sttp['id_sttp']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-print"></i> Cetak Dokumen
                                                    </a>
                                                    <?php
                                                    if (isset($row_sttp['berkasOnline']) && !empty($row_sttp['berkasOnline'])) {
                                                        echo '<a href="../uploads/berkasOnline/' . htmlspecialchars($row_sttp['berkasOnline']) . '" class="btn btn-sm btn-info mt-1" target="_blank">';
                                                        echo '<i class="fas fa-eye"></i> Lihat Berkas Online';
                                                        echo '</a>';
                                                    } else {
                                                        echo '<button type="button" class="btn btn-sm btn-success mt-1 btn-upload-berkas-sttp" data-bs-toggle="modal" data-bs-target="#uploadBerkasModal" data-id="' . $row_sttp['id_sttp'] . '">';
                                                        echo '<i class="fas fa-upload"></i> Kirim Berkas Online';
                                                        echo '</button>';
                                                    }
                                                    ?>
                                                    <a href="kirim_email_sttp.php?user_id=<?php echo $row_sttp['user_id']; ?>" class="btn btn-sm btn-info mt-1">
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
                                        $total_rows_sttp = mysqli_num_rows($result_sttp);
                                        echo "Showing 1 to " . $total_rows_sttp . " of " . $total_rows_sttp . " entries";
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
                        <h5 class="modal-title" id="uploadBerkasModalLabel">Unggah Berkas Online STTP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_sttp_upload" id="id_sttp_upload">
                            <div class="mb-3">
                                <label for="berkas_online_sttp" class="form-label">Pilih Berkas (PDF, Max 2MB):</label>
                                <input type="file" class="form-control" id="berkas_online_sttp" name="berkas_online_sttp" accept="application/pdf">
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
                const uploadBerkasButtonsSTTP = document.querySelectorAll('.btn-upload-berkas-sttp');
                const idSttpUploadInput = document.getElementById('id_sttp_upload');

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

                uploadBerkasButtonsSTTP.forEach(button => {
                    button.addEventListener('click', function() {
                        const idSttp = this.dataset.id;
                        idSttpUploadInput.value = idSttp;
                    });
                });
            });
        </script>

</body>

</html>