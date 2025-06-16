<?php
include '../include/koneksi.php';
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
            <?php
            $notifikasi_admin_session = $_SESSION['notifikasi_admin'] ?? [];
            $jumlah_notifikasi_admin = count($notifikasi_admin_session);
            if ($jumlah_notifikasi_admin > 0): ?>
                <span class="badge bg-danger header-icon-badge pulse pulse-danger" id="notification-icon-badge"><?= $jumlah_notifikasi_admin; ?></span>
            <?php endif; ?>
        </a>

        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationDropdown" style="width: 300px;">
            <li class="dropdown-header fw-bold">Notifikasi Admin</li>
            <?php if (!empty($notifikasi_admin_session)) : ?>
                <?php foreach ($notifikasi_admin_session as $notif) : ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <?php
                            $ikon = '<i class="fe fe-info me-2"></i>';
                            if ($notif['jenis'] === 'berita_baru') {
                                $ikon = '<i class="fe fe-newspaper text-success me-2"></i>';
                            }
                            echo $ikon;
                            ?>
                            <?= htmlspecialchars($notif['pesan']); ?>
                            <small class="text-muted ms-2"><?= date('H:i', strtotime($notif['waktu'])); ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-center text-primary fw-bold" href="semua_notifikasi_admin.php">Lihat semua</a>
                </li>
            <?php else : ?>
                <li>
                    <a class="dropdown-item text-center">Tidak ada notifikasi admin</a>
                </li>
            <?php endif; ?>
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
                    <span class="fs-11">Admin</span>
                </div>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
            <li><a class="dropdown-item" href="profile.php"><i class="fe fe-user me-2"></i> Profil</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmationModal"><i class="fe fe-power me-2"></i> Keluar</a></li>
        </ul>
    </div>
</div>


