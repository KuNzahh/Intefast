<?php
session_start();
include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = isset($_POST['id_kecamatan']) ? mysqli_real_escape_string($conn, $_POST['id_kecamatan']) : '';
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_kecamatan']);

    if ($id) {
        // Update data
        $query = "UPDATE kecamatan SET nama_kecamatan='$nama' ";
        $query .= " WHERE id_kecamatan='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Kecamatan berhasil diperbarui!' : 'Gagal memperbarui kecamatan.';
    } else {
        // Tambah data baru
        $query = "INSERT INTO kecamatan (nama_kecamatan) VALUES ('$nama')";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Kecamatan berhasil ditambahkan!' : 'Gagal menambahkan kecamatan.';
    }

    header('Location: data_kecamatan.php');
    exit();
}


if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM kecamatan WHERE id_kecamatan='$id'");
    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Kecamatan berhasil dihapus!' : 'Gagal menghapus kecamatan.';
    header('Location: data_kecamatan.php');
    exit();
}
?>

<script>
    function editKecamatan(id) {
        const row = document.querySelector(`#row-${id}`);
        const nama = row.querySelector('.data-nama').textContent;

        document.getElementById('id_kecamatan').value = id; // Set kecamatan di hidden field
        document.getElementById('nama_kecamatan').value = nama;
    }


    function hapusKecamatan(id) {
        if (confirm('Yakin ingin menghapus Kecamatan ini?')) {
            window.location = `?hapus=${id}`;
        }
    }

    // Filter table by role
    function filterTable() {
        const filter = document.getElementById("filterBulan").value.toLowerCase();
        const rows = document.querySelectorAll("#tabelKecamatan tbody tr");

        rows.forEach(row => {
            const role = row.cells[4].textContent.toLowerCase(); // Correct column index for role
            row.style.display = (filter === "" || role === filter) ? "" : "none";
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
                            <li class="breadcrumb-item active" aria-current="page">Data Kecamatan</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::Form Tambah Pengguna -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Tambah Kecamatan</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_kecamatan" class="form-label">Nama Kecamatan</label>
                                            <input type="text" class="form-control" id="nama_kecamatan" name="nama_kecamatan" placeholder="Masukkan nama kecamatan">
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_kecamatan" name="id_kecamatan" value=""> <!-- Hidden input untuk id_user -->
                                    <button type="submit" class="btn btn-success w-100 mt-3">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Form Tambah Pengguna -->

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Daftar Kecamatan</h5>
                                <div style="min-width: 200px;">
                                    <input type="text" id="cariInputKecamatan" class="form-control form-control-sm" placeholder="Cari Kecamatan..." onkeyup="cariDataKecamatan()">
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelKecamatan">
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
                                            <th>Nama Kecamatan</th>
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

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kecamatan");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query = mysqli_query($conn, "SELECT * FROM kecamatan ORDER BY id_kecamatan DESC LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_kecamatan']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama_kecamatan']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editKecamatan(<?= $data['id_kecamatan']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusKecamatan(<?= $data['id_kecamatan']; ?>)" title="Hapus">
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
                                            dari <b><?= $totalRows; ?></b> kecamatan
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
                <script>
                    function editKecamatan(id) {
                        const row = document.querySelector(`#row-${id}`);
                        const nama = row.querySelector('.data-nama').textContent;

                        document.getElementById('id_kecamatan').value = id;
                        document.getElementById('nama_kecamatan').value = nama; // Pastikan ada input dengan id 'nama_kecamatan' di form edit Anda
                    }

                    function hapusKecamatan(id) {
                        // Buat modal konfirmasi jika belum ada
                        let modal = document.getElementById('modalHapusKecamatan');
                        if (!modal) {
                            modal = document.createElement('div');
                            modal.innerHTML = `
                                <div class="modal fade" id="modalHapusKecamatan" tabindex="-1" aria-labelledby="modalHapusKecamatanLabel" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header bg-danger">
                                        <h5 class="modal-title text-white" id="modalHapusKecamatanLabel">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus kecamatan ini?
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusKecamatan">Hapus</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            `;
                            document.body.appendChild(modal);
                        }

                        // Tampilkan modal
                        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusKecamatan'));
                        bsModal.show();

                        // Set event tombol hapus
                        document.getElementById('btnKonfirmasiHapusKecamatan').onclick = function() {
                            window.location = `?hapus=${id}`;
                        };
                    }

                    function cariDataKecamatan() {
                        const input = document.getElementById("cariInputKecamatan");
                        const filter = input.value.toLowerCase();
                        const table = document.getElementById("tabelKecamatan");
                        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

                        for (let i = 0; i < rows.length; i++) {
                            const namaKecamatan = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
                            if (namaKecamatan.includes(filter)) {
                                rows[i].style.display = "";
                            } else {
                                rows[i].style.display = "none";
                            }
                        }
                    }
                </script>
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