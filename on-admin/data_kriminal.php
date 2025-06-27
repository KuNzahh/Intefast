<?php
session_start();
// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// Ambil data kecamatan dari tabel kecamatan
$query_kecamatan = mysqli_query($conn, "SELECT id_kecamatan, nama_kecamatan FROM kecamatan");
$data_kecamatan = mysqli_fetch_all($query_kecamatan, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // tangkap & escape
    $id         = mysqli_real_escape_string($conn, $_POST['id_kriminal'] ?? '');
    $NIK        = mysqli_real_escape_string($conn, $_POST['NIK']);
    $nama       = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kecamatan_id = mysqli_real_escape_string($conn, $_POST['kecamatan_id']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $cttkriminal   = mysqli_real_escape_string($conn, $_POST['cttkriminal']);

    if ($id) {
        /* ---------- UPDATE ---------- */
        $sql = "UPDATE kriminal
                SET NIK='$NIK',
                    nama='$nama',
                    alamat='$alamat',
                    kecamatan_id='$kecamatan_id',
                    jenis_kelamin='$jenis_kelamin',
                    cttkriminal='$cttkriminal'
                WHERE id_kriminal='$id'";
    } else {
        /* ---------- INSERT ---------- */
        $sql = "INSERT INTO kriminal
                (NIK, nama, alamat, kecamatan_id, jenis_kelamin, cttkriminal)
                VALUES
                ('$NIK', '$nama', '$alamat', '$kecamatan_id', '$jenis_kelamin', '$cttkriminal')";
    }

    $run = mysqli_query($conn, $sql);
    if (!$run) die("Query error: " . mysqli_error($conn));     // debug sementara

    $_SESSION[$run ? 'success' : 'error'] =
        $id ? ($run ? 'Data berhasil di-update' : 'Gagal update')
        : ($run ? 'Data berhasil ditambah' : 'Gagal tambah');

    header('Location: data_kriminal.php');
    exit();
}

/* ------------- Hapus ------------- */
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $del = mysqli_query($conn, "DELETE FROM kriminal WHERE id_kriminal='$id'");
    $_SESSION[$del ? 'success' : 'error'] =
        $del ? 'Data berhasil dihapus' : 'Gagal hapus data';
    header('Location: data_kriminal.php');
    exit();
}
?>


<script>
    function editKriminal(id) {
        const row = document.querySelector(`#row-${id}`);
        const NIK = row.querySelector('.data-NIK').textContent;
        const nama = row.querySelector('.data-nama').textContent;
        const alamat = row.querySelector('.data-alamat').textContent;
        const kecamatan_id_data = row.querySelector('.data-kecamatan').getAttribute('data-kecamatan-id'); // Ambil ID kecamatan dari atribut data
        const jenis_kelamin_data = row.querySelector('.data-jenis_kelamin').textContent;
        const cttkriminal = row.querySelector('.data-cttkriminal').textContent;
    
        document.getElementById('id_kriminal').value = id;
        document.getElementById('NIK').value = NIK;
        document.getElementById('nama').value = nama;
        document.getElementById('alamat').value = alamat;
        document.getElementById('kecamatan_id').value = kecamatan_id_data; // Set nilai ID kecamatan
    
        // Set nilai dropdown jenis kelamin
        const selectJenisKelamin = document.getElementById('jenis_kelamin');
        for (let i = 0; i < selectJenisKelamin.options.length; i++) {
            if (selectJenisKelamin.options[i].value === jenis_kelamin_data) {
                selectJenisKelamin.selectedIndex = i;
                break;
            }
        }
    
        document.getElementById('cttkriminal').value = cttkriminal;
    }


    function hapusKriminal(id) {
        if (confirm('Yakin ingin menghapus Kriminal ini?')) {
            window.location = `?hapus=${id}`;
        }
    }

    function filterTable() {
        const filter = document.getElementById("filterBulan").value.toLowerCase();
        const rows = document.querySelectorAll("#tabelPengguna tbody tr");

        rows.forEach(row => {
            const kecamatan = row.cells[4].textContent.toLowerCase(); // Sekarang kolom ke-5 (index 4) adalah Nama Kecamatan
            row.style.display = (filter === "" || kecamatan.includes(filter)) ? "" : "none";
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
                            <li class="breadcrumb-item active" aria-current="page">Data Kriminal masyarakat</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Tambah Data Kriminal masyarakat</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="NIK" class="form-label">NIK</label>
                                            <input type="text" class="form-control" id="NIK" name="NIK" placeholder="Masukkan nik">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nama" class="form-label">Nama</label>
                                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="alamat" class="form-label">Alamat</label>
                                            <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Masukkan alamat">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="kecamatan_id" class="form-label">Kecamatan</label>
                                            <select class="form-select" id="kecamatan_id" name="kecamatan_id" required>
                                                <option value="">-- Pilih Kecamatan --</option>
                                                <?php foreach ($data_kecamatan as $kecamatan) : ?>
                                                    <option value="<?= htmlspecialchars($kecamatan['id_kecamatan']); ?>">
                                                        <?= htmlspecialchars($kecamatan['nama_kecamatan']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cttkriminal" class="form-label"> Catatan Kriminal</label>
                                            <input type="text" class="form-control" id="cttkriminal" name="cttkriminal" placeholder="Masukkan catatan kriminal">
                                        </div>
                                    </div>
                                    <input type="hidden" id="id_kriminal" name="id_kriminal" value=""> <button type="submit" class="btn btn-success w-100 mt-3">
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
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Daftar Data Kriminal Masyarakat</h5>
                                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                                    <div style="min-width: 200px;">
                                        <input type="text" id="cariInputKriminal" class="form-control form-control-sm" placeholder="Cari NIK / Nama..." onkeyup="cariDataKriminal()">
                                    </div>
                                    <div style="min-width: 180px;">
                                        <select class="form-select form-select-sm" id="filterKecamatan" onchange="filterTableKriminal()">
                                            <option value="">Semua Kecamatan</option>
                                            <?php foreach ($data_kecamatan as $kecamatan_filter) : ?>
                                                <option value="<?= htmlspecialchars($kecamatan_filter['nama_kecamatan']); ?>">
                                                    <?= htmlspecialchars($kecamatan_filter['nama_kecamatan']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelKriminalMasyarakat">
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
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Alamat</th>
                                            <th>Kecamatan</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Catatan Kriminal</th>
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

                                        $totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kriminal");
                                        $totalData = mysqli_fetch_assoc($totalQuery);
                                        $totalRows = $totalData['total'];
                                        $totalPages = ceil($totalRows / $perPage);

                                        $query_kriminal = mysqli_query($conn, "SELECT k.*, kec.nama_kecamatan
                                            FROM kriminal k
                                            INNER JOIN kecamatan kec ON k.kecamatan_id = kec.id_kecamatan
                                            ORDER BY k.id_kriminal DESC
                                            LIMIT $start, $perPage");
                                        $no = $start + 1;
                                        while ($data_kriminal = mysqli_fetch_assoc($query_kriminal)) {
                                        ?>
                                            <tr id="row-<?= $data_kriminal['id_kriminal']; ?>">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td class="data-NIK"><?= htmlspecialchars($data_kriminal['NIK']); ?></td>
                                                <td class="data-nama"><?= htmlspecialchars($data_kriminal['nama']); ?></td>
                                                <td class="data-alamat"><?= htmlspecialchars($data_kriminal['alamat']); ?></td>
                                                <td class="data-kecamatan" data-kecamatan-id="<?= htmlspecialchars($data_kriminal['kecamatan_id']); ?>"><?= htmlspecialchars($data_kriminal['nama_kecamatan']); ?></td>
                                                <td class="data-jenis_kelamin"><?= htmlspecialchars($data_kriminal['jenis_kelamin']); ?></td>
                                                <td class="data-cttkriminal"><?= htmlspecialchars($data_kriminal['cttkriminal']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" onclick="editKriminal(<?= $data_kriminal['id_kriminal']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="hapusKriminal(<?= $data_kriminal['id_kriminal']; ?>)" title="Hapus">
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
                                            dari <b><?= $totalRows; ?></b> data kriminal
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

                <script>
                    function editKriminal(id) {
                        const row = document.querySelector(`#row-${id}`);
                        const NIK = row.querySelector('.data-NIK').textContent;
                        const nama = row.querySelector('.data-nama').textContent;
                        const alamat = row.querySelector('.data-alamat').textContent;
                        const kecamatan_id_data = row.querySelector('.data-kecamatan').getAttribute('data-kecamatan-id'); // Ambil ID kecamatan dari atribut data
                        const jenis_kelamin_data = row.querySelector('.data-jenis_kelamin').textContent;
                        const cttkriminal = row.querySelector('.data-cttkriminal').textContent;
                    
                        document.getElementById('id_kriminal').value = id;
                        document.getElementById('NIK').value = NIK;
                        document.getElementById('nama').value = nama;
                        document.getElementById('alamat').value = alamat;
                        document.getElementById('kecamatan_id').value = kecamatan_id_data; // Set nilai ID kecamatan
                    
                        // Set nilai dropdown jenis kelamin
                        const selectJenisKelamin = document.getElementById('jenis_kelamin');
                        for (let i = 0; i < selectJenisKelamin.options.length; i++) {
                            if (selectJenisKelamin.options[i].value === jenis_kelamin_data) {
                                selectJenisKelamin.selectedIndex = i;
                                break;
                            }
                        }
                    
                        document.getElementById('cttkriminal').value = cttkriminal;
                    }


                    function filterTable() {
                        const filter = document.getElementById("filterBulan").value.toLowerCase();
                        const rows = document.querySelectorAll("#tabelPengguna tbody tr");

                        rows.forEach(row => {
                            const kecamatan = row.cells[4].textContent.toLowerCase(); // Sekarang kolom ke-5 (index 4) adalah Nama Kecamatan
                            row.style.display = (filter === "" || kecamatan.includes(filter)) ? "" : "none";
                        });
                    }
                </script>
                <script>
                    // HapusKriminal tetap bisa digunakan jika ingin menambah logika lain
                    function hapusKriminal(id) {
                        // Buat modal konfirmasi jika belum ada
                        let modal = document.getElementById('modalHapusKriminal');
                        if (!modal) {
                            modal = document.createElement('div');
                            modal.innerHTML = `
                                <div class="modal fade" id="modalHapusKriminal" tabindex="-1" aria-labelledby="modalHapusKriminalLabel" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header bg-danger">
                                        <h5 class="modal-title text-white" id="modalHapusKriminalLabel">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus data kriminal ini?
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusKriminal">Hapus</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            `;
                            document.body.appendChild(modal);
                        }

                        // Tampilkan modal
                        var bsModal = new bootstrap.Modal(document.getElementById('modalHapusKriminal'));
                        bsModal.show();

                        // Set event tombol hapus
                        document.getElementById('btnKonfirmasiHapusKriminal').onclick = function() {
                            window.location = `?hapus=${id}`;
                        };
                    }

                    function cariDataKriminal() {
                        const input = document.getElementById("cariInputKriminal");
                        const filter = input.value.toLowerCase();
                        const table = document.getElementById("tabelKriminalMasyarakat");
                        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

                        for (let i = 0; i < rows.length; i++) {
                            const nik = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
                            const nama = rows[i].getElementsByTagName("td")[2].textContent.toLowerCase();
                            if (nik.includes(filter) || nama.includes(filter)) {
                                rows[i].style.display = "";
                            } else {
                                rows[i].style.display = "none";
                            }
                        }
                    }

                    function filterTableKriminal() {
                        const filter = document.getElementById("filterKecamatan").value.toLowerCase();
                        const table = document.getElementById("tabelKriminalMasyarakat");
                        const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

                        for (let i = 0; i < rows.length; i++) {
                            const kecamatan = rows[i].getElementsByTagName("td")[4].textContent.toLowerCase();
                            if (filter === "" || kecamatan === filter) {
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
        <?php include 'script.php'; ?>

</body>

</html>