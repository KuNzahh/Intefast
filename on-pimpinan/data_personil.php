<?php
session_start();

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = isset($_POST['id_personil']) ? mysqli_real_escape_string($conn, $_POST['id_personil']) : '';
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan  = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $nrp      = mysqli_real_escape_string($conn, $_POST['nrp']);

    if ($id) {
        // Update data
        $query = "UPDATE personil_satintel SET nama='$nama', jabatan='$jabatan', nrp='$nrp' WHERE id_personil='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Personil berhasil diperbarui!' : 'Gagal memperbarui Personil.';
    } else {
        // Tambah data baru
        $query = "INSERT INTO personil_satintel (nama, jabatan, nrp) VALUES ('$nama', '$jabatan', '$nrp')";
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

        document.getElementById('id_personil').value = id;
        document.getElementById('nama').value = nama;
        document.getElementById('jabatan').value = jabatan;
        document.getElementById('nrp').value = nrp;
    }

    function hapusPersonil(id) {
        if (confirm('Yakin ingin menghapus data Personil ini?')) {
            window.location = `?hapus=${id}`;
        }
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
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Tambah Data Personil</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="nama" class="form-label">Nama Personil</label>
                                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan Nama">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="jabatan" class="form-label">Jabatan</label>
                                            <input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Masukkan Jabatan">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="nrp" class="form-label">NRP</label>
                                            <input type="text" class="form-control" id="nrp" name="nrp" placeholder="Masukkan NRP">
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
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>NRP</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($conn, "SELECT * FROM personil_satintel");
                                        $no = 1;
                                        while ($data = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr id="row-<?= $data['id_personil']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data['nama']); ?></td>
                                                <td class="data-jabatan"><?= htmlspecialchars($data['jabatan']); ?></td>
                                                <td class="data-nrp"><?= htmlspecialchars($data['nrp']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editPersonil(<?= $data['id_personil']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusPersonil(<?= $data['id_personil']; ?>)">
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
