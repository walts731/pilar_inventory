<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleSidebar");
        const sidebar = document.getElementById("sidebar");

        toggleButton.addEventListener("click", function() {
            sidebar.classList.toggle("d-none"); // Hide or show sidebar
        });
    });

    $(document).ready(function() {
        $('#activitiesTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true
        });

        $('#inventoryActionsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true
        });
    });
</script>