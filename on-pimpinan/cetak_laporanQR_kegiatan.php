<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

$sql_kegiatan_masyarakat = "SELECT nama_pemohon, tanggal_kegiatan, jenis, tempat, dasar
                           FROM (
                               SELECT u.nama AS nama_pemohon, s.tgl_kegiatan AS tanggal_kegiatan, 'SIK' AS jenis, s.tempat, s.dasar
                               FROM sik s JOIN users u ON s.user_id = u.id_user
                               UNION ALL
                               SELECT u.nama AS nama_pemohon, s.tgl_kampanye AS tanggal_kegiatan, 'STTP' AS jenis, s.tempat, s.memperhatikan as dasar
                               FROM sttp s JOIN users u ON s.user_id = u.id_user
                           ) AS semua_kegiatan
                           ORDER BY tanggal_kegiatan DESC";
$result_kegiatan = mysqli_query($conn, $sql_kegiatan_masyarakat);
$data_kegiatan = mysqli_fetch_all($result_kegiatan, MYSQLI_ASSOC);

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
$qr_data = "Laporan Kegiatan Masyarakat, Ditandatangani oleh: $nama_pimpinan, Tanggal: $tanggal_ttd $waktu_ttd";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_data);

class PDF extends FPDF
{
    public $conn;
    public $qr_api_url;
    function __construct($conn, $qr_api_url)
    {
        parent::__construct('P');
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

            $this->Image('../dist/img/logo.jpeg', $xPosition, $this->GetY(), $logoWidth);
            $this->Ln(23);
        }
        
        $this->SetFont('Arial','B',12);
        $judul = 'Laporan Data Kegiatan Masyarakat';
        $this->Cell(0,10,$judul,0,1,'C');

        $this->SetFont('Arial', '', 11);
        $this->Ln(3);
        $paragraf = "Laporan ini memuat data kegiatan masyarakat yang telah terdaftar dan tercatat pada Satuan Intelkam Polres Barito Kuala. Setiap kegiatan yang tercantum telah melalui proses verifikasi sesuai prosedur yang berlaku, guna memastikan ketertiban, keamanan, dan kelancaran pelaksanaan kegiatan masyarakat di wilayah hukum Polres Barito Kuala. Data pada laporan ini diharapkan dapat menjadi acuan dalam pengambilan keputusan serta evaluasi pelaksanaan tugas kepolisian terkait pengawasan kegiatan masyarakat.";
        $this->MultiCell(0, 7, $paragraf, 0, 'J');
        $this->Ln(5);
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

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    // Hitung jumlah baris yang dibutuhkan untuk setiap kolom pada satu data
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
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
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    // Tabel fleksibel, tinggi baris menyesuaikan isi terpanjang
    function TabelKegiatan($header, $data)
    {
        // Definisi kolom: label, width, align
        $columns = [
            ['label' => 'No', 'width' => 10, 'align' => 'C'],
            ['label' => 'Nama', 'width' => 35, 'align' => 'L'],
            ['label' => 'Tanggal', 'width' => 25, 'align' => 'C'],
            ['label' => 'Jenis', 'width' => 20, 'align' => 'C'],
            ['label' => 'Tempat', 'width' => 40, 'align' => 'L'],
            ['label' => 'Keterangan', 'width' => 0, 'align' => 'L'], // 0 = otomatis sisa lebar
        ];

        // Hitung sisa lebar halaman untuk kolom terakhir
        $totalWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;
        $fixedWidth = 0;
        foreach ($columns as $col) {
            if ($col['width'] > 0) $fixedWidth += $col['width'];
        }
        foreach ($columns as &$col) {
            if ($col['width'] == 0) $col['width'] = $totalWidth - $fixedWidth;
        }
        unset($col);

        // Header
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 10);
        foreach ($columns as $col) {
            $this->Cell($col['width'], 8, $col['label'], 1, 0, $col['align'], true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $no = 1;
        foreach ($data as $row) {
            $cellData = [
                $no,
                $row['nama_pemohon'],
                date('d-m-Y', strtotime($row['tanggal_kegiatan'])),
                $row['jenis'],
                $row['tempat'],
                strip_tags($row['dasar'])
            ];

            // Hitung tinggi baris berdasarkan multicell terpanjang
            $cellHeights = [];
            foreach ($columns as $i => $col) {
                $cellHeights[] = $this->NbLines($col['width'], $cellData[$i]) * 6;
            }
            $rowHeight = max($cellHeights);

            // Cek page break
            $this->CheckPageBreak($rowHeight);

            $x = $this->GetX();
            $y = $this->GetY();

            // Cetak cell satu per satu, MultiCell untuk semua kolom agar wrap otomatis
            for ($i = 0; $i < count($columns); $i++) {
                $col = $columns[$i];
                $w = $col['width'];
                $align = $col['align'];
                $txt = $cellData[$i];

                $this->SetXY($x, $y);
                $this->MultiCell($w, $rowHeight / $this->NbLines($w, $txt), $txt, 1, $align, false);
                $x += $w;
            }
            $this->SetY($y + $rowHeight);
            $no++;
        }

        // Garis bawah tabel
        $this->Cell($totalWidth, 0, '', 'T');
    }

}

$pdf = new PDF($conn, $qr_api_url);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage('P');
$header = array('No', 'Nama', 'Tanggal', 'Jenis', 'Tempat', 'Keterangan');
$pdf->TabelKegiatan($header, $data_kegiatan);
$pdf->Output('laporan_kegiatan_masyarakat_' . date('YmdHis') . '.pdf', 'I');