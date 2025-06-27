<?php
session_start();

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = isset($_POST['id_kampanye']) ? mysqli_real_escape_string($conn, $_POST['id_kampanye']) : '';
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_kampanye']);
    $deskripsi    = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if ($id) {
        // Update data
        $query = "UPDATE kampanye SET nama_kampanye='$nama', deskripsi='$deskripsi' ";
        $query .= " WHERE id_kampanye='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Kampanye berhasil diperbarui!' : 'Gagal memperbarui Kampanye.';
    } else {
        // Tambah data baru
        $query = "INSERT INTO kampanye (nama_kampanye, deskripsi) VALUES ('$nama', '$deskripsi')";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Kampanye berhasil ditambahkan!' : 'Gagal menambahkan Kampanye.';
    }

    header('Location: data_kampanye.php');
    exit();
}


if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM kampanye WHERE id_kampanye='$id'");
    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Kampanye berhasil dihapus!' : 'Gagal menghapus Kampanye.';
    header('Location: data_kampanye.php');
    exit();
}
?>

<script>
    function editKampanye(id) {
        const row = document.querySelector(`#row-${id}`);
        const nama = row.querySelector('.data-nama').textContent;
        const deskripsi = row.querySelector('.data-deskripsi').textContent;

        document.getElementById('id_kampanye').value = id;
        document.getElementById('nama_kampanye').value = nama;
        document.getElementById('deskripsi').value = deskripsi;
    }

    function hapusKampanye(id) {
        // Buat modal konfirmasi jika belum ada
        let modal = document.getElementById('modalHapusKampanye');
        if (!modal) {
            modal = document.createElement('div');
            modal.innerHTML = `
                <div class="modal fade" id="modalHapusKampanye" tabindex="-1" aria-labelledby="modalHapusKampanyeLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalHapusKampanyeLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Apakah Anda yakin ingin menghapus data Kampanye ini?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusKampanye">Hapus</button>
                      </div>
                    </div>
                  </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Tampilkan modal
        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusKampanye'));
        bsModal.show();

        // Set event tombol hapus
        document.getElementById('btnKonfirmasiHapusKampanye').onclick = function() {
            window.location = `?hapus=${id}`;
        };
    }

    // Filter table by bulan (if needed)
    function filterBulan() {
        const filter = document.getElementById("filterBulan").value.toLowerCase();
        const rows = document.querySelectorAll("#tabelPengguna tbody tr");

        rows.forEach(row => {
            // Ganti sesuai kolom bulan jika ada, jika tidak, sembunyikan filter
            // row.cells[?] -> sesuaikan index jika ada kolom bulan
            row.style.display = ""; // Tidak ada kolom bulan, tampilkan semua
        });
    }
</script>



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
                    <img src="../assets/images/brand-logos/logopanjangbiru.jpg" class="desktop-dark" alt="logo">
                </a>
            </div>
            <!-- End::main-sidebar-header -->
            <!-- Start::main-sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- End::main-sidebar -->
        </aside>
        <!-- End::app-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb mb-4">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Data Sistem</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Kampanye</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::Form Tambah Pengguna -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Tambah Data Kampanye</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_kampanye" class="form-label">Jenis Kampanye</label>
                                            <input type="text" class="form-control" id="nama_kampanye" name="nama_kampanye" placeholder="Masukkan Kampanye">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="deskripsi" class="form-label">Deskripsi</label>
                                            <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="Masukkan Deskripsi">
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_kampanye" name="id_kampanye" value=""> <!-- Hidden input untuk id_kampanye -->
                                    <button type="submit" class="btn btn-success w-100 mt-3">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Form Tambah Pengguna -->

                <!-- Start::Tabel Data Pengguna -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Daftar Kampanye</h5>
                                <div class="mt-2 mt-md-0" style="min-width: 180px;">
                                    <select class="form-select" id="filterBulan" onchange="filterBulan()">
                                        <option value="">Semua Bulan</option>
                                        <option value="Januari">Januari</option>
                                        <option value="Februari">Februari</option>
                                        <option value="Maret">Maret</option>
                                        <option value="April">April</option>
                                        <option value="Mei">Mei</option>
                                        <option value="Juni">Juni</option>
                                        <option value="Juli">Juli</option>
                                        <option value="Agustus">Agustus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPengguna">
                                    <!-- Modal Notifikasi -->
                                    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
                                        <div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header <?= isset($_SESSION['error']) ? 'bg-danger' : 'bg-success'; ?>">
                                                        <h5 class="modal-title text-white" id="notifModalLabel">
                                                            <?= isset($_SESSION['error']) ? 'Gagal!' : 'Berhasil!'; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?= isset($_SESSION['error']) ? $_SESSION['error'] : $_SESSION['success']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            // Tampilkan modal setelah halaman dimuat
                                            document.addEventListener('DOMContentLoaded', function() {
                                                var notifModal = new bootstrap.Modal(document.getElementById('notifModal'));
                                                notifModal.show();
                                            });
                                        </script>
                                        <?php unset($_SESSION['error'], $_SESSION['success']); ?>
                                    <?php endif; ?>
                                <thead class="table-primary text-center align-middle">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Nama Kampanye</th>
                                            <th>Deskripsi</th>
                                            <th style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Pagination setup
                                        $perPage = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        if ($page < 1) $page = 1;
                                        $start = ($page - 1) * $perPage;

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kampanye");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query = mysqli_query($conn, "SELECT * FROM kampanye ORDER BY id_kampanye DESC LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_kampanye']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama_kampanye']); ?></td>
                                                <td class="data-deskripsi"><?= htmlspecialchars($data['deskripsi']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editKampanye(<?= $data['id_kampanye']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusKampanye(<?= $data['id_kampanye']; ?>)" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <!-- Pagination Preview -->
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap">
                                    <div>
                                        <small>
                                            Menampilkan 
                                            <b><?= min($start + 1, $totalRows); ?></b>
                                            -
                                            <b><?= min($start + $perPage, $totalRows); ?></b>
                                            dari <b><?= $totalRows; ?></b> kampanye
                                        </small>
                                    </div>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item<?= ($page <= 1) ? ' disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?= $page - 1; ?>" tabindex="-1">&laquo;</a>
                                            </li>
                                            <?php
                                            for ($i = 1; $i <= $totalPages; $i++) {
                                                if ($i == $page || ($i <= 2 || $i > $totalPages - 2 || abs($i - $page) <= 1)) {
                                                    // Show first 2, last 2, and 1 around current
                                            ?>
                                                <li class="page-item<?= ($i == $page) ? ' active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                                </li>
                                            <?php
                                                } elseif ($i == 3 && $page > 4) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                } elseif ($i == $totalPages - 2 && $page < $totalPages - 3) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }
                                            ?>
                                            <li class="page-item<?= ($page >= $totalPages) ? ' disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?= $page + 1; ?>">&raquo;</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Tabel Data Pengguna -->
            </div>
        </div>
        <!-- End::app-content -->


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