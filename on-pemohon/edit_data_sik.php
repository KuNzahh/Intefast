<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}

$id_sik = isset($_GET['id_sik']) ? intval($_GET['id_sik']) : 0;
if ($id_sik <= 0) {
    echo "ID SIK tidak valid.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM sik WHERE id_sik = '$id_sik'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

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

    $lampiran_baru = '';
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext)) {
            $lampiran_baru = uniqid('lampiran_', true) . '.' . $ext;
            $upload_path = '../uploads/' . $lampiran_baru;
            if (!move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                $_SESSION['error'] = 'Gagal mengupload lampiran baru.';
                header("Location: edit_data_sik.php?id_sik=$id_sik");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Format lampiran tidak valid.';
            header("Location: edit_data_sik.php?id_sik=$id_sik");
            exit();
        }
    }

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

    if (!empty($lampiran_baru)) {
        $sql .= ", lampiran='$lampiran_baru'";
    }

    $run = mysqli_query($conn, $sql);

    if ($run) {
        $_SESSION['success'] = 'Data berhasil diperbarui.';
        header('Location: statushistory.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal memperbarui data: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Dashboard - <?= htmlspecialchars($_SESSION['user_name']) ?></title>
</head>

<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

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
                        <h2 class="main-content-title fs-24 mb-1">Pelayanan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Update SIK</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Update SIK</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_sik" value="<?= $data['id_sik'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                                            <input type="hidden" name="tanggal_pengajuan" value="<?= htmlspecialchars($data['tanggal_pengajuan']) ?>">
                                            <input type="hidden" name="dasar" value="<?= isset($data['dasar']) ? htmlspecialchars($data['dasar']) : 'Belum ada Dasar, Baca Lampiran' ?>">
                                            <input type="hidden" name="progres" value="penelitian">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="nama_instansi" class="form-label">Nama Instansi</label>
                                                    <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" value="<?= htmlspecialchars($data['nama_instansi'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                                    <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" value="<?= htmlspecialchars($data['penanggung_jawab'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="pekerjaan_id" class="form-label">Pekerjaan</label>
                                                    <select class="form-control" id="pekerjaan_id" name="pekerjaan_id" required>
                                                        <option value="">-- Pilih Pekerjaan --</option>
                                                        <?php
                                                        $queryPekerjaan = mysqli_query($conn, "SELECT * FROM pekerjaan");
                                                        while ($row = mysqli_fetch_assoc($queryPekerjaan)) {
                                                            $selected = ($row['id_pekerjaan'] == ($data['pekerjaan_id'] ?? '')) ? 'selected' : '';
                                                            echo '<option value="' . $row['id_pekerjaan'] . '" ' . $selected . '>' . $row['nama_pekerjaan'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="no_telp" class="form-label">No. Telepon</label>
                                                    <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= htmlspecialchars($data['no_telp'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="keramaian_id" class="form-label">Jenis Keramaian</label>
                                                    <select class="form-control" id="keramaian_id" name="keramaian_id" required>
                                                        <option value="">-- Pilih Jenis Keramaian --</option>
                                                        <?php
                                                        $queryKeramaian = mysqli_query($conn, "SELECT * FROM jeniskeramaian");
                                                        while ($row = mysqli_fetch_assoc($queryKeramaian)) {
                                                            $selected = ($row['id_keramaian'] == ($data['keramaian_id'] ?? '')) ? 'selected' : '';
                                                            echo '<option value="' . $row['id_keramaian'] . '" ' . $selected . '>' . $row['nama_keramaian'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="tgl_kegiatan" class="form-label">Tanggal Kegiatan</label>
                                                    <input type="date" class="form-control" id="tgl_kegiatan" name="tgl_kegiatan" value="<?= htmlspecialchars($data['tgl_kegiatan'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="tempat" class="form-label">Tempat Kegiatan</label>
                                                    <input type="text" class="form-control" id="tempat" name="tempat" value="<?= htmlspecialchars($data['tempat'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="kecamatan_id" class="form-label">Kecamatan</label>
                                                    <select class="form-control" id="kecamatan_id" name="kecamatan_id" required>
                                                        <option value="">-- Pilih Kecamatan --</option>
                                                        <?php
                                                        $queryKecamatan = mysqli_query($conn, "SELECT * FROM kecamatan");
                                                        while ($row = mysqli_fetch_assoc($queryKecamatan)) {
                                                            $selected = ($row['id_kecamatan'] == ($data['kecamatan_id'] ?? '')) ? 'selected' : '';
                                                            echo '<option value="' . $row['id_kecamatan'] . '" ' . $selected . '>' . $row['nama_kecamatan'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="rangka" class="form-label">Rangka Acara</label>
                                                    <input type="text" class="form-control" id="rangka" name="rangka" value="<?= htmlspecialchars($data['rangka'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="peserta" class="form-label">Jumlah Peserta</label>
                                                    <input type="number" class="form-control" id="peserta" name="peserta" value="<?= htmlspecialchars($data['peserta'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="lampiran" class="form-label">Lampiran Baru (Opsional)</label>
                                                    <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="text-muted">Kosongkan jika tidak ingin mengganti.</small>
                                                </div>
                                                <div class="col-12 text-end mt-3">
                                                    <button type="submit" class="btn btn-success">Update Data</button>
                                                    <a href="statushistory.php" class="btn btn-secondary">Batal</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'foot.php'; ?>
        <?php include 'script.php'; ?>
    </div>
</body>

</html>