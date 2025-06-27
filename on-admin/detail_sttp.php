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

$query_sttp_detail = "
    SELECT
        sttp.*,
        users.username,
        kam.nama_kampanye,
        kec.nama_kecamatan
    FROM sttp
    LEFT JOIN users ON sttp.user_id = users.id_user
    LEFT JOIN kampanye kam ON sttp.kampanye_id = kam.id_kampanye
    LEFT JOIN kecamatan kec ON sttp.kecamatan_id = kec.id_kecamatan
    WHERE sttp.id_sttp = $id
";

$result_sttp_detail = mysqli_query($conn, $query_sttp_detail);

if (!$result_sttp_detail || mysqli_num_rows($result_sttp_detail) == 0) {
    echo "<h3>Data STTP tidak ditemukan.</h3>";
    exit;
}

$data_sttp = mysqli_fetch_assoc($result_sttp_detail);
$username_pemohon = htmlspecialchars($data_sttp['username']);
$user_id_pemohon = $data_sttp['user_id']; // Pastikan kolom ini ada di tabel sttp jika diperlukan untuk chat
$dasar_lama = htmlspecialchars($data_sttp['memperhatikan']); // Memakai kolom 'memperhatikan' sebagai pengganti 'dasar'

if (isset($_POST['terima_berkas_sttp'])) {
    $update_query_terima = "UPDATE sttp SET progres = 'diterima' WHERE id_sttp = $id";
    if (mysqli_query($conn, $update_query_terima)) {
        echo "<script>alert('Berkas STTP atas nama " . htmlspecialchars($data_sttp['nama_paslon']) . " telah diterima oleh $petugas_nama.'); window.location.href='berkas_sttp.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui status terima berkas STTP.');</script>";
    }
}

if (isset($_POST['kirim_pesan_tolak_sttp'])) {
    $pesan_tolak_sttp = isset($_POST['pesan_tolak_sttp']) ? htmlspecialchars($_POST['pesan_tolak_sttp']) : '';
    if (!empty($pesan_tolak_sttp)) {
        $update_progres_query_tolak = "UPDATE sttp SET progres = 'ditolak' WHERE id_sttp = $id";
        if (mysqli_query($conn, $update_progres_query_tolak)) {
                echo "<script>alert('Pesan penolakan STTP telah dikirim kepada " . htmlspecialchars($data_sttp['nama_paslon']) . ". Status berkas STTP diubah menjadi ditolak.'); window.location.href='berkas_sttp.php';</script>";
            
        } else {
            echo "<script>alert('Gagal memperbarui status berkas STTP menjadi ditolak.');</script>";
        }
    } else {
        echo "<script>alert('Pesan penolakan STTP tidak boleh kosong.');</script>";
    }
}

// Proses simpan memperhatikan (menggantikan dasar)
if (isset($_POST['simpan_memperhatikan'])) {
    $memperhatikan_baru = isset($_POST['memperhatikan']) ? htmlspecialchars($_POST['memperhatikan']) : '';
    $update_memperhatikan_query = "UPDATE sttp SET memperhatikan = '$memperhatikan_baru' WHERE id_sttp = $id";
    if (mysqli_query($conn, $update_memperhatikan_query)) {
        echo "<script>alert('Catatan berhasil disimpan.'); window.location.href='berkas_sttp.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan catatan: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Detail STTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Detail Data Pemohon STTP</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Username</th>
                        <td><?php echo $username_pemohon; ?></td>
                    </tr>
                    <tr>
                        <th>Nama Paslon</th>
                        <td><?php echo htmlspecialchars($data_sttp['nama_paslon']); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?php echo htmlspecialchars($data_sttp['alamat']); ?></td>
                    </tr>
                    <tr>
                        <th>Penanggung Jawab</th>
                        <td><?php echo htmlspecialchars($data_sttp['penanggung_jawab']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama Kampanye</th>
                        <td><?php echo htmlspecialchars($data_sttp['nama_kampanye']); ?></td>
                    </tr>
                    <tr>
                        <th>Tgl Kampanye</th>
                        <td><?php echo htmlspecialchars($data_sttp['tgl_kampanye']); ?></td>
                    </tr>
                    <tr>
                        <th>Tempat</th>
                        <td><?php echo htmlspecialchars($data_sttp['tempat']); ?></td>
                    </tr>
                    <tr>
                        <th>Kecamatan</th>
                        <td><?php echo htmlspecialchars($data_sttp['nama_kecamatan']); ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Peserta</th>
                        <td><?php echo htmlspecialchars($data_sttp['jumlah_peserta']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama Jurkam</th>
                        <td><?php echo htmlspecialchars($data_sttp['nama_jurkam']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <td><?php echo htmlspecialchars($data_sttp['tanggal_pengajuan']); ?></td>
                    </tr>
                    <tr>
                        <th>Catatan Memperhatikan</th>
                        <td>
                            <form method="post" class="d-flex align-items-center">
                                <input type="text" class="form-control form-control-sm me-2" name="memperhatikan" value="<?php echo htmlspecialchars($data_sttp['memperhatikan']); ?>">
                                <button type="submit" name="simpan_memperhatikan" class="btn btn-sm btn-primary">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <th>Lampiran</th>
                        <td>
                            <?php if (!empty($data_sttp['lampiran'])): ?>
                                <embed src="../uploads/<?php echo urlencode($data_sttp['lampiran']); ?>" type="application/pdf" width="100%" height="600px" />
                            <?php else: ?>
                                <span class="text-muted">Tidak ada lampiran</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Berkas Online</th>
                        <td>
                            <?php if (!empty($data_sttp['berkasOnline'])): ?>
                                <a href="../uploads/berkasOnline/<?php echo urlencode($data_sttp['berkasOnline']); ?>" target="_blank">Lihat Berkas Online</a>
                            <?php else: ?>
                                <span class="text-muted">Belum ada Berkas Online</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Progres</th>
                        <td><?php echo htmlspecialchars($data_sttp['progres']); ?></td>
                    </tr>
                </table>

                <form method="post" class="mt-3">
                    <a href="data_pengajuan.php" class="btn btn-secondary ms-2">Kembali</a>

                    <div class="modal fade" id="modalTerimaSttp" tabindex="-1" aria-labelledby="modalTerimaSttpLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTerimaSttpLabel">Konfirmasi Penerimaan STTP</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menerima berkas STTP atas nama <strong><?php echo htmlspecialchars($data_sttp['nama_paslon']); ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form method="post" class="d-inline">
                                        <button type="submit" name="terima_berkas_sttp" class="btn btn-success">Terima</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalTolakChatSttp" tabindex="-1" aria-labelledby="modalTolakChatSttpLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTolakChatSttpLabel">Kirim Pesan Penolakan STTP</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <p>Anda akan mengirim pesan penolakan STTP kepada <strong><?php echo htmlspecialchars($data_sttp['nama_paslon']); ?></strong>.</p>
                                        <div class="mb-3">
                                            <label for="pesan_tolak_sttp" class="form-label">Pesan:</label>
                                            <textarea class="form-control" id="pesan_tolak_sttp" name="pesan_tolak_sttp" rows="5" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="kirim_pesan_tolak_sttp" class="btn btn-danger">Kirim Pesan Tolak</button>
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