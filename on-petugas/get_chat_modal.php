<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak.";
    exit();
}

include '../include/koneksi.php';
$id_petugas = $_SESSION['user_id'];
$id_pemohon = isset($_GET['id_pemohon']) ? intval($_GET['id_pemohon']) : 0;

if ($id_pemohon > 0) {
    $query_pesan = "SELECT c.*, u_pengirim.username AS nama_pengirim
                    FROM chat c
                    JOIN users u_pengirim ON c.id_pengirim = u_pengirim.id_user
                    WHERE (c.id_pengirim = ? AND c.id_penerima = ?) OR (c.id_pengirim = ? AND c.id_penerima = ?)
                    ORDER BY c.timestamp ASC";
    $stmt_pesan = mysqli_prepare($conn, $query_pesan);
    mysqli_stmt_bind_param($stmt_pesan, "iiii", $id_pemohon, $id_petugas, $id_petugas, $id_pemohon);
    mysqli_stmt_execute($stmt_pesan);
    $result_pesan = mysqli_stmt_get_result($stmt_pesan);
    $daftar_pesan = mysqli_fetch_all($result_pesan, MYSQLI_ASSOC);

    $output = '';
    if (!empty($daftar_pesan)) {
        foreach ($daftar_pesan as $pesan) {
            $is_petugas = ($pesan['id_pengirim'] == $id_petugas);
            $align = $is_petugas ? 'justify-content-end' : 'justify-content-start';
            $bg = $is_petugas ? 'bg-primary text-white' : 'bg-light text-dark border';
            $pengirim = $is_petugas ? 'Anda' : htmlspecialchars($pesan['nama_pengirim']);
            $rounded = 'rounded-3';

            $output .= '<div class="d-flex ' . $align . ' mb-2">';
            $output .= '    <div class="' . $rounded . ' p-2 ' . $bg . '" style="max-width: 80%;">';
            $output .= '        <div class="small text-muted ' . ($is_petugas ? 'text-end' : 'text-start') . '">' . date('H:i', strtotime($pesan['timestamp'])) . '</div>';
            $output .= '        ' . htmlspecialchars($pesan['pesan']);
            $output .= '    </div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p class="text-center">Belum ada pesan.</p>';
    }
    echo $output;
} else {
    echo "ID pemohon tidak valid.";
}
?>