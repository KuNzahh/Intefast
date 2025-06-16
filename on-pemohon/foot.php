
<div class="container">
    <span class="text-muted">
        Copyright © <span id="year"></span>
        <a href="javascript:void(0);" class="text-dark fw-semibold">Intelfast</a>.
        Designed with <i class="bi bi-heart-fill text-danger"></i> by
        <a href="javascript:void(0);">
            <span class="fw-semibold text-primary text-decoration-underline">Azijah</span>
        </a>.
        All rights reserved.
    </span>

</div>

<!-- Link jQuery (Select2 membutuhkan jQuery) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- Link JavaScript Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Pastikan jQuery dimuat sebelum Select2 diinisialisasi
    $(document).ready(function() {
        // Data negara dan kode ISO 3166-1 alpha-2 untuk bendera
        const countries = [
            { id: 'Indonesia', text: 'Indonesia' },
            { id: 'MY', text: 'Malaysia' },
            { id: 'SG', text: 'Singapura' },
            { id: 'TH', text: 'Thailand' },
            { id: 'VN', text: 'Vietnam' },
            { id: 'PH', text: 'Filipina' },
            { id: 'AU', text: 'Australia' },
            { id: 'CN', text: 'China' },
            { id: 'JP', text: 'Jepang' },
            { id: 'KR', text: 'Korea Selatan' },
            { id: 'US', text: 'Amerika Serikat' },
            { id: 'GB', text: 'Britania Raya' },
            { id: 'DE', text: 'Jerman' },
            { id: 'FR', text: 'Prancis' },
            { id: 'CA', text: 'Kanada' },
            { id: 'IN', text: 'India' },
            // Tambahkan negara lain sesuai kebutuhan Anda
        ];

        // Inisialisasi Select2 pada elemen #kebangsaan
        $('#kebangsaan').select2({
            placeholder: '-- Pilih Kebangsaan --',
            allowClear: true, // Memungkinkan untuk mengosongkan pilihan
            data: countries, // Menggunakan data negara yang telah kita definisikan
            templateResult: formatState, // Fungsi untuk merender setiap opsi
            templateSelection: formatState // Fungsi untuk merender pilihan yang sudah dipilih
        });

        // Fungsi untuk merender opsi dengan bendera
        function formatState (state) {
            if (!state.id) {
                return state.text;
            }
            // Menggunakan kelas flag-icon-css untuk menampilkan bendera
            // state.id adalah kode negara (misal: 'ID', 'US')
            // state.text adalah nama negara (misal: 'Indonesia', 'Amerika Serikat')
            var $state = $(
                '<span><span class="flag-icon flag-icon-' + state.id.toLowerCase() + '"></span> ' + state.text + '</span>'
            );
            return $state;
        };
    }); // Penutup document.ready
</script>