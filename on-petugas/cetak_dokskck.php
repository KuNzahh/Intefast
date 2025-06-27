<?php
require('../fpdf/fpdf.php');

class PDF extends FPDF
{
    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetMargins(15, 2, 2);
        $this->SetAutoPageBreak(true, 2);
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
    }

    function Body()
    {
        $this->SetFont('Arial', 'B', 12);
        $textIndo = 'SURAT KETERANGAN CATATAN KEPOLISIAN';
        $lineWidth = 95;

        $this->SetX(($this->GetPageWidth() - $lineWidth) / 2);
        $this->Cell($lineWidth, 5, $textIndo, 'B', 1, 'C');

        $this->SetFont('Arial', 'B', 12);
        $textEng = 'POLICE RECORD';

        $this->SetX(($this->GetPageWidth() - $lineWidth) / 2);
        $this->Cell($lineWidth, 6, $textEng, '', 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->Ln(1);

        $this->Cell(0, 3, 'Nomor: SKCK/   / VIII /YAN.2.1./ 2025 / INTELKAM', 0, 1, 'C');
        $this->Ln(3);

        $this->SetFont('Arial', 'B', 11);
        $textIndo = 'Diterangkan bersama ini bahwa';
        $textWidth = $this->GetStringWidth($textIndo) + 2;

        $this->Cell($textWidth, 3, $textIndo, 'B', 1);

        $this->SetFont('Arial', 'I', 11);
        $textEng = 'This is to certify that';
        $textWidth = $this->GetStringWidth($textEng) + 2;

        $this->Cell($textWidth, 6.5, $textEng, '', 1);

        include "../include/koneksi.php";
        session_start();

        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }

        $id_skck = $_GET['id'] ?? null;

        if (!$id_skck) {
            die("ID skck tidak ditemukan atau tidak valid.");
        }

        // Query dengan JOIN ke tabel kecamatan dan pekerjaan
        $query = "
            SELECT 
                s.nama, 
                s.jenis_kelamin,
                s.tempat_lahir,
                s.tanggal_lahir,
                s.kebangsaan,
                s.agama,
                p.nama_pekerjaan,
                s.alamat,
                k.nama_kecamatan,
                s.nik,
                s.no_komponen,
                s.tanggal_pengajuan,
                s.keperluan
            FROM skck s
            LEFT JOIN kecamatan k ON s.kecamatan_id = k.id_kecamatan
            LEFT JOIN pekerjaan p ON s.pekerjaan_id = p.id_pekerjaan
            WHERE s.id_skck = ?
        ";

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die("Kesalahan dalam query: " . $conn->error);
        }

        $stmt->bind_param("i", $id_skck);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();

            $this->SetFont('Arial', '', 11);

            function printBilingualField($pdf, $labelIndo, $labelEng, $value)
            {
                $marginLeft = 20;
                $lineHeight = 3;

                // Label Bahasa Indonesia dengan underline
                $pdf->SetX($marginLeft - 5);
                $pdf->SetFont('Arial', 'BU', 11);
                $pdf->Cell(60, $lineHeight, $labelIndo, 0, 0, 'L');

                // Titik dua dan nilai tanpa underline
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(3, $lineHeight, ':', 0, 0, 'L');
                $pdf->Cell(0, $lineHeight, $value, 0, 1, 'L');

                // Baris Bahasa Inggris (Italic)
                $pdf->SetX($marginLeft - 5);
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 3, $labelEng, 0, 1, 'L');

                $pdf->Ln(1); // Jarak antar field
            }

            printBilingualField($this, 'Nama', 'Name', $data['nama']);
            printBilingualField($this, 'Jenis Kelamin', 'Gender', $data['jenis_kelamin']);
            printBilingualField($this, 'Kebangsaan', 'Nationality', $data['kebangsaan']);
            printBilingualField($this, 'Agama', 'Religion', $data['agama']);
            printBilingualField($this, 'Tempat Lahir', 'Place of Birth', $data['tempat_lahir']);
            printBilingualField($this, 'Tanggal Lahir', 'Date of Birth', date('d F Y', strtotime($data['tanggal_lahir'])));
            printBilingualField($this, 'Alamat', 'Address', $data['alamat']);
            printBilingualField($this, 'Kecamatan', 'District', $data['nama_kecamatan']);
            printBilingualField($this, 'Pekerjaan', 'Occupation', $data['nama_pekerjaan']);
            printBilingualField($this, 'No. Komponen', 'Component No.', $data['no_komponen']);


            $this->Ln(1);
            // Teks Indonesia
            $this->SetFont('Arial', 'B', 11);
            $textIndo = 'Setelah diadakan peneitian hingga saat dikeluarkan surat keterangan ini didasarkan kepada :';
            $textWidth = $this->GetStringWidth($textIndo) + 2;
            $this->Cell($textWidth, 3, $textIndo, 'B', 1);

            // Teks Inggris
            $this->SetFont('Arial', '', 11);
            $textEng = 'After conducting the investigation until the issuance of this certificate, it is based on the following:';
            $textWidth = $this->GetStringWidth($textEng) + 2;
            $this->Cell($textWidth, 5, $textEng, '', 1);

            $marginLeft = 30;

            // Poin a
            $this->SetFont('Arial', '', 11);
            $this->SetX($marginLeft);
            $this->Cell(10, 5, 'a.', 0, 0); // nomor poin
            $this->SetFont('Arial', 'U', 11);
            $this->Cell(0, 3, 'Catatan Kepolisian yang ada', 0, 1, 'L'); // Teks Indonesia

            $this->SetFont('Arial', 'I', 11); // Italic untuk Inggris
            $this->SetX($marginLeft + 10); // indentasi ke kanan
            $this->Cell(0, 4, 'Existing police record', 0, 1, 'L'); // Teks Inggris

            // Poin b
            $this->SetFont('Arial', '', 11);
            $this->SetX($marginLeft);
            $this->Cell(10, 4, 'b.', 0, 0); // nomor poin
            $this->SetFont('Arial', 'U', 11);
            $this->Cell(0, 4, 'Surat keterangan dari Kepala Desa/Lurah', 0, 1, 'L'); // Teks Indonesia

            $this->SetFont('Arial', 'I', 11); // Italic untuk Inggris
            $this->SetX($marginLeft + 10); // indentasi ke kanan
            $this->Cell(0, 4, 'Statement from the Village Head or Urban Ward Chief', 0, 1, 'L'); // Teks Inggris

            $this->SetFont('Arial', 'B', 11);
            $this->SetX($marginLeft);
            // Cek apakah NIK ada di tabel kriminal
            $nik = $data['nik'];
            $queryKriminal = "SELECT cttkriminal FROM kriminal WHERE nik = ?";
            $stmtKriminal = $conn->prepare($queryKriminal);
            $stmtKriminal->bind_param("s", $nik);
            $stmtKriminal->execute();
            $stmtKriminal->store_result();

            if ($stmtKriminal->num_rows > 0) {
                $stmtKriminal->bind_result($cttkriminal);
                $stmtKriminal->fetch();

                // Jika ada catatan kriminal
                $this->SetFont('Arial', 'BU', 11);
                $this->Cell(0, 5, 'Bahwa nama tersebut pernah melakukan tindak kriminal sebagai berikut:', 0, 1, 'L'); // Teks Indonesia

                $this->SetFont('Times', 'BIU', 11);
                $this->MultiCell(0, 5, $cttkriminal, 0, 'C');

            } else {
                // Tidak ada catatan kriminal
                $this->SetFont('Arial', 'BU', 11);
                $this->Cell(0, 5, 'Bahwa nama tersebut tidak memiliki catatan atau keterlibatan dalam kegiatan kriminal apapun', 0, 1, 'L'); // Teks Indonesia

                $this->SetFont('Arial', 'I', 11); // Italic untuk Inggris
                $this->SetX($marginLeft); // indentasi ke kanan
                $this->Cell(0, 3, 'the bearer hereof proves not to be involved in any criminal cases', 0, 1, 'L'); // Teks Inggris
            }
            $stmtKriminal->close();

            $this->Ln(2); // Jarak atas

            $marginLeft = 30;
            $lineHeight = 3.5;

            // Format tanggal
            $tgl_dari = date('d F Y', strtotime($data['tanggal_lahir']));
            $tgl_sampai = date('d F Y', strtotime($data['tanggal_pengajuan']));

            // Baris 1 - Bahasa Indonesia (underline sebelum :)
            $this->SetFont('Arial', 'U', 11);
            $this->SetX($marginLeft);
            $this->Cell(80, $lineHeight, 'Selama ia berada di Indonesia dari', 0, 0, 'L');

            $this->SetFont('Arial', '', 11);
            $this->Cell(0, $lineHeight, ' : ' . $tgl_dari, 0, 1, 'L');

            // Baris 2 - Bahasa Inggris
            $this->SetFont('Arial', '', 11);
            $this->SetX($marginLeft);
            $this->MultiCell(0, $lineHeight, 'During his/her stay in Indonesia from', 0, 'L');

            $this->Ln(1); // Spasi antar bagian

            // Baris 3 - Bahasa Indonesia (underline sebelum :)
            $this->SetFont('Arial', 'U', 11);
            $this->SetX($marginLeft);
            $this->Cell(80, $lineHeight, 'Sampai dengan', 0, 0, 'L');

            $this->SetFont('Arial', '', 11);
            $this->Cell(0, $lineHeight, ' : ' . $tgl_sampai, 0, 1, 'L');

            // Baris 4 - Bahasa Inggris
            $this->SetFont('Arial', 'I', 11);
            $this->SetX($marginLeft);
            $this->MultiCell(0, $lineHeight, 'to', 0, 'L');

            $this->Ln(1); // Spasi antar bagian

            // Baris Bahasa Indonesia - bold dan center
            $this->SetFont('Arial', 'U', 11);
            $this->Cell(0, 3, 'Keterangan ini diberikan berhubungan dengan permohonan', 0, 1, 'C');

            // Baris Bahasa Inggris - italic dan center
            $this->SetFont('Arial', 'I', 11);
            $this->MultiCell(0, $lineHeight, 'This certificate is issued in relation to the applicant\'s request', 0, 'C');

            $this->Ln(3);
            printBilingualField($this, 'Untuk keperlua/menuju*', 'Purpose', $data['keperluan']);

            $marginLeft = 15;
            $lineHeight = 3.5;

            // Format tanggal
            $tgl_dari = date('d F Y');
            $tgl_sampai = date('d F Y', strtotime('+3 months'));

            // Baris 1 - Bahasa Indonesia (underline sebelum :)
            $this->SetFont('Arial', 'U', 11);
            $this->SetX($marginLeft);
            $this->Cell(59, $lineHeight, 'Berlaku dari tanggal', 0, 0, 'L');

            $this->SetFont('Arial', '', 11);
            $this->Cell(0, $lineHeight, ' : ' . $tgl_dari, 0, 1, 'L');

            // Baris 2 - Bahasa Inggris
            $this->SetFont('Arial', 'I', 11);
            $this->SetX($marginLeft);
            $this->MultiCell(0, $lineHeight, 'Valid from', 0, 'L');

            $this->Ln(1); // Spasi antar bagian

            // Baris 3 - Bahasa Indonesia (underline sebelum :)
            $this->SetFont('Arial', 'U', 11);
            $this->SetX($marginLeft);
            $this->Cell(59, $lineHeight, 'Sampai dengan', 0, 0, 'L');

            $this->SetFont('Arial', '', 11);
            $this->Cell(0, $lineHeight, ' : ' . $tgl_sampai, 0, 1, 'L');

            // Baris 4 - Bahasa Inggris
            $this->SetFont('Arial', 'I', 11);
            $this->SetX($marginLeft);
            $this->MultiCell(0, $lineHeight, 'to', 0, 'L');
        } else {
            $this->Cell(0, 10, "Data tidak ditemukan.", 0, 1);
        }

        if ($this->GetY() + 40 > $this->GetPageHeight() - 20) {
            $this->AddPage();
        }

        $this->Ln(3); // Jarak atas
        $marginLeft = 60;
        $fotoWidth = 40; // 6 cm
        $fotoHeight = 60; // 4 cm

        $this->SetX($marginLeft);
        $this->Cell($fotoWidth, $fotoHeight, '', 1); // Kotak kosong dengan border

        // Geser posisi ke kanan untuk teks di samping foto
        $textX = $marginLeft + $fotoWidth + 1;
        $textY = $this->GetY();

        $this->SetXY($textX, $textY);

        // Baris: "Dikeluarkan di : Marabahan"
        $this->SetFont('Arial', 'U', 11); // Garis bawah untuk "Dikeluarkan di"
        $this->Cell(45, $lineHeight, 'Dikeluarkan di', 0, 0, 'L');
        $this->SetFont('Arial', '', 11); // Tidak ada garis bawah untuk titik dua dan lokasi
        $this->Cell(2, $lineHeight, ':', 0, 0, 'L');
        $this->Cell(5, $lineHeight, 'Marabahan', 0, 1, 'L');

        // Baris: "Issued in: Marabahan"
        $this->SetFont('Arial', 'I', 10);
        $this->SetX($textX);
        $this->Cell(3, $lineHeight - 2, 'Issued in ', 0, 1, 'L');

        $this->Ln(2);

        // Baris: "Pada tanggal : [tgl]" dengan garis bawah
        $this->SetFont('Arial', 'U', 11); // Garis bawah untuk "Pada tanggal"
        $this->SetX($textX);
        $this->Cell(45, $lineHeight, 'Pada tanggal', 0, 0, 'L');
        $this->SetFont('Arial', '', 11); // Tidak ada garis bawah untuk titik dua dan tanggal
        $this->Cell(2, $lineHeight, ':', 0, 0, 'L');
        $this->Cell(50, $lineHeight, date('d F Y'), 0, 1, 'L');

        // Baris: "on"
        $this->SetFont('Arial', 'I', 10);
        $this->SetX($textX);
        $this->Cell(0, $lineHeight - 2, 'on', 0, 1, 'L');

        $this->Ln(4);
        // Menyimpan posisi X dan Y saat ini
        $x = $this->GetX();
        $y = $this->GetY();

        // Mengatur teks yang akan digarisbawahi
        $text = 'a.n. KAPOLRES BARITO KUALA POLDA KALSEL';
        $width = $this->GetStringWidth($text) + 2; // Menambahkan padding kecil agar garis lebih pas

        // Mengatur posisi X agar teks berada di tengah, dengan offset ke kanan jika diperlukan
        $offset = 39.6; // Tambahkan nilai offset ke kanan sesuai kebutuhan
        $x_center = ($this->GetPageWidth() - $width) / 2 + $offset;

        // Mengatur panjang garis secara manual (misalnya, lebih panjang dari teks)
        $line_length = $width + 0; // Panjang garis yang diatur secara manual, sesuaikan nilai '20' sesuai kebutuhan

        // Menggambar garis di atas teks (posisi Y dikurangi sedikit untuk mengatur garis di atas teks)
        $this->Line($x_center, $y - 1, $x_center + $line_length, $y - 1); // Menggunakan panjang garis yang diatur manual

        // Menampilkan teks di posisi yang diatur
        $this->SetX($x_center);
        $this->Cell(
            $width,
            4,
            $text,
            0,
            1,
            'C'
        );


        // Ambil data personil dengan jabatan "KASAT INTELKAM"
        $query = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stmt->bind_result($nama, $jabatan, $nrp);
        $stmt->fetch();
        $stmt->close();

        // Bagian PDF
        $this->SetFont('Arial', '', 11);
        $this->Cell(245, 4, 'KEPALA SATUAN INTELKAM', 0, 1, 'C');
        $this->Ln(20);

        // Jika data ditemukan, tampilkan di PDF
        if ($nama && $nrp) {
            // Tampilkan nama
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(245, 7, $nama, 0, 1, 'C');

            // Buat garis di antara nama dan NRP
            $x1 = ($this->GetPageWidth() - 0.01) / 2;
            $x2 = $x1 + 66;
            $y = $this->GetY();
            $this->Line($x1, $y, $x2, $y);

            // Tampilkan NRP langsung di bawah nama
            $this->SetFont('Arial', '', 11);
            $this->Cell(245, 5, "$nrp", 0, 1, 'C');
        } else {
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(245, 7, 'Data tidak ditemukan', 0, 1, 'C');
            $this->SetFont('Arial', '', 11);
            $this->Cell(245, 5, '-', 0, 1, 'C');
        }
    }
}

// Inisialisasi PDF
$paperSize = 'A4';
$pdf = new PDF('P', 'mm', $paperSize);
$pdf->AddPage();
$pdf->Body();
$pdf->Output();
