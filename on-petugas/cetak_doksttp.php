<?php
// Memanggil pustaka FPDF
require('../fpdf/fpdf.php');
require('../include/koneksi.php');
session_start();


$errorMessage = '';

// Pastikan koneksi ke database berhasil
if ($conn->connect_error) {
    $errorMessage = "Koneksi gagal: " . $conn->connect_error;
}

// Pastikan $id_sttp diatur dengan benar. Contoh: ambil dari parameter GET atau POST
$id_sttp = $_GET['id'] ?? null;

// Periksa apakah $id_sttp valid
if (!$id_sttp && empty($errorMessage)) { // Only set error if no other error
    $errorMessage = "ID Sttp tidak ditemukan atau tidak valid.";
}

// If there's an error, don't proceed with PDF generation
if (!empty($errorMessage)) {
    // You could redirect to an error page, or display a simple HTML error
    echo "<h1>Error:</h1><p>" . htmlspecialchars($errorMessage) . "</p>";
    exit(); // Stop script execution
}

// Making a class PDF which is a subclass of FPDF
class PDF extends FPDF
{
    protected $conn; // Add connection property

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4', $conn)
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetMargins(21, 21, 21);
        $this->conn = $conn; // Assign connection
    }

    // Function to add Header to each page
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

    // Function to add content or body of the PDF document
    function Body($id_sttp) // Pass id_sttp to the body function
    {
        // Set document title
        $this->SetFont('Arial', 'B', 12);

        // Set title text and text width
        $text = 'SURAT TANDA TERIMA PEMBERITAHUAN KAMPANYE';

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
        $this->Cell(0, 4, 'Nomor: STTP/         /IX/YAN.2.2/2024/Sat Intelkam', 0, 1, 'C');
        $this->Ln(3);

        $this->SetFont('Arial', '', 11);
        $this->Cell(
            10,
            6.5,
            'Pertimbangan        :',
            0,
            0
        );
        $this->Ln(1); // Move to next line

        $pertimbangan_texts = [
            "Bahwa telah dipenuhi semua ketentuan tentang Kampanye Pemilihan Umum, sebagaimana dimaksud Pasal 267 sampai dengan Pasal 305 Undang - Undang Republik Indonesia Nomor 7 Tahun 2017 tentang Pemilihan Umum.",
        ];

        // Set the starting X position for the bullet points
        $this->SetX(60); // Adjust the X position after "Pertimbangan:"

        foreach ($pertimbangan_texts as $index => $text) {
            $this->MultiCell(0, 5, $text, 0, 'J');
            $this->SetX(60); // Reset X position for next text
        }
        $this->Ln(5);

        $this->SetFont('Arial', '', 11);
        $this->Cell(
            10,
            6.5,
            'Dasar                 :',
            0,
            0
        );
        $this->Ln(1); // Move to next line

        $dasar_texts = [
            "Undang - Undang Republik Indonesia Nomor 2 Tahun 2002 tentang  Kepolisian Negara Republik Indonesia.",
            "Peraturan Kapolri Nomor 6 Tahun 2012 tentang Tata Cara Pemberitahuan dan Penerbitan Surat Tanda Terima Pemberitahuan Kampanye Pemilihan Umum;",
            "Peraturan Pemerintah Republik Indonesia Nomor 60 Tahun 2017 tentang Tata Cara Perizinan dan Pengawasan Kegiatan Keramaian Umum, Kegiatan Masyarakat Lainnya, dan Pemberitahuan Kegiatan Politik;",
            "Undang - Undang Nomor 7 Tahun 2023 tentang Penetapan Peraturan Pemerintah Pengganti Undang-Undang Nomor 1 Tahun 2022 tentang Perubahan atas Undang-Undang Nomor 7 Tahun 2017 tentang Pemilihan Umum Menjadi Undang-Undang;",
            "Peraturan Komisi Pemilihan Umum Republik Indonesia Nomor 15 Tahun 2023 tentang Kampanye Pemilihan Umum;",
            "Peraturan Komisi Pemilihan Umum Republik Indonesia Nomor 13 Tahun 2024 tentang Kampanye Pemilihan Gubernur dan Wakil Gubernur, Bupati dan Wakil Bupati, serta Walikota dan Wakil Walikota;",
            "Peraturan Kepolisian Negara Republik Indonesia Nomor 5 Tahun 2024 tentang Teknis Pemberitahuan Kegiatan Politik.",
        ];

        // Set the starting X position for the bullet points
        $this->SetX(60); // Adjust the X position after "Pertimbangan:"

        foreach ($dasar_texts as $index => $text) {
            $this->Cell(5, 5, ($index + 1) . '.', 0, 0); // Sequence number
            $this->MultiCell(0, 5, $text, 0, 'J');
            $this->SetX(60); // Reset X position for next text
        }
        $this->Ln(5);

        $this->Cell(
            10,
            6.5,
            'Memperhatikan     :',
            0,
            0
        );
        $this->Ln(1); // Move to next line

        $row = null; // Initialize $row
        // Run query to retrieve basic data
        $query = "
            SELECT
                s.nama_paslon,
                s.alamat,
                s.penanggung_jawab,
                s.kampanye_id,
                s.tgl_kampanye,
                s.tempat,
                k.nama_kecamatan,
                s.jumlah_peserta,
                s.nama_jurkam,
                s.memperhatikan
            FROM sttp s
                LEFT JOIN kecamatan k ON s.kecamatan_id = k.id_kecamatan
            WHERE s.id_sttp = ?
        ";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Check if query was successfully prepared
        if ($stmt === false) {
            // Log error, don't output directly to browser
            error_log("Kesalahan dalam query: " . $this->conn->error);
            $memperhatikan_value = "Data tidak dapat dimuat karena kesalahan query.";
        } else {
            // Bind parameter and execute query
            $stmt->bind_param("i", $id_sttp);
            $stmt->execute();

            // Get query result
            $result = $stmt->get_result();

            // Get value from basic column
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $memperhatikan_value = $row['memperhatikan']; // Get basic column value
            } else {
                $memperhatikan_value = "Tidak ada data ditemukan untuk ID STTP: " . $id_sttp;
            }
            $stmt->close();
        }

        // Insert value into array
        $memperhatikan_texts = [
            $memperhatikan_value // Automatically taken from basic column
        ];

        // Set the starting X position for the bullet points
        $this->SetX(60); // Adjust the X position after "Memperhatikan:"

        foreach ($memperhatikan_texts as $index => $text) {
            $this->MultiCell(
                0,    // Cell width adjusts to remaining page width
                5,    // Height of each text line
                $text, // Text to display
                0,    // No border
                'J'   // Justify text
            );
            $this->SetX(60); // Reset X position for next text
        }

        $this->Ln(5);

        // Add a new page here as it was in the original code.
        $this->AddPage();

        // Page 2
        // Set document title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 4, 'MEMBERIKAN ', 0, 1, 'C');
        $this->Ln(2);

        // Set document title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 4, ' SURAT TANDA TERIMA PEMBERITAHUAN KAMPANYE', 0, 1, 'C');
        $this->Ln(2);

        // Activity information
        $this->SetFont('Arial', '', 11);

        // Ambil nama_kampanye dari tabel kampanye berdasarkan kampanye_id
        $nama_kampanye = 'N/A';
        $nama_kampanye_db = null; // Initialize the variable before use
        if (!empty($row['kampanye_id'])) {
            $query_kampanye = "SELECT nama_kampanye FROM kampanye WHERE id_kampanye = ?";
            $stmt_kampanye = $this->conn->prepare($query_kampanye);
            if ($stmt_kampanye) {
                $stmt_kampanye->bind_param("i", $row['kampanye_id']);
                $stmt_kampanye->execute();
                $stmt_kampanye->bind_result($nama_kampanye_db);
                if ($stmt_kampanye->fetch()) {
                    $nama_kampanye = $nama_kampanye_db;
                }
                $stmt_kampanye->close();
            }
        }


        $catatan_texts = [
            "Kepada" => "",
            "1.Nama Paslon" => $row['nama_paslon'] ?? 'N/A', // Use N/A if row is null
            "2.Alamat" => $row['alamat'] ?? 'N/A',
            "3.Penanggung Jawab" => $row['penanggung_jawab'] ?? 'N/A',
            "Untuk Menyelenggarakan Kegiatan sebagai berikut :" => "",
            "4. Bentuk Kampanye" => $nama_kampanye,
            "5. Waktu" => isset($row['tgl_kampanye']) && $row['tgl_kampanye'] != '' ? date('d F Y', strtotime($row['tgl_kampanye'])) : 'N/A',
            "6. Tempat" => $row['tempat'] . ', Kec.' . $row['nama_kecamatan'],
            "7. Jumlah Peserta" => $row['jumlah_peserta'] ?? 'N/A',
            "8. Nama Jurkam"  => $row['nama_jurkam'] ?? 'N/A',
            "9. Penggunaan kendaraan    " => "    (lihat lampiran)",
            "10.Penggunaan alat peraga  " => "    (lihat lampiran)",
        ];

        // Loop to display each key-value pair
        foreach ($catatan_texts as $key => $text) {
            // Check if the key is one of the 5 data to be adjusted
            if (in_array($key, ["4. Bentuk Kampanye", "5. Waktu", "6. Tempat", "7. Jumlah Peserta", "8. Nama Jurkam", "9. Penggunaan kendaraan    ", "10.Penggunaan alat peraga  "])) {
                $this->SetX(66); // Set a special starting position for these 5 data
            }

            // Display key (e.g., "Nama Instansi", "Penanggung Jawab", etc.)
            $this->Cell(40, 5, $key, 0, 0);
            // Display ":" to separate label and value
            $this->Cell(
                5,
                5,
                ':',
                0,
                0
            );
            // Display long value using MultiCell for wrapping text neatly
            $this->MultiCell(
                0,    // Cell width adjusts to remaining page width
                5,    // Height of each text line
                $text, // Text to display
                0,    // No border
                ''    // Justify text
            );
            // Move to next line after each key-value pair
            $this->Ln(1);
        }
        $this->Ln(3);

        $this->SetFont(
            'Arial',
            '',
            11
        );

        // Additional provisions
        $this->SetFont('Arial', '', 11);
        $ketentuan = [
            "Dengan Ketentuan" => "",
            "11." => "Pelanggaran atas ketentuan mengenai larangan pelaksanaan kampanye pemilihan umum dapat dibubarkan atau diberhentikan pelaksanaannya oleh yang berwenang.",
            "12." => "Semua pihak harus berpedoman kepada tetap terpeliharanya persatuan dan kesatuan bangsa yang berbudaya sesuai moral dan etika politik yang bersumber pada nilai-nilai luhur Pancasila.",
            "13." => "Peserta Kampanye rapat terbatas tidak dibenarkan melakukan pawai kendaraan bermotor di luar rute perjalanan yang telah ditentukan, memasuki wilayah daerah pemilihan lain dan melanggar peraturan lalu lintas.",
            "14." => "Apabila dalam pelaksanaan Kampanye Pemilu terjadi gangguan keamanan, Polri setempat dapat mengambil tindakan yang dianggap perlu sesuai dengan peraturan perundang-undangan.",
            "15." => "Apabila situasi keamanan di wilayah tempat/lokasi kampanye tidak memungkinkan diselenggarakan kampanye, Polri setempat dapat mengusulkan kepada KPU Kabupaten Barito Kuala untuk membatalkan, menunda atau memindahkan tempat pelaksanaan kampanye."
        ];

        foreach ($ketentuan as $key => $text) {
            if ($key === "Dengan Ketentuan") {
                $width = $this->GetStringWidth($key) + 2;
                $x = $this->GetX();
                $y = $this->GetY();

                $this->Cell($width, 8, $key, 0, 0);
                $this->Cell(5, 6, ':', 0, 1);
                $this->Line($x, $y + 5.5, $x + $width, $y + 5.5);
            } else {
                $this->Cell(10, 5, $key, 0, 0);
                $this->MultiCell(0, 5, $text, 0, 'J');
            }
            $this->Ln(1);
        }

        $this->Ln(5);

        // Date and location
        $positionX = $this->GetPageWidth() - 110.6;

        $this->SetX($positionX);
        $this->Cell(30, 4, 'Dikeluarkan di', 0, 0, 'L');
        $this->Cell(4, 4, ':', 0, 0, 'L');
        $this->Cell(16, 4, 'Marabahan', 0, 1, 'L');

        $this->SetX($positionX);
        $this->Cell(30, 4, 'Pada Tanggal', 0, 0, 'L');
        $this->Cell(4, 4, ':', 0, 0, 'L');

        // Get campaign date
        // Note: Using $id_sttp from the method parameter, not $_GET directly in the class
        $tanggal_surat = null;
        $query = "SELECT tgl_kampanye FROM sttp WHERE id_sttp = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id_sttp);
            $stmt->execute();
            $stmt->bind_result($tanggal_surat);
            $stmt->fetch();
            $stmt->close();
        } else {
            error_log("Error preparing tanggal_surat query: " . $this->conn->error);
        }


        if ($tanggal_surat) {
            $formatted_date = date("d F Y", strtotime($tanggal_surat));
            $this->SetFont('Arial', '', 11);
            $this->Cell(16, 4, $formatted_date, 0, 1, 'L');
        } else {
            $this->SetFont('Arial', 'I', 11); // Italic for missing date
            $this->Cell(16, 4, 'Tanggal tidak tersedia', 0, 1, 'L');
            error_log('Data tanggal surat tidak ditemukan untuk id_sttp: ' . $id_sttp);
        }

        $this->Ln(2);
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

        $this->Ln(2);

        // Get personnel data with position "KASAT INTELKAM"
        $nama = null;
        $jabatan = null;
        $nrp = null;
        $query = "SELECT nama, jabatan, nrp FROM personil_satintel WHERE jabatan LIKE '%KASAT INTELKAM%' ORDER BY id_personil DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($nama, $jabatan, $nrp);
            $stmt->fetch();
            $stmt->close();
        } else {
            error_log("Error preparing personil_satintel query: " . $this->conn->error);
        }


        // PDF section
        $this->SetFont('Arial', '', 11);
        $this->Cell(245, 4, 'KEPALA SATUAN INTELKAM', 0, 1, 'C');
        $this->Ln(20);

        // If data found, display in PDF
        if ($nama && $nrp) {
            // Display name
            $this->SetFont('Arial', 'BU', 11);
            $this->Cell(245, 7, $nama, 0, 1, 'C');

            // Display NRP directly below name
            $this->SetFont('Arial', '', 11);
            $this->Cell(245, 5, "$nrp", 0, 1, 'C');
        } else {
            $this->SetFont('Arial', 'I', 11); // Italic for missing data
            $this->Cell(245, 7, 'Nama Kepala Satintel tidak tersedia', 0, 1, 'C');
            $this->Cell(245, 5, 'NRP tidak tersedia', 0, 1, 'C');
            error_log('Data Kepala Satintel tidak ditemukan.');
        }

        $this->Ln(-20);

        // Add footer for "Tembusan"
        $this->SetFont('Arial', '', 9);
        $this->Cell(2, 6, 'Tembusan :', 0, 1, 'L'); // Text "Tembusan" on the left with ":"

        // Determine the length of the "Tembusan" text
        $tembusan_text_label = 'Tembusan';
        $width = $this->GetStringWidth($tembusan_text_label) + 2; // Add a little padding

        // Store current X and Y positions
        $x = $this->GetX();
        $y = $this->GetY() - 1; // Move up a bit so the line is closer to the text

        // Add an underline below "Tembusan" text only
        $this->Line($x, $y, $x + $width, $y); // Draw line from X position to the width of "Tembusan" text

        // "Tembusan" data
        $tembusan_texts = [
            "1. Kapolda Kalsel.",
            "2. Dir Intelkam Polda Kalsel.",
            "3. Kabagops Polres Barito Kuala.",
            "4. Kapolsek Jajaran Polres Barito Kuala.",
            "5. Ketua KPU Kab. Barito Kuala.",
            "6. Ketua Bawaslu Kab. Barito Kuala."
        ];

        // Loop to display each "Tembusan" line
        foreach ($tembusan_texts as $text) {
            // Add leading space for indentation if needed
            $this->Cell(2, 4, '', 0, 0); // Empty space for indentation
            $this->Cell(3, 6, $text, 0, 1, 'L'); // "Tembusan" text with left alignment
        }

        // Underline for the last item if needed
        $this->Ln(0);
        $this->SetX(22); // Set X position to left margin
        $this->Cell(62, 0, '', 'B', 0, 'L'); // Underline with 80mm length (adjust length as needed)
    }
}

// Set desired page size (e.g., 'A4')
$paperSize = 'A4';

// Create new PDF object with custom page size and pass the database connection
$pdf = new PDF('P', 'mm', $paperSize, $conn);
$pdf->AddPage();

// Run Body function to add document content
$pdf->Body($id_sttp); // Pass id_sttp to the Body function

// Close the database connection after all queries are done
$conn->close();

// Output the PDF file to the browser
$pdf->Output();
?>