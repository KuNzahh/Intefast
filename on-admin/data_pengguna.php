<?php
session_start();
include '../include/koneksi.php';

// --- Handle POST Add/Edit User ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id_user']) ? mysqli_real_escape_string($conn, $_POST['id_user']) : '';
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : '';

    if ($id) {
        $query = "UPDATE users SET nama='$nama', email='$email', username='$username', role='$role'";
        if ($password) {
            $query .= ", password='$password'";
        }
        $query .= " WHERE id_user='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Pengguna berhasil diperbarui!' : 'Gagal memperbarui pengguna.';
    } else {
        $query = "INSERT INTO users (nama, email, username, password, role) VALUES ('$nama', '$email', '$username', '$password', '$role')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $_SESSION['success'] = 'Pengguna berhasil ditambahkan!';
            // Notifikasi ke admin
            $_SESSION['notifikasi_admin'][] = [
                'pesan' => "Pemohon baru terdaftar: $nama",
                'jenis' => 'pengguna_baru',
                'waktu' => date("Y-m-d H:i:s")
            ];
        } else {
            $_SESSION['error'] = 'Gagal menambahkan pengguna.';
        }
    }
    header('Location: data_pengguna.php');
    exit();
}

// --- Handle Delete User --- 
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);

    // 1. Cek apakah masih ada data yang terhubung dengan pengguna di semua tabel terkait
    // Cek apakah user yang akan dihapus adalah admin
    $query_role = "SELECT role FROM users WHERE id_user = ?";
    $stmt_role = mysqli_prepare($conn, $query_role);
    mysqli_stmt_bind_param($stmt_role, "i", $id);
    mysqli_stmt_execute($stmt_role);
    $result_role = mysqli_stmt_get_result($stmt_role);
    $data_role = mysqli_fetch_assoc($result_role);

    if ($data_role && strtolower($data_role['role']) === 'admin') {
        $_SESSION['error'] = 'Pengguna dengan role admin tidak dapat dihapus.';
        header('Location: data_pengguna.php');
        exit();
    }

    $tables = [
        ['table' => 'sik', 'column' => 'user_id', 'label' => 'SIK'],
        ['table' => 'skck', 'column' => 'user_id', 'label' => 'SKCK'],
        ['table' => 'sttp', 'column' => 'user_id', 'label' => 'STTP'],
        ['table' => 'personil_satintel', 'column' => 'user_id', 'label' => 'Personil Sat Intelkam'],
        ['table' => 'survey_kepuasan', 'column' => 'user_id', 'label' => 'Survey Kepuasan'],
        ['table' => 'chat', 'column' => 'id_pengirim', 'label' => 'Chat sebagai Pengirim'],
        ['table' => 'chat', 'column' => 'id_penerima', 'label' => 'Chat sebagai Penerima'],
        ['table' => 'kinerja_petugas', 'column' => 'user_id', 'label' => 'Kinerja Petugas'],
        // Tambahkan tabel lain di sini jika ada relasi ke user
    ];

    foreach ($tables as $tbl) {
        // Pastikan kolom benar-benar ada di tabel sebelum melakukan query
        $query_check = "SHOW COLUMNS FROM {$tbl['table']} LIKE '{$tbl['column']}'";
        $result_column = mysqli_query($conn, $query_check);
        if (mysqli_num_rows($result_column) == 0) {
            continue; // Kolom tidak ada, skip
        }

        $query_check = "SELECT COUNT(*) as total FROM {$tbl['table']} WHERE {$tbl['column']} = ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "i", $id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $data_check = mysqli_fetch_assoc($result_check);

        if ($data_check['total'] > 0) {
            $_SESSION['error'] = 'Tidak dapat menghapus pengguna karena masih ada data terkait di tabel: ' . $tbl['label'] . '.';
            header('Location: data_pengguna.php');
            exit();
        }
    }

    // Hapus data terkait terlebih dahulu
    mysqli_query($conn, "DELETE FROM chat WHERE id_pengirim='$id'");
    mysqli_query($conn, "DELETE FROM kinerja_petugas WHERE id_user='$id'");
    mysqli_query($conn, "DELETE FROM chat WHERE id_penerima='$id'");

    // Setelah semua data terkait dihapus, hapus user
    $hapus = mysqli_query($conn, "DELETE FROM users WHERE id_user='$id'");

    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Pengguna berhasil dihapus!' : 'Gagal menghapus pengguna.';
    header('Location: data_pengguna.php');
    exit();
}

?>


<script>
    function editUser(id) {
        const row = document.querySelector(`#row-${id}`);
        const nama = row.querySelector('.data-nama').textContent;
        const email = row.querySelector('.data-email').textContent;
        const username = row.querySelector('.data-username').textContent;
        const role = row.querySelector('.data-role').textContent.toLowerCase();

        document.getElementById('id_user').value = id;
        document.getElementById('nama').value = nama;
        document.getElementById('email').value = email;
        document.getElementById('username').value = username;
        document.getElementById('role').value = role;

        document.getElementById('password').removeAttribute('required');
    }

    function hapusUser(id) {
        // Buat modal konfirmasi jika belum ada
        let modal = document.getElementById('modalHapusUser');
        if (!modal) {
            modal = document.createElement('div');
            modal.innerHTML = `
                <div class="modal fade" id="modalHapusUser" tabindex="-1" aria-labelledby="modalHapusUserLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalHapusUserLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Apakah Anda yakin ingin menghapus pengguna ini?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusUser">Hapus</button>
                      </div>
                    </div>
                  </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Tampilkan modal
        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusUser'));
        bsModal.show();

        // Set event tombol hapus
        document.getElementById('btnKonfirmasiHapusUser').onclick = function() {
            window.location = `?hapus=${id}`;
        };
    }
    function filterTable() {
        const filter = document.getElementById('filterRole').value.toLowerCase();
        const rows = document.querySelectorAll('#tabelPengguna tbody tr');

        rows.forEach(row => {
            const role = row.querySelector('.data-role').textContent.toLowerCase();
            if (filter === '' || role === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
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
                <?php include 'logo.php'; ?>
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
                            <li class="breadcrumb-item active" aria-current="page">Data Pengguna</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Tambah Pengguna</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email pengguna">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Kata Sandi</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">-- Pilih Role --</option>
                                                <option value="admin">Admin</option>
                                                <option value="petugas">Petugas</option>
                                                <option value="pemohon">Pemohon</option>
                                                <option value="pimpinan">Pimpinan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_user" name="id_user" value="">
                                    <button type="submit" class="btn btn-success w-100 mt-3">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                       
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Pengguna</h5>
                                <div class="mt-2 mt-md-0" style="min-width: 180px;">
                                    <select class="form-select" id="filterRole" onchange="filterTable()">
                                        <option value="">Semua Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="petugas">Petugas</option>
                                        <option value="pemohon">Pemohon</option>
                                        <option value="pimpinan">Pimpinan</option>
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
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Username</th>
                                            <th style="width: 120px;">Role</th>
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

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_user']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama']); ?></td>
                                                <td class="data-email"><?= htmlspecialchars($data['email']); ?></td>
                                                <td class="data-username"><?= htmlspecialchars($data['username']); ?></td>
                                                <td class="data-role text-capitalize text-center"><?= htmlspecialchars($data['role']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editUser(<?= $data['id_user']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusUser(<?= $data['id_user']; ?>)" title="Hapus">
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
                                            dari <b><?= $totalRows; ?></b> pengguna
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
    </div>

    <?php include 'script.php'; ?>

</body>

</html>