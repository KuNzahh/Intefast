<?php
session_start(); // Tambahkan ini di baris paling atas

// Pastikan session nama sudah di-set
if (!isset($_SESSION['nama'])) {
    // Jika belum di-set, mungkin perlu redirect ke halaman login
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

// Ambil data pengajuan SKCK
$sql_skck = "SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, s.keperluan, s.progres, s.id_skck
             FROM skck s
             JOIN users u ON s.user_id = u.id_user
             ORDER BY s.tanggal_pengajuan DESC";
$result_skck = mysqli_query($conn, $sql_skck);
$data_skck = mysqli_fetch_all($result_skck, MYSQLI_ASSOC);

// Ambil data pengajuan SIK
$sql_sik = "SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, s.nama_instansi, s.penanggung_jawab, s.progres, s.id_sik
            FROM sik s
            JOIN users u ON s.user_id = u.id_user
            ORDER BY s.tanggal_pengajuan DESC";
$result_sik = mysqli_query($conn, $sql_sik);
$data_sik = mysqli_fetch_all($result_sik, MYSQLI_ASSOC);

// Ambil data pengajuan STTP
$sql_sttp = "SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, s.nama_paslon, k.nama_kampanye, s.progres, s.id_sttp
             FROM sttp s
             JOIN users u ON s.user_id = u.id_user
             JOIN kampanye k ON s.kampanye_id = k.id_kampanye
             ORDER BY s.tanggal_pengajuan DESC";
$result_sttp = mysqli_query($conn, $sql_sttp);
$data_sttp = mysqli_fetch_all($result_sttp, MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Data Permohonan - Sistem Pelayanan</title>
</head>

<body>
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
                        <h2 class="main-content-title fs-24 mb-1">Data Permohonan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Permohonan</li>
                        </ol>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lihat Data Permohonan</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="jenis_permohonan" class="form-label">Pilih Jenis Permohonan:</label>
                                                <select class="form-select" id="jenis_permohonan" name="jenis">
                                                    <option value="semua">Semua Permohonan</option>
                                                    <option value="skck" <?php if (isset($_GET['jenis']) && $_GET['jenis'] == 'skck') echo 'selected'; ?>>SKCK</option>
                                                    <option value="sik" <?php if (isset($_GET['jenis']) && $_GET['jenis'] == 'sik') echo 'selected'; ?>>SIK</option>
                                                    <option value="sttp" <?php if (isset($_GET['jenis']) && $_GET['jenis'] == 'sttp') echo 'selected'; ?>>STTP</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 align-self-end mb-3">
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Filter</button>
                                            <a href="data_pengajuan.php" class="btn btn-secondary"><i class="fas fa-undo me-2"></i>Reset Filter</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Data Permohonan</h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="tabelPermohonan">
                                    <thead class="table-primary text-center align-middle">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th style="width: 160px;">Jenis Permohonan</th>
                                            <th>Nama Pemohon</th>
                                            <th style="width: 140px;">Tanggal Pengajuan</th>
                                            <th>Detail</th>
                                            <th style="width: 120px;">Status</th>
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

                                        $jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';

                                        // Gabungkan semua data sesuai filter
                                        $all_data = [];
                                        if ($jenis_filter == 'semua' || $jenis_filter == 'skck') {
                                            foreach ($data_skck as $skck) {
                                                $all_data[] = [
                                                    'jenis' => 'SKCK',
                                                    'nama_pemohon' => $skck['nama_pemohon'],
                                                    'tanggal_pengajuan' => $skck['tanggal_pengajuan'],
                                                    'detail' => 'Keperluan: ' . htmlspecialchars($skck['keperluan']),
                                                    'progres' => $skck['progres'],
                                                    'id' => $skck['id_skck'],
                                                    'detail_link' => 'detail_skck.php?id=' . $skck['id_skck'],
                                                    'hapus_link' => 'proses_hapus.php?jenis=skck&id=' . $skck['id_skck'],
                                                ];
                                            }
                                        }
                                        if ($jenis_filter == 'semua' || $jenis_filter == 'sik') {
                                            foreach ($data_sik as $sik) {
                                                $all_data[] = [
                                                    'jenis' => 'SIK',
                                                    'nama_pemohon' => $sik['nama_pemohon'],
                                                    'tanggal_pengajuan' => $sik['tanggal_pengajuan'],
                                                    'detail' => 'Instansi: ' . htmlspecialchars($sik['nama_instansi']) . '<br>Penanggung Jawab: ' . htmlspecialchars($sik['penanggung_jawab']),
                                                    'progres' => $sik['progres'],
                                                    'id' => $sik['id_sik'],
                                                    'detail_link' => 'detail_sik.php?id=' . $sik['id_sik'],
                                                    'hapus_link' => 'proses_hapus.php?jenis=sik&id=' . $sik['id_sik'],
                                                ];
                                            }
                                        }
                                        if ($jenis_filter == 'semua' || $jenis_filter == 'sttp') {
                                            foreach ($data_sttp as $sttp) {
                                                $all_data[] = [
                                                    'jenis' => 'STTP',
                                                    'nama_pemohon' => $sttp['nama_pemohon'],
                                                    'tanggal_pengajuan' => $sttp['tanggal_pengajuan'],
                                                    'detail' => 'Paslon: ' . htmlspecialchars($sttp['nama_paslon']) . '<br>Kampanye: ' . htmlspecialchars($sttp['nama_kampanye']),
                                                    'progres' => $sttp['progres'],
                                                    'id' => $sttp['id_sttp'],
                                                    'detail_link' => 'detail_sttp.php?id=' . $sttp['id_sttp'],
                                                    'hapus_link' => 'proses_hapus.php?jenis=sttp&id=' . $sttp['id_sttp'],
                                                ];
                                            }
                                        }

                                        // Urutkan berdasarkan tanggal_pengajuan DESC
                                        usort($all_data, function ($a, $b) {
                                            return strtotime($b['tanggal_pengajuan']) - strtotime($a['tanggal_pengajuan']);
                                        });

                                        $totalRows = count($all_data);
                                        $totalPages = ceil($totalRows / $perPage);

                                        $data_page = array_slice($all_data, $start, $perPage);

                                        $no = $start + 1;
                                        if (!empty($data_page)) {
                                            foreach ($data_page as $row) {
                                                // Badge class
                                                $progres = $row['progres'];
                                                $badge_class = '';
                                                if ($progres == 'pengajuan') $badge_class = 'bg-warning text-dark';
                                                elseif ($progres == 'penelitian') $badge_class = 'bg-info';
                                                elseif ($progres == 'diterima') $badge_class = 'bg-success';
                                                elseif ($progres == 'ditolak') $badge_class = 'bg-danger';
                                        ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td class="text-center"><?= $row['jenis']; ?></td>
                                                    <td><?= htmlspecialchars($row['nama_pemohon']); ?></td>
                                                    <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                    <td><?= $row['detail']; ?></td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $badge_class; ?>"><?= htmlspecialchars(ucfirst($progres)); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= $row['detail_link']; ?>" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-danger" onclick="hapusPermohonan('<?= $row['hapus_link']; ?>')" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data permohonan.</td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <!-- Pagination Preview -->
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap">
                                    <div>
                                        <small>
                                            Menampilkan
                                            <b><?= ($totalRows == 0) ? 0 : ($start + 1); ?></b>
                                            -
                                            <b><?= ($totalRows == 0) ? 0 : min($start + $perPage, $totalRows); ?></b>
                                            dari <b><?= $totalRows; ?></b> data
                                        </small>
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?jenis=<?= $jenis_filter; ?>&page=<?= $page - 1; ?>">Sebelumnya</a>
                                                </li>
                                            <?php endif; ?>
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?jenis=<?= $jenis_filter; ?>&page=<?= $i; ?>"><?= $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?jenis=<?= $jenis_filter; ?>&page=<?= $page + 1; ?>">Berikutnya</a>
                                                </li>
                                            <?php endif; ?>
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
        <?php include 'script.php'; ?>



        <script>
            function hapusPermohonan(hapusUrl) {
                // Cek apakah modal sudah ada
                let modal = document.getElementById('modalHapusPermohonan');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.innerHTML = `
            <div class="modal fade" id="modalHapusPermohonan" tabindex="-1" aria-labelledby="modalHapusPermohonanLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="modalHapusPermohonanLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data permohonan ini?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusPermohonan">Hapus</button>
                  </div>
                </div>
              </div>
            </div>
        `;
                    document.body.appendChild(modal);
                }

                // Tampilkan modal
                var bsModal = new bootstrap.Modal(document.getElementById('modalHapusPermohonan'));
                bsModal.show();

                // Set event tombol hapus
                document.getElementById('btnKonfirmasiHapusPermohonan').onclick = function() {
                    window.location = hapusUrl;
                };
            }
        </script>

        <script src="../assets/plugins/jquery/jquery.min.js">
            $(document).ready(function() {
                $('#tabelPermohonan').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                    },
                    "order": [
                        [3, "desc"]
                    ] // Mengurutkan berdasarkan tanggal pengajuan terbaru
                });
            });
        </script>

</body>

</html>