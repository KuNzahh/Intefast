<?php
session_start();
require('../fpdf/fpdf.php');
require('../include/koneksi.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda belum login.'); window.location.href = '../login.php';</script>";
    exit();
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID SKCK tidak valid.'); window.location.href = 'statushistory.php';</script>";
    exit();
}

$id_skck = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data dari database
$sql = "SELECT s.id_skck, u.nama AS nama_pemohon, s.progres, tanggal_pengajuan
        FROM skck s
        LEFT JOIN users u ON s.user_id = u.id_user
        WHERE s.id_skck = ? AND s.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_skck, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    echo "<script>alert('Data SKCK tidak ditemukan.'); window.location.href = 'statushistory.php';</script>";
    exit();
}

// Mapping status
function mapProgres($progres)
{
    switch ($progres) {
        case 'pengajuan':
            return 'Pengajuan';
        case 'penelitian':
            return 'Penelitian';
        case 'diterima':
            return 'Diterima';
        case 'ditolak':
            return 'Ditolak';
        default:
            return 'Tidak Diketahui';
    }
}

// Membuat PDF
class PDF extends FPDF
{
    function Header()
    {
        // Check if this is the first page
        if ($this->PageNo() == 1) {
            // Set font for header
            $this->SetFont('Arial', '', 11);
            // Add title information to the left of the page but text centered within the page
            $this->Cell(85, 5, 'KEPOLISIAN NEGARA REPUBLIK INDONESIA', 0, 1, 'C');
            $this->Cell(85, 5, 'DAERAH KALIMANTAN SELATAN', 0, 1, 'C');
            $this->Cell(85, 5, 'RESOR BARITO KUALA', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(85, 5, 'Jl. Gusti M. Seman No. 1 Marabahan 70511', 'B', 1, 'C');
            // Add spacing after header
            $this->Ln(5);

            // Set left, right, and top margins
            $margin = 21; // Left and right margin in mm
            $logoWidth = 23; // Logo width in mm
            $pageWidth = $this->GetPageWidth(); // Page width

            // Available width between left and right margins
            $usableWidth = $pageWidth - (2 * $margin);

            // Calculate X position so that the logo is centered in the available area between margins
            $xPosition = $margin + (($usableWidth - $logoWidth) / 2);

            // Add logo image below title information at current Y position
            $this->Image('../dist/img/logo.jpeg', $xPosition, $this->GetY(), $logoWidth);

            // Add vertical space after logo
            $this->Ln(23);
        }
    }
    //function Header() {
    // Judul
    // $this->SetFont('Arial', 'B', 14);
    //$this->Cell(0, 10, 'Bukti Pengambilan Fisik SKCK', 0, 1, 'C');
    //$this->Ln(5);
    //}

    function Body($data)
    {
        // Judul surat
        // Set document title
        $this->SetFont('Arial', 'B', 12);

        // Set title text and text width
        $text = ' Surat Bukti Pengambilan Fisik SKCK';

        // Determine underline length as needed
        $lineWidth = 79; // Underline length (in mm), can be adjusted

        // Set X position so that the text is centered on the page
        $this->SetX(($this->GetPageWidth() - $lineWidth) / 2);

        // Create a cell with the adjusted line length and text centered within the cell
        $this->Cell($lineWidth, 4, $text, 'B', 1, 'C'); // Option 'B' adds an underline along $lineWidth

        // Set font for subsequent content
        $this->SetFont('Arial', '', 11);
        $this->Ln(1);

        // Add document number, displayed in the center of the page
        $this->Cell(0, 4, 'Nomor: SBPF/IX/YAN.2.2/2025/Sat Intelkam', 0, 1, 'C');
        $this->Ln(3);

        // Data pemohon
        $this->SetFont('Arial', '', 12);
        $this->Cell(50, 8, 'Atas Nama', 0, 0);
        $this->Cell(5, 8, ':', 0, 0);
        $this->Cell(0, 8, $data['nama_pemohon'], 0, 1);

        $this->Cell(50, 8, 'Status', 0, 0);
        $this->Cell(5, 8, ':', 0, 0);
        $this->Cell(0, 8, mapProgres($data['progres']), 0, 1);

        $this->Cell(50, 8, 'Pesan', 0, 0);
        $this->Cell(5, 8, ':', 0, 0);
        $this->Cell(0, 8, 'Bisa Diambil', 0, 1);

        $this->Cell(50, 8, 'Tanggal Cetak', 0, 0);
        $this->Cell(5, 8, ':', 0, 0);
        $this->Cell(0, 8, $data['tanggal_pengajuan'], 0, 1);

        $this->Ln(8);

        // Paragraf isi surat
        $this->SetFont('Arial', '', 12);
        $isiSurat = "Saya yang bertanda tangan di atas adalah pemohon Surat Keterangan Catatan Kepolisian (SKCK) "
            . "yang telah mengajukan permohonan di Polres Barito Kuala. Dengan ini, saya menyerahkan surat ini "
            . "sebagai bukti identitas dan keabsahan permohonan untuk melakukan pengambilan fisik SKCK.";

        $this->MultiCell(0, 7, $isiSurat, 0, 'J');
        $this->Ln(5);

        $penutup = "Demikian surat ini saya buat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.";
        $this->MultiCell(0, 7, $penutup, 0, 'J');
        $this->Ln(15);

        // Tempat & tanggal
        $this->SetX($this->GetPageWidth() - 80);
        $this->Cell(0, 6, 'Marabahan, ' . date('d F Y'), 0, 1, 'R');
        $this->Ln(10);

        // Tanda tangan
        $this->SetX(30);
        $this->Cell(80, 6, 'Pemohon,', 0, 0, 'C');
        $this->SetX(-90);
        $this->Cell(80, 6, 'Petugas Penerima,', 0, 1, 'C');

        $this->Ln(20);
        $this->SetX(30);
        $this->Cell(80, 6, $data['nama_pemohon'], 0, 0, 'C');
        $this->SetX(-90);
        $this->Cell(80, 6, '(__________________)', 0, 1, 'C');
    }
}

// Inisialisasi dan output PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->Body($data);
$pdf->Output('I', 'Bukti_SKCK_' . $data['id_skck'] . '.pdf');
