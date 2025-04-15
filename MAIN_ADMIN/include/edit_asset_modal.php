<!-- Edit Asset Modal -->
<div class="modal fade" id="editAssetModal" tabindex="-1" aria-labelledby="editAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Added 'modal-lg' for medium size -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssetModalLabel">Edit Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="edit_asset.php" method="POST" id="editAssetForm">
                    <input type="hidden" name="asset_id" id="editAssetId">

                    <!-- First Column of Inputs -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editAssetName" class="form-label">Asset Name</label>
                                <input type="text" class="form-control" id="editAssetName" name="asset_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="editCategory" class="form-label">Category</label>
                                <select class="form-select" id="editCategory" name="category" required>
                                    <!-- Populate categories dynamically -->
                                    <?php
                                    $categoryQuery = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
                                    while ($category = $categoryQuery->fetch_assoc()) {
                                        echo "<option value='{$category['id']}'>{$category['category_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editQuantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="editQuantity" name="quantity" required>
                            </div>
                        </div>

                        <!-- Second Column of Inputs -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editUnit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="editUnit" name="unit" required>
                            </div>

                            <div class="mb-3">
                                <label for="editStatus" class="form-label">Status</label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="in use">In Use</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="unserviceable">Unserviceable</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editAcquisitionDate" class="form-label">Acquisition Date</label>
                                <input type="date" class="form-control" id="editAcquisitionDate" name="acquisition_date" required>
                            </div>

                            <div class="mb-3">
                                <label for="editValue" class="form-label">Value</label>
                                <input type="number" class="form-control" id="editValue" name="value" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="editQRCode" class="form-label">QR Code (Optional)</label>
                                <input type="file" class="form-control" id="editQRCode" name="qr_code">
                            </div>

                            <div class="mb-3">
                                <label for="editLastUpdated" class="form-label">Last Updated</label>
                                <input type="text" class="form-control" id="editLastUpdated" name="last_updated" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
