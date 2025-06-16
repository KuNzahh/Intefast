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
        if (confirm('Yakin ingin menghapus pengguna ini?')) {
            window.location = `?hapus=${id}`;
        }
    }

    function filterTable() {
        const filter = document.getElementById("filterRole").value.toLowerCase();
        const rows = document.querySelectorAll("#tabelPengguna tbody tr");

        rows.forEach(row => {
            const role = row.cells[5].textContent.toLowerCase();
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
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>username</th>
                                            <th>Password</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($conn, "SELECT * FROM users");
                                        $no = 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_user']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama']); ?></td>
                                                <td class="data-email"><?= htmlspecialchars($data['email']); ?></td>
                                                <td class="data-username"><?= htmlspecialchars($data['username']); ?></td>
                                                <td class="data-password"><?= htmlspecialchars($data['password']); ?></td>
                                                <td class="data-role text-capitalize"><?= htmlspecialchars($data['role']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editUser(<?= $data['id_user']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusUser(<?= $data['id_user']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
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
    </div>

    <?php include 'script.php'; ?>

</body>

</html>