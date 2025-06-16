<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}

$id_sttp = isset($_GET['id_sttp']) ? intval($_GET['id_sttp']) : 0;
if ($id_sttp <= 0) {
    echo "ID STTP tidak valid.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM sttp WHERE id_sttp = '$id_sttp'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sttp = mysqli_real_escape_string($conn, $_POST['id_sttp'] ?? '');
    $user_id = $_SESSION['user_id'];
    $nama_paslon = mysqli_real_escape_string($conn, $_POST['nama_paslon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $penanggung_jawab = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);
    $kampanye_id = mysqli_real_escape_string($conn, $_POST['kampanye_id']);
    $tgl_kampanye = mysqli_real_escape_string($conn, $_POST['tgl_kampanye']);
    $tempat = mysqli_real_escape_string($conn, $_POST['tempat']);
    $kecamatan_id = mysqli_real_escape_string($conn, $_POST['kecamatan_id']);
    $jumlah_peserta = mysqli_real_escape_string($conn, $_POST['jumlah_peserta']);
    $nama_jurkam = mysqli_real_escape_string($conn, $_POST['nama_jurkam']);
    $memperhatikan = mysqli_real_escape_string($conn, $_POST['memperhatikan']);
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

    if (!empty($id_sttp)) {
        // UPDATE
        $sql = "UPDATE sttp SET 
                user_id='$user_id',
                nama_paslon='$nama_paslon',
                penanggung_jawab='$penanggung_jawab',
                kampanye_id='$kampanye_id',
                alamat='$alamat',
                tgl_kampanye='$tgl_kampanye',
                tempat='$tempat',
                kecamatan_id='$kecamatan_id',
                jumlah_peserta='$jumlah_peserta',
                nama_jurkam='$nama_jurkam',
                memperhatikan='$memperhatikan',
                tanggal_pengajuan='$tanggal_pengajuan',
                progres='$progres'
                    " . (!empty($lampiran) ? ", lampiran='$lampiran'" : "") . "
                WHERE id_sttp='$id_sttp'";
    } else {
        // INSERT
        $sql = "INSERT INTO sttp (
                    user_id, nama_paslon, penanggung_jawab, kampanye_id,
                alamat, tgl_kampanye, tempat, kecamatan_id , jumlah_peserta, 
                nama_jurkam, memperhatikan, progres, lampiran, tanggal_pengajuan
                ) VALUES (
                    '$user_id', '$nama_paslon', '$penanggung_jawab', '$kampanye_id', 
                '$alamat', '$tgl_kampanye', '$tempat', '$kecamatan_id', '$jumlah_peserta',
                '$nama_jurkam', '$memperhatikan', '$progres', '$lampiran', '$tanggal_pengajuan'
                )";
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
    <title>Dashboard - <?= htmlspecialchars($_SESSION['username']) ?></title>
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
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Update STTP</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Update STTP</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_sttp" value="<?php echo htmlspecialchars($data['id_sttp']); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="nama_paslon" class="form-label">Nama Pasangan Calon</label>
                                                    <input type="text" class="form-control" id="nama_paslon" name="nama_paslon" required value="<?php echo htmlspecialchars($data['nama_paslon']); ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                                    <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" required value="<?php echo htmlspecialchars($data['penanggung_jawab']); ?>">
                                                </div>

                                                <div class="col-md-12">
                                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="kampanye_id" class="form-label">Bentuk Kampanye</label>
                                                    <select class="form-control" id="kampanye_id" name="kampanye_id" required>
                                                        <option value="">-- Pilih Bentuk Kampanye --</option>
                                                        <?php
                                                        $queryKampanye = mysqli_query($conn, "SELECT * FROM kampanye ORDER BY nama_kampanye ASC");
                                                        while ($row = mysqli_fetch_assoc($queryKampanye)) {
                                                            $selected = ($row['id_kampanye'] == $data['kampanye_id']) ? 'selected' : '';
                                                            echo '<option value="' . htmlspecialchars($row['id_kampanye']) . '" ' . $selected . '>' . htmlspecialchars($row['nama_kampanye']) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="tgl_kampanye" class="form-label">Tanggal Kampanye</label>
                                                    <input type="date" class="form-control" id="tgl_kampanye" name="tgl_kampanye" required value="<?php echo htmlspecialchars($data['tgl_kampanye']); ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="tempat" class="form-label">Tempat</label>
                                                    <input type="text" class="form-control" id="tempat" name="tempat" required value="<?php echo htmlspecialchars($data['tempat']); ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="kecamatan_id" class="form-label">Kecamatan</label>
                                                    <select class="form-control" id="kecamatan_id" name="kecamatan_id" required>
                                                        <option value="">-- Pilih Kecamatan --</option>
                                                        <?php
                                                        $queryKecamatan = mysqli_query($conn, "SELECT * FROM kecamatan");
                                                        while ($row = mysqli_fetch_assoc($queryKecamatan)) {
                                                            $selected = ($row['id_kecamatan'] == $data['kecamatan_id']) ? 'selected' : '';
                                                            echo '<option value="' . htmlspecialchars($row['id_kecamatan']) . '" ' . $selected . '>' . htmlspecialchars($row['nama_kecamatan']) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="jumlah_peserta" class="form-label">Jumlah Peserta</label>
                                                    <input type="text" class="form-control" id="jumlah_peserta" name="jumlah_peserta" required value="<?php echo htmlspecialchars($data['jumlah_peserta']); ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="nama_jurkam" class="form-label">Nama Juru Kamera</label>
                                                    <input type="text" class="form-control" id="nama_jurkam" name="nama_jurkam" required value="<?php echo htmlspecialchars($data['nama_jurkam']); ?>">
                                                </div>

                                                <input type="hidden" id="memperhatikan" name="memperhatikan" value="<?php echo htmlspecialchars($data['memperhatikan']); ?>">

                                                <input type="hidden" id="progres" name="progres" value="penelitian">

                                                <div class="col-md-12">
                                                    <label for="lampiran" class="form-label">Lampiran</label>
                                                    <?php if (!empty($data['lampiran'])): ?>
                                                        <div class="mb-2">
                                                            <a href="../uploads/<?php echo htmlspecialchars($data['lampiran']); ?>" target="_blank">Lihat Lampiran Saat Ini</a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah lampiran.</small>
                                                </div>

                                                <div class="col-12 mt-4">
                                                    <button type="submit" class="btn btn-primary w-100">Update STTP</button>
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
