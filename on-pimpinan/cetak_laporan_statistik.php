<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

require('../fpdf/fpdf.php');
include '../include/koneksi.php';

$bulan_map = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

function ambil_data($conn, $tabel)
{
    $result = array_fill(1, 12, 0);
    $query = "SELECT MONTH(tanggal_pengajuan) AS bulan, COUNT(*) AS jumlah FROM $tabel WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE()) GROUP BY bulan";
    $res = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $result[(int)$row['bulan']] = (int)$row['jumlah'];
    }
    return $result;
}

$skck = ambil_data($conn, 'skck');
$sik = ambil_data($conn, 'sik');
$sttp = ambil_data($conn, 'sttp');

$statistik = [];
foreach (range(1, 12) as $b) {
    $statistik[] = [
        'bulan' => $bulan_map[$b],
        'skck' => $skck[$b],
        'sik' => $sik[$b],
        'sttp' => $sttp[$b]
    ];
}

$q = "SELECT nama, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$hasil = mysqli_query($conn, $q);
$pimpinan = mysqli_fetch_assoc($hasil);
$nama_pimpinan = $pimpinan['nama'] ?? '';
$nrp_pimpinan = $pimpinan['nrp'] ?? '';

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

            $margin = 21;
            $logoWidth = 23;
            $pageWidth = $this->GetPageWidth();
            $usableWidth = $pageWidth - (2 * $margin);
            $xPosition = $margin + (($usableWidth - $logoWidth) / 2);

            $this->Image('../dist/img/logo.jpeg', $xPosition, $this->GetY(), $logoWidth);
            $this->Ln(23);

            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, 'Laporan Statistik Jumlah Pelayanan Administrasi Sat Intelkam', 0, 1, 'C');
            $this->Ln(3);
            $this->SetFont('Arial', '', 10);
            $this->MultiCell(0, 6, "Laporan ini menampilkan statistik permohonan SKCK, SIK, dan STTP di Sat Intelkam Polres Barito Kuala selama tahun berjalan.", 0, 'J');
            $this->Ln(5);
        }
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }

    function GrafikStatistik($data)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Grafik Permohonan SKCK, SIK, dan STTP', 0, 1, 'C');
        $this->Ln(2);

        $max = 10;
        foreach ($data as $d) {
            $max = max($max, $d['skck'], $d['sik'], $d['sttp']);
        }
        $max = ceil($max / 10) * 10;

        $chartX = 20;
        $chartY = $this->GetY() + 5;
        $chartW = 150;
        $chartH = 50;
        $barW = 4;
        $gap = 6;
        $groupW = $barW * 3 + $gap;

        // Sumbu Y
        $this->SetFont('Arial', '', 7);
        for ($i = 0; $i <= 5; $i++) {
            $y = $chartY + $chartH - ($i * ($chartH / 5));
            $val = $i * ($max / 5);
            $this->SetXY($chartX - 10, $y - 2);
            $this->Cell(8, 3, (int)$val, 0, 0, 'R');
            $this->SetDrawColor(220, 220, 220);
            $this->Line($chartX, $y, $chartX + $chartW, $y);
        }

        // Grafik batang
        $x = $chartX + 10;
        $colors = [[52, 152, 219], [241, 196, 15], [46, 204, 113]]; // SKCK, SIK, STTP

        foreach ($data as $i => $row) {
            $vals = [$row['skck'], $row['sik'], $row['sttp']];
            for ($j = 0; $j < 3; $j++) {
                $h = ($vals[$j] / $max) * $chartH;
                $barX = $x + ($groupW * $i) + ($barW * $j);
                $barY = $chartY + $chartH - $h;
                $this->SetFillColor(...$colors[$j]);
                $this->Rect($barX, $barY, $barW, $h, 'F');

                if ($vals[$j] > 0) {
                    $this->SetXY($barX, $barY - 3);
                    $this->Cell($barW + 1, 3, $vals[$j], 0, 0, 'C');
                }
            }

            // Label bulan
            $this->SetXY($x + ($groupW * $i), $chartY + $chartH + 1);
            $this->Cell($groupW, 3, substr($row['bulan'], 0, 3), 0, 0, 'C');
        }

        // Legenda warna
        $this->Ln($chartH + 8);
        $this->SetFont('Arial', '', 8);
        $legendX = 250; // Posisikan di kanan grafik
        $legendY = $chartY + 5;

        $labels = ['SKCK', 'SIK', 'STTP'];
        foreach ($labels as $i => $label) {
            $this->SetFillColor(...$colors[$i]);
            $this->Rect($legendX, $legendY + ($i * 6), 5, 5, 'F');
            $this->SetXY($legendX + 7, $legendY + ($i * 6));
            $this->Cell(20, 5, $label, 0, 1, 'L');
        }

        $this->Ln(90);
    }


    function TabelStatistik($data)
    {
        $w = [10, 30, 25, 25, 25];
    $totalWidth = array_sum($w);
    $startX = ($this->GetPageWidth() - $totalWidth) / 2;
        $this->SetX($startX);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 6, 'No', 1);
        $this->Cell(30, 6, 'Bulan', 1);
        $this->Cell(25, 6, 'SKCK', 1);
        $this->Cell(25, 6, 'SIK', 1);
        $this->Cell(25, 6, 'STTP', 1);
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        $no = 1;
        foreach ($data as $row) {
            $this->SetX($startX);
            $this->Cell(10, 6, $no++, 1);
            $this->Cell(30, 6, $row['bulan'], 1);
            $this->Cell(25, 6, $row['skck'], 1, 0, 'C');
            $this->Cell(25, 6, $row['sik'], 1, 0, 'C');
            $this->Cell(25, 6, $row['sttp'], 1, 0, 'C');
            $this->Ln();
        }
    }

    function TtdAkhir($nama, $nrp)
    {
        $this->Ln(20);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Marabahan, ' . date('d F Y'), 0, 1, 'C');
        $this->Ln(3);
        $this->Cell(0, 6, 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL', 0, 1, 'C');
        $this->Cell(0, 6, 'KEPALA SATUAN INTELKAM', 0, 1, 'C');
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, $nama, 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $nrp, 0, 1, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();
$pdf->GrafikStatistik($statistik);
$pdf->TabelStatistik($statistik);
$pdf->TtdAkhir($nama_pimpinan, $nrp_pimpinan);
$pdf->Output('I', 'laporan_statistik_pelayanan_' . date('YmdHis') . '.pdf');
