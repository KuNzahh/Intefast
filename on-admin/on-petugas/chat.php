<?php
session_start();
include '../include/koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['nama']) || !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$id_petugas_login = $_SESSION['user_id'];
$daftar_percakapan = [];

// 1. Ambil daftar unik ID pemohon yang pernah mengirim pesan
$query_pemohon = "SELECT DISTINCT id_pengirim FROM chat
                  WHERE (SELECT role FROM users WHERE id_user = chat.id_pengirim) = 'pemohon'";
$result_pemohon = mysqli_query($conn, $query_pemohon);

if ($result_pemohon) {
    while ($row_pemohon = mysqli_fetch_assoc($result_pemohon)) {
        $id_pemohon = $row_pemohon['id_pengirim'];

        // 2. Ambil informasi username pemohon
        $query_user = "SELECT username FROM users WHERE id_user = ?";
        $stmt_user = mysqli_prepare($conn, $query_user);
        mysqli_stmt_bind_param($stmt_user, "i", $id_pemohon);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
        $data_user = mysqli_fetch_assoc($result_user);

        // 3. Ambil pesan terakhir dalam percakapan antara petugas ini dan pemohon
        $query_terakhir = "SELECT pesan, timestamp FROM chat
                           WHERE (id_pengirim = ? AND id_penerima = ?) OR (id_pengirim = ? AND id_penerima = ?)
                           ORDER BY timestamp DESC LIMIT 1";
        $stmt_terakhir = mysqli_prepare($conn, $query_terakhir);
        mysqli_stmt_bind_param($stmt_terakhir, "iiii", $id_pemohon, $id_petugas_login, $id_petugas_login, $id_pemohon);
        mysqli_stmt_execute($stmt_terakhir);
        $result_terakhir = mysqli_stmt_get_result($stmt_terakhir);
        $pesan_terakhir = mysqli_fetch_assoc($result_terakhir);

        if ($data_user) {
            $daftar_percakapan[] = [
                'id_pemohon' => $id_pemohon,
                'username' => $data_user['username'],
                'pesan_terakhir' => $pesan_terakhir ? $pesan_terakhir['pesan'] : 'Belum ada pesan',
                'timestamp' => $pesan_terakhir ? $pesan_terakhir['timestamp'] : null,
            ];
        }
    }
}

// Urutkan berdasarkan timestamp terbaru
usort($daftar_percakapan, function ($a, $b) {
    if ($a['timestamp'] == $b['timestamp']) {
        return 0;
    }
    return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
});

// Proses hapus percakapan (jika ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_percakapan'])) {
    $id_pemohon_hapus = mysqli_real_escape_string($conn, $_POST['id_pemohon']);

    // Hapus semua percakapan dengan pemohon
    $query = "DELETE FROM chat WHERE id_pengirim = ? OR id_penerima = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_pemohon_hapus, $id_pemohon_hapus);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo 'success';
    } else {
        echo 'Gagal menghapus percakapan.';
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="index.php" class="header-logo">
                    <img src="../assets/images/brand-logos/desktop-white.png" class="desktop-white" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-white.png" class="toggle-white" alt="logo">
                    <img src="../assets/images/brand-logos/desktop-logo.png" class="desktop-logo" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-dark.png" class="toggle-dark" alt="logo">
                    <img src="../assets/images/brand-logos/toggle-logo.png" class="toggle-logo" alt="logo">
                    <img src="../assets/images/brand-logos/logopanjangbiru.jpg" class="desktop-dark" alt="logo">
                </a>
            </div>
            <?php include 'sidebar.php'; ?>
        </aside>
        <div class="main-content app-content">
            <div class="container-fluid">
                <div class="d-md-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                    <div>
                        <h2 class="main-content-title fs-24 mb-1">Chat</h2>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chat</li>
                        </ol>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="container-fluid">
                        <div class="card shadow-lg p-4">
                            <h5 class="mb-4">Daftar Percakapan</h5>
                            <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                                <?php
                                // Ambil semua pesan yang pernah dikirim ke/oleh petugas login, urutkan terbaru
                                $query_all = "SELECT c.*, u.username 
                                              FROM chat c 
                                              JOIN users u ON u.id_user = c.id_pengirim
                                              WHERE c.id_pengirim = ? OR c.id_penerima = ?
                                              ORDER BY c.timestamp DESC";
                                $stmt_all = mysqli_prepare($conn, $query_all);
                                mysqli_stmt_bind_param($stmt_all, "ii", $id_petugas_login, $id_petugas_login);
                                mysqli_stmt_execute($stmt_all);
                                $result_all = mysqli_stmt_get_result($stmt_all);

                                $percakapan_terakhir = [];
                                while ($row = mysqli_fetch_assoc($result_all)) {
                                    // Tentukan id lawan bicara (pemohon)
                                    if ($row['id_pengirim'] == $id_petugas_login) {
                                        $id_pemohon = $row['id_penerima'];
                                    } else {
                                        $id_pemohon = $row['id_pengirim'];
                                    }
                                    // Hanya tampilkan percakapan dengan pemohon (role)
                                    $query_role = "SELECT role, username FROM users WHERE id_user = ?";
                                    $stmt_role = mysqli_prepare($conn, $query_role);
                                    mysqli_stmt_bind_param($stmt_role, "i", $id_pemohon);
                                    mysqli_stmt_execute($stmt_role);
                                    $result_role = mysqli_stmt_get_result($stmt_role);
                                    $user_role = mysqli_fetch_assoc($result_role);
                                    if ($user_role && $user_role['role'] == 'pemohon') {
                                        if (!isset($percakapan_terakhir[$id_pemohon])) {
                                            $percakapan_terakhir[$id_pemohon] = [
                                                'id_pemohon' => $id_pemohon,
                                                'username' => $user_role['username'],
                                                'pesan_terakhir' => $row['pesan'],
                                                'timestamp' => $row['timestamp'],
                                            ];
                                        }
                                    }
                                }
                                if (!empty($percakapan_terakhir)):
                                    foreach ($percakapan_terakhir as $percakapan):
                                ?>
                                    <div class="list-group-item d-flex align-items-center justify-content-between" data-id-pemohon="<?php echo $percakapan['id_pemohon']; ?>">
                                        <div class="d-flex align-items-start">
                                            <img src="../assets/images/faces/4.jpg" alt="<?php echo htmlspecialchars($percakapan['username']); ?>" class="rounded-circle me-3 mt-1" style="width: 50px; height: 50px;">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($percakapan['username']); ?></div>
                                                <div class="text-muted text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($percakapan['pesan_terakhir']); ?></div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end ms-3">
                                            <?php if ($percakapan['timestamp']): ?>
                                                <small class="text-muted text-nowrap mb-2"><?php echo date('H:i', strtotime($percakapan['timestamp'])); ?></small>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger delete-conversation" data-id-pemohon="<?php echo $percakapan['id_pemohon']; ?>">
                                                <i class="fe fe-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <div class="list-group-item">Belum ada percakapan.</div>
                                <?php endif; ?>
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
            document.addEventListener('DOMContentLoaded', function() {
                const deleteConversationButtons = document.querySelectorAll('.delete-conversation');

                deleteConversationButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        const idPemohon = this.getAttribute('data-id-pemohon');

                        // Konfirmasi penghapusan seluruh percakapan
                        if (confirm('Apakah Anda yakin ingin menghapus seluruh percakapan dengan pemohon ini?')) {
                            fetch('chat.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'hapus_percakapan=true&id_pemohon=' + idPemohon
                            })
                            .then(response => response.text())
                            .then(data => {
                                if (data === 'success') {
                                    // Hapus percakapan dari tampilan daftar percakapan
                                    const conversationItem = this.closest('.list-group-item');
                                    conversationItem.remove();
                                    alert('Percakapan berhasil dihapus.');
                                } else {
                                    alert('Gagal menghapus percakapan.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat menghapus percakapan.');
                            });
                        }
                    });
                });
            });
        </script>
    </div>
</body>
</html>
