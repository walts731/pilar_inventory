<!-- Add New Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssetModalLabel">Add New Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="add_asset.php" method="POST">
                    <div class="row">
                        <!-- Asset Name -->
                        <div class="col-md-6 mb-3">
                            <label for="asset_name" class="form-label">Asset Name</label>
                            <input type="text" class="form-control" id="asset_name" name="asset_name" required>
                        </div>

                        <!-- Category Dropdown -->
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php
                                $catQuery = "SELECT id, category_name FROM categories";
                                $catResult = mysqli_query($conn, $catQuery);
                                while ($category = mysqli_fetch_assoc($catResult)) {
                                    echo "<option value='{$category['id']}'>{$category['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Description (Full-width) -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Quantity -->
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>

                        <!-- Unit -->
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Value -->
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label">Value (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                        </div>

                        <!-- Status Dropdown -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Available">Available</option>
                                <option value="In Use">In Use</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Disposed">Disposed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Office Dropdown -->
                        <div class="col-md-6 mb-3">
                            <label for="office" class="form-label">Office</label>
                            <select class="form-control" id="office" name="office_id" required>
                                <option value="">Select Office</option>
                                <?php
                                $officeQuery = "SELECT id, office_name FROM offices";
                                $officeResult = mysqli_query($conn, $officeQuery);
                                while ($office = mysqli_fetch_assoc($officeResult)) {
                                    echo "<option value='{$office['id']}'>{$office['office_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Acquisition Date -->
                        <div class="col-md-6 mb-3">
                            <label for="acquisition_date" class="form-label">Acquisition Date</label>
                            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" required>
                        </div>
                        <div class="row" id="qrCodeContainer" style="display: none;">
                            <div class="col-12 text-center">
                                <label class="form-label">QR Code</label>
                                <div id="qrCodePreview"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>