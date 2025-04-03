<div class="modal fade" id="editAssetModal" tabindex="-1" aria-labelledby="editAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAssetModalLabel">Edit Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editAssetForm">
          <input type="hidden" id="edit_asset_id" name="asset_id">
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_asset_name" class="form-label">Asset Name</label>
              <input type="text" class="form-control" id="edit_asset_name" name="asset_name" required>
            </div>
            <div class="col-md-6">
              <label for="edit_category" class="form-label">Category</label>
              <select class="form-select" id="edit_category" name="category" required>
                <?php
                $categories = mysqli_query($conn, "SELECT * FROM categories");
                while ($cat = mysqli_fetch_assoc($categories)) {
                  echo '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
                }
                ?>
              </select>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="edit_description" class="form-label">Description</label>
              <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="edit_quantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
            </div>
            <div class="col-md-3">
              <label for="edit_unit" class="form-label">Unit</label>
              <input type="text" class="form-control" id="edit_unit" name="unit">
            </div>
            <div class="col-md-3">
              <label for="edit_value" class="form-label">Value (₱)</label>
              <input type="number" class="form-control" id="edit_value" name="value" step="0.01" min="0">
            </div>
            <div class="col-md-3">
              <label for="edit_status" class="form-label">Status</label>
              <select class="form-select" id="edit_status" name="status" required>
                <option value="Available">Available</option>
                <option value="In Use">In Use</option>
                <option value="Under Maintenance">Under Maintenance</option>
                <option value="Disposed">Disposed</option>
              </select>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_office" class="form-label">Office</label>
              <select class="form-select" id="edit_office" name="office_id" required>
                <?php
                $offices = mysqli_query($conn, "SELECT * FROM offices");
                while ($office = mysqli_fetch_assoc($offices)) {
                  echo '<option value="'.$office['id'].'">'.$office['office_name'].'</option>';
                }
                ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_acquisition_date" class="form-label">Acquisition Date</label>
              <input type="date" class="form-control" id="edit_acquisition_date" name="acquisition_date">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_generate_qr" name="generate_qr">
                <label class="form-check-label" for="edit_generate_qr">
                  Regenerate QR Code
                </label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveAssetChanges">Save Changes</button>
      </div>
    </div>
  </div>
</div>