<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Tangkap data
    $id_skck            = mysqli_real_escape_string($conn, $_POST['id_skck'] ?? '');
    $user_id            = $_SESSION['user_id'];
    $nama               = mysqli_real_escape_string($conn, $_POST['nama']);
    $tempat_lahir       = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir      = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $kebangsaan         = mysqli_real_escape_string($conn, $_POST['kebangsaan']);
    $pekerjaan_id       = mysqli_real_escape_string($conn, $_POST['pekerjaan_id']);
    $alamat             = mysqli_real_escape_string($conn, $_POST['alamat']);
    $agama             = mysqli_real_escape_string($conn, $_POST['agama']);
    $kecamatan_id       = mysqli_real_escape_string($conn, $_POST['kecamatan_id']);
    $nik                = mysqli_real_escape_string($conn, $_POST['nik']);
    $no_komponen        = mysqli_real_escape_string($conn, $_POST['no_komponen']);
    $tanggal_pengajuan  = mysqli_real_escape_string($conn, $_POST['tanggal_pengajuan']);
    $keperluan          = mysqli_real_escape_string($conn, $_POST['keperluan']);
    $progres            = mysqli_real_escape_string($conn, $_POST['progres']);

    // 4. Validasi Input Server-Side
    $errors = [];
    if (empty($nama)) $errors[] = 'Nama lengkap harus diisi.';
    if (empty($tempat_lahir)) $errors[] = 'Tempat lahir harus diisi.';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir harus diisi.';
    if (empty($kebangsaan)) $errors[] = 'Kebangsaan harus diisi.';
    if (empty($alamat)) $errors[] = 'Alamat lengkap harus diisi.';
    if (empty($nik)) $errors[] = 'NIK harus diisi.';
    if (empty($no_komponen)) $errors[] = 'No. Komponen harus diisi.';
    if (empty($tanggal_pengajuan)) $errors[] = 'Tanggal pengajuan harus diisi.';
    if (empty($keperluan)) $errors[] = 'Keperluan SKCK harus diisi.';

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: ajuan_skck.php');
        exit();
    }

    // 2. Otomatisasi kriminal_id
    $kriminal_id = 0; // Default jika tidak ada catatan kriminal
    $check_kriminal_query = mysqli_query($conn, "SELECT id_kriminal FROM kriminal WHERE nik = '$nik'");
    if (mysqli_num_rows($check_kriminal_query) > 0) {
        $kriminal_data = mysqli_fetch_assoc($check_kriminal_query);
        $kriminal_id = $kriminal_data['id_kriminal'];
    }

    // 2. Upload Lampiran dulu
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
                $_SESSION['error'] = 'Gagal mengupload lampiran. Pastikan folder uploads memiliki izin menulis.';
                header('Location: ajuan_skck.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Format lampiran tidak valid. Hanya PDF, JPG, JPEG, dan PNG yang diperbolehkan.';
            header('Location: ajuan_skck.php');
            exit();
        }
    } else if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] != 4) {
        $_SESSION['error'] = 'Terjadi kesalahan saat mengupload lampiran.';
        header('Location: ajuan_skck.php');
        exit();
    }

    // 3. Baru eksekusi SQL
    if (!empty($id_skck)) {
        // Update (belum kita fokuskan, tapi strukturnya ada)
        $sql = "UPDATE skck SET
                    user_id='$user_id',
                    kriminal_id='$kriminal_id',
                    nama='$nama',
                    tempat_lahir='$tempat_lahir',
                    tanggal_lahir='$tanggal_lahir',
                    kebangsaan='$kebangsaan',
                    pekerjaan_id='$pekerjaan_id',
                    alamat='$alamat',
                    kecamatan_id='$kecamatan_id',
                    nik='$nik',
                    agama='$agama',
                    no_komponen='$no_komponen',
                    tanggal_pengajuan='$tanggal_pengajuan',
                    keperluan='$keperluan',
                    progres='$progres'";

        if (!empty($lampiran)) {
            $sql .= ", lampiran='$lampiran'";
        }

        $sql .= " WHERE id_skck='$id_skck'";
    } else {
        // Insert (Pengajuan Baru)
        $sql = "INSERT INTO skck (
                    user_id, kriminal_id, nama, tempat_lahir, tanggal_lahir, kebangsaan,
                    pekerjaan_id, alamat, kecamatan_id, nik, agama, no_komponen,
                    tanggal_pengajuan, keperluan, progres, lampiran
                ) VALUES (
                    '$user_id', '$kriminal_id', '$nama', '$tempat_lahir', '$tanggal_lahir', '$kebangsaan',
                    '$pekerjaan_id', '$alamat', '$kecamatan_id', '$nik', '$agama','$no_komponen',
                    '$tanggal_pengajuan', '$keperluan', 'penelitian', '$lampiran'
                )";
    }

    // Menyimpan pesan di session dan redirect ke statushistory.php setelah submit berhasil
    $run = mysqli_query($conn, $sql);

    if ($run) {
        $_SESSION['success'] = 'Berkas Anda sudah terkirim dan akan diproses.';
    } else {
        $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan data, silakan coba lagi. ' . mysqli_error($conn);
    }

    header('Location: statushistory.php'); // Redirect ke halaman statushistory.php
    exit(); // Pastikan tidak ada output lebih lanjut

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
    <?php
    if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; ?>
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
                            <li class="breadcrumb-item active" aria-current="page">Pelayanan / Ajuan SKCK</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-table mb-0">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Formulir Pengajuan SKCK</h4>
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="id_skck" value="<?php echo $_GET['id_skck'] ?? ''; ?>">

                                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                                            <input type="hidden" name="kriminal_id" id="kriminal_id_input" value="0">


                                            <div class="col-md-12">
                                                <label for="nama" class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama" name="nama" required placeholder="Isi Sesuai KTP">
                                            </div>

                                            <div class="col-md-12 mt-3">
                                                <label for="nik" class="form-label">NIK</label>
                                                <input type="text" class="form-control" id="nik" name="nik" required onblur="cekKriminal(this.value)" placeholder="Isi Sesuai KTP">
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                                    <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-3">
                                                <label for="kebangsaan" class="form-label">Kebangsaan</label>
                                                <select class="form-control" id="kebangsaan" name="kebangsaan" required style="width: 100%;">
                                                    <option value="">-- Pilih Kebangsaan --</option>
                                                    <!-- Opsi akan diisi oleh JavaScript atau Anda bisa isi manual di sini -->
                                                </select>
                                            </div>
                                            <script>
                                                $(document).ready(function() {
                                                    // Inisialisasi Select2 untuk kebangsaan
                                                    $('#kebangsaan').select2({
                                                        placeholder: '-- Pilih Kebangsaan --',
                                                        allowClear: true,
                                                        data: [{
                                                                id: 'Indonesia',
                                                                text: 'Indonesia'
                                                            },
                                                            {
                                                                id: 'MY',
                                                                text: 'Malaysia'
                                                            },
                                                            {
                                                                id: 'SG',
                                                                text: 'Singapura'
                                                            },
                                                            {
                                                                id: 'TH',
                                                                text: 'Thailand'
                                                            },
                                                            {
                                                                id: 'VN',
                                                                text: 'Vietnam'
                                                            },
                                                            {
                                                                id: 'PH',
                                                                text: 'Filipina'
                                                            },
                                                            {
                                                                id: 'AU',
                                                                text: 'Australia'
                                                            },
                                                            {
                                                                id: 'CN',
                                                                text: 'China'
                                                            },
                                                            {
                                                                id: 'JP',
                                                                text: 'Jepang'
                                                            },
                                                            {
                                                                id: 'KR',
                                                                text: 'Korea Selatan'
                                                            },
                                                            {
                                                                id: 'US',
                                                                text: 'Amerika Serikat'
                                                            },
                                                            {
                                                                id: 'GB',
                                                                text: 'Britania Raya'
                                                            },
                                                            {
                                                                id: 'DE',
                                                                text: 'Jerman'
                                                            },
                                                            {
                                                                id: 'FR',
                                                                text: 'Prancis'
                                                            },
                                                            {
                                                                id: 'CA',
                                                                text: 'Kanada'
                                                            },
                                                            {
                                                                id: 'IN',
                                                                text: 'India'
                                                            }
                                                        ],
                                                        templateResult: function(state) {
                                                            if (!state.id) {
                                                                return state.text;
                                                            }
                                                            return $('<span class="flag-icon flag-icon-' + state.id.toLowerCase() + '">' + state.text + '</span>');
                                                        }
                                                    });
                                                });
                                            </script>

                                            <div class="row mt-3">
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
                                                    <label for="agama" class="form-label">Agama</label>
                                                    <select class="form-control text-black" id="agama" name="agama" required>
                                                        <option value="">-- Pilih Agama --</option>
                                                        <option value="Islam">Islam</option>
                                                        <option value="Kristen Protestan">Kristen Protestan</option>
                                                        <option value="Kristen Katolik">Kristen Katolik</option>
                                                        <option value="Hindu">Hindu</option>
                                                        <option value="Buddha">Buddha</option>
                                                        <option value="Konghucu">Konghucu</option>
                                                        <option value="Lainnya">Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-3">
                                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                                <textarea class="form-control" id="alamat" name="alamat" rows="2" required placeholder="Isi Sesuai KTP"></textarea>
                                                <script>
                                                    // Pastikan placeholder muncul dengan benar
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var alamat = document.getElementById('alamat');
                                                        if (alamat) {
                                                            alamat.placeholder = "Isi Sesuai KTP";
                                                        }
                                                    });
                                                </script>
                                            </div>

                                            <div class="col-md-12 mt-3">
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

                                            <div class="col-md-6" style="display: none;">
                                                <label for="no_komponen" class="form-label">No. Komponen</label>
                                                <input type="text" class="form-control" id="no_komponen" name="no_komponen" value="01712">
                                            </div>

                                            <div class="col-md-6" style="display: none;">
                                                <label for="tanggal_pengajuan" class="form-label">Tanggal Pengajuan</label>
                                                <input type="date" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>

                                            <div class="col-md-12 mt-3">
                                                <label for="keperluan" class="form-label">Keperluan SKCK</label>
                                                <textarea class="form-control" id="keperluan" name="keperluan" rows="2" required placeholder="Keperluan diisi pendek, jangan lebih dari 10 kata"></textarea>
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <label for="lampiran" class="form-label">Lampiran Persyaratan (PDF/JPG/PNG)</label>
                                                <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf, .jpg, .jpeg, .png" required>
                                            </div>

                                            <input class="mt-3" type="hidden" name="progres" value="penelitian">

                                            <div class="col-md-12 mt-3">
                                                <button type="submit" class="btn btn-primary">Ajukan SKCK</button>
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
        function cekKriminal(nik) {
            fetch(`cek_kriminal.php?nik=${nik}`)
                .then(response => response.json())
                .then(data => {
                    if (data.kriminal_id) {
                        document.getElementById('kriminal_id_input').value = data.kriminal_id;
                        alert(`Perhatian: NIK ini terdeteksi memiliki catatan kriminal dengan ID: ${data.kriminal_id} dan Catatan: ${data.cttkriminal}`);
                        // Anda mungkin ingin menambahkan logika lain di sini, seperti konfirmasi dari pengguna
                    } else {
                        document.getElementById('kriminal_id_input').value = 0;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>

</body>

</html>