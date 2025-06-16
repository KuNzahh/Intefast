<?php
session_start();
include '../include/koneksi.php';

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

$petugas_nama = $_SESSION['nama'];
$petugas_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query_sik_detail = "
    SELECT
        sik.*,
        users.username,
        users.id_user AS user_id,
        p.nama_pekerjaan,
        jk.nama_keramaian,
        kec.nama_kecamatan
    FROM sik
    LEFT JOIN users ON sik.user_id = users.id_user
    LEFT JOIN pekerjaan p ON sik.pekerjaan = p.id_pekerjaan
    LEFT JOIN jeniskeramaian jk ON sik.keramaian_id = jk.id_keramaian
    LEFT JOIN kecamatan kec ON sik.kecamatan_id = kec.id_kecamatan
    WHERE sik.id_sik = $id
";
$result_sik_detail = mysqli_query($conn, $query_sik_detail);

if (!$result_sik_detail || mysqli_num_rows($result_sik_detail) == 0) {
    echo "<h3>Data SIK tidak ditemukan.</h3>";
    exit;
}

$data_sik = mysqli_fetch_assoc($result_sik_detail);
$username_pemohon = htmlspecialchars($data_sik['username']);
$user_id_pemohon = $data_sik['user_id'];
$dasar_lama = htmlspecialchars($data_sik['dasar']);

if (isset($_POST['terima_berkas_sik'])) {
    $update_query_terima = "UPDATE sik SET progres = 'diterima' WHERE id_sik = $id";
    if (mysqli_query($conn, $update_query_terima)) {
        echo "<script>alert('Berkas SIK atas nama $username_pemohon telah diterima oleh $petugas_nama.'); window.location.href='berkas_sik.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui status terima berkas SIK.');</script>";
    }
}

if (isset($_POST['kirim_pesan_tolak_sik'])) {
    $pesan_tolak_sik = isset($_POST['pesan_tolak_sik']) ? htmlspecialchars($_POST['pesan_tolak_sik']) : '';
    if (!empty($pesan_tolak_sik)) {
        $update_progres_query_tolak = "UPDATE sik SET progres = 'ditolak' WHERE id_sik = $id";
        if (mysqli_query($conn, $update_progres_query_tolak)) {
            $query_insert_chat_tolak = "INSERT INTO chat (id_pengirim, id_penerima, pesan, timestamp)
                                        VALUES ($petugas_id, $user_id_pemohon, '$pesan_tolak_sik', NOW())";
            if (mysqli_query($conn, $query_insert_chat_tolak)) {
                echo "<script>alert('Pesan penolakan SIK telah dikirim kepada $username_pemohon. Status berkas SIK diubah menjadi ditolak.'); window.location.href='berkas_sik.php';</script>";
            } else {
                echo "<script>alert('Gagal mengirim pesan penolakan SIK.');</script>";
            }
        } else {
            echo "<script>alert('Gagal memperbarui status berkas SIK menjadi ditolak.');</script>";
        }
    } else {
        echo "<script>alert('Pesan penolakan SIK tidak boleh kosong.');</script>";
    }
}

// Proses simpan dasar
if (isset($_POST['simpan_dasar'])) {
    $dasar_baru = isset($_POST['dasar']) ? htmlspecialchars($_POST['dasar']) : '';
    $update_dasar_query = "UPDATE sik SET dasar = '$dasar_baru' WHERE id_sik = $id";
    if (mysqli_query($conn, $update_dasar_query)) {
        echo "<script>alert('Dasar berhasil disimpan.'); window.location.href='berkas_sik.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan dasar: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Detail SIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Detail Data Pemohon SIK</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Username</th>
                        <td><?php echo $username_pemohon; ?></td>
                    </tr>
                    <tr>
                        <th>Nama Instansi</th>
                        <td><?php echo htmlspecialchars($data_sik['nama_instansi']); ?></td>
                    </tr>
                    <tr>
                        <th>Penanggung Jawab</th>
                        <td><?php echo htmlspecialchars($data_sik['penanggung_jawab']); ?></td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td><?php echo htmlspecialchars($data_sik['nama_pekerjaan']); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?php echo htmlspecialchars($data_sik['alamat']); ?></td>
                    </tr>
                    <tr>
                        <th>No Telp</th>
                        <td><?php echo htmlspecialchars($data_sik['no_telp']); ?></td>
                    </tr>
                    <tr>
                        <th>Keramaian</th>
                        <td><?php echo htmlspecialchars($data_sik['nama_keramaian']); ?></td>
                    </tr>
                    <tr>
                        <th>Tgl Kegiatan</th>
                        <td><?php echo htmlspecialchars($data_sik['tgl_kegiatan']); ?></td>
                    </tr>
                    <tr>
                        <th>Tempat</th>
                        <td><?php echo htmlspecialchars($data_sik['tempat']); ?></td>
                    </tr>
                    <tr>
                        <th>Kecamatan</th>
                        <td><?php echo htmlspecialchars($data_sik['nama_kecamatan']); ?></td>
                    </tr>
                    <tr>
                        <th>Rangka</th>
                        <td><?php echo htmlspecialchars($data_sik['rangka']); ?></td>
                    </tr>
                    <tr>
                        <th>Peserta</th>
                        <td><?php echo htmlspecialchars($data_sik['peserta']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <td><?php echo htmlspecialchars($data_sik['tanggal_pengajuan']); ?></td>
                    </tr>
                    <tr>
                        <th>Lampiran Persyaratan</th>
                        <td>
                            <?php if (!empty($data_sik['lampiran'])): ?>
                                <embed src="../uploads/<?php echo urlencode($data_sik['lampiran']); ?>" type="application/pdf" width="100%" height="600px" />
                            <?php else: ?>
                                <span class="text-muted">Tidak ada lampiran</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Dasar</th>
                        <td>
                            <form method="post" class="d-flex align-items-center">
                                <input type="text" class="form-control form-control-sm me-2" name="dasar" value="<?php echo htmlspecialchars($data_sik['dasar']); ?>">
                                <button type="submit" name="simpan_dasar" class="btn btn-sm btn-primary">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <th>Berkas Online</th>
                        <td>
                            <?php if (!empty($data_sik['berkasOnline'])): ?>
                                <a href="../uploads/berkas_online_sik/<?php echo urlencode($data_sik['berkasOnline']); ?>" target="_blank">Lihat Berkas Online</a>
                            <?php else: ?>
                                <span class="text-muted">Belum ada Berkas Online</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Progres</th>
                        <td><?php echo htmlspecialchars($data_sik['progres']); ?></td>
                    </tr>
                </table>

                <form method="post" class="mt-3">
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalTerimaSik">
                        <i class="fas fa-check"></i> Terima
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTolakChatSik">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                    <a href="berkas_sik.php" class="btn btn-secondary ms-2">Kembali</a>

                    <div class="modal fade" id="modalTerimaSik" tabindex="-1" aria-labelledby="modalTerimaSikLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTerimaSikLabel">Konfirmasi Penerimaan SIK</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menerima berkas SIK atas nama <strong><?php echo $username_pemohon; ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form method="post" class="d-inline">
                                        <button type="submit" name="terima_berkas_sik" class="btn btn-success">Terima</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalTolakChatSik" tabindex="-1" aria-labelledby="modalTolakChatSikLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTolakChatSikLabel">Kirim Pesan Penolakan SIK</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <p>Anda akan mengirim pesan penolakan SIK kepada <strong><?php echo $username_pemohon; ?></strong>.</p>
                                        <div class="mb-3">
                                            <label for="pesan_tolak_sik" class="form-label">Pesan:</label>
                                            <textarea class="form-control" id="pesan_tolak_sik" name="pesan_tolak_sik" rows="5" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="kirim_pesan_tolak_sik" class="btn btn-danger">Kirim Pesan Tolak</button>
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