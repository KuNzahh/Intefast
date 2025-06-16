<?php
session_start();
include '../include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sik = mysqli_real_escape_string($conn, $_POST['id_sik'] ?? '');
    $user_id = $_SESSION['user_id'];
    $nama_instansi = mysqli_real_escape_string($conn, $_POST['nama_instansi']);
    $penanggung_jawab = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);
    $pekerjaan = mysqli_real_escape_string($conn, $_POST['pekerjaan_id']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $keramaian_id = mysqli_real_escape_string($conn, $_POST['keramaian_id']);
    $tgl_kegiatan = mysqli_real_escape_string($conn, $_POST['tgl_kegiatan']);
    $tempat = mysqli_real_escape_string($conn, $_POST['tempat']);
    $kecamatan_id = mysqli_real_escape_string($conn, $_POST['kecamatan_id']);
    $rangka = mysqli_real_escape_string($conn, $_POST['rangka']);
    $peserta = mysqli_real_escape_string($conn, $_POST['peserta']);
    $dasar = mysqli_real_escape_string($conn, $_POST['dasar'] ?? '');
    $progres = mysqli_real_escape_string($conn, $_POST['progres'] ?? '');
    $tanggal_pengajuan = mysqli_real_escape_string($conn, $_POST['tanggal_pengajuan'] ?? date('Y-m-d'));

    $lampiran = '';
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('lampiran_', true) . '.' . $file_ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                $lampiran = $new_filename;
            } else {
                $_SESSION['error'] = 'Gagal mengupload lampiran.';
                header('Location: statushistory.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Format lampiran tidak valid.';
            header('Location: statushistory.php');
            exit();
        }
    }

    if (!empty($id_sik)) {
        // UPDATE
        $sql = "UPDATE sik SET 
                    user_id='$user_id',
                    nama_instansi='$nama_instansi',
                    penanggung_jawab='$penanggung_jawab',
                    pekerjaan_id='$pekerjaan',
                    alamat='$alamat',
                    no_telp='$no_telp',
                    keramaian_id='$keramaian_id',
                    tgl_kegiatan='$tgl_kegiatan',
                    tempat='$tempat',
                    kecamatan_id='$kecamatan_id',
                    rangka='$rangka',
                    peserta='$peserta',
                    tanggal_pengajuan='$tanggal_pengajuan',
                    dasar='$dasar',
                    progres='$progres'
                    " . (!empty($lampiran) ? ", lampiran='$lampiran'" : "") . "
                WHERE id_sik='$id_sik'";
    } else {
        // INSERT
        $sql = "INSERT INTO sik (
                user_id, nama_instansi, penanggung_jawab, pekerjaan_id, alamat, no_telp,
                keramaian_id, tgl_kegiatan, tempat, kecamatan_id, rangka, peserta, dasar, progres, lampiran, tanggal_pengajuan
            ) VALUES (
                '$user_id', '$nama_instansi', '$penanggung_jawab', '$pekerjaan', '$alamat', '$no_telp',
                '$keramaian_id', '$tgl_kegiatan', '$tempat', '$kecamatan_id', '$rangka', '$peserta', '$dasar', '$progres', '$lampiran', '$tanggal_pengajuan'
            )";
        }

    $run = mysqli_query($conn, $sql);

    $_SESSION[$run ? 'success' : 'error'] = $run
        ? (!empty($id_sik) ? 'Data berhasil di-update' : 'Data berhasil dikirim')
        : (!empty($id_sik) ? 'Gagal update data' : 'Gagal tambah data');

    header('Location: statushistory.php'); // <--- ini diganti
    exit();
}

// Handle delete
if (isset($_GET['hapus'])) {
    $id_sik = mysqli_real_escape_string($conn, $_GET['hapus']);
    $del = mysqli_query($conn, "DELETE FROM sik WHERE id_sik='$id_sik'");
    $_SESSION[$del ? 'success' : 'error'] = $del
        ? 'Data berhasil dihapus' : 'Gagal hapus data';
    header('Location: statushistory.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Dashboard - <?php echo $_SESSION['user_name']; ?></title>
</head>

<body>
    <!-- Loader -->
    <div id="loader">
        <img src="../assets/images/media/media-79.svg" alt="">
    </div>
    <!-- Loader -->

    <div class="page">
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
                    <img src="../assets/images/brand-logos/desktop-dark.png" class="desktop-dark" alt="logo">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <ul class="main-menu">
                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">Menu Utama</span></li>
                        <!-- End::slide__category -->

                        <!-- Dashboard -->
                        <li class="slide">
                            <a href="dashboard.php" class="side-menu__item">
                                <i class="ti-home side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>

                        <!-- Buat Permohonan -->
                        <li class="slide">
                            <a href="pelayanan.php" class="side-menu__item">
                                <i class="ti-file side-menu__icon"></i>
                                <span class="side-menu__label">Pelayanan</span>
                            </a>
                        </li>

                        <!-- Riwayat Permohonan -->
                        <li class="slide">
                            <a href="statushistory.php" class="side-menu__item">
                                <i class="ti-list side-menu__icon"></i>
                                <span class="side-menu__label">Status dan Histroy</span>
                            </a>
                        </li>

                        <!-- Bantuan -->
                        <li class="slide">
                            <a href="survey.php" class="side-menu__item">
                                <i class="ti-help-alt side-menu__icon"></i>
                                <span class="side-menu__label">Survey Kepuasan</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End::nav -->
            </div>
            <!-- End::main-sidebar -->
        </aside>
        <!-- End::app-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Pelayanan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Ajuan SIK</li>
                        </ol>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::Formulir Pengajuan Sik -->
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Pengajuan Sik</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_sik" value="<?php echo $_GET['id_sik'] ?? ''; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="nama_instansi" class="form-label">Nama Instansi</label>
                                                    <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                                    <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="pekerjaan_id" class="form-label">Pekerjaan</label>
                                                    <select class="form-control" id="pekerjaan_id" name="pekerjaan_id" required>
                                                        <option value="">-- Pilih Pekerjaan --</option>
                                                        <?php
                                                        $queryPekerjaan = mysqli_query($conn, "SELECT * FROM pekerjaan");
                                                        while ($row = mysqli_fetch_assoc($queryPekerjaan)) {
                                                            echo '<option value="' . $row['id_pekerjaan'] . '">' . $row['nama_pekerjaan'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="no_telp" class="form-label">No. Telepon</label>
                                                    <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="keramaian_id" class="form-label">Jenis Keramaian</label>
                                                    <select class="form-control" id="keramaian_id" name="keramaian_id" required>
                                                        <option value="">-- Pilih Jenis Keramaaian --</option>
                                                        <?php
                                                        $queryKeramaian = mysqli_query($conn, "SELECT * FROM jeniskeramaian");
                                                        while ($row = mysqli_fetch_assoc($queryKeramaian)) {
                                                            echo '<option value="' . $row['id_keramaian'] . '">' . $row['nama_keramaian'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="tgl_kegiatan" class="form-label">Tanggal Kegiatan</label>
                                                    <input type="date" class="form-control" id="tgl_kegiatan" name="tgl_kegiatan" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="tempat" class="form-label">Tempat Kegiatan</label>
                                                    <input type="text" class="form-control" id="tempat" name="tempat" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="kecamatan_id" class="form-label">Kecamatan</label>
                                                    <select class="form-control" id="kecamatan_id" name="kecamatan_id" required>
                                                        <option value="">-- Pilih Kecamatan --</option>
                                                        <?php
                                                        $queryKecamatan = mysqli_query($conn, "SELECT * FROM kecamatan");
                                                        while ($row = mysqli_fetch_assoc($queryKecamatan)) {
                                                            echo '<option value="' . $row['id_kecamatan'] . '">' . $row['nama_kecamatan'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="rangka" class="form-label">Rangka Acara</label>
                                                    <input type="text" class="form-control" id="rangka" name="rangka" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="peserta" class="form-label">Jumlah Peserta</label>
                                                    <input type="number" class="form-control" id="peserta" name="peserta" required>
                                                </div>

                                                <input type="hidden" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan" value="<?php echo date('Y-m-d'); ?>">

                                                <input type="hidden" id="dasar" name="dasar" value="Belum ada Dasar, Baca Lampiran">
                                                <input type="hidden" id="progres" name="progres" value="penelitian">
                                                <div class="col-md-12">
                                                    <label for="lampiran" class="form-label">Lampiran</label>
                                                    <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="col-12 text-end mt-3">
                                                    <button type="submit" class="btn btn-primary">Ajukan SIK</button>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Formulir -->
            </div>
        </div>
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="fe fe-arrow-up"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

    <!-- Footer Start -->
    <footer class="footer mt-auto py-3 bg-white text-center">
        <div class="container">
            <?php include 'foot.php'; ?>
        </div>
    </footer>
    <!-- Footer End -->

    <?php include 'script.php'; ?>

</body>

</html>