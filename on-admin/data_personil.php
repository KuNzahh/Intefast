<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// Ambil data user dengan role petugas
$user_petugas = [];
$user_query = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE role = 'petugas'");
while ($u = mysqli_fetch_assoc($user_query)) {
    $user_petugas[] = $u;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = isset($_POST['id_personil']) ? mysqli_real_escape_string($conn, $_POST['id_personil']) : '';
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan  = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $nrp      = mysqli_real_escape_string($conn, $_POST['nrp']);
    $user_id  = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? mysqli_real_escape_string($conn, $_POST['user_id']) : 'NULL';

    if ($id) {
        $query = "UPDATE personil_satintel SET nama='$nama', jabatan='$jabatan', nrp='$nrp', user_id=" . ($user_id === 'NULL' ? "NULL" : "'$user_id'") . " WHERE id_personil='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Personil berhasil diperbarui!' : 'Gagal memperbarui Personil.';
    } else {
        $query = "INSERT INTO personil_satintel (nama, jabatan, nrp, user_id) VALUES ('$nama', '$jabatan', '$nrp', " . ($user_id === 'NULL' ? "NULL" : "'$user_id'") . ")";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Personil berhasil ditambahkan!' : 'Gagal menambahkan Personil.';
    }

    header('Location: data_personil.php');
    exit();
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM personil_satintel WHERE id_personil='$id'");
    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Personil berhasil dihapus!' : 'Gagal menghapus Personil.';
    header('Location: data_personil.php');
    exit();
}
?>
<script>
    function editPersonil(id) {
        const row = document.querySelector(`#row-${id}`);
        const nama = row.querySelector('.data-nama').textContent;
        const jabatan = row.querySelector('.data-jabatan').textContent;
        const nrp = row.querySelector('.data-nrp').textContent;
        const user_id = row.querySelector('.data-user_id').getAttribute('data-user-id');

        document.getElementById('id_personil').value = id;
        document.getElementById('nama').value = nama;
        document.getElementById('jabatan').value = jabatan;
        document.getElementById('nrp').value = nrp;

        // Set selected option for user_id
        const userSelect = document.getElementById('user_id');
        for (let i = 0; i < userSelect.options.length; i++) {
            if (userSelect.options[i].value === user_id) {
                userSelect.selectedIndex = i;
                break;
            }
        }
    }

    function hapusPersonil(id) {
        let modal = document.getElementById('modalHapusPersonil');
        if (!modal) {
            modal = document.createElement('div');
            modal.innerHTML = `
                <div class="modal fade" id="modalHapusPersonil" tabindex="-1" aria-labelledby="modalHapusPersonilLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalHapusPersonilLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Apakah Anda yakin ingin menghapus data Personil ini?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusPersonil">Hapus</button>
                      </div>
                    </div>
                  </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Selalu pasang ulang event handler agar tombol berfungsi setiap kali modal dibuka
        setTimeout(function() {
            var btn = document.getElementById('btnKonfirmasiHapusPersonil');
            if (btn) {
                btn.onclick = function() {
                    window.location = `?hapus=${id}`;
                };
            }
            var bsModal = new bootstrap.Modal(document.getElementById('modalHapusPersonil'));
            bsModal.show();
        }, 100);
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
        }

        .form-control::placeholder {
            color: #999;
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
            <div class="main-header-container container-fluid">
                <?php include 'container.php'; ?>
            </div>
        </header>
        <!-- /app-header -->
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
                        <h2 class="main-content-title fs-24 mb-1">Data Sistem</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Personil Satintel</li>
                        </ol>
                    </div>
                </div>

                <!-- Start::Form Tambah Personil -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah / Edit Data Personil</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" autocomplete="off">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="nama" class="form-label">Nama Personil</label>
                                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan Nama" value="">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="jabatan" class="form-label">Jabatan</label>
                                            <input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Masukkan Jabatan" value="">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="nrp" class="form-label">NRP</label>
                                            <input type="text" class="form-control" id="nrp" name="nrp" placeholder="Masukkan NRP" value="">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="user_id" class="form-label">User Petugas</label>
                                            <select class="form-control" id="user_id" name="user_id">
                                                <option value="">-- Tidak Ada / Pilih User Petugas --</option>
                                                <?php foreach ($user_petugas as $u) { ?>
                                                    <option value="<?= $u['id_user']; ?>"><?= htmlspecialchars($u['nama']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_personil" name="id_personil" value="">
                                    <button type="submit" class="btn btn-success w-100 mt-3">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Form Tambah Personil -->

                <!-- Start::Tabel Data Personil -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Daftar Personil Satintel</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPersonil">
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
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>NRP</th>
                                            <th>User Aplikasi</th>
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

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM personil_satintel");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query = mysqli_query($conn, "SELECT p.*, u.nama as nama_user FROM personil_satintel p LEFT JOIN users u ON p.user_id = u.id_user ORDER BY p.id_personil DESC LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_personil']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama']); ?></td>
                                                <td class="data-jabatan"><?= htmlspecialchars($data['jabatan']); ?></td>
                                                <td class="data-nrp"><?= htmlspecialchars($data['nrp']); ?></td>
                                                <td class="data-user_id" data-user-id="<?= $data['user_id']; ?>"><?= htmlspecialchars($data['nama_user']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editPersonil(<?= $data['id_personil']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusPersonil(<?= $data['id_personil']; ?>)" title="Hapus">
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
                                            dari <b><?= $totalRows; ?></b> personil
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
                <!-- End::Tabel Data Personil -->
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