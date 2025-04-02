<!-- Add New Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssetModalLabel">Add New Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAssetForm">
                    <div class="row">
                        <!-- Asset Name -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_name" class="form-label">Asset Name</label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" required>
                            </div>
                        </div>

                        <!-- Category Dropdown -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Quantity -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                            </div>
                        </div>

                        <!-- Unit -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" required>
                            </div>
                        </div>

                        <!-- Value (in Peso ₱) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="value" class="form-label">Value (₱)</label>
                                <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Acquisition Date -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="acquisition_date" class="form-label">Acquisition Date</label>
                                <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" required>
                            </div>
                        </div>

                        <!-- Office Dropdown -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="office" class="form-label">Office</label>
                                <select class="form-select" id="office" name="office" required>
                                    <option value="">Select Office</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Add Asset</button>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
