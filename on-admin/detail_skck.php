<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

$petugas_nama = $_SESSION['nama']; // Ambil nama petugas dari session
$petugas_id = $_SESSION['user_id']; // Ambil ID petugas dari session

// Tangkap ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data SKCK dan user
$query = "
    SELECT skck.*, users.username, users.id_user AS user_id
    FROM skck
    LEFT JOIN users ON skck.user_id = users.id_user
    WHERE skck.id_skck = $id
";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<h3>Data tidak ditemukan.</h3>";
    exit;
}

$data = mysqli_fetch_assoc($result);
$username_pemohon = htmlspecialchars($data['username']);
$user_id_pemohon = $data['user_id'];

// Proses terima berkas
if (isset($_POST['terima_berkas'])) {
    $update_query = "UPDATE skck SET progres = 'diterima' WHERE id_skck = $id";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Berkas SKCK atas nama $username_pemohon telah diterima oleh $petugas_nama.'); window.location.href='berkas_skck.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui status.');</script>";
    }
}

// Proses kirim pesan tolak
if (isset($_POST['kirim_pesan_tolak'])) {
    $pesan_tolak = isset($_POST['pesan_tolak']) ? htmlspecialchars($_POST['pesan_tolak']) : '';
    if (!empty($pesan_tolak)) {
        // Update status progres menjadi 'Ditolak'
        $update_progres_query = "UPDATE skck SET progres = 'ditolak' WHERE id_skck = $id";
        if (mysqli_query($conn, $update_progres_query)) {
            // Jika berhasil update progres, baru kirim pesan chat
            $query_insert_chat = "INSERT INTO chat (id_pengirim, id_penerima, pesan, timestamp)
                                      VALUES ($petugas_id, $user_id_pemohon, '$pesan_tolak', NOW())";
            if (mysqli_query($conn, $query_insert_chat)) {
                echo "<script>alert('Pesan penolakan telah dikirim kepada $username_pemohon. Status berkas diubah menjadi ditolak.'); window.location.href='berkas_skck.php';</script>";
            } else {
                echo "<script>alert('Gagal mengirim pesan penolakan.');</script>";
            }
        } else {
            echo "<script>alert('Gagal memperbarui status berkas menjadi ditolak.');</script>";
        }
    } else {
        echo "<script>alert('Pesan penolakan tidak boleh kosong.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Detail SKCK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Detail Data Pemohon SKCK</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Username</th>
                        <td><?php echo htmlspecialchars($data['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td><?php echo htmlspecialchars($data['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td><?php echo htmlspecialchars($data['nik']); ?></td>
                    </tr>
                    <tr>
                        <th>Tempat Lahir</th>
                        <td><?php echo htmlspecialchars($data['tempat_lahir']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Lahir</th>
                        <td><?php echo htmlspecialchars($data['tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td><?php echo htmlspecialchars($data['jenis_kelamin']); ?></td>
                    </tr>
                    <tr>
                        <th>Kebangsaan</th>
                        <td><?php echo htmlspecialchars($data['kebangsaan']); ?></td>
                    </tr>
                    <tr>
                        <th>Agama</th>
                        <td><?php echo htmlspecialchars($data['agama']); ?></td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td>
                            <?php
                            $pekerjaan_id = (int) $data['pekerjaan_id']; // amankan dari injeksi
                            $query_pekerjaan = "SELECT nama_pekerjaan FROM pekerjaan WHERE id_pekerjaan = $pekerjaan_id";
                            $result_pekerjaan = mysqli_query($conn, $query_pekerjaan);

                            if ($result_pekerjaan && mysqli_num_rows($result_pekerjaan) > 0) {
                                $data_pekerjaan = mysqli_fetch_assoc($result_pekerjaan);
                                echo htmlspecialchars($data_pekerjaan['nama_pekerjaan']);
                            } else {
                                echo "<span class='text-muted'>Tidak diketahui</span>";
                            }
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Alamat</th>
                        <td><?php echo htmlspecialchars($data['alamat']); ?></td>
                    </tr>
                    <tr>
                        <th>Kecamatan</th>
                        <td>
                            <?php
                            $kecamatan_id = (int) $data['kecamatan_id'];
                            $query_kecamatan = "SELECT nama_kecamatan FROM kecamatan WHERE id_kecamatan = $kecamatan_id";
                            $result_kecamatan = mysqli_query($conn, $query_kecamatan);

                            if ($result_kecamatan && mysqli_num_rows($result_kecamatan) > 0) {
                                $data_kecamatan = mysqli_fetch_assoc($result_kecamatan);
                                echo htmlspecialchars($data_kecamatan['nama_kecamatan']);
                            } else {
                                echo "<span class='text-muted'>Tidak diketahui</span>";
                            }
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <th>No Komponen</th>
                        <td><?php echo htmlspecialchars($data['no_komponen']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <td><?php echo htmlspecialchars($data['tanggal_pengajuan']); ?></td>
                    </tr>
                    <tr>
                        <th>Keperluan</th>
                        <td><?php echo htmlspecialchars($data['keperluan']); ?></td>
                    </tr>
                    <tr>
                        <th>Progres</th>
                        <td><?php echo htmlspecialchars($data['progres']); ?></td>
                    </tr>
                    <tr>
                        <th>Lampiran Persyaratan</th>
                        <td>
                            <?php if (!empty($data['lampiran'])): ?>
                                <embed src="../uploads/<?php echo urlencode($data['lampiran']); ?>" type="application/pdf" width="100%" height="600px" />
                            <?php else: ?>
                                <span class="text-muted">Tidak ada lampiran</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <form method="post" class="mt-3">

                    <a href="data_pengajuan.php" class="btn btn-secondary ms-2">Kembali</a>

                    <div class="modal fade" id="modalTerima" tabindex="-1" aria-labelledby="modalTerimaLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTerimaLabel">Konfirmasi Penerimaan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menerima berkas SKCK atas nama <strong><?php echo $username_pemohon; ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form method="post" class="d-inline">
                                        <button type="submit" name="terima_berkas" class="btn btn-success">Terima</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalTolakChat" tabindex="-1" aria-labelledby="modalTolakChatLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTolakChatLabel">Kirim Pesan Penolakan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <p>Anda akan mengirim pesan penolakan kepada <strong><?php echo $username_pemohon; ?></strong>.</p>
                                        <div class="mb-3">
                                            <label for="pesan_tolak" class="form-label">Pesan:</label>
                                            <textarea class="form-control" id="pesan_tolak" name="pesan_tolak" rows="5" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="kirim_pesan_tolak" class="btn btn-danger">Kirim Pesan Tolak</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>