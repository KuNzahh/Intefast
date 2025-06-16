<?php
// container.php

if (!isset($conn)) {
    include '../include/koneksi.php';
}

$user_id = $_SESSION['user_id'];

$notifikasi = [];

// Notifikasi Status SKCK
$query_skck = "SELECT 'SKCK' AS jenis, progres, tanggal_pengajuan AS timestamp FROM skck WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_skck = mysqli_query($conn, $query_skck);
$notifikasi_skck = mysqli_fetch_assoc($result_skck);
if ($notifikasi_skck) {
    $notifikasi[] = [
        'type' => 'status',
        'message' => 'SKCK: ' . $notifikasi_skck['progres'],
        'timestamp' => $notifikasi_skck['timestamp']
    ];
}

// Notifikasi Status SIK
$query_sik = "SELECT 'SIK' AS jenis, progres, tanggal_pengajuan AS timestamp FROM sik WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_sik = mysqli_query($conn, $query_sik);
$notifikasi_sik = mysqli_fetch_assoc($result_sik);
if ($notifikasi_sik) {
    $notifikasi[] = [
        'type' => 'status',
        'message' => 'SIK: ' . $notifikasi_sik['progres'],
        'timestamp' => $notifikasi_sik['timestamp']
    ];
}

// Notifikasi Status STTP
$query_sttp = "SELECT 'STTP' AS jenis, progres, tanggal_pengajuan AS timestamp FROM sttp WHERE user_id = '$user_id' ORDER BY tanggal_pengajuan DESC LIMIT 1";
$result_sttp = mysqli_query($conn, $query_sttp);
$notifikasi_sttp = mysqli_fetch_assoc($result_sttp);
if ($notifikasi_sttp) {
    $notifikasi[] = [
        'type' => 'status',
        'message' => 'STTP: ' . $notifikasi_sttp['progres'],
        'timestamp' => $notifikasi_sttp['timestamp']
    ];
}

// Notifikasi Pesan dari Petugas
$query_pesan = "SELECT c.pesan, c.timestamp, u.nama AS nama_pengirim
                    FROM chat c
                    JOIN users u ON c.id_pengirim = u.id_user
                    WHERE c.id_penerima = '$user_id' AND u.role = 'petugas'
                    ORDER BY c.timestamp DESC LIMIT 3";
$result_pesan = mysqli_query($conn, $query_pesan);
$notifikasi_pesan = mysqli_fetch_all($result_pesan, MYSQLI_ASSOC);
foreach ($notifikasi_pesan as $np) {
    $notifikasi[] = [
        'type' => 'pesan',
        'message' => 'Pesan dari ' . $np['nama_pengirim'],
        'timestamp' => $np['timestamp']
    ];
}

// Notifikasi Survey
$query_survey = "SELECT tanggal_survey FROM survey_kepuasan WHERE user_id = '$user_id' ORDER BY tanggal_survey DESC LIMIT 1";
$result_survey = mysqli_query($conn, $query_survey);
$notifikasi_survey = mysqli_fetch_assoc($result_survey);
if ($notifikasi_survey) {
    $notifikasi[] = [
        'type' => 'survey',
        'message' => 'Survey telah diisi',
        'timestamp' => $notifikasi_survey['tanggal_survey']
    ];
}

// Urutkan notifikasi berdasarkan timestamp terbaru
usort($notifikasi, function ($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

$jumlah_notifikasi = count($notifikasi);

?>

<div class="header-content-left">
    <div class="header-element">
        <div class="horizontal-logo">
            <a href="dashboard.php" class="header-logo d-flex align-items-center">
                <img src="../assets/images/brand-logos/logopanjang.png" alt="logo" class="desktop-logo" height="40">
                <span class="system-name ms-2">INTELFAST</span>
            </a>
        </div>
    </div>
    <div class="header-element">
        <a aria-label="Hide Sidebar"
            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
        </div>
    </div>
<div class="header-content-right">

    <div class="header-element notifications-dropdown">
        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
            id="notificationDropdown" aria-expanded="false">
            <i class="fe fe-bell header-link-icon"></i>
            <?php if ($jumlah_notifikasi > 0): ?>
                <span class="badge bg-danger header-icon-badge pulse pulse-danger" id="notification-icon-badge"><?php echo $jumlah_notifikasi; ?></span>
            <?php endif; ?>
        </a>

        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationDropdown" style="width: 300px;">
            <li class="dropdown-header fw-bold">Notifikasi</li>
            <?php if (empty($notifikasi)): ?>
                <li><a class="dropdown-item text-center text-muted">Tidak ada notifikasi baru</a></li>
            <?php else: ?>
                <?php foreach ($notifikasi as $n): ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <?php if ($n['type'] == 'status'): ?>
                                <i class="fe fe-info text-warning me-2"></i>
                                <?php
                                $status_lower = strtolower($n['message']);
                                if (strpos($status_lower, 'proses') !== false): ?>
                                    <i class="fe fe-clock text-warning me-2"></i>
                                <?php elseif (strpos($status_lower, 'diterima') !== false): ?>
                                    <i class="fe fe-check-circle text-success me-2"></i>
                                <?php elseif (strpos($status_lower, 'ditolak') !== false): ?>
                                    <i class="fe fe-x-circle text-danger me-2"></i>
                                <?php else: ?>
                                    <i class="fe fe-info text-info me-2"></i>
                                <?php endif; ?>
                            <?php elseif ($n['type'] == 'pesan'): ?>
                                <i class="fe fe-message-circle text-primary me-2"></i>
                            <?php elseif ($n['type'] == 'survey'): ?>
                                <i class="fe fe-check-square text-success me-2"></i>
                            <?php endif; ?>
                            <span><?php echo $n['message']; ?></span>
                            <span class="ms-auto text-muted small"><?php echo date('H:i', strtotime($n['timestamp'])); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-center text-primary fw-bold" href="#">Lihat semua</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>


    <div class="header-element">
        <a href="bantuan.php" class="header-link">
            <i class="fe fe-help-circle header-link-icon"></i>
        </a>
    </div>

    <div class="header-element">
        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="userProfileDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center">
                <div class="header-link-icon">
                    <img src="../assets/images/faces/4.jpg" alt="User" width="32" height="32" class="rounded-circle">
                </div>
                <div class="d-none d-lg-block ms-2">
                    <p class="fw-semibold mb-0"><?php echo $_SESSION['nama']; ?> </p>
                    <span class="fs-11">Pemohon</span>
                </div>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
            <li><a class="dropdown-item" href="profile.php"><i class="fe fe-user me-2"></i> Profil</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" onclick="confirmLogout();"><i class="fe fe-power me-2"></i> Keluar</a></li>
        </ul>
    </div>
</div>



<script>
function confirmLogout() {
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "logout.php";
    }
}

function confirmLogout() {
    alert("Harap periksa status dan history permohonan Anda secara berkala untuk mengetahui perkembangan terbaru.");
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "logout.php";
    }
}
</script>

