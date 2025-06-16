<?php
session_start();

// Jika pengguna sudah login (sesi sudah ada), langsung arahkan ke dashboard yang sesuai
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $role = $_SESSION['role'];
    $redirect_page = 'index.php'; // Halaman default jika peran tidak cocok
    switch ($role) {
        case 'admin':
            $redirect_page = 'on-admin/dashboard.php';
            break;
        case 'pemohon':
            $redirect_page = 'on-pemohon/dashboard.php';
            break;
        case 'pimpinan':
            $redirect_page = 'on-pimpinan/dashboard.php';
            break;
        case 'petugas':
            $redirect_page = 'on-petugas/dashboard.php';
            break;
    }
    // Menggunakan JavaScript untuk redirect agar pesan sukses sempat terlihat
    echo "<script>window.location.href = '$redirect_page';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include 'include/head.php'; // Pastikan path ini benar ?>
    <title>Login - Intelfast</title>
    <link rel="icon" href="assets/images/brand-logos/logoaplikasi.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #4e73df, #224abe);
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .login-left {
            background: #224abe;
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
        }
        .login-left img {
            width: 110px;
            margin-bottom: 20px;
        }
        .login-left h2 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .login-left p {
            font-size: 15px;
            opacity: 0.85;
        }
        .login-right {
            padding: 40px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #4e73df;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #365dc7;
        }
        .login-footer {
            font-size: 14px;
            margin-top: 20px;
        }
        .login-footer a {
            color: #4e73df;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
       
        @media (max-width: 767.98px) {
            .login-left { display: none; }
            .login-right { padding: 30px 20px; }
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-card row g-0 w-100" style="max-width: 900px;">
            <div class="col-md-5 d-none d-md-flex login-left">
                <a href="index.php">
                    <img src="assets/images/brand-logos/logoaplikasi.png" alt="Logo Intelfast" style="width: 250px; height: auto;">
                </a>
                <h2>Intelfast</h2>
                <p>Pelayanan mudah dan cepat</p>
            </div>

            <div class="col-md-7 login-right">
                
                <div id="alert-container"></div>

                <form action="include/login.php" method="POST">
                    <h4 class="mb-4 fw-bold text-dark">Login ke Akun Anda</h4>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" placeholder="Masukkan username Anda" type="text" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" name="password" placeholder="Masukkan password Anda" type="password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">🔐 Login</button>
                    </div>

                    <div class="login-footer text-start">
                        <a href="forgot.php">Lupa password?</a><br>
                        Belum punya akun? <a href="register.php">Daftar di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom-switcher.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const alertContainer = document.getElementById('alert-container');

            if (status) {
                let pesanAlert = '';
                let kelasAlert = '';

                if (status === 'error') {
                    pesanAlert = '<strong>Gagal!</strong> Username atau password salah.';
                    kelasAlert = 'alert-danger';
                } else if (status === 'success') {
                    pesanAlert = '<strong>Login Berhasil!</strong> Anda akan diarahkan...';
                    kelasAlert = 'alert-success';
                }
                
                if (pesanAlert) {
                    // 1. Buat elemen div dengan kelas 'fade' dari Bootstrap
                    const divAlert = document.createElement('div');
                    // Kelas 'fade' membuat elemen transparan (opacity: 0) secara default
                    divAlert.className = `alert ${kelasAlert} alert-dismissible fade`; 
                    divAlert.setAttribute('role', 'alert');
                    divAlert.innerHTML = `${pesanAlert}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                    
                    // 2. Masukkan elemen ke dalam halaman
                    alertContainer.appendChild(divAlert);

                    // 3. Beri jeda sesaat, lalu tambahkan kelas 'show' untuk memicu transisi fade-in
                    setTimeout(() => {
                        divAlert.classList.add('show');
                    }, 10); // Jeda 10 milidetik sudah cukup

                    // Hapus parameter dari URL agar notifikasi tidak muncul lagi saat refresh
                    history.pushState(null, "", window.location.pathname);
                }
            }
        });
    </script>
</body>
</html>