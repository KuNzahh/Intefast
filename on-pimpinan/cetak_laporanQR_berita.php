<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

// Ambil data berita
$sql_notifikasi = "SELECT id_berita, judul, isi, waktu_ubah FROM berita ORDER BY waktu_ubah DESC";
$result_notif = mysqli_query($conn, $sql_notifikasi);
$data_notif = mysqli_fetch_all($result_notif, MYSQLI_ASSOC);

// Ambil data pimpinan
$query_pimpinan = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
$result_pimpinan = mysqli_query($conn, $query_pimpinan);
$nama_pimpinan = $jabatan_pimpinan = $nrp_pimpinan = "";
if ($row = mysqli_fetch_assoc($result_pimpinan)) {
    $nama_pimpinan = $row['nama'];
    $jabatan_pimpinan = $row['jabatan'];
    $nrp_pimpinan = $row['nrp'];
}

$tanggal_ttd = date('d-m-Y');
$waktu_ttd = date('H:i:s');
$qr_data = "Laporan Berita & Peringatan Dini, Ditandatangani oleh: $nama_pimpinan, Tanggal: $tanggal_ttd $waktu_ttd";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_data);

class PDF extends FPDF
{
    public $conn;
    public $qr_api_url;
    public $nama_pimpinan;
    public $nrp_pimpinan;

    function __construct($conn, $qr_api_url, $nama_pimpinan, $nrp_pimpinan)
    {
        parent::__construct('P');
        $this->conn = $conn;
        $this->qr_api_url = $qr_api_url;
        $this->nama_pimpinan = $nama_pimpinan;
        $this->nrp_pimpinan = $nrp_pimpinan;
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
        $judul = 'Laporan Berita Pemberitahuan & Peringatan Dini';
        $this->Cell(0,10,$judul,0,1,'C');
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

        // Pastikan folder temp ada
        if (!is_dir('temp')) mkdir('temp');
        $qrcode_file = 'temp/qrcode_' . date('YmdHis') . '_' . uniqid() . '.png';
        file_put_contents($qrcode_file, file_get_contents($qr_api_url));

        if (file_exists($qrcode_file)) {
            $this->Image($qrcode_file, 95, $this->GetY() - 1, 25, 25);
            unlink($qrcode_file);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl;
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function TabelBerita($header, $data)
    {
        $w = [10, 50, 90, 40]; // Lebar kolom: No, Judul, Isi, Tanggal
        $align = ['C', 'L', 'L', 'C'];

        // Header
        $this->SetFont('Arial', 'B', 10);
        foreach ($header as $i => $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, $align[$i]);
        }
        $this->Ln();

        // Data
        $this->SetFont('Arial', '', 10);
        $no = 1;

        foreach ($data as $row) {
            // Hitung jumlah baris terbanyak dari kolom multiline
            $lines_judul = $this->NbLines($w[1], $row['judul']);
            $lines_isi = $this->NbLines($w[2], $row['isi']);
            $max_lines = max($lines_judul, $lines_isi);
            $height = 5 * $max_lines;

            $this->CheckPageBreak($height);

            $x = $this->GetX();
            $y = $this->GetY();

            // No
            $this->MultiCell($w[0], $height, $no++, 1, $align[0]);
            $this->SetXY($x + $w[0], $y);

            // Judul Berita
            $this->MultiCell($w[1], 5, $row['judul'], 1, $align[1]);
            $this->SetXY($x + $w[0] + $w[1], $y);

            // Isi Berita
            $this->MultiCell($w[2], 5, $row['isi'], 1, $align[2]);
            $this->SetXY($x + $w[0] + $w[1] + $w[2], $y);

            // Tanggal
            $this->MultiCell($w[3], $height, date('d-m-Y H:i', strtotime($row['waktu_ubah'])), 1, $align[3]);

            // Pindah ke baris baru setelah menyelesaikan semua kolom
            $this->SetY($y + $height);
        }

        // Garis penutup bawah
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

$pdf = new PDF($conn, $qr_api_url, $nama_pimpinan, $nrp_pimpinan);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 30);
$pdf->AddPage();
$header = ['No', 'Judul Berita', 'Isi Berita', 'Waktu Posting'];
$pdf->TabelBerita($header, $data_notif);
$pdf->Output('I', 'laporan_berita_pemberitahuan_peringatan_dini_' . date('YmdHis') . '.pdf');
