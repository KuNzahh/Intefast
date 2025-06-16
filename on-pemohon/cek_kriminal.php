<?php
include '../include/koneksi.php';
header('Content-Type: application/json');

$nik = $_GET['nik'] ?? '';

if (!empty($nik)) {
    $query = mysqli_query($conn, "SELECT id_kriminal, cttkriminal FROM kriminal WHERE nik = '$nik'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        echo json_encode(['kriminal_id' => $row['id_kriminal'], 'cttkriminal' => $row['cttkriminal']]);
    } else {
        echo json_encode(['kriminal_id' => 0]);
    }
} else {
    echo json_encode(['kriminal_id' => 0]);
}
?>