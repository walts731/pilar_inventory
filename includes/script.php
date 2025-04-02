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

    $(document).ready(function() {
        $('#addAssetModal').on('show.bs.modal', function() {
            fetchCategories();
            fetchOffices();
        });

        function fetchCategories() {
            $.ajax({
                url: "fetch_categories_for_add.php",
                type: "GET",
                success: function(data) {
                    $("#category").html('<option value="">Select Category</option>' + data);
                }
            });
        }

        function fetchOffices() {
            $.ajax({
                url: "fetch_offices.php",
                type: "GET",
                success: function(data) {
                    $("#office").html('<option value="">Select Office</option>' + data);
                }
            });
        }

        $("#addAssetForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "add_asset.php",
                type: "POST",
                data: $("#addAssetForm").serialize(),
                success: function(response) {
                    alert(response);
                    $('#addAssetModal').modal('hide');
                    $("#addAssetForm")[0].reset();
                }
            });
        });
    });
</script>