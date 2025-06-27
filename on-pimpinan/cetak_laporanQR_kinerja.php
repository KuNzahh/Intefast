<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

// Ambil data laporan kinerja
$sql_laporan_kinerja = "SELECT kp.*, u.nama AS nama_petugas
                         FROM kinerja_petugas kp
                         JOIN users u ON kp.id_user = u.id_user
                         WHERE u.role = 'petugas'
                         ORDER BY kp.tanggal_penilaian DESC";
$result_laporan_kinerja = mysqli_query($conn, $sql_laporan_kinerja);
$data_laporan_kinerja = mysqli_fetch_all($result_laporan_kinerja, MYSQLI_ASSOC);

// Ambil pimpinan terbaru
$query_pimpinan = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$result_pimpinan = mysqli_query($conn, $query_pimpinan);
$nama_pimpinan = $jabatan_pimpinan = $nrp_pimpinan = "";
if ($row = mysqli_fetch_assoc($result_pimpinan)) {
    $nama_pimpinan = $row['nama'];
    $jabatan_pimpinan = $row['jabatan'];
    $nrp_pimpinan = $row['nrp'];
}

// QR Data
$tanggal_ttd = date('d-m-Y');
$waktu_ttd = date('H:i:s');
$qr_data = "Laporan Kinerja Petugas, Ditandatangani oleh: $nama_pimpinan, Tanggal: $tanggal_ttd $waktu_ttd";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_data);

class PDF_Kinerja extends FPDF
{
    public $conn;
    function __construct($conn)
    {
        parent::__construct('L');
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

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Laporan Kinerja Petugas', 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->MultiCell(0, 7, "Laporan ini dibuat untuk mengevaluasi kinerja petugas dan mendorong perbaikan berkelanjutan. Petugas yang menunjukkan kinerja baik akan mendapatkan penghargaan sebagai bentuk apresiasi.", 0, 'J');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-90);
        $this->SetFont('Arial', 'U', 11);
        $this->Cell(45, 6, 'Dikeluarkan di', 0, 0, 'L');
        $this->SetFont('Arial', '', 11);
        $this->Cell(2, 6, ':', 0, 0, 'L');
        $this->Cell(5, 6, 'Marabahan', 0, 1, 'L');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 4, 'Issued in Marabahan', 0, 1, 'L');
        $this->Ln(2);

        $this->SetFont('Arial', 'U', 11);
        $this->Cell(45, 6, 'Pada tanggal', 0, 0, 'L');
        $this->SetFont('Arial', '', 11);
        $this->Cell(2, 6, ':', 0, 0, 'L');
        $this->Cell(50, 6, date('d F Y'), 0, 1, 'L');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 4, 'on', 0, 1, 'L');
        $this->Ln(4);

        $this->SetFont('Arial', 'U', 11);
        $this->Cell(0, 6, 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL', 0, 1, 'C');

        // TTD Pimpinan
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

        // QR Code
        $this->SetY(-52);
        $this->SetFont('Arial', '', 12);
        global $qr_api_url;
        if (!is_dir('temp')) mkdir('temp');
        $qrcode_file = 'temp/qrcode_' . date('YmdHis') . '_' . uniqid() . '.png';
        file_put_contents($qrcode_file, file_get_contents($qr_api_url));
        if (file_exists($qrcode_file)) {
            $this->Image($qrcode_file, 140, $this->GetY() - 1, 25, 25);
            unlink($qrcode_file);
        }
    }

    function FancyTable($header, $data)
    {
        $w = array(10, 35, 30, 20, 25, 20, 20, 25, 55);
        $align = array('C', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'L');
        $tableWidth = array_sum($w);
        $pageWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;
        $x = $this->lMargin + (($pageWidth - $tableWidth) / 2);
        $this->SetX($x);

        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 9);
        foreach ($header as $i => $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, $align[$i]);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        $fill = false;
        $no = 1;
        foreach ($data as $row) {
            $this->SetX($x);
            $this->Cell($w[0], 6, $no++, 1, 0, $align[0], $fill);
            $this->Cell($w[1], 6, $row['nama_petugas'], 1, 0, $align[1], $fill);
            $this->Cell($w[2], 6, $row['tanggal_penilaian'], 1, 0, $align[2], $fill);
            $this->Cell($w[3], 6, $row['kedisiplinan'], 1, 0, $align[3], $fill);
            $this->Cell($w[4], 6, $row['tanggung_jawab'], 1, 0, $align[4], $fill);
            $this->Cell($w[5], 6, $row['kerjasama'], 1, 0, $align[5], $fill);
            $this->Cell($w[6], 6, $row['inisiatif'], 1, 0, $align[6], $fill);
            $this->Cell($w[7], 6, $row['kualitas_kerja'], 1, 0, $align[7], $fill);
            $this->Cell($w[8], 6, $row['catatan'], 1, 0, $align[8], $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX($x);
        $this->Cell($tableWidth, 0, '', 'T');
    }
}

$pdf = new PDF_Kinerja($conn);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();
$header_kinerja = array('No', 'Nama Petugas', 'Tanggal', 'Disiplin', 'Tanggung Jawab', 'Kerja Sama', 'Inisiatif', 'Kualitas', 'Catatan');
$pdf->FancyTable($header_kinerja, $data_laporan_kinerja);
$pdf->Output('laporan_kinerja_' . date('YmdHis') . '.pdf', 'I');
