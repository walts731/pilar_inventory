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

  $(document).ready(function() {
  // Handle edit button click
  $('.edit-asset').click(function() {
    var assetId = $(this).data('asset-id');
    loadAssetData(assetId);
  });

  // Load asset data
  function loadAssetData(assetId) {
    $.ajax({
      url: 'get_asset_data.php',
      type: 'GET',
      data: { asset_id: assetId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          $('#edit_asset_id').val(response.data.id);
          $('#edit_asset_name').val(response.data.asset_name);
          $('#edit_description').val(response.data.description);
          $('#edit_quantity').val(response.data.quantity);
          $('#edit_unit').val(response.data.unit);
          $('#edit_value').val(response.data.value);
          $('#edit_status').val(response.data.status);
          $('#edit_office').val(response.data.office_id);
          $('#edit_category').val(response.data.category);
          $('#edit_acquisition_date').val(response.data.acquisition_date);
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function() {
        alert('Error loading asset data');
      }
    });
  }

  // Save changes
  $('#saveAssetChanges').click(function() {
    var formData = $('#editAssetForm').serialize();
    
    $.ajax({
      url: 'update_asset.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          alert('Asset updated successfully');
          location.reload(); // Refresh the page
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function() {
        alert('Error saving changes');
      }
    });
  });
});

// Handle the Red Tag button click event
$(document).on('click', '.red-tag-asset', function() {
  var assetId = $(this).data('asset-id');
  $('#asset_id').val(assetId);  // Set the asset ID in the hidden input field
});

// Handle form submission for red tagging
$('#redTagForm').on('submit', function(e) {
  e.preventDefault();
  
  var assetId = $('#asset_id').val();
  
  // Send AJAX request to update the asset status
  $.ajax({
    url: 'red_tag_asset.php',
    method: 'POST',
    data: { asset_id: assetId },
    success: function(response) {
      alert(response);  // Notify the user
      location.reload(); // Reload the page to reflect changes
    },
    error: function() {
      alert('An error occurred while updating the asset status.');
    }
  });
});

function validatePassword() {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;
    if (password !== confirm) {
        alert("Passwords do not match.");
        return false;
    }
    return true;
}
</script>