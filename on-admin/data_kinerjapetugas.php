<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kinerja = isset($_POST['id_kinerja']) ? mysqli_real_escape_string($conn, $_POST['id_kinerja']) : '';
    $id_user = isset($_POST['id_user']) ? mysqli_real_escape_string($conn, $_POST['id_user']) : '';
    $tanggal_penilaian = mysqli_real_escape_string($conn, $_POST['tanggal_penilaian']);
    $kedisiplinan = mysqli_real_escape_string($conn, $_POST['kedisiplinan']);
    $tanggung_jawab = mysqli_real_escape_string($conn, $_POST['tanggung_jawab']);
    $kerjasama = mysqli_real_escape_string($conn, $_POST['kerjasama']);
    $inisiatif = mysqli_real_escape_string($conn, $_POST['inisiatif']);
    $kualitas_kerja = mysqli_real_escape_string($conn, $_POST['kualitas_kerja']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $dinilai_oleh = $_SESSION['user_id'];

    if ($id_user) {
        if ($id_kinerja) {
            $query = "UPDATE kinerja_petugas SET
                        id_user='$id_user',
                        tanggal_penilaian='$tanggal_penilaian',
                        kedisiplinan='$kedisiplinan',
                        tanggung_jawab='$tanggung_jawab',
                        kerjasama='$kerjasama',
                        inisiatif='$inisiatif',
                        kualitas_kerja='$kualitas_kerja',
                        catatan='$catatan',
                        dinilai_oleh='$dinilai_oleh'
                      WHERE id_kinerja='$id_kinerja'";
        } else {
            $query = "INSERT INTO kinerja_petugas (id_user, tanggal_penilaian, kedisiplinan, tanggung_jawab, kerjasama, inisiatif, kualitas_kerja, catatan, dinilai_oleh)
                      VALUES ('$id_user', '$tanggal_penilaian', '$kedisiplinan', '$tanggung_jawab', '$kerjasama', '$inisiatif', '$kualitas_kerja', '$catatan', '$dinilai_oleh')";
        }
        $result = mysqli_query($conn, $query);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Data Kinerja Petugas berhasil disimpan!' : 'Gagal menyimpan Data Kinerja Petugas.';
        header('Location: data_kinerjapetugas.php');
        exit();
    } else {
        $_SESSION['error'] = 'Nama Petugas harus dipilih!';
        header('Location: data_kinerjapetugas.php');
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $id_kinerja_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM kinerja_petugas WHERE id_kinerja='$id_kinerja_hapus'");
    $_SESSION[$hapus ? 'success' : 'error'] = $hapus ? 'Data kinerja petugas berhasil dihapus!' : 'Gagal menghapus data kinerja petugas.';
    header('Location: data_kinerja_petugas.php');
    exit();
}
?>

<script>
    function editKinerja(id_kinerja) {
        const row = document.querySelector(`#row-${id_kinerja}`);
        const user_id = row.querySelector('.data-id_user').getAttribute('data-id'); // Tetap ambil dari data-id
        const tanggal_penilaian = row.querySelector('.data-tanggal_penilaian').textContent;
        const kedisiplinan = row.querySelector('.data-kedisiplinan').textContent;
        const tanggung_jawab = row.querySelector('.data-tanggung_jawab').textContent;
        const kerjasama = row.querySelector('.data-kerjasama').textContent;
        const inisiatif = row.querySelector('.data-inisiatif').textContent;
        const kualitas_kerja = row.querySelector('.data-kualitas_kerja').textContent;
        const catatan = row.querySelector('.data-catatan').textContent;

        document.getElementById('id_kinerja').value = id_kinerja;
        document.getElementById('id_user').value = user_id; // Set nilai select nama petugas
        document.getElementById('tanggal_penilaian').value = tanggal_penilaian;
        document.getElementById('kedisiplinan').value = kedisiplinan;
        document.getElementById('tanggung_jawab').value = tanggung_jawab;
        document.getElementById('kerjasama').value = kerjasama;
        document.getElementById('inisiatif').value = inisiatif;
        document.getElementById('kualitas_kerja').value = kualitas_kerja;
        document.getElementById('catatan').value = catatan;

        // Set nilai dropdown
        document.getElementById('kedisiplinan').value = kedisiplinan;
        document.getElementById('tanggung_jawab').value = tanggung_jawab;
        document.getElementById('kerjasama').value = kerjasama;
        document.getElementById('inisiatif').value = inisiatif;
        document.getElementById('kualitas_kerja').value = kualitas_kerja;
    }

    function hapusKinerja(id_kinerja) {
        if (confirm('Yakin ingin menghapus data kinerja petugas ini?')) {
            window.location = `?hapus=${id_kinerja}`;
        }
    }
</script>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Data Kinerja Petugas</title>
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
                            <li class="breadcrumb-item active" aria-current="page">Data Kinerja Petugas</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Tambah/Edit Kinerja Petugas</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="id_user" class="form-label">Nama Petugas</label>
                                            <select class="form-select" id="id_user" name="id_user" required>
                                                <option value="">Pilih Petugas</option>
                                                <?php
                                                $query_petugas = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE role = 'petugas'");
                                                while ($row_petugas = mysqli_fetch_assoc($query_petugas)) {
                                                    echo "<option value='" . $row_petugas['id_user'] . "'>" . htmlspecialchars($row_petugas['nama']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian</label>
                                            <input type="date" class="form-control" id="tanggal_penilaian" name="tanggal_penilaian" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="kedisiplinan" class="form-label">Kedisiplinan</label>
                                            <select class="form-select" id="kedisiplinan" name="kedisiplinan">
                                                <option value="">Pilih Nilai</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="tanggung_jawab" class="form-label">Tanggung Jawab</label>
                                            <select class="form-select" id="tanggung_jawab" name="tanggung_jawab">
                                                <option value="">Pilih Nilai</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="kerjasama" class="form-label">Kerjasama</label>
                                            <select class="form-select" id="kerjasama" name="kerjasama">
                                                <option value="">Pilih Nilai</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="inisiatif" class="form-label">Inisiatif</label>
                                            <select class="form-select" id="inisiatif" name="inisiatif">
                                                <option value="">Pilih Nilai</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="kualitas_kerja" class="form-label">Kualitas Kerja</label>
                                            <select class="form-select" id="kualitas_kerja" name="kualitas_kerja">
                                                <option value="">Pilih Nilai</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="catatan" class="form-label">Catatan</label>
                                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan penilaian"></textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_kinerja" name="id_kinerja" value="">
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
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Kinerja Petugas</h5>
                                <div style="min-width: 200px;">
                                    <input type="text" id="cariInputKinerja" class="form-control form-control-sm" placeholder="Cari Nama Petugas..." onkeyup="cariDataKinerja()">
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelKinerja">
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
                                            <th>Nama Petugas</th>
                                            <th style="width: 140px;">Tanggal Penilaian</th>
                                            <th>Kedisiplinan</th>
                                            <th>Tanggung Jawab</th>
                                            <th>Kerjasama</th>
                                            <th>Inisiatif</th>
                                            <th>Kualitas Kerja</th>
                                            <th>Catatan</th>
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

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kinerja_petugas kp JOIN users u ON kp.id_user = u.id_user WHERE u.role = 'petugas'");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query_kinerja = mysqli_query($conn, "SELECT kp.*, u.nama, u.id_user
                                            FROM kinerja_petugas kp
                                            JOIN users u ON kp.id_user = u.id_user
                                            WHERE u.role = 'petugas'
                                            ORDER BY kp.id_kinerja DESC
                                            LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data_kinerja = mysqli_fetch_assoc($query_kinerja)) {
                                        ?>
                                            <tr id="row-<?= $data_kinerja['id_kinerja']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-nama_petugas data-id_user" data-id="<?= $data_kinerja['id_user']; ?>"><?= htmlspecialchars($data_kinerja['nama']); ?></td>
                                                <td class="data-tanggal_penilaian text-center"><?= htmlspecialchars($data_kinerja['tanggal_penilaian']); ?></td>
                                                <td class="data-kedisiplinan"><?= htmlspecialchars($data_kinerja['kedisiplinan']); ?></td>
                                                <td class="data-tanggung_jawab"><?= htmlspecialchars($data_kinerja['tanggung_jawab']); ?></td>
                                                <td class="data-kerjasama"><?= htmlspecialchars($data_kinerja['kerjasama']); ?></td>
                                                <td class="data-inisiatif"><?= htmlspecialchars($data_kinerja['inisiatif']); ?></td>
                                                <td class="data-kualitas_kerja"><?= htmlspecialchars($data_kinerja['kualitas_kerja']); ?></td>
                                                <td class="data-catatan"><?= htmlspecialchars($data_kinerja['catatan']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editKinerja(<?= $data_kinerja['id_kinerja']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusKinerja(<?= $data_kinerja['id_kinerja']; ?>)" title="Hapus">
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
                                            dari <b><?= $totalRows; ?></b> kinerja petugas
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
                    // Fungsi editKinerja sudah didefinisikan di atas, tidak perlu didefinisikan ulang di sini

                    function hapusKinerja(id) {
                        // Buat modal konfirmasi jika belum ada
                        let modal = document.getElementById('modalHapusKinerja');
                        if (!modal) {
                            modal = document.createElement('div');
                            modal.innerHTML = `
                                <div class="modal fade" id="modalHapusKinerja" tabindex="-1" aria-labelledby="modalHapusKinerjaLabel" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header bg-danger">
                                        <h5 class="modal-title text-white" id="modalHapusKinerjaLabel">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus data kinerja petugas ini?
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusKinerja">Hapus</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            `;
                            document.body.appendChild(modal);
                        }

                        // Tampilkan modal
                        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusKinerja'));
                        bsModal.show();

                        // Set event tombol hapus
                        document.getElementById('btnKonfirmasiHapusKinerja').onclick = function() {
                            window.location = `?hapus=${id}`;
                        };
                    }

                    function cariDataKinerja() {
                        const input = document.getElementById("cariInputKinerja");
                        const filter = input.value.toLowerCase();
                        const table = document.getElementById("tabelKinerja");
                        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

                        for (let i = 0; i < rows.length; i++) {
                            const namaPetugas = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
                            if (namaPetugas.includes(filter)) {
                                rows[i].style.display = "";
                            } else {
                                rows[i].style.display = "none";
                            }
                        }
                    }
                </script>
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