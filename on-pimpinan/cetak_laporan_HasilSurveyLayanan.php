<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

require('../fpdf/fpdf.php');
include '../include/koneksi.php';

// Konversi jawaban string ke angka dengan CASE agar AVG valid
$query = "
SELECT 
    AVG(CASE WHEN q1 = 'sangat_setuju' THEN 4 WHEN q1 = 'setuju' THEN 3 WHEN q1 = 'kurang_setuju' THEN 2 WHEN q1 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q1,
    AVG(CASE WHEN q2 = 'sangat_setuju' THEN 4 WHEN q2 = 'setuju' THEN 3 WHEN q2 = 'kurang_setuju' THEN 2 WHEN q2 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q2,
    AVG(CASE WHEN q3 = 'sangat_setuju' THEN 4 WHEN q3 = 'setuju' THEN 3 WHEN q3 = 'kurang_setuju' THEN 2 WHEN q3 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q3,
    AVG(CASE WHEN q4 = 'sangat_setuju' THEN 4 WHEN q4 = 'setuju' THEN 3 WHEN q4 = 'kurang_setuju' THEN 2 WHEN q4 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q4,
    AVG(CASE WHEN q5 = 'sangat_setuju' THEN 4 WHEN q5 = 'setuju' THEN 3 WHEN q5 = 'kurang_setuju' THEN 2 WHEN q5 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q5,
    AVG(CASE WHEN q6 = 'sangat_setuju' THEN 4 WHEN q6 = 'setuju' THEN 3 WHEN q6 = 'kurang_setuju' THEN 2 WHEN q6 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q6,
    AVG(CASE WHEN q7 = 'sangat_setuju' THEN 4 WHEN q7 = 'setuju' THEN 3 WHEN q7 = 'kurang_setuju' THEN 2 WHEN q7 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q7,
    AVG(CASE WHEN q8 = 'sangat_setuju' THEN 4 WHEN q8 = 'setuju' THEN 3 WHEN q8 = 'kurang_setuju' THEN 2 WHEN q8 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q8,
    AVG(CASE WHEN q9 = 'sangat_setuju' THEN 4 WHEN q9 = 'setuju' THEN 3 WHEN q9 = 'kurang_setuju' THEN 2 WHEN q9 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q9,
    AVG(CASE WHEN q10 = 'sangat_setuju' THEN 4 WHEN q10 = 'setuju' THEN 3 WHEN q10 = 'kurang_setuju' THEN 2 WHEN q10 = 'tidak_setuju' THEN 1 ELSE NULL END) AS q10
FROM survey_kepuasan
";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Label pertanyaan
$labels = [
    'q1' => 'Seberapa mudah Anda menemukan informasi yang dibutuhkan di aplikasi ini?',
    'q2' => 'Bagaimana pendapat Anda tentang tampilan antarmuka (interface) aplikasi ini?',
    'q3' => 'Seberapa cepat proses pengajuan layanan melalui aplikasi ini?',
    'q4' => 'Apakah Anda merasa terbantu dengan fitur status dan history pengajuan?',
    'q5' => 'Seberapa responsif sistem terhadap aksi yang Anda lakukan?',
    'q6' => 'Apakah Anda merasa aman menggunakan aplikasi ini untuk pengajuan layanan?',
    'q7' => 'Bagaimana kualitas informasi yang disajikan dalam aplikasi ini?',
    'q8' => 'Apakah Anda akan merekomendasikan aplikasi ini kepada orang lain?',
    'q9' => 'Bagaimana kemudahan aksesibilitas aplikasi ini (misalnya, di berbagai perangkat)?',
    'q10'=> 'Secara keseluruhan, seberapa puas Anda dengan pelayanan online melalui aplikasi ini?',
];

// Format data
$survey_data = [];
foreach ($labels as $key => $pertanyaan) {
    $survey_data[] = [
        'pertanyaan' => $pertanyaan,
        'skor' => round($row[$key] ?? 0, 2)
    ];
}

// Data pimpinan
$q = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$res = mysqli_query($conn, $q);
$pimpinan = mysqli_fetch_assoc($res);
$nama_pimpinan = $pimpinan['nama'] ?? '';
$nrp_pimpinan = $pimpinan['nrp'] ?? '';

// Kelas PDF
class PDF extends FPDF
{
    function Header()
    {
        if ($this->PageNo() == 1) {
            $this->SetFont('Arial', '', 11);
            $this->Cell(85, 5, 'KEPOLISIAN NEGARA REPUBLIK INDONESIA', 0, 1, 'C');
            $this->Cell(85, 5, 'DAERAH KALIMANTAN SELATAN', 0, 1, 'C');
            $this->Cell(85, 5, 'RESOR BARITO KUALA', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(85, 5, 'Jl. Gusti M. Seman No. 1 Marabahan 70511', 'B', 1, 'C');
            $this->Ln(3);
            $this->Image('../dist/img/logo.jpeg', 94, $this->GetY(), 23);
            $this->Ln(23);
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Laporan Hasil Survei Kepuasan Masyarakat', 0, 1, 'C');
        $this->Ln(2);

        $this->SetFont('Arial', '', 11);
        $this->MultiCell(0, 7, "Berikut merupakan hasil rata-rata survei terhadap kepuasan pelayanan Satuan Intelkam Polres Barito Kuala, berdasarkan jawaban dari 10 pertanyaan inti yang diajukan kepada masyarakat.", 0, 'J');
        $this->Ln(5);
    }

    function Footer()
    {
        global $nama_pimpinan, $nrp_pimpinan;
        $this->SetY(-60);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Marabahan, ' . date('d F Y'), 0, 1, 'C');
        $this->Ln(4);
        $this->Cell(0, 6, 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL', 0, 1, 'C');
        $this->Cell(0, 6, 'KEPALA SATUAN INTELKAM', 0, 1, 'C');
        $this->Ln(20);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, $nama_pimpinan, 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $nrp_pimpinan, 0, 1, 'C');
    }

    function GrafikSurvey($data)
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Grafik Rata-rata Hasil Survei', 0, 1, 'C');
        $this->Ln(3);

        $maxWidth = 120;
        $startX = 45;
        $barHeight = 6;
        $rowSpacing = 2;
        $totalHeight = count($data) * ($barHeight + $rowSpacing);
        $baseY = $this->GetY();

        $this->SetDrawColor(200, 200, 200);
        $this->SetFont('Arial', '', 7);
        for ($i = 1; $i <= 4; $i++) {
            $x = $startX + (($i / 4) * $maxWidth);
            $this->Line($x, $baseY, $x, $baseY + $totalHeight);
            $this->SetXY($x - 2, $baseY + $totalHeight + 1);
            $this->Cell(5, 4, $i, 0, 0, 'C');
        }
        $this->SetDrawColor(0);

        foreach ($data as $i => $row) {
            $y = $baseY + ($i * ($barHeight + $rowSpacing));
            $value = floatval($row['skor']);
            $barLength = max(1, ($value / 4) * $maxWidth);

            $this->SetXY(10, $y);
            $this->SetFont('Arial', '', 9);
            $this->Cell(30, $barHeight, 'Q' . ($i + 1), 0, 0, 'L');
            $this->Cell(5, $barHeight, ':', 0, 0);

            if ($value >= 3.5) $this->SetFillColor(46, 204, 113);
            elseif ($value >= 2.5) $this->SetFillColor(241, 196, 15);
            else $this->SetFillColor(231, 76, 60);

            $this->Rect($startX, $y, $barLength, $barHeight, 'F');
            $this->Rect($startX, $y, $barLength, $barHeight);

            $this->SetXY($startX + $maxWidth + 5, $y);
            $this->Cell(20, $barHeight, number_format($value, 2) . ' / 4', 0, 0);
        }

        $this->Ln($totalHeight + 10);
    }

    function TabelSurvey($data)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 7, 'No', 1, 0, 'C');
        $this->Cell(135, 7, 'Pertanyaan Survei', 1, 0, 'C');
        $this->Cell(40, 7, 'Rata-rata Skor', 1, 1, 'C');

        $this->SetFont('Arial', '', 9);
        $no = 1;
        foreach ($data as $row) {
            $this->Cell(10, 6, $no++, 1, 0, 'C');
            $this->Cell(135, 6, $row['pertanyaan'], 1);
            $this->Cell(40, 6, number_format($row['skor'], 2) . ' / 4', 1, 1, 'C');
        }
    }
}

// Generate PDF
$pdf = new PDF();
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();
$pdf->GrafikSurvey($survey_data);
$pdf->TabelSurvey($survey_data);
$pdf->Output('I', 'laporan_survei_kepuasan_' . date('YmdHis') . '.pdf');
