<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

$user_id = $_SESSION['user_id'];

// Proses pengiriman pesan (tetap sama)
if (isset($_POST['kirim_pesan'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    $id_penerima = 7; // Mengarah ke 'Petugas Umum'
    $query_insert = "INSERT INTO chat (id_pengirim, id_penerima, pesan, timestamp)
                      VALUES ('$user_id', '$id_penerima', '$pesan', NOW())";
    if (mysqli_query($conn, $query_insert)) {
        // Berhasil
    } else {
        echo "Error: " . $query_insert . "<br>" . mysqli_error($conn);
    }
}

// Ambil pesan-pesan
$query_chat = "SELECT c.*, u.username AS nama_pengirim
               FROM chat c
               JOIN users u ON c.id_pengirim = u.id_user
               WHERE (c.id_pengirim = '$user_id' AND c.id_penerima = 7)
                  OR (u.role = 'petugas' AND c.id_penerima = '$user_id')
               ORDER BY c.timestamp ASC";
$result_chat = mysqli_query($conn, $query_chat);
$chats = mysqli_fetch_all($result_chat, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <title>Bantuan - <?php echo $_SESSION['nama']; ?></title>
    <style>
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background-color: #a8a8a8;
            border-radius: 3px;
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
                        <h2 class="main-content-title fs-24 mb-1">Bantuan</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bantuan</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="card shadow-lg p-4">

                            <div class="d-flex align-items-center mb-3">
                                <img src="../assets/images/faces/5.jpg" alt="Petugas" class="rounded-circle me-2" style="width: 50px; height: 50px;">
                                <div>
                                    <strong>PETUGAS </strong>
                                    <div class="text-muted">Pelayanan</div>
                                </div>
                            </div>

                            <div class="chat-box p-3" style="height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 10px;">
                                <?php if (!empty($chats)): ?>
                                    <?php foreach ($chats as $chat): ?>
                                        <?php if ($chat['id_pengirim'] == $user_id): ?>
                                            <div class="text-end mb-3">
                                                <strong class="text-muted">Anda</strong>
                                                <div class="p-2 bg-primary text-white rounded shadow-sm d-inline-block" style="max-width: 50%;">
                                                    <?php echo $chat['pesan']; ?>
                                                </div>
                                                <div class="text-muted small"><?php echo date('H:i', strtotime($chat['timestamp'])); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <strong class="text-muted"><?php echo htmlspecialchars($chat['nama_pengirim']); ?></strong>
                                                <div class="p-2 bg-light rounded shadow-sm d-inline-block" style="max-width: 75%;">
                                                    <?php echo $chat['pesan']; ?>
                                                </div>
                                                <div class="text-muted small"><?php echo date('H:i', strtotime($chat['timestamp'])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">Belum ada percakapan. Mulai percakapan Anda.</div>
                                <?php endif; ?>
                            </div>

                            <form method="post" class="mt-3 d-flex">
                                <input type="text" class="form-control me-2" placeholder="Kirim Pesan Pertanyaan anda disini" style="border-radius: 10px;" name="pesan" required>
                                <button type="submit" class="btn btn-dark px-4" name="kirim_pesan">Kirim</button>
                            </form>

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
        var chatBox = document.querySelector('.chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>

</body>

</html>