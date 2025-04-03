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

    $(document).ready(function() {
        // 🔹 Load categories when modal opens
        $('#addCategoryModal').on('show.bs.modal', function() {
            fetchCategories();
        });

        // 🔹 Fetch categories via AJAX
        function fetchCategories() {
            $.ajax({
                url: "fetch_categories_for_manage.php",
                type: "GET",
                success: function(data) {
                    $("#categoryList").html(data);
                }
            });
        }

        // 🔹 Add new category
        $("#addCategoryForm").submit(function(e) {
            e.preventDefault();
            var categoryName = $("#category_name").val();

            $.ajax({
                url: "add_category.php",
                type: "POST",
                data: {
                    category_name: categoryName
                },
                success: function(response) {
                    alert(response);
                    fetchCategories(); // Refresh category list
                    $("#category_name").val(""); // Clear input field
                }
            });
        });

        // 🔹 Delete category (Only if asset count is 0)
        $(document).on("click", ".delete-category", function() {
            var categoryId = $(this).data("id");

            if (confirm("Are you sure you want to delete this category?")) {
                $.ajax({
                    url: "delete_category.php",
                    type: "POST",
                    data: {
                        category_id: categoryId
                    },
                    success: function(response) {
                        alert(response);
                        fetchCategories(); // Refresh category list
                    }
                });
            }
        });
    });

    $(document).ready(function () {
    $('#category').change(function () {
      var selectedCategory = $("#category option:selected").text().toLowerCase();
      
      if (selectedCategory !== "office supply") {
        var qrText = "Asset Name: " + $('#asset_name').val() + "\nCategory: " + selectedCategory;
        var qrURL = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" + encodeURIComponent(qrText);
        $("#qrCodePreview").html('<img src="' + qrURL + '" alt="QR Code">');
        $("#qrCodeContainer").show();
      } else {
        $("#qrCodeContainer").hide();
      }
    });
  });
</script>