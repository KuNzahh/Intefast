<?php
// Koneksi ke database (pastikan file koneksi.php sudah di-include sebelumnya)
if (!isset($conn)) {
    include '../include/koneksi.php';
}

$user_id_login = $_SESSION['user_id'];
$notifikasi = [];
$jumlah_notifikasi = 0;

// Notifikasi Chat Baru (dihitung berdasarkan adanya pesan untuk petugas)
$query_chat_baru = "SELECT COUNT(DISTINCT id_pengirim) AS jumlah FROM chat
                    WHERE id_penerima = ?";
$stmt_chat_baru = mysqli_prepare($conn, $query_chat_baru);
mysqli_stmt_bind_param($stmt_chat_baru, "i", $user_id_login);
mysqli_stmt_execute($stmt_chat_baru);
$result_chat_baru = mysqli_stmt_get_result($stmt_chat_baru);
$data_chat_baru = mysqli_fetch_assoc($result_chat_baru);
$jumlah_chat_baru = $data_chat_baru['jumlah'];
if ($jumlah_chat_baru > 0) {
    $notifikasi[] = [
        'tipe' => 'chat',
        'pesan' => 'Ada ' . $jumlah_chat_baru . ' percakapan baru',
        'link' => 'chat.php'
    ];
    $jumlah_notifikasi += $jumlah_chat_baru;
}


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

            <?php if (!empty($notifikasi)): ?>
                <?php foreach ($notifikasi as $item): ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?php echo $item['link']; ?>">
                            <?php
                            $icon = '';
                            switch ($item['tipe']) {
                                case 'chat':
                                    $icon = '<i class="fe fe-message-square text-primary me-2"></i>';
                                    break;
                                case 'skck':
                                    $icon = '<i class="fe fe-file-text text-success me-2"></i>';
                                    break;
                                case 'sik':
                                    $icon = '<i class="fe fe-file-plus text-info me-2"></i>';
                                    break;
                                case 'sttp':
                                    $icon = '<i class="fe fe-file text-warning me-2"></i>';
                                    break;
                                default:
                                    $icon = '<i class="fe fe-bell text-secondary me-2"></i>';
                                    break;
                            }
                            echo $icon;
                            ?>
                            <?php echo $item['pesan']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><a class="dropdown-item text-center" href="#">Tidak ada notifikasi baru</a></li>
            <?php endif; ?>

            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <a class="dropdown-item text-center text-primary fw-bold" href="chat.php">Lihat semua</a> </li>
        </ul>
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
                    <span class="fs-11">Pimpinan</span>
                </div>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
            <li><a class="dropdown-item" href="profile.php"><i class="fe fe-user me-2"></i> Profil</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmationModal"><i class="fe fe-power me-2"></i> Keluar</a></li>
        </ul>
    </div>
</div>