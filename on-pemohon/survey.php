<?php
session_start();
include '../include/koneksi.php';

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];




$survey_submitted = false; // Tambahkan flag untuk menandai apakah survei sudah dikirim

// Proses pengiriman survei
if (isset($_POST['submit_survey'])) {
    $responses = [];
    for ($i = 1; $i <= 10; $i++) {
        if (isset($_POST['q' . $i])) {
            $responses['q' . $i] = $_POST['q' . $i];
        } else {
            $error = "Mohon jawab semua pertanyaan.";
            break;
        }
    }

    if (!isset($error)) {
        $tanggal_survey = date('Y-m-d H:i:s');
        $sql_insert_survey = "INSERT INTO survey_kepuasan (user_id, tanggal_survey, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10)
                                VALUES ('$user_id', '$tanggal_survey',
                                        '" . $responses['q1'] . "', '" . $responses['q2'] . "', '" . $responses['q3'] . "', '" . $responses['q4'] . "',
                                        '" . $responses['q5'] . "', '" . $responses['q6'] . "', '" . $responses['q7'] . "', '" . $responses['q8'] . "',
                                        '" . $responses['q9'] . "', '" . $responses['q10'] . "')";

        if (mysqli_query($conn, $sql_insert_survey)) {
            $_SESSION['success'] = 'Terima kasih! Survei Anda telah dimasukkan.';
            $survey_submitted = true; // Set flag ke true jika survei berhasil dikirim
            header("Location: survey.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan jawaban survei: " . mysqli_error($conn);
            header("Location: survey.php");
            exit();
        }
    } else {
        $_SESSION['error'] = $error;
        header("Location: survey.php");
        exit();
    }
}

// Fungsi untuk mendapatkan nilai dari pilihan jawaban
function getSurveyValue($pilihan)
{
    switch ($pilihan) {
        case 'sangat_setuju':
            return 4;
        case 'setuju':
            return 3;
        case 'kurang_setuju':
            return 2;
        case 'tidak_setuju':
            return 1;
        default:
            return 0;
    }
}

// Menampilkan pesan sukses/error (sekarang tidak langsung ditampilkan)
if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php unset($_SESSION['error']);
endif;
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Survey Kepuasan - <?php echo $_SESSION['user_name']; ?></title>
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
                        <h2 class="main-content-title fs-24 mb-1">Survey Kepuasan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Survey Kepuasan</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Berikan Penilaian Anda Terhadap Pelayanan Online Kami</h3>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <ol>
                                        <li>
                                            <label class="form-label">Seberapa mudah Anda menemukan informasi yang dibutuhkan di aplikasi ini?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Bagaimana pendapat Anda tentang tampilan antarmuka (interface) aplikasi ini?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Seberapa cepat proses pengajuan layanan melalui aplikasi ini?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q3" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q3" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q3" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q3" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Apakah Anda merasa terbantu dengan fitur status dan history pengajuan?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q4" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q4" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q4" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q4" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Seberapa responsif sistem terhadap aksi yang Anda lakukan?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q5" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q5" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q5" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q5" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Apakah Anda merasa aman menggunakan aplikasi ini untuk pengajuan layanan?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q6" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q6" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q6" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q6" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Bagaimana kualitas informasi yang disajikan dalam aplikasi ini?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q7" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q7" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q7" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q7" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Apakah Anda akan merekomendasikan aplikasi ini kepada orang lain?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q8" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q8" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q8" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q8" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Bagaimana kemudahan aksesibilitas aplikasi ini (misalnya, di berbagai perangkat)?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q9" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q9" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q9" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q9" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                        <br>
                                        <li>
                                            <label class="form-label">Secara keseluruhan, seberapa puas Anda dengan pelayanan online melalui aplikasi ini?</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q10" value="sangat_setuju">
                                                <label class="form-check-label">Sangat Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q10" value="setuju">
                                                <label class="form-check-label">Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q10" value="kurang_setuju">
                                                <label class="form-check-label">Kurang Setuju</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q10" value="tidak_setuju">
                                                <label class="form-check-label">Tidak Setuju</label>
                                            </div>
                                        </li>
                                    </ol>
                                    <button type="submit" name="submit_survey" class="btn btn-primary mt-3">Kirim Survei</button>
                                </form>
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

    <?php
    if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php unset($_SESSION['error']);
    endif; ?>

    <?php
    if (isset($_SESSION['success'])): ?>
        <script>
            alert("<?= $_SESSION['success']; ?>");
            window.location.href = 'dashboard.php';
        </script>
    <?php unset($_SESSION['success']);
    endif; ?>


</body>

</html>