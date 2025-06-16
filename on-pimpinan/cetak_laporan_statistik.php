<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

require('../fpdf/fpdf.php');
include '../include/koneksi.php';

$all_months = range(1, 12);
$statistik_skck = array_fill(1, 12, 0);
$statistik_sik = array_fill(1, 12, 0);
$statistik_sttp = array_fill(1, 12, 0);

// SKCK
$sql_skck = "SELECT MONTH(tanggal_pengajuan) AS bulan, COUNT(*) AS jumlah FROM skck WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE()) GROUP BY bulan";
$result_skck = mysqli_query($conn, $sql_skck);
while ($row = mysqli_fetch_assoc($result_skck)) {
    $statistik_skck[(int)$row['bulan']] = (int)$row['jumlah'];
}

// SIK
$sql_sik = "SELECT MONTH(tanggal_pengajuan) AS bulan, COUNT(*) AS jumlah FROM sik WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE()) GROUP BY bulan";
$result_sik = mysqli_query($conn, $sql_sik);
while ($row = mysqli_fetch_assoc($result_sik)) {
    $statistik_sik[(int)$row['bulan']] = (int)$row['jumlah'];
}

// STTP
$sql_sttp = "SELECT MONTH(tanggal_pengajuan) AS bulan, COUNT(*) AS jumlah FROM sttp WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE()) GROUP BY bulan";
$result_sttp = mysqli_query($conn, $sql_sttp);
while ($row = mysqli_fetch_assoc($result_sttp)) {
    $statistik_sttp[(int)$row['bulan']] = (int)$row['jumlah'];
}

// Gabungkan data statistik per bulan
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$statistik = [];
foreach ($all_months as $bulan) {
    $statistik[] = [
        'bulan' => $nama_bulan[$bulan],
        'skck' => $statistik_skck[$bulan],
        'sik' => $statistik_sik[$bulan],
        'sttp' => $statistik_sttp[$bulan]
    ];
}

// Ambil pimpinan
$query = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$res = mysqli_query($conn, $query);
$pimpinan = mysqli_fetch_assoc($res);
$nama_pimpinan = $pimpinan['nama'] ?? '';
$nrp_pimpinan = $pimpinan['nrp'] ?? '';

class PDF extends FPDF
{
    function Header()
    {
        if ($this->PageNo() == 1) {
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 5, 'KEPOLISIAN NEGARA REPUBLIK INDONESIA', 0, 1, 'C');
            $this->Cell(0, 5, 'DAERAH KALIMANTAN SELATAN', 0, 1, 'C');
            $this->Cell(0, 5, 'RESOR BARITO KUALA', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, 'Jl. Gusti M. Seman No. 1 Marabahan 70511', 'B', 1, 'C');
            $this->Ln(5);
            $this->Image('../dist/img/logo.jpeg', ($this->GetPageWidth() - 23) / 2, $this->GetY(), 23);
            $this->Ln(25);
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Laporan Statistik Pelayanan SKCK, SIK, dan STTP', 0, 1, 'C');
        $this->Ln(2);
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

    function TabelStatistik($data)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 7, 'No', 1, 0, 'C');
        $this->Cell(50, 7, 'Bulan', 1, 0, 'C');
        $this->Cell(40, 7, 'SKCK', 1, 0, 'C');
        $this->Cell(40, 7, 'SIK', 1, 0, 'C');
        $this->Cell(40, 7, 'STTP', 1, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $no = 1;
        foreach ($data as $row) {
            $this->Cell(10, 6, $no++, 1, 0, 'C');
            $this->Cell(50, 6, $row['bulan'], 1, 0, 'L');
            $this->Cell(40, 6, $row['skck'], 1, 0, 'C');
            $this->Cell(40, 6, $row['sik'], 1, 0, 'C');
            $this->Cell(40, 6, $row['sttp'], 1, 1, 'C');
        }
    }
}

// Cetak PDF
$pdf = new PDF();
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();
$pdf->TabelStatistik($statistik);
$pdf->Output('I', 'laporan_statistik_pelayanan_' . date('YmdHis') . '.pdf');
