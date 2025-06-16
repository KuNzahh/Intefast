<script>
            window.addEventListener("DOMContentLoaded", function() {
                const dropdown = document.getElementById("notificationDropdown");
                const badge = document.getElementById("notification-icon-badge");

                if (dropdown && badge) {
                    dropdown.addEventListener("click", function() {
                        badge.classList.add('d-none'); // lebih smooth daripada style.display = 'none'
                    });
                }
            });
        </script>
<!-- Popper JS -->
<script src="../assets/libs/@popperjs/core/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Defaultmenu JS -->
<script src="../assets/js/defaultmenu.min.js"></script>

<!-- Node Waves JS-->
<script src="../assets/libs/node-waves/waves.min.js"></script>

<!-- Sticky JS -->
<script src="../assets/js/sticky.js"></script>

<!-- Simplebar JS -->
<script src="../assets/libs/simplebar/simplebar.min.js"></script>
<script src="../assets/js/simplebar.js"></script>

<!-- Color Picker JS -->
<script src="../assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>


<!-- JSVector Maps JS -->
<script src="../assets/libs/jsvectormap/js/jsvectormap.min.js"></script>

<!-- JSVector Maps MapsJS -->
<script src="../assets/libs/jsvectormap/maps/world-merc.js"></script>

<!-- Apex Charts JS -->
<script src="../assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Main-Dashboard -->
<script src="../assets/js/index.js"></script>


<!-- Custom-Switcher JS -->
<script src="../assets/js/custom-switcher.min.js"></script>

<!-- Custom JS -->
<script src="../assets/js/custom.js"></script>
    <!-- Chartjs Chart JS -->
    <script src="../assets/libs/chart.js/chart.min.js"></script>

    <!-- Imternal Chartjs JS -->
    <script src="../assets/js/chartjs-charts.js"></script>

    <!-- Custom JS -->
    <script src="../assets/js/custom.js"></script>