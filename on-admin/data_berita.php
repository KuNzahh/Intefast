<?php
session_start();
include '../include/koneksi.php';
if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = isset($_POST['id_berita']) ? mysqli_real_escape_string($conn, $_POST['id_berita']) : '';
    $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi      = mysqli_real_escape_string($conn, $_POST['isi']);
    $waktu_ubah = date('Y-m-d H:i:s');

    // Handle upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'berita_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/images/berita/' . $gambar);
    }

    if ($id) {
        // Ambil gambar lama jika tidak upload baru
        if (!$gambar) {
            $q = mysqli_query($conn, "SELECT gambar FROM berita WHERE id_berita='$id'");
            $d = mysqli_fetch_assoc($q);
            $gambar = $d ? $d['gambar'] : '';
        }
        // Update data
        $query = "UPDATE berita SET judul='$judul', isi='$isi', waktu_ubah='$waktu_ubah', gambar='$gambar' WHERE id_berita='$id'";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Berita berhasil diperbarui!' : 'Gagal memperbarui berita.';
    } else {
        // Tambah data baru
        $query = "INSERT INTO berita (judul, isi, gambar, waktu_ubah) VALUES ('$judul', '$isi', '$gambar', '$waktu_ubah')";
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Berita berhasil ditambahkan!' : 'Gagal menambahkan berita.';
    }

    header('Location: data_berita.php');
    exit();
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    // Hapus gambar dari server
    $q = mysqli_query($conn, "SELECT gambar FROM berita WHERE id_berita='$id'");
    $d = mysqli_fetch_assoc($q);
    if ($d && $d['gambar'] && file_exists('../uploads/' . $d['gambar'])) {
        unlink('../uploads/' . $d['gambar']);
    }
    $hapus = mysqli_query($conn, "DELETE FROM berita WHERE id_berita='$id'");
    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Berita berhasil dihapus!' : 'Gagal menghapus berita.';
    header('Location: data_berita.php');
    exit();
}
?>

<script>
    function editBerita(id) {
        const row = document.querySelector(`#row-${id}`);
        const judul = row.querySelector('.data-judul').textContent;
        const isi = row.querySelector('.data-isi').textContent;

        document.getElementById('id_berita').value = id;
        document.getElementById('judul').value = judul;
        document.getElementById('isi').value = isi;
        // Gambar tidak diisi ulang
    }

    // Modal konfirmasi hapus untuk berita
    function hapusBerita(id) {
        let modal = document.getElementById('modalHapusBerita');
        if (!modal) {
            modal = document.createElement('div');
            modal.innerHTML = `
                <div class="modal fade" id="modalHapusBerita" tabindex="-1" aria-labelledby="modalHapusBeritaLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalHapusBeritaLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Apakah Anda yakin ingin menghapus berita ini?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusBerita">Hapus</button>
                      </div>
                    </div>
                  </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusBerita'));
        bsModal.show();

        document.getElementById('btnKonfirmasiHapusBerita').onclick = function() {
            window.location = `?hapus=${id}`;
        };
    }

    function cariDataBerita() {
        const input = document.getElementById("cariInputBerita");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("tabelBerita");
        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            const judul = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
            const isi = rows[i].getElementsByTagName("td")[2].textContent.toLowerCase();
            if (judul.includes(filter) || isi.includes(filter)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
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
    <div class="page">
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
        <div class="page-main">
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
                <?php include 'sidebar.php'; ?>
                <!-- End::main-sidebar -->
            </aside>
            <!-- End::app-sidebar -->
            <div class="main-content app-content">
                <div class="container-fluid">
                    <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb mb-4">
                        <div>
                            <hr>
                            <h3 class="main-content-title fs-24 mb-1">Data Berita</h3>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Data Berita</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Form Tambah Berita -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Tambah Data Berita</h5>
                                </div>
                                <div class="card-body">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="judul" class="form-label">Judul</label>
                                                <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan Judul">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="gambar" class="form-label">Gambar</label>
                                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="isi" class="form-label">Isi</label>
                                            <textarea class="form-control" id="isi" name="isi" placeholder="Masukkan Isi Berita"></textarea>
                                        </div>
                                        <input type="hidden" id="id_berita" name="id_berita" value="">
                                        <button type="submit" class="btn btn-success w-100 mt-3">
                                            <i class="fas fa-save me-1"></i> Simpan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tabel Data Berita -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Daftar Berita</h5>
                                    <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                                        <div style="min-width: 200px;">
                                            <input type="text" id="cariInputBerita" class="form-control form-control-sm" placeholder="Cari Berita..." onkeyup="cariDataBerita()">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-striped align-middle" id="tabelBerita">
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
                                                <th>Judul</th>
                                                <th>Isi</th>
                                                <th style="width: 120px;">Gambar</th>
                                                <th style="width: 180px;">Waktu Ubah</th>
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

                                            $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM berita");
                                            $totalData = mysqli_fetch_assoc($totalQuery);
                                            $totalRows = $totalData['total'];
                                            $totalPages = ceil($totalRows / $perPage);

                                            $query = mysqli_query($conn, "SELECT * FROM berita ORDER BY waktu_ubah DESC LIMIT $start, $perPage");
                                            $no = $start + 1;
                                            while ($data = mysqli_fetch_assoc($query)) {
                                            ?>
                                                <tr id="row-<?= $data['id_berita']; ?>">
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td class="data-judul"><?= htmlspecialchars($data['judul']); ?></td>
                                                    <td class="data-isi"><?= htmlspecialchars($data['isi']); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($data['gambar']) { ?>
                                                            <img src="../assets/images/berita/<?= htmlspecialchars($data['gambar']); ?>" alt="gambar" style="max-width:80px;max-height:80px;">
                                                        <?php } else { ?>
                                                            <span class="text-muted">Tidak ada gambar</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($data['waktu_ubah']); ?></td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-warning" onclick="editBerita(<?= $data['id_berita']; ?>)" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="hapusBerita(<?= $data['id_berita']; ?>)" title="Hapus">
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
                                                dari <b><?= $totalRows; ?></b> berita
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
                    <!-- End Tabel Data Berita -->
                </div>
            </div>
            <!-- ... footer ... -->
            <?php include 'script.php'; ?>
        </div>
</body>

</html>