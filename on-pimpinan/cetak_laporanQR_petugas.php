<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

// Query untuk mengambil data user role petugas
$sql_petugas = "SELECT
                    u.id_user,
                    u.nama,
                    u.email
                    FROM users u
                    WHERE u.role = 'petugas'
                    ORDER BY u.nama ASC";
$result_datpetugas = mysqli_query($conn, $sql_petugas);
$data_datpetugas = mysqli_fetch_all($result_datpetugas, MYSQLI_ASSOC);

// Info QR Code
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
$qr_data = "Laporan Data Petugas Sat Intelkam, Ditandatangani oleh: $nama_pimpinan, Tanggal: $tanggal_ttd $waktu_ttd";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_data);

// FPDF Custom Class
class PDF extends FPDF
{
    public $conn;
    public $qr_api_url;

    function __construct($conn = null, $qr_api_url = '')
    {
        parent::__construct();
        $this->conn = $conn;
        $this->qr_api_url = $qr_api_url;
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

            $this->Image(__DIR__ . '/../dist/img/logo.jpeg', $xPosition, $this->GetY(), $logoWidth);
            $this->Ln(23);
        }

        $this->SetFont('Arial', 'B', 12);
        $judul = 'Laporan Petugas Pelayanan Satuan Intelkam';
        $this->Cell(0, 10, $judul, 0, 1, 'C');
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

        // Centered a.n. text
        $this->SetFont('Arial', 'U', 11);
        $text = 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL';
        $this->Cell(0, 6, $text, 0, 1, 'C');

        // Query personil
        $query = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
        $nama = $jabatan = $nrp = '';
        if ($this->conn) {
            $stmt = $this->conn->prepare($query);
            if ($stmt) {
                $stmt->execute();
                $stmt->bind_result($nama, $jabatan, $nrp);
                $stmt->fetch();
                $stmt->close();
            }
        }

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 4, 'KEPALA SATUAN INTELKAM', 0, 1, 'C');
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
        $this->SetFont('Arial', '', 12);

        // Pastikan folder temp ada
        $tempDir = __DIR__ . '/temp';
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
        $qrcode_file = $tempDir . '/qrcode_' . date('YmdHis') . '_' . uniqid() . '.png';
        file_put_contents($qrcode_file, file_get_contents($this->qr_api_url));

        if (file_exists($qrcode_file)) {
            $this->Image($qrcode_file, 95, $this->GetY() - 1, 25, 25);
            unlink($qrcode_file);
        }
    }

    function TabelPetugas($header, $data)
    {
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', '');

        $w = array(10, 60, 80); // No, Nama, Email
        $this->SetFont('Arial', 'B', 10);
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', false);
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $fill = 0;
        $no = 1;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $no++, '1', 0, 'C', $fill);
            $this->Cell($w[1], 6, $row['nama'], '1', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row['email'], '1', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// Generate PDF
$pdf = new PDF($conn, $qr_api_url);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();
$header = array('No', 'Nama Petugas', 'Email');
$pdf->TabelPetugas($header, $data_datpetugas);
$pdf->Output('laporan_data_petugas_' . date('YmdHis') . '.pdf', 'I');
