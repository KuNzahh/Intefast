<?php
include '../include/koneksi.php'; // Sesuaikan path ke koneksi Anda
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "<p style='color: red;'>ID Pengguna tidak valid.</p>";
    exit();
}

$id_user = $_GET['user_id'];

// Ambil email pengguna dari tabel users
$sql_user = "SELECT u.email, u.nama FROM users u JOIN skck s ON u.id_user = s.user_id WHERE s.user_id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $id_user);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$data_user = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

if (!$data_user) {
    echo "<p style='color: red;'>Data pengguna tidak ditemukan.</p>";
    exit();
}

$to = $data_user['email'];
$nama_penerima = $data_user['nama'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['judul_email'];
    $body = $_POST['isi_email'];

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                       // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'intelfastskripsi@gmail.com';
        $mail->Password   = 'obaz tugk vnnr qlsu'; // Pastikan ini sandi aplikasi
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;                                        // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('intelfastskripsi@gmail.com', 'Notifikasi IntelFast');
        $mail->addAddress($to, $nama_penerima);     // Add a recipient

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        echo '<div style="padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">';
        echo '<h4 style="margin-top: 0;">Berhasil!</h4>';
        echo '<p>Email notifikasi berhasil dikirim ke: <strong>' . htmlspecialchars($to) . '</strong></p>';
        echo '<p><a href="berkas_skck.php" style="color: #155724; font-weight: bold; text-decoration: none;">Kembali ke Berkas SKCK</a></p>';
        echo '</div>';
    } catch (Exception $e) {
        echo '<div style="padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">';
        echo '<h4 style="margin-top: 0;">Gagal!</h4>';
        echo '<p>Gagal mengirim email notifikasi ke: <strong>' . htmlspecialchars($to) . '</strong></p>';
        echo '<p>Error: ' . $mail->ErrorInfo . '</p>';
        echo '<p><a href="berkas_skck.php" style="color: #721c24; font-weight: bold; text-decoration: none;">Kembali ke Berkas SKCK</a></p>';
        echo '</div>';
    }
} else {
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Kirim Notifikasi Email</title>
        <style>
            body {
                font-family: sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }

            .container {
                background-color: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                width: 80%;
                max-width: 600px;
            }

            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 20px;
            }

            p {
                color: #555;
                margin-bottom: 15px;
                text-align: center;
            }

            label {
                display: block;
                margin-bottom: 5px;
                color: #333;
                font-weight: bold;
            }

            input[type="text"],
            textarea {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }

            textarea {
                resize: vertical;
                min-height: 100px;
            }

            input[type="submit"],
            a.kembali {
                background-color: #007bff;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin-top: 10px;
            }

            input[type="submit"]:hover,
            a.kembali:hover {
                background-color: #0056b3;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .kembali-container {
                text-align: center;
                margin-top: 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Kirim Notifikasi Email</h1>
            <p>Mengirim notifikasi ke: <b><?php echo htmlspecialchars($nama_penerima); ?> (<?php echo htmlspecialchars($to); ?>)</b></p>
            <form method="post" action="">
                <input type="hidden" name="email_tujuan" value="<?php echo htmlspecialchars($to); ?>">
                <div class="form-group">
                    <label for="judul_email">Judul Email:</label>
                    <input type="text" name="judul_email" id="judul_email" value="Notifikasi Berkas SKCK Anda Siap Diambil" required>
                </div>
                <div class="form-group">
                    <label for="isi_email">Isi Email:</label><br>
                    <textarea name="isi_email" id="isi_email" rows="5"><?php echo 'Yth. ' . htmlspecialchars($nama_penerima) . ",<br><br>Berkas SKCK Anda sudah dapat diambil di SPKT POLRES BARITO KUALA, Siapkan Uang Rp.30.000 untuk biaya cetak Blanko SKCK di tempat.<br><br>Terima kasih."; ?></textarea><br><br>
                </div>
                <input type="submit" value="Kirim Notifikasi">
            </form>
            <div class="kembali-container">
                <a href="berkas_skck.php" class="kembali">Kembali ke Berkas SKCK</a>
            </div>
        </div>
    </body>

    </html>
<?php
}
?>