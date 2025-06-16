<?php
session_start();
include 'include/koneksi.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'pemohon';

    $query = "INSERT INTO users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Akun berhasil dibuat! Silakan login.';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal membuat akun. Coba lagi!';
        header('Location: register.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/head.php'; ?>
    <title>Register - Intelfast</title>
    <style>
        body {
            background: linear-gradient(to right, #4e73df, #224abe);
            font-family: 'Segoe UI', sans-serif;
        }

        .register-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .register-left {
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

        .register-left img {
            width: 250px;
            margin-bottom: 20px;
        }

        .register-left h2 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .register-left p {
            font-size: 15px;
            opacity: 0.85;
        }

        .register-right {
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

        .register-footer {
            font-size: 14px;
            margin-top: 20px;
        }

        .register-footer a {
            color: #4e73df;
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 767.98px) {
            .register-left {
                display: none;
            }

            .register-right {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="register-card row g-0 w-100" style="max-width: 800px;">
            <!-- Left Side -->
            <div class="col-md-5 d-none d-md-flex register-left" style="width: 250px; height: auto;">
                <a href="index.php">
                    <img src="assets/images/brand-logos/logoaplikasi.png" alt="Logo Intelfast">
                </a>
                <h2>Intelfast</h2>
                <p>Pelayanan mudah dan cepat</p>
            </div>

            <!-- Right Side -->
            <div class="col-md-7 register-right">
                <form method="POST" action="register.php">
                    <h4 class="mb-4 fw-bold text-dark">Daftar Akun</h4>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input class="form-control" name="nama" placeholder="Masukkan nama Anda" type="text" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" placeholder="Masukkan username Anda" type="text" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" placeholder="Masukkan email Anda" type="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" name="password" placeholder="Masukkan password Anda" type="password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">📝 Daftar</button>
                    </div>

                    <div class="register-footer text-start mt-3">
                        Sudah punya akun? <a href="index.php">Login di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/custom-switcher.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
