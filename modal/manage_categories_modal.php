<!-- Manage Categories Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Category Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs for List and Add -->
                <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-tab-pane"
                            type="button" role="tab" aria-controls="list-tab-pane" aria-selected="true">
                            <i class="fas fa-list me-1"></i> Categories List
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-tab-pane"
                            type="button" role="tab" aria-controls="add-tab-pane" aria-selected="false">
                            <i class="fas fa-plus me-1"></i> Add New Category
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="categoryTabsContent">
                    <!-- 🔹 Categories List Tab -->
                    <div class="tab-pane fade show active" id="list-tab-pane" role="tabpanel" aria-labelledby="list-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Assets Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryList">
                                    <!-- Categories will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 🔹 Add New Category Tab -->
                    <div class="tab-pane fade" id="add-tab-pane" role="tabpanel" aria-labelledby="add-tab">
                        <form id="addCategoryForm">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Add Category</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
