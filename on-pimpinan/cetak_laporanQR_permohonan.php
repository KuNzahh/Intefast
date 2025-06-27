<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['nama'])) {
    header("Location: ../index.php");
    exit();
}

include '../include/koneksi.php';
require('../fpdf/fpdf.php');

$filter_jenis = isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : 'semua';
$filter_bulan = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : '';
$tahun_sekarang = date('Y');
$nama_bulan = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

$where_clause = "WHERE 1=1";
if ($filter_jenis != 'semua') {
    $where_clause .= " AND jenis = '".mysqli_real_escape_string($conn, $filter_jenis)."'";
}
if (!empty($filter_bulan)) {
    $where_clause .= " AND DATE_FORMAT(tanggal_pengajuan, '%Y-%m') = '$tahun_sekarang-$filter_bulan'";
}

$sql_laporan = "SELECT nama_pemohon, tanggal_pengajuan, jenis, detail
                FROM (
                    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SKCK' AS jenis, s.keperluan AS detail
                    FROM skck s JOIN users u ON s.user_id = u.id_user
                    UNION ALL
                    SELECT u.nama AS nama_pemohon, s.tanggal_pengajuan, 'SIK' AS jenis, CONCAT('Instansi: ', s.nama_instansi, ' | Penanggung Jawab: ', s.penanggung_jawab) AS detail
                    FROM sik s JOIN users u ON s.user_id = u.id_user
                    UNION ALL
                    SELECT 
                        u.nama AS nama_pemohon, 
                        s.tanggal_pengajuan, 
                        'STTP' AS jenis, 
                        CONCAT('Paslon: ', s.nama_paslon, ' | Kampanye: ', k.nama_kampanye) AS detail
                    FROM sttp s 
                    JOIN users u ON s.user_id = u.id_user
                    LEFT JOIN kampanye k ON s.kampanye_id = k.id_kampanye
                ) AS semua $where_clause ORDER BY tanggal_pengajuan DESC";

$result_laporan = mysqli_query($conn, $sql_laporan);
$data_laporan = mysqli_fetch_all($result_laporan, MYSQLI_ASSOC);

// Ambil nama pimpinan dari database (personil_satintel dengan jabatan KASAT INTELKAM terbaru)
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
$bulan_laporan_text = !empty($filter_bulan) ? $nama_bulan[intval($filter_bulan)] . " " . $tahun_sekarang : $tahun_sekarang;
$qr_data = "Laporan Permohonan Bulan: " . $bulan_laporan_text . ", Ditandatangani oleh: " . $nama_pimpinan . ", Tanggal: " . $tanggal_ttd . " " . $waktu_ttd;
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
        $judul = 'Laporan Data Permohonan';
        global $filter_jenis, $filter_bulan, $nama_bulan, $tahun_sekarang;
        $this->Cell(0,10,$judul,0,1,'C');

        // Paragraf penjelas
        $jumlah_data = isset($GLOBALS['data_laporan']) ? count($GLOBALS['data_laporan']) : 0;
        $paragraf = "Laporan Rekapitulasi Permohonan Administrasi pada Satuan Intelkam Polres Barito Kuala";
        $jenis_text = '';
        $bulan_text = '';
        $tahun_text = '';
        if ($filter_jenis != 'semua') {
            $jenis_text = strtoupper($filter_jenis);
        }
        if (!empty($filter_bulan)) {
            $bulan_text = $nama_bulan[intval($filter_bulan)];
        }
        $tahun_text = $tahun_sekarang;

        $this->SetFont('Arial','',9);
        $this->Write(8, $paragraf);

        if ($jenis_text && $bulan_text) {
            $this->Write(8, " Jenis ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $jenis_text);
            $this->SetFont('Arial','',9);
            $this->Write(8, " Bulan ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $bulan_text);
            $this->SetFont('Arial','',9);
            $this->Write(8, " Tahun ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $tahun_text);
            $this->SetFont('Arial','',9);
        } elseif ($jenis_text) {
            $this->Write(8, " Jenis ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $jenis_text);
            $this->SetFont('Arial','',9);
            $this->Write(8, " Tahun ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $tahun_text);
            $this->SetFont('Arial','',9);
        } elseif ($bulan_text) {
            $this->Write(8, " Bulan ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $bulan_text);
            $this->SetFont('Arial','',9);
            $this->Write(8, " Tahun ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $tahun_text);
            $this->SetFont('Arial','',9);
        } else {
            $this->Write(8, " Tahun ");
            $this->SetFont('Arial','B',9);
            $this->Write(8, $tahun_text);
            $this->SetFont('Arial','',9);
        }
        $this->SetFont('Arial','',9);
        $this->Write(8, ". Jumlah permohonan: ");
        $this->SetFont('Arial','B',9);
        $this->Write(8, $jumlah_data);
        $this->SetFont('Arial','',9);
        $this->Write(8, ".");
        $this->Ln(10);
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

    function FancyTable($header, $data)
    {
        // Tentukan header dan lebar kolom secara dinamis
        $columns = [
            ['label' => 'No', 'width' => 10, 'align' => 'C'],
            ['label' => 'Nama Pemohon', 'width' => 30, 'align' => 'L'],
            ['label' => 'Jenis', 'width' => 12, 'align' => 'L'],
            ['label' => 'Tanggal Permohonan', 'width' => 37, 'align' => 'C'],
            ['label' => 'Tanggal Kegiatan', 'width' => 32, 'align' => 'C'],
            ['label' => 'Keterangan Umum', 'width' => 0, 'align' => 'L'], // 0 = otomatis sisa lebar
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
        $this->SetFillColor(230,230,230);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial','B',10);
        foreach ($columns as $col) {
            $this->Cell($col['width'], 7, $col['label'], 1, 0, $col['align'], true);
        }
        $this->Ln();

        // Data
        $this->SetFont('Arial','',10);
        $fill = false;
        $no = 1;
        foreach ($data as $row) {
            // Ambil tanggal kegiatan sesuai jenis
            $tanggal_kegiatan = '-';
            if ($row['jenis'] == 'STTP') {
                // Ambil tgl_kampanye dari tabel sttp
                $q = "SELECT tgl_kampanye FROM sttp WHERE tanggal_pengajuan = ? AND user_id = (SELECT id_user FROM users WHERE nama = ? LIMIT 1) LIMIT 1";
                $stmt = $this->conn->prepare($q);
                $tgl_kampanye = null;
                $stmt->bind_param('ss', $row['tanggal_pengajuan'], $row['nama_pemohon']);
                $stmt->execute();
                $stmt->bind_result($tgl_kampanye);
                if ($stmt->fetch() && $tgl_kampanye) {
                    $tanggal_kegiatan = date('d-m-Y', strtotime($tgl_kampanye));
                }
                $stmt->close();
            } elseif ($row['jenis'] == 'SIK') {
                // Ambil tgl_kegiatan dari tabel sik
                $q = "SELECT tgl_kegiatan FROM sik WHERE tanggal_pengajuan = ? AND user_id = (SELECT id_user FROM users WHERE nama = ? LIMIT 1) LIMIT 1";
                $stmt = $this->conn->prepare($q);
                $tgl_kegiatan = null;
                $stmt->bind_param('ss', $row['tanggal_pengajuan'], $row['nama_pemohon']);
                $stmt->execute();
                $stmt->bind_result($tgl_kegiatan);
                if ($stmt->fetch() && $tgl_kegiatan) {
                    $tanggal_kegiatan = date('d-m-Y', strtotime($tgl_kegiatan));
                }
                $stmt->close();
            }

            $cellData = [
                $no++,
                $row['nama_pemohon'],
                $row['jenis'],
                date('d-m-Y', strtotime($row['tanggal_pengajuan'])),
                $tanggal_kegiatan,
                strip_tags($row['detail'])
            ];

            $cellHeights = [];
            // Hitung tinggi baris berdasarkan multicell terpanjang (khusus kolom terakhir)
            foreach ($columns as $i => $col) {
                if ($i == count($columns) - 1) {
                    $cellHeights[] = $this->NbLines($col['width'], $cellData[$i]) * 6;
                } else {
                    $cellHeights[] = 6;
                }
            }
            $rowHeight = max($cellHeights);

            $x = $this->GetX();
            $y = $this->GetY();

            // Cetak cell satu per satu, MultiCell hanya untuk kolom terakhir, posisi X/Y diatur manual
            for ($i = 0; $i < count($columns); $i++) {
                $col = $columns[$i];
                $w = $col['width'];
                $h = ($i == count($columns) - 1) ? $rowHeight : $rowHeight;
                $align = $col['align'];
                $txt = $cellData[$i];

                if ($i == count($columns) - 1) {
                    $this->SetXY($x, $y);
                    $this->MultiCell($w, 6, $txt, 1, $align, $fill);
                } else {
                    $this->SetXY($x, $y);
                    $this->Cell($w, $rowHeight, $txt, 1, 0, $align, $fill);
                }
                $x += $w;
            }
            $this->SetY($y + $rowHeight);
            $fill = !$fill;
        }
        // Garis bawah tabel
        $this->Cell($totalWidth, 0, '', 'T');
    }

    // Tambahkan fungsi bantu untuk menghitung jumlah baris pada multicell
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l > $wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

$pdf = new PDF($conn);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();

$header = array('No', 'Jenis', 'Tanggal', 'Keterangan Umum');
$pdf->FancyTable($header, $data_laporan);

$pdf->Output('laporan_permohonan_' . date('YmdHis') . '.pdf', 'I');
?>
