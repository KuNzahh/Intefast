<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

$sql_intelijen_keamanan = "SELECT
                                kec.nama_kecamatan,
                                COUNT(k.id_kriminal) AS jumlah_kasus,
                                CASE
                                    WHEN COUNT(k.id_kriminal) > 10 THEN 'Tinggi'
                                    WHEN COUNT(k.id_kriminal) > 5 THEN 'Sedang'
                                    ELSE 'Rendah'
                                END AS situasi_keamanan
                            FROM kriminal k
                            JOIN kecamatan kec ON k.kecamatan_id = kec.id_kecamatan
                            GROUP BY kec.nama_kecamatan
                            ORDER BY kec.nama_kecamatan";
$result_intel = mysqli_query($conn, $sql_intelijen_keamanan);
$data_intel = mysqli_fetch_all($result_intel, MYSQLI_ASSOC);

$nama_pimpinan = "";
$jabatan_pimpinan = "";
$nrp_pimpinan = "";
$query_pimpinan = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$result_pimpinan = mysqli_query($conn, $query_pimpinan);
if ($row_pimpinan = mysqli_fetch_assoc($result_pimpinan)) {
    $nama_pimpinan = $row_pimpinan['nama'];
    $jabatan_pimpinan = $row_pimpinan['jabatan'];
    $nrp_pimpinan = $row_pimpinan['nrp'];
}

$tanggal_ttd = date('d-m-Y');
$waktu_ttd = date('H:i:s');
$qr_data = "Laporan Intelijen Situasi Keamanan, Ditandatangani oleh: $nama_pimpinan, Tanggal: $tanggal_ttd $waktu_ttd";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_data);

class PDF extends FPDF
{
    public $conn;
    function __construct($conn)
    {
        parent::__construct();
        $this->conn = $conn;
    }

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
        }

        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Intelijen Situasi Keamanan',0,1,'C');
    }

    function Footer()
    {
        $lineHeight = 6;
        $textX = 10;

        $this->SetY(-90);

        $this->SetFont('Arial', 'U', 11);
        $this->Cell(45, $lineHeight, 'Dikeluarkan di', 0, 0, 'L');
        $this->SetFont('Arial', '', 11);
        $this->Cell(2, $lineHeight, ':', 0, 0, 'L');
        $this->Cell(5, $lineHeight, 'Marabahan', 0, 1, 'L');

        $this->SetFont('Arial', 'I', 10);
        $this->SetX($textX);
        $this->Cell(0, $lineHeight - 2, 'Issued in Marabahan', 0, 1, 'L');

        $this->Ln(2);

        $this->SetFont('Arial', 'U', 11);
        $this->SetX($textX);
        $this->Cell(45, $lineHeight, 'Pada tanggal', 0, 0, 'L');
        $this->SetFont('Arial', '', 11);
        $this->Cell(2, $lineHeight, ':', 0, 0, 'L');
        $this->Cell(50, $lineHeight, date('d F Y'), 0, 1, 'L');

        $this->SetFont('Arial', 'I', 10);
        $this->SetX($textX);
        $this->Cell(0, $lineHeight - 2, 'on', 0, 1, 'L');

        $this->Ln(4);

        $this->SetFont('Arial', 'U', 11);
        $text = 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL';
        $this->Cell(0, 6, $text, 0, 1, 'C');

        $query = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $nama = $jabatan = $nrp = '';
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($nama, $jabatan, $nrp);
            $stmt->fetch();
            $stmt->close();
        }

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 4, 'KEPALA SATUAN INTELKAM', 0, 4, 'C');
        $this->Ln(30);

        if ($nama && $nrp) {
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(0, 7, $nama, 0, 1, 'C');
            $x1 = ($this->GetPageWidth() - 66) / 2;
            $x2 = $x1 + 66;
            $y = $this->GetY();
            $this->Line($x1, $y, $x2, $y);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 5, "$nrp", 0, 1, 'C');
        }

        $this->SetY(-52);
        $this->SetFont('Arial','',12);
        global $qr_api_url;

        if (!is_dir('temp')) mkdir('temp');
        $qrcode_file = 'temp/qrcode_' . date('YmdHis') . '_' . uniqid() . '.png';
        file_put_contents($qrcode_file, file_get_contents($qr_api_url));

        if (file_exists($qrcode_file)) {
            $this->Image($qrcode_file, 95, $this->GetY() - 1, 25, 25);
            unlink($qrcode_file);
        }
    }

    function TabelKeamanan($header, $data)
    {
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('');

        $w = array(10, 80, 40, 50);
        $this->SetFont('Arial','B',10);
        foreach ($header as $i => $col)
            $this->Cell($w[$i],7,$col,1,0,'C',false);
        $this->Ln();

        $this->SetFont('Arial','',10);
        $no = 1;
        foreach ($data as $row)
        {
            $this->Cell($w[0],6,$no++,'1',0,'C',false);
            $this->Cell($w[1],6,$row['nama_kecamatan'],'1',0,'L',false);
            $this->Cell($w[2],6,$row['jumlah_kasus'],'1',0,'C',false);
            $this->Cell($w[3],6,$row['situasi_keamanan'],'1',0,'C',false);
            $this->Ln();
        }
        $this->Cell(array_sum($w),0,'','T');
    }
}

$pdf = new PDF($conn);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();

$header = array('No', 'Nama Kecamatan', 'Jumlah Kasus', 'Situasi');
$pdf->TabelKeamanan($header, $data_intel);

$pdf->Output('laporan_intelijen_keamanan_' . date('YmdHis') . '.pdf', 'I');
