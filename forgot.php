<?php
session_start();
include 'include/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "SELECT * FROM users WHERE username = '$username' AND email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE users SET password = '$new_password' WHERE username = '$username' AND email = '$email'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = 'Password berhasil diubah. Silakan login.';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Gagal memperbarui password. Coba lagi.';
        }
    } else {
        $_SESSION['error'] = 'Username atau email tidak ditemukan!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/head.php'; ?>
    <title>Reset Password - Intelfast</title>
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

        @media (max-width: 767.98px) {
            .login-left {
                display: none;
            }

            .login-right {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-card row g-0 w-100" style="max-width: 900px;">
            <!-- Left -->
            <div class="col-md-5 d-none d-md-flex login-left">
                <a href="index.php">
                    <img src="assets/images/brand-logos/logoaplikasi.png" alt="Logo Intelfast" style="width: 300px; height: auto;">
                </a>
                <h2>Reset Password</h2>
                <p>Pastikan data Anda benar!</p>
            </div>

            <!-- Right -->
            <div class="col-md-7 login-right">
                <form method="POST" action="forgot.php">
                    <h4 class="mb-4 fw-bold text-dark">Reset Password</h4>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" type="text" placeholder="Masukkan username Anda" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" type="email" placeholder="Masukkan email Anda" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input class="form-control" name="password" type="password" placeholder="Masukkan password baru Anda" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>

                    <?php if (isset($_SESSION['error'])) { ?>
                        <div class="alert alert-danger mt-3">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php } ?>

                    <?php if (isset($_SESSION['success'])) { ?>
                        <div class="alert alert-success mt-3">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php } ?>

                    <div class="text-start mt-3">
                        <a href="index.php">Kembali ke Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/custom-switcher.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
