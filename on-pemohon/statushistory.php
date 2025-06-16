<?php
session_start();
include '../include/koneksi.php';

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];

// Ambil data SKCK user yang login
$sql_history_skck = "SELECT s.*, u.nama AS nama_pemohon, p.nama_pekerjaan, k.nama_kecamatan
                        FROM skck s
                        LEFT JOIN users u ON s.user_id = u.id_user
                        LEFT JOIN pekerjaan p ON s.pekerjaan_id = p.id_pekerjaan
                        LEFT JOIN kecamatan k ON s.kecamatan_id = k.id_kecamatan
                        WHERE s.user_id = '$user_id'
                        ORDER BY s.tanggal_pengajuan DESC";
$result_history_skck = mysqli_query($conn, $sql_history_skck);
$riwayat_skck = mysqli_fetch_all($result_history_skck, MYSQLI_ASSOC);

// Ambil data SIK user yang login
$sql_history_sik = "SELECT s.*
                    FROM sik s
                    WHERE s.user_id = '$user_id'
                    ORDER BY s.tanggal_pengajuan DESC";
$result_history_sik = mysqli_query($conn, $sql_history_sik);
$riwayat_sik = mysqli_fetch_all($result_history_sik, MYSQLI_ASSOC);

// Ambil data STTP user yang login
$sql_history_sttp = "SELECT s.*
                     FROM sttp s
                     WHERE s.user_id = '$user_id'
                     ORDER BY s.tanggal_pengajuan DESC";
$result_history_sttp = mysqli_query($conn, $sql_history_sttp);
$riwayat_sttp = mysqli_fetch_all($result_history_sttp, MYSQLI_ASSOC);

// Fungsi untuk menampilkan tracking steps
function displayTracking($progres)
{
    $progress_width = 0;
    if ($progres == 'pengajuan') {
        $progress_width = 25;
    } elseif ($progres == 'penelitian') {
        $progress_width = 50;
    } elseif ($progres == 'diterima' || $progres == 'ditolak') {
        $progress_width = 100;
    }
?>
    <div class="tracking-steps">
        <div class="line"></div>
        <div class="progress-line" style="width: <?php echo $progress_width; ?>%;"></div>
        <div class="step <?php if ($progres == 'pengajuan' || $progres == 'penelitian' || $progres == 'diterima' || $progres == 'ditolak') echo 'active'; ?>">
            <div class="step-circle">1</div>
            <div class="step-label">Pengajuan</div>
        </div>
        <div class="step <?php if ($progres == 'penelitian' || $progres == 'diterima' || $progres == 'ditolak') echo 'active'; ?>">
            <div class="step-circle <?php if ($progres == 'ditolak') echo 'bg-danger text-white'; ?>">2</div>
            <div class="step-label"><?php echo ($progres == 'ditolak') ? '<span class="text-danger fw-bold">Ditolak</span>' : 'Penelitian'; ?></div>
        </div>
        <div class="step <?php if ($progres == 'diterima') echo 'active'; ?>">
            <div class="step-circle">3</div>
            <div class="step-label">Berkas Diterima</div>
        </div>
    </div>
    <?php
    if ($progres == 'diterima') {
        echo '<p class="text-success fw-bold">Berkas sudah diterima. Silakan lihat di riwayat pengajuan untuk mengunduh jika tersedia.</p>';
    } elseif ($progres == 'ditolak') {
        echo '<p class="text-danger fw-bold">Pengajuan ditolak. Silakan periksa kembali dan cek pesan dari petugas pada notifikasi.</p>';
    } elseif ($progres == 'penelitian') {
        echo '<p class="text-info">Berkas sedang dalam tahap penelitian.</p>';
    } elseif ($progres == 'pengajuan') {
        echo '<p class="text-warning">Berkas baru diajukan dan menunggu penelitian.</p>';
    } else {
        echo '<p class="text-secondary">Status pengajuan Anda.</p>';
    }
}

$sql_latest_progres_skck = "SELECT 'SKCK' AS jenis_layanan, id_skck, progres, tanggal_pengajuan FROM skck WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_latest_progres_skck = mysqli_query($conn, $sql_latest_progres_skck);
$latest_progres_skck = mysqli_fetch_assoc($result_latest_progres_skck);

$sql_latest_progres_sik = "SELECT 'SIK' AS jenis_layanan, id_sik, progres, tanggal_pengajuan FROM sik WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_latest_progres_sik = mysqli_query($conn, $sql_latest_progres_sik);
$latest_progres_sik = mysqli_fetch_assoc($result_latest_progres_sik);

$sql_latest_progres_sttp = "SELECT 'STTP' AS jenis_layanan, id_sttp, progres, tanggal_pengajuan FROM sttp WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_latest_progres_sttp = mysqli_query($conn, $sql_latest_progres_sttp);
$latest_progres_sttp = mysqli_fetch_assoc($result_latest_progres_sttp);

$latest_progres = [];
if ($latest_progres_skck) {
    $latest_progres[] = $latest_progres_skck;
}
if ($latest_progres_sik) {
    $latest_progres[] = $latest_progres_sik;
}
if ($latest_progres_sttp) {
    $latest_progres[] = $latest_progres_sttp;
}

// Urutkan berdasarkan tanggal pengajuan (jika ada)
usort($latest_progres, function ($a, $b) {
    if (isset($a['tanggal_pengajuan']) && isset($b['tanggal_pengajuan'])) {
        return strtotime($b['tanggal_pengajuan']) - strtotime($a['tanggal_pengajuan']);
    }
    return 0;
});

// Fungsi untuk mendapatkan progres terbaru berdasarkan jenis layanan
function getLatestProgres($latest_progres_array, $jenis_layanan)
{
    foreach ($latest_progres_array as $progres_data) {
        if ($progres_data['jenis_layanan'] == $jenis_layanan) {
            return $progres_data['progres'];
        }
    }
    return null;
}

// Menampilkan pesan sukses jika ada
if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['success']); // Hapus pesan sukses setelah ditampilkan
    ?>
<?php // Menampilkan pesan error jika ada
elseif (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['error']); // Hapus pesan error setelah ditampilkan
    ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Dashboard - <?php echo $_SESSION['user_name']; ?></title>
    <style>
        .tracking-steps {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            position: relative;
            /* Make container relative for absolute positioning of lines */
        }

        .step {
            text-align: center;
            position: relative;
            flex-grow: 1;
            /* Distribute space evenly */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ced4da;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }

        .step.active .step-circle {
            background-color: #007bff;
        }

        .step-label {
            margin-top: 5px;
            font-size: small;
            color: #6c757d;
        }

        .step.active .step-label {
            color: #343a40;
            font-weight: bold;
        }

        .line {
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #ced4da;
            z-index: 0;
        }

        .progress-line {
            position: absolute;
            top: 15px;
            left: 0;
            height: 2px;
            background-color: #007bff;
            z-index: 1;
        }
    </style>
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
                    <img src="../assets/images/brand-logos/desktop-dark.png" class="desktop-dark" alt="logo">
                </a>
            </div>
            <div class="main-sidebar" id="sidebar-scroll">

                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <ul class="main-menu">
                        <li class="slide__category"><span class="category-name">Menu Utama</span></li>
                        <li class="slide">
                            <a href="dashboard.php" class="side-menu__item">
                                <i class="ti-home side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="pelayanan.php" class="side-menu__item">
                                <i class="ti-file side-menu__icon"></i>
                                <span class="side-menu__label">Pelayanan</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="statushistory.php" class="side-menu__item">
                                <i class="ti-list side-menu__icon"></i>
                                <span class="side-menu__label">Status dan Histroy</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="survey.php" class="side-menu__item">
                                <i class="ti-help-alt side-menu__icon"></i>
                                <span class="side-menu__label">Survey Kepuasan</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Status dan History</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Status Dan History</li>
                        </ol>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card p-3 mb-4">
                        <h5 class="text-dark fw-bold mb-3">Progres Pengajuan SKCK Anda</h5>
                        <?php
                        $sql_latest_skck = "SELECT id_skck, progres FROM skck WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
                        $result_latest_skck = mysqli_query($conn, $sql_latest_skck);
                        $latest_skck = mysqli_fetch_assoc($result_latest_skck);

                        if ($latest_skck && isset($latest_skck['progres'])) {
                            $progres_terbaru = $latest_skck['progres'];
                        ?>
                            <div class="tracking-steps">
                                <div class="line"></div>
                                <div class="progress-line" style="width: <?php
                                                                            if ($progres_terbaru == 'pengajuan') echo '25%';
                                                                            elseif ($progres_terbaru == 'penelitian' || $progres_terbaru == 'ditolak') echo '50%';
                                                                            elseif ($progres_terbaru == 'diterima') echo '100%';
                                                                            else echo '0%';
                                                                            ?>;"></div>
                                <div class="step <?php if ($progres_terbaru == 'pengajuan' || $progres_terbaru == 'penelitian' || $progres_terbaru == 'diterima' || $progres_terbaru == 'ditolak') echo 'active'; ?>">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">Pengajuan</div>
                                </div>
                                <div class="step <?php if ($progres_terbaru == 'penelitian' || $progres_terbaru == 'diterima' || $progres_terbaru == 'ditolak') echo 'active'; ?>">
                                    <div class="step-circle <?php if ($progres_terbaru == 'ditolak') echo 'bg-danger text-white'; ?>">2</div>
                                    <div class="step-label"><?php echo ($progres_terbaru == 'ditolak') ? '<span class="text-danger fw-bold">Ditolak</span>' : 'Penelitian'; ?></div>
                                </div>
                                <div class="step <?php if ($progres_terbaru == 'diterima') echo 'active'; ?>">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">Berkas Diterima</div>
                                </div>
                            </div>
                        <?php
                            if ($progres_terbaru == 'diterima') {
                                echo '<p class="text-success fw-bold">Berkas sudah diterima. Silakan lihat di riwayat pengajuan untuk mengunduh jika tersedia.</p>';
                            } elseif ($progres_terbaru == 'ditolak') {
                                echo '<p class="text-danger fw-bold">Pengajuan ditolak. Silakan periksa kembali dan cek pesan dari petugas pada notifikasi.</p>';
                                echo '<div class="mt-3"><a href="edit_data_skck.php?id_skck=' . $latest_skck['id_skck'] . '" class="btn btn-warning">Edit Data</a></div>';
                            } elseif ($progres_terbaru == 'penelitian') {
                                echo '<p class="text-info">Berkas sedang dalam tahap penelitian.</p>';
                            } elseif ($progres_terbaru == 'pengajuan') {
                                echo '<p class="text-warning">Berkas baru diajukan dan menunggu penelitian.</p>';
                            } else {
                                echo '<p class="text-secondary">Status pengajuan Anda.</p>';
                            }
                        } else {
                            echo '<p class="text-muted">Belum ada pengajuan SKCK.</p>';
                        }
                        ?>
                    </div>

                    <div class="card p-3 mb-4">
                        <h5 class="text-dark fw-bold mb-3">Progres Pengajuan SIK Anda</h5>
                        <?php
                        $sql_latest_sik = "SELECT id_sik, progres FROM sik WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
                        $result_latest_sik = mysqli_query($conn, $sql_latest_sik);
                        $latest_sik = mysqli_fetch_assoc($result_latest_sik);
                        if ($latest_sik && isset($latest_sik['progres'])) {
                            displayTracking($latest_sik['progres']);
                            if ($latest_sik['progres'] == 'ditolak') {
                                echo '<div class="mt-3"><a href="edit_data_sik.php?id_sik=' . $latest_sik['id_sik'] . '" class="btn btn-warning">Edit Data</a></div>';
                            }
                        } else {
                            echo '<p class="text-muted">Belum ada pengajuan SIK.</p>';
                        }
                        ?>
                    </div>

                    <div class="card p-3 mb-4">
                        <h5 class="text-dark fw-bold mb-3">Progres Pengajuan STTP Anda</h5>
                        <?php
                        $sql_latest_sttp = "SELECT id_sttp, progres FROM sttp WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
                        $result_latest_sttp = mysqli_query($conn, $sql_latest_sttp);
                        $latest_sttp = mysqli_fetch_assoc($result_latest_sttp);
                        if ($latest_sttp && isset($latest_sttp['progres'])) {
                            displayTracking($latest_sttp['progres']);
                            if ($latest_sttp['progres'] == 'ditolak') {
                                echo '<div class="mt-3"><a href="edit_data_sttp.php?id_sttp=' . $latest_sttp['id_sttp'] . '" class="btn btn-warning">Edit Data</a></div>';
                            }
                        } else {
                            echo '<p class="text-muted">Belum ada pengajuan STTP.</p>';
                        }
                        ?>
                    </div>

                    <div class="card p-3">
                        <h5 class="text-dark fw-bold mb-3">Riwayat Pengajuan SKCK</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Keperluan</th>
                                        <th>Status</th>
                                        <th>Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($riwayat_skck)): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($riwayat_skck as $skck): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($skck['tanggal_pengajuan'])); ?></td>
                                                <td><?php echo htmlspecialchars($skck['keperluan']); ?></td>
                                                <td>
                                                    <?php
                                                    $progres = $skck['progres'];
                                                    if ($progres == 'pengajuan') {
                                                        echo '<span class="badge bg-warning text-dark">Pengajuan</span>';
                                                    } elseif ($progres == 'penelitian') {
                                                        echo '<span class="badge bg-info">Penelitian</span>';
                                                    } elseif ($progres == 'diterima') {
                                                        echo '<span class="badge bg-success">Diterima</span>';
                                                    } elseif ($progres == 'ditolak') {
                                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $bukti = $skck['bukti'];
                                                    if ($bukti == 'bisa diambil') : ?>
                                                        <a href="cetak_bukti_skck.php?id=<?php echo $skck['id_skck']; ?>" class="btn btn-success btn-sm" target="_blank">Cetak Bukti Pengambilan</a>
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada riwayat pengajuan SKCK.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card p-3">
                        <h5 class="text-dark fw-bold mb-3">Riwayat Pengajuan SIK</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Nama Instansi</th>
                                        <th>Nama Penanggung Jawab</th>
                                        <th>Status</th>
                                        <th>Berkas Online</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($riwayat_sik)): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($riwayat_sik as $sik): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($sik['tanggal_pengajuan'])); ?></td>
                                                <td><?php echo htmlspecialchars($sik['nama_instansi']); ?></td>
                                                <td><?php echo htmlspecialchars($sik['penanggung_jawab']); ?></td>
                                                <td>
                                                    <?php
                                                    $progres = $sik['progres'];
                                                    if ($progres == 'pengajuan') {
                                                        echo '<span class="badge bg-warning text-dark">Pengajuan</span>';
                                                    } elseif ($progres == 'penelitian') {
                                                        echo '<span class="badge bg-info">Penelitian</span>';
                                                    } elseif ($progres == 'diterima') {
                                                        echo '<span class="badge bg-success">Diterima</span>';
                                                    } elseif ($progres == 'ditolak') {
                                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sik['berkasOnline'])): ?>
                                                        <a href="../uploads/berkasOnline/<?php echo htmlspecialchars($sik['berkasOnline']); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-primary btn-sm d-inline-flex align-items-center" 
                                                           style="background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%); border: none; border-radius: 20px; font-weight: bold;">
                                                            <i class="ti-download me-1"></i> Lihat Berkas
                                                        </a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada riwayat pengajuan SIK.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card p-3">
                        <h5 class="text-dark fw-bold mb-3">Riwayat Pengajuan STTP</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Nama Paslon</th>
                                        <th>Nama Kampanye</th>
                                        <th>Status</th>
                                        <th>Berkas Online</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($riwayat_sttp)): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($riwayat_sttp as $sttp): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($sttp['tanggal_pengajuan'])); ?></td>
                                                <td><?php echo htmlspecialchars($sttp['nama_paslon']); ?></td>
                                                <?php
                                                // Ambil nama kampanye dari tabel kampanye berdasarkan kampanye_id
                                                $nama_kampanye = '-';
                                                if (!empty($sttp['kampanye_id'])) {
                                                    $kampanye_id = $sttp['kampanye_id'];
                                                    $query_kampanye = mysqli_query($conn, "SELECT nama_kampanye FROM kampanye WHERE id_kampanye = '$kampanye_id' LIMIT 1");
                                                    if ($row_kampanye = mysqli_fetch_assoc($query_kampanye)) {
                                                        $nama_kampanye = htmlspecialchars($row_kampanye['nama_kampanye']);
                                                    }
                                                }
                                                ?>
                                                <td><?php echo $nama_kampanye; ?></td>
                                                <td>
                                                    <?php
                                                    $progres = $sttp['progres'];
                                                    if ($progres == 'pengajuan') {
                                                        echo '<span class="badge bg-warning text-dark">Pengajuan</span>';
                                                    } elseif ($progres == 'penelitian') {
                                                        echo '<span class="badge bg-info">Penelitian</span>';
                                                    } elseif ($progres == 'diterima') {
                                                        echo '<span class="badge bg-success">Diterima</span>';
                                                    } elseif ($progres == 'ditolak') {
                                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sttp['berkasOnline'])): ?>
                                                        <a href="../uploads/berkasOnline/<?php echo htmlspecialchars($sttp['berkasOnline']); ?>" target="_blank">Lihat Berkas</a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada riwayat pengajuan STTP.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

</body>

</html>