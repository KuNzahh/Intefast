<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}

$id_skck = isset($_GET['id_skck']) ? intval($_GET['id_skck']) : 0;
if ($id_skck <= 0) {
    echo "ID SKCK tidak valid.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM skck WHERE id_skck = '$id_skck'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama           = mysqli_real_escape_string($conn, $_POST['nama']);
    $nik            = mysqli_real_escape_string($conn, $_POST['nik']);
    $tempat_lahir   = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir  = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $kebangsaan     = mysqli_real_escape_string($conn, $_POST['kebangsaan']);
    $pekerjaan_id   = mysqli_real_escape_string($conn, $_POST['pekerjaan_id']);
    $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
    $agama          = mysqli_real_escape_string($conn, $_POST['agama']);
    $kecamatan_id   = mysqli_real_escape_string($conn, $_POST['kecamatan_id']);
    $tanggal_pengajuan = mysqli_real_escape_string($conn, $_POST['tanggal_pengajuan']);
    $keperluan      = mysqli_real_escape_string($conn, $_POST['keperluan']);

    $lampiran_baru = '';
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext)) {
            $lampiran_baru = uniqid('lampiran_', true) . '.' . $ext;
            $upload_path = '../uploads/' . $lampiran_baru;
            if (!move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                $_SESSION['error'] = 'Gagal mengupload lampiran baru.';
                header("Location: edit_data_skck.php?id_skck=$id_skck");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Format lampiran tidak valid.';
            header("Location: edit_data_skck.php?id_skck=$id_skck");
            exit();
        }
    }

    $sql = "UPDATE skck SET
        nama='$nama',
        nik='$nik',
        tempat_lahir='$tempat_lahir',
        tanggal_lahir='$tanggal_lahir',
        kebangsaan='$kebangsaan',
        pekerjaan_id='$pekerjaan_id',
        alamat='$alamat',
        agama='$agama',
        kecamatan_id='$kecamatan_id',
        tanggal_pengajuan='$tanggal_pengajuan',
        keperluan='$keperluan',
        progres='penelitian'";

    if (!empty($lampiran_baru)) {
        $sql .= ", lampiran='$lampiran_baru'";
    }

    $sql .= " WHERE id_skck='$id_skck'";

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
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Update SKCK</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Update SKCK</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_skck" value="<?= $data['id_skck'] ?>">
                                            <input type="hidden" name="tanggal_pengajuan" value="<?= $data['tanggal_pengajuan'] ?>">

                                            <div class="mb-3">
                                                <label>Nama</label>
                                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>NIK</label>
                                                <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($data['nik']) ?>" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label>Tempat Lahir</label>
                                                    <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data['tempat_lahir']) ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Tanggal Lahir</label>
                                                    <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label>Kebangsaan</label>
                                                <input type="text" name="kebangsaan" class="form-control" value="<?= htmlspecialchars($data['kebangsaan']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Pekerjaan</label>
                                                <select name="pekerjaan_id" class="form-control" required>
                                                    <?php
                                                    $q = mysqli_query($conn, "SELECT * FROM pekerjaan");
                                                    while ($p = mysqli_fetch_assoc($q)) {
                                                        $sel = $p['id_pekerjaan'] == $data['pekerjaan_id'] ? 'selected' : '';
                                                        echo "<option value='{$p['id_pekerjaan']}' $sel>{$p['nama_pekerjaan']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Alamat</label>
                                                <textarea name="alamat" class="form-control" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Agama</label>
                                                <select name="agama" class="form-control" required>
                                                    <?php
                                                    $agama_arr = ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
                                                    foreach ($agama_arr as $ag) {
                                                        $sel = ($data['agama'] == $ag) ? 'selected' : '';
                                                        echo "<option value='$ag' $sel>$ag</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Kecamatan</label>
                                                <select name="kecamatan_id" class="form-control" required>
                                                    <?php
                                                    $q = mysqli_query($conn, "SELECT * FROM kecamatan");
                                                    while ($k = mysqli_fetch_assoc($q)) {
                                                        $sel = ($k['id_kecamatan'] == $data['kecamatan_id']) ? 'selected' : '';
                                                        echo "<option value='{$k['id_kecamatan']}' $sel>{$k['nama_kecamatan']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Keperluan</label>
                                                <textarea name="keperluan" class="form-control" rows="2" required><?= htmlspecialchars($data['keperluan']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Lampiran Baru (Opsional)</label>
                                                <input type="file" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="text-muted">Kosongkan jika tidak ingin mengganti.</small>
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" class="btn btn-success">Update Data</button>
                                                <a href="statushistory.php" class="btn btn-secondary">Batal</a>
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
