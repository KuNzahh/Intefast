<?php
// Memanggil pustaka FPDF
require('../fpdf/fpdf.php');

// Membuat kelas PDF yang merupakan subclass dari FPDF
class PDF extends FPDF
{
    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetMargins(21, 21, 21);
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
            $this->Ln(5);

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
        $text = 'SURAT - IJIN';
        $lineWidth = 79;
        $this->SetX(($this->GetPageWidth() - $lineWidth) / 2);
        $this->Cell($lineWidth, 4, $text, 'B', 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->Ln(1);
        $this->Cell(0, 4, 'Nomor: SI/   / VIII /YAN.2.1./ 2024 / Intelkam', 0, 1, 'C');
        $this->Ln(3);

        $this->Cell(10, 6.5, 'Pertimbangan        :', 0, 0);
        $this->Ln(1);

        $pertimbangan_texts = [
            "Bahwa telah dipenuhi hal yang merupakan persyaratan formal dalam hal ini kegiatan yang diajukan oleh pemohon.",
            "Bahwa kegiatan yang akan dilaksanakan dimaksud perlu diketahui oleh pihak Kepolisian Negara Republik Indonesia untuk dapat dilakukan pemantauan situasi.",
            "Bahwa kegiatan yang akan dilaksanakan ini mungkin tidak menimbulkan kerawanan kamtibmas terutama di lingkungan tempat atau lokasi kegiatan dilaksanakan."
        ];

        $this->SetX(60);
        foreach ($pertimbangan_texts as $index => $text) {
            $this->Cell(5, 5, ($index + 1) . '.', 0, 0);
            $this->MultiCell(0, 5, $text, 0, 'J');
            $this->SetX(60);
        }
        $this->Ln(5);

        $this->Cell(10, 6.5, 'Dasar                     :', 0, 0);
        $this->Ln(1);

        include "../include/koneksi.php";
        session_start();

        $id_sik = $_GET['id'] ?? null;
        $dasar_value = '-';

        if ($id_sik) {
            $query = "
                SELECT 
                    s.nama_instansi, s.penanggung_jawab, s.dasar, p.nama_pekerjaan,
                    s.no_telp, s.peserta, s.alamat, s.tempat, s.tgl_kegiatan,
                    s.rangka, k.nama_kecamatan, kg.nama_keramaian
                FROM sik s
                LEFT JOIN pekerjaan p ON s.pekerjaan_id = p.id_pekerjaan
                LEFT JOIN kecamatan k ON s.kecamatan_id = k.id_kecamatan
                LEFT JOIN jeniskeramaian kg ON s.keramaian_id = kg.id_keramaian
                WHERE s.id_sik = ?
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id_sik);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dasar_value = $row['dasar'];
            }
        }

        $dasar_texts = [
            "Undang-undang Nomor 2 Tahun 2002 tentang Kepolisian Negara Republik Indonesia.",
            "Peraturan Pemerintah Nomor 60 Tahun 2017 Tentang Tata Cara Penerbitan Izin dan Pengawasan Kegiatan Keramaian, serta Kegiatan Masyarakat lainnya.",
            "Peraturan Presiden Nomor 54 Tahun 2022 Tentang Perubahan Kedua Atas Peraturan Presiden Nomor 52 Tahun 2010 Tentang Susunan Organisasi dan Tata Kerja Kepolisian Negara Republik Indonesia.",
            "Peraturan Kepala Kepolisian Negara Republik Indonesia Nomor 2 Tahun 2013 Tentang Tata Cara Perizinan dan Pemberitahuan Kegiatan Masyarakat.",
            "Peraturan Kepolisian Negara Republik Indonesia Nomor 7 Tahun 2023 Tentang Teknis perizinan, pengawasan, dan Tindakan Kepolisian pada kegiatan Keramaian umum pada kegiatan Masyarakat lainnya.",
            "Surat Telegram Kapolda Kalsel Nomor: STR/578/V/YAN.2.1/2022 Tanggal 6 Juni 2022 Tentang Tata cara penerbitan Surat Izin Keramaian.",
            $dasar_value
        ];

        $this->SetX(60);
        foreach ($dasar_texts as $index => $text) {
            $this->Cell(5, 5, ($index + 1) . '.', 0, 0);
            $this->MultiCell(0, 5, $text, 0, 'J');
            $this->SetX(60);
        }

        $this->Ln(5);
        $this->Cell(10, 6.5, 'Mengingat            :', 0, 0);
        $this->Ln(1);

        $kebijaksanaan_texts = [
            "Kebijaksanaan pemerintah berhubungan dengan ketentuan perundang-undangan yang berlaku untuk kegiatan Masyarakat."
        ];

        $this->SetX(61);
        foreach ($kebijaksanaan_texts as $text) {
            $this->MultiCell(0, 5, $text, 0, 'J');
            $this->SetX(61);
        }

        $this->Ln(5);

        $this->AddPage();

        //Halaman 2
        // Mengatur judul dokumen
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(
            0,
            4,
            'MEMBERIKAN IJIN',
            0,
            1,
            'C'
        );
        $this->Ln(2);

        $this->SetFont('Arial', '', 11);

        // Data tabel informasi kegiatan
        $dasar_texts = [
            "Kepada" => "", // Jika tidak diambil dari database, tetap kosong
            "Nama Instansi" => $row['nama_instansi'],
            "Penanggung Jawab" => $row['penanggung_jawab'],
            "Pekerjaan" => $row['nama_pekerjaan'], // Jika ini berbeda, sesuaikan kolomnya
            "Alamat" => $row['alamat'],
            "No. HP" => $row['no_telp'],
            "Untuk" => "Penyelenggaraan Kegiatan sebagai berikut :",
            "1. Bentuk/Macam" => $row['nama_keramaian'], // Jika ini berbeda, sesuaikan kolomnya
            "2. Waktu" => $row['tgl_kegiatan'],
            "3. Tempat" => $row['tempat'] . ', ' . $row['nama_kecamatan'],
            "4. Dalam Rangka" => $row['rangka'],
            "5. Peserta" => $row['peserta']
        ];


        // Perulangan untuk menampilkan setiap pasangan kunci-nilai
        foreach ($dasar_texts as $key => $text) {
            // Cek apakah kunci adalah salah satu dari 5 data yang ingin diatur posisi awalnya
            if (in_array($key, ["1. Bentuk/Macam", "2. Waktu", "3. Tempat", "4. Dalam Rangka", "5. Peserta"])) {
                $this->SetX(66); // Mengatur posisi awal khusus untuk 5 data ini
            }

            // Menampilkan kunci (misalnya "Nama Instansi", "Penanggung Jawab", dll.)
            $this->Cell(40, 5, $key, 0, 0);
            // Menampilkan tanda ":" untuk memisahkan label dan nilai
            $this->Cell(
                5,
                5,
                ':',
                0,
                0
            );
            // Menampilkan nilai yang panjang menggunakan MultiCell agar teks dibungkus dengan rapi
            $this->MultiCell(
                0,    // Lebar sel menyesuaikan sisa lebar halaman
                5,    // Tinggi setiap baris teks
                $text, // Teks yang akan ditampilkan
                0,    // Tidak ada border
                ''    // Menyusun teks secara justify
            );
            // Pindah ke baris berikutnya setelah setiap pasangan kunci-nilai
            $this->Ln(1);
        }
        $this->Ln(3);

        $this->SetFont(
            'Arial',
            '',
            11
        );

        // Data tabel informasi kegiatan
        $catatan_texts = [
            "Dengan Catatan" => "", // Jika tidak diambil dari database, tetap kosong
            "1. " => "Penanggung jawab wajib menaati ketentuan-ketentuan sebagai berikut:",
            "a." => "Wajib menjaga keamanan dan ketertiban dalam kegiatan tersebut.",
            "b." => "Wajib mencegah supaya para peserta tidak melakukan kegiatan-kegiatan lain yang bertentangan ataupun menyimpang dari tujuan kegiatan dan tidak melanggar hukum; jika melanggar akan dicabut izinnya/diberhentikan.",
            "c." => "Wajib lapor dalam 3 x 24 jam sebelum kegiatan dilaksanakan pada Kepolisian setempat.",
            "d." => "Wajib menaati ketentuan-ketentuan lain yang diberikan oleh pejabat setempat sehubungan dengan kegiatan yang akan dilaksanakan.",
            "2." => "Bilamana terdapat penyimpangan dan melakukan pelanggaran tindak pidana terhadap hukum yang berlaku, Petugas Kepolisian/Keamanan dapat membubarkan/menghentikan atau mengambil tindakan lain berdasarkan ketentuan hukum yang berlaku.",
            "3." => "Surat izin ini diberikan kepada yang berkepentingan untuk dipergunakan sebagaimana mestinya, kecuali dalam hal terdapat kekeliruan akan diadakan ralat seperlunya.",
            "4." => "Setelah selesai kegiatan, maka penanggung jawab wajib melaporkan hasilnya kepada Kepolisian setempat yang mengeluarkan izin selambat-lambatnya satu minggu setelah kegiatan."
        ];


        // Perulangan untuk menampilkan setiap pasangan kunci-nilai
        foreach ($catatan_texts as $key => $text) {
            // Cek apakah kunci adalah salah satu dari 5 data yang ingin diatur posisi awalnya
            if (in_array($key, ["a.", "b.", "c.", "d."])) {
                $this->SetX(31); // Mengatur posisi awal khusus untuk 5 data ini
            }

            // Periksa jika kunci adalah "Dengan Catatan" untuk menambahkan garis bawah hanya pada teks
            if ($key === "Dengan Catatan") {
                // Hitung lebar teks "Dengan Catatan" saja (tanpa ":")
                $width = $this->GetStringWidth($key) + 2; // Menambahkan sedikit padding

                // Simpan posisi X dan Y saat ini
                $x = $this->GetX();
                $y = $this->GetY();

                // Tampilkan teks "Dengan Catatan" tanpa border
                $this->Cell($width, 8, $key, 0, 0);

                // Tampilkan tanda ":" setelah teks
                $this->Cell(5, 6, ':', 0, 1);

                // Tambahkan garis bawah (underline) hanya di bawah teks "Dengan Catatan"
                $this->Line($x, $y + 5.5, $x + $width, $y + 5.5); // Sesuaikan Y agar lebih dekat dengan teks
            } else {
                // Menampilkan kunci biasa tanpa garis bawah
                $this->Cell(10, 5, $key, 0, 0);
                $this->MultiCell(
                    0,    // Lebar sel menyesuaikan sisa lebar halaman
                    5,    // Tinggi setiap baris teks
                    $text, // Teks yang akan ditampilkan
                    0,    // Tidak ada border
                    'J'    // Menyusun teks secara justify
                );
            }
            // Pindah ke baris berikutnya setelah setiap pasangan kunci-nilai
            $this->Ln(1);
        }


        // Menambahkan bagian Tanggal dan Penandatanganan di kanan dengan teks rata tengah
        $this->Ln(5);

        // Mengatur posisi X untuk 50mm dari kanan halaman
        $this->SetX($this->GetPageWidth() - 71); // Margin kanan 50mm

        // Tentukan posisi X yang sama untuk kedua teks
        $positionX = $this->GetPageWidth() - 110.6; // Margin kanan 50mm

        // Atur posisi X sebelum mencetak teks pertama
        $this->SetX($positionX);
        $this->Cell(
            30,
            4,
            'Dikeluarkan di',
            0,
            0,
            'L'
        ); // Teks sebelum ':'
        $this->Cell(4, 4, ':', 0, 0, 'L');               // Tanda ':'
        $this->Cell(16, 4, 'Marabahan', 0, 1, 'L');      // Teks setelah ':'

        // Atur posisi X sebelum mencetak teks kedua
        $this->SetX($positionX);
        $this->Cell(
            30,
            4,
            'Pada Tanggal',
            0,
            0,
            'L'
        );   // Teks sebelum ':'
        $this->Cell(4, 4, ':', 0, 0, 'L');               // Tanda ':'
        $formatted_date = date("d F Y");
        $this->SetFont('Arial', '', 11);
        $this->Cell(16, 4, $formatted_date, 0, 1, 'L');




        // Menambahkan jarak (Ln) setelah dua baris teks pertama
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
            echo "<script>alert('Data Kepala Satintel tidak ditemukan.');</script>";
        }


        $this->Ln(-11);

        // Menambahkan header tembusan
        $this->SetFont('Arial', '', 9);
        $this->Cell(3, 6, 'Tembusan :', 0, 1, 'L'); // Teks "Tembusan" di kiri dengan tanda ":"

        // Menentukan panjang teks "Tembusan"
        $tembusan_text = 'Tembusan';
        $width = $this->GetStringWidth($tembusan_text) + 2; // Tambahkan padding sedikit

        // Menyimpan posisi X dan Y saat ini
        $x = $this->GetX();
        $y = $this->GetY() - 1; // Naikkan sedikit agar garis lebih dekat ke teks

        // Menambahkan garis bawah di bawah teks "Tembusan" saja
        $this->Line($x, $y, $x + $width, $y); // Gambar garis dari posisi X ke lebar teks "Tembusan"

        // Data tembusan
        $tembusan_texts = [
            "1. Kapolres Barito Kuala",
            "2. Kabagops Polres Barito Kuala",
            "3. Kapolsek Marabahan Kota"
        ];

        // Perulangan untuk menampilkan setiap baris tembusan
        foreach ($tembusan_texts as $text) {
            // Menambahkan spasi di depan untuk indentasi jika diperlukan
            $this->Cell(5, 4, '', 0, 0); // Spasi kosong untuk indentasi
            $this->Cell(3, 6, $text, 0, 1, 'L'); // Teks tembusan dengan alignment kiri
        }

        // Garis bawah untuk item terakhir jika dibutuhkan
        $this->Ln(0);
        $this->SetX(22); // Atur posisi X ke margin kiri
        $this->Cell(62, 0, '', 'B', 0, 'L'); // Garis bawah dengan panjang 80mm (sesuaikan panjang sesuai kebutuhan)

    }
}

$paperSize = 'A4';
$pdf = new PDF('P', 'mm', $paperSize);
$pdf->AddPage();
$pdf->Body();
$pdf->Output();
