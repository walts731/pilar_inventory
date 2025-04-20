<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="add_asset.php" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addAssetModalLabel">Add New Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label for="asset_name" class="form-label">Asset Name</label>
                        <input type="text" class="form-control" name="asset_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="" disabled selected>Select a category</option>
                            <?php while ($cat = $categoryQuery->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>"> <!-- Use category ID -->
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>


                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="col-md-4">
                        <label for="unit" class="form-label">Unit</label>
                        <select class="form-select" name="unit" required>
                            <option value="" disabled selected>Select unit</option>
                            <option value="pcs">pcs</option>
                            <option value="box">box</option>
                            <option value="kg">kg</option>
                            <option value="liters">liters</option>
                            <option value="pack">pack</option>
                            <option value="set">set</option>
                            <option value="unit">unit</option> <!-- for vehicle or general asset -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="In Use">In Use</option>
                            <option value="unserviceable">Unserviceable</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="acquisition_date" class="form-label">Acquisition Date</label>
                        <input type="date" class="form-control" name="acquisition_date" required>
                    </div>
                    <div class="col-md-6">
                        <label for="value" class="form-label">Value</label>
                        <input type="number" step="0.01" class="form-control" name="value">
                    </div>
                    <div class="col-md-6">
                        <label for="red_tagged" class="form-label">Red Tagged</label>
                        <select class="form-select" name="red_tagged">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="save_asset" class="btn btn-primary">Save Asset</button>
                </div>
            </div>
        </form>
    </div>
</div>