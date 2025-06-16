<?php
include '../include/koneksi.php';

$sql = "SELECT 
            AVG(CASE WHEN q1 = 'sangat_setuju' THEN 4 WHEN q1 = 'setuju' THEN 3 WHEN q1 = 'kurang_setuju' THEN 2 WHEN q1 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q1,
            AVG(CASE WHEN q2 = 'sangat_setuju' THEN 4 WHEN q2 = 'setuju' THEN 3 WHEN q2 = 'kurang_setuju' THEN 2 WHEN q2 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q2,
            AVG(CASE WHEN q3 = 'sangat_setuju' THEN 4 WHEN q3 = 'setuju' THEN 3 WHEN q3 = 'kurang_setuju' THEN 2 WHEN q3 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q3,
            AVG(CASE WHEN q4 = 'sangat_setuju' THEN 4 WHEN q4 = 'setuju' THEN 3 WHEN q4 = 'kurang_setuju' THEN 2 WHEN q4 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q4,
            AVG(CASE WHEN q5 = 'sangat_setuju' THEN 4 WHEN q5 = 'setuju' THEN 3 WHEN q5 = 'kurang_setuju' THEN 2 WHEN q5 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q5,
            AVG(CASE WHEN q6 = 'sangat_setuju' THEN 4 WHEN q6 = 'setuju' THEN 3 WHEN q6 = 'kurang_setuju' THEN 2 WHEN q6 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q6,
            AVG(CASE WHEN q7 = 'sangat_setuju' THEN 4 WHEN q7 = 'setuju' THEN 3 WHEN q7 = 'kurang_setuju' THEN 2 WHEN q7 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q7,
            AVG(CASE WHEN q8 = 'sangat_setuju' THEN 4 WHEN q8 = 'setuju' THEN 3 WHEN q8 = 'kurang_setuju' THEN 2 WHEN q8 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q8,
            AVG(CASE WHEN q9 = 'sangat_setuju' THEN 4 WHEN q9 = 'setuju' THEN 3 WHEN q9 = 'kurang_setuju' THEN 2 WHEN q9 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q9,
            AVG(CASE WHEN q10 = 'sangat_setuju' THEN 4 WHEN q10 = 'setuju' THEN 3 WHEN q10 = 'kurang_setuju' THEN 2 WHEN q10 = 'tidak_setuju' THEN 1 ELSE 0 END) AS avg_q10
        FROM survey_kepuasan";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

$labels = [
    'Kemudahan Informasi',
    'Tampilan Aplikasi',
    'Kecepatan Proses',
    'Manfaat Status & History',
    'Responsif Sistem',
    'Keamanan Aplikasi',
    'Kualitas Informasi',
    'Rekomendasi',
    'Aksesibilitas',
    'Kepuasan Keseluruhan'
];

$averageScores = [
    floatval($data['avg_q1']),
    floatval($data['avg_q2']),
    floatval($data['avg_q3']),
    floatval($data['avg_q4']),
    floatval($data['avg_q5']),
    floatval($data['avg_q6']),
    floatval($data['avg_q7']),
    floatval($data['avg_q8']),
    floatval($data['avg_q9']),
    floatval($data['avg_q10'])
];

$response = [
    'labels' => $labels,
    'averageScores' => $averageScores
];

header('Content-Type: application/json');
echo json_encode($response);
?>