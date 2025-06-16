<?php
session_start();

if (!isset($_SESSION['nama']) || !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';

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

?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <?php include 'head.php'; ?>
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
                                <?php if (!empty($daftar_percakapan)): ?>
                                    <?php foreach ($daftar_percakapan as $percakapan): ?>
                                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-start" data-id-pemohon="<?php echo $percakapan['id_pemohon']; ?>" style="cursor: pointer;">
                                            <img src="../assets/images/faces/4.jpg" alt="<?php echo htmlspecialchars($percakapan['username']); ?>" class="rounded-circle me-3 mt-1" style="width: 50px; height: 50px;">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold"><?php echo htmlspecialchars($percakapan['username']); ?></div>
                                                <div class="text-muted text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($percakapan['pesan_terakhir']); ?></div>
                                            </div>
                                            <?php if ($percakapan['timestamp']): ?>
                                                <small class="text-muted text-nowrap ms-auto"><?php echo date('H:i', strtotime($percakapan['timestamp'])); ?></small>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
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

        <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="chatModalLabel">Chat dengan <span id="nama-pemohon-modal"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="isi-chat-modal">
                    </div>
                    <div class="modal-footer">
                        <form id="form-balas" method="post" class="w-100">
                            <input type="hidden" name="id_pemohon_balas" id="id-pemohon-balas">
                            <div class="d-flex align-items-center">
                                <textarea class="form-control rounded-start me-2" name="balasan_modal" placeholder="Tulis balasan Anda di sini..."></textarea>
                                <button class="btn btn-primary rounded-end" type="submit"><i class="fas fa-paper-plane"></i> Kirim</button>
                            </div>
                            <div id="error-balas-modal" class="mt-2 text-danger"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
            const listGroupItems = document.querySelectorAll('.list-group-item-action');
            const modalBody = document.getElementById('isi-chat-modal');
            const modalTitlePemohon = document.getElementById('nama-pemohon-modal');
            const formBalas = document.getElementById('form-balas');
            const idPemohonBalasInput = document.getElementById('id-pemohon-balas');
            const errorBalasModal = document.getElementById('error-balas-modal');

            listGroupItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const idPemohon = this.getAttribute('data-id-pemohon');
                    const usernamePemohon = this.querySelector('.fw-bold').textContent;

                    modalTitlePemohon.textContent = usernamePemohon;
                    idPemohonBalasInput.value = idPemohon;
                    modalBody.innerHTML = '<div class="text-center">Memuat pesan...</div>';
                    chatModal.show();

                    // Muat isi chat menggunakan AJAX
                    fetch('get_chat_modal.php?id_pemohon=' + idPemohon)
                        .then(response => response.text())
                        .then(data => {
                            modalBody.innerHTML = data;
                            modalBody.scrollTop = modalBody.scrollHeight; // Scroll ke bawah
                        })
                        .catch(error => {
                            modalBody.innerHTML = '<div class="alert alert-danger">Gagal memuat pesan.</div>';
                            console.error('Error:', error);
                        });
                });
            });

            formBalas.addEventListener('submit', function(e) {
                e.preventDefault();
                const idPemohon = idPemohonBalasInput.value;
                const balasan = this.querySelector('textarea').value;

                if (balasan.trim() !== '') {
                    fetch('kirim_balasan_modal.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id_penerima=' + idPemohon + '&balasan=' + encodeURIComponent(balasan)
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                // Reload isi chat setelah berhasil mengirim
                                fetch('get_chat_modal.php?id_pemohon=' + idPemohon)
                                    .then(response => response.text())
                                    .then(newData => {
                                        modalBody.innerHTML = newData;
                                        modalBody.scrollTop = modalBody.scrollHeight;
                                        formBalas.querySelector('textarea').value = ''; // Kosongkan textarea
                                        errorBalasModal.textContent = '';
                                    })
                                    .catch(error => {
                                        errorBalasModal.textContent = 'Gagal memperbarui pesan.';
                                        console.error('Error:', error);
                                    });
                            } else {
                                errorBalasModal.textContent = data; // Tampilkan pesan error dari server
                            }
                        })
                        .catch(error => {
                            errorBalasModal.textContent = 'Terjadi kesalahan saat mengirim pesan.';
                            console.error('Error:', error);
                        });
                } else {
                    errorBalasModal.textContent = 'Balasan tidak boleh kosong.';
                }
            });
        </script>

</body>

</html>