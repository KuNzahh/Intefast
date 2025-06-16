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
$sql_sttp = "SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, s.nama_paslon, s.nama_kampanye, s.progres, s.id_sttp
             FROM sttp s
             JOIN users u ON s.user_id = u.id_user
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
                                            <a href="data_pegajuan.php" class="btn btn-secondary"><i class="fas fa-undo me-2"></i>Reset Filter</a>
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
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Nama Pemohon</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Detail</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';

                                        if ($jenis_filter == 'semua' || $jenis_filter == 'skck'):
                                            if (!empty($data_skck)):
                                                foreach ($data_skck as $skck): ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td class="text-center">SKCK</td>
                                                        <td><?php echo htmlspecialchars($skck['nama_pemohon']); ?></td>
                                                        <td class="text-center"><?php echo date('d-m-Y', strtotime($skck['tanggal_pengajuan'])); ?></td>
                                                        <td>Keperluan: <?php echo htmlspecialchars($skck['keperluan']); ?></td>
                                                        <td class="text-center">
                                                            <?php
                                                            $progres = $skck['progres'];
                                                            $badge_class = '';
                                                            if ($progres == 'pengajuan') $badge_class = 'bg-warning text-dark';
                                                            elseif ($progres == 'penelitian') $badge_class = 'bg-info';
                                                            elseif ($progres == 'diterima') $badge_class = 'bg-success';
                                                            elseif ($progres == 'ditolak') $badge_class = 'bg-danger';
                                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars(ucfirst($progres)) . '</span>';
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="detail_skck.php?id=<?php echo $skck['id_skck']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                            <a href="proses_hapus.php?jenis=skck&id=<?= $skck['id_skck']; ?>" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;
                                            else:
                                                if ($jenis_filter == 'skck'): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Tidak ada data permohonan SKCK.</td>
                                                    </tr>
                                                <?php endif;
                                            endif;
                                        endif;

                                        if ($jenis_filter == 'semua' || $jenis_filter == 'sik'):
                                            if (!empty($data_sik)):
                                                foreach ($data_sik as $sik): ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td class="text-center">SIK</td>
                                                        <td><?php echo htmlspecialchars($sik['nama_pemohon']); ?></td>
                                                        <td class="text-center"><?php echo date('d-m-Y', strtotime($sik['tanggal_pengajuan'])); ?></td>
                                                        <td>Paslon: <?php echo htmlspecialchars($sik['nama_instansi']); ?><br>Kampanye: <?php echo htmlspecialchars($sik['penanggung_jawab']); ?></td>
                                                        <td class="text-center">
                                                            <?php
                                                            $progres = $sik['progres'];
                                                            $badge_class = '';
                                                            if ($progres == 'pengajuan') $badge_class = 'bg-warning text-dark';
                                                            elseif ($progres == 'penelitian') $badge_class = 'bg-info';
                                                            elseif ($progres == 'diterima') $badge_class = 'bg-success';
                                                            elseif ($progres == 'ditolak') $badge_class = 'bg-danger';
                                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars(ucfirst($progres)) . '</span>';
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="detail_sik.php?id=<?php echo $sik['id_sik']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                            <a href="proses_hapus.php?jenis=sik&id=<?= $sik['id_sik']; ?>" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>

                                                        </td>
                                                    </tr>
                                                <?php endforeach;
                                            else:
                                                if ($jenis_filter == 'sik'): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Tidak ada data permohonan SIK.</td>
                                                    </tr>
                                                <?php endif;
                                            endif;
                                        endif;

                                        if ($jenis_filter == 'semua' || $jenis_filter == 'sttp'):
                                            if (!empty($data_sttp)):
                                                foreach ($data_sttp as $sttp): ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td class="text-center">STTP</td>
                                                        <td><?php echo htmlspecialchars($sttp['nama_pemohon']); ?></td>
                                                        <td class="text-center"><?php echo date('d-m-Y', strtotime($sttp['tanggal_pengajuan'])); ?></td>
                                                        <td>Paslon: <?php echo htmlspecialchars($sttp['nama_paslon']); ?><br>Kampanye: <?php echo htmlspecialchars($sttp['nama_kampanye']); ?></td>
                                                        <td class="text-center">
                                                            <?php
                                                            $progres = $sttp['progres'];
                                                            $badge_class = '';
                                                            if ($progres == 'pengajuan') $badge_class = 'bg-warning text-dark';
                                                            elseif ($progres == 'penelitian') $badge_class = 'bg-info';
                                                            elseif ($progres == 'diterima') $badge_class = 'bg-success';
                                                            elseif ($progres == 'ditolak') $badge_class = 'bg-danger';
                                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars(ucfirst($progres)) . '</span>';
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="detail_sttp.php?id=<?php echo $sttp['id_sttp']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                            <a href="proses_hapus.php?jenis=sttp&id=<?= $sttp['id_sttp']; ?>" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;
                                            else:
                                                if ($jenis_filter == 'sttp'): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Tidak ada data permohonan STTP.</td>
                                                    </tr>
                                            <?php endif;
                                            endif;
                                        endif;

                                        if ($jenis_filter == 'semua' && empty($data_skck) && empty($data_sik) && empty($data_sttp)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data permohonan.</td>
                                            </tr>
                                        <?php endif;
                                        ?>
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
        <?php include 'script.php'; ?>

        <script>
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