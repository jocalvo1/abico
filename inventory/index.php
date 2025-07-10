<?php
// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/includes/Product.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /ABICO/login.php');
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Product object
$product = new Product($db);

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get all products
$stmt = $product->readAll($search);

// Include template header and sidebar
include __DIR__ . "/../templates/links.php";
include __DIR__ . "/../templates/sidebar.php";
?>

<div class="main-panel">
    <?php include __DIR__ . "/../templates/header.php"; ?>
    
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Inventory Management</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home">
                        <a href="/ABICO">
                            <i class="flaticon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Inventory</a>
                    </li>
                </ul>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">Products</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search and Add Product Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <form action="" method="get" class="form-inline">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="submit">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                <?php if (!empty($search)): ?>
                                                    <a href="index.php" class="btn btn-outline-danger">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                        <i class="fa fa-plus"></i> Add New Product
                                    </button>
                                </div>
                            </div>

                            <!-- Products Table -->
                            <div class="table-responsive">
                                <table id="productTable" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product Name</th>
                                            <th>Description</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td class="text-right"><?php echo number_format($row['quantity']); ?></td>
                                                <td class="text-right">₱<?php echo number_format($row['price'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-warning btn-sm edit-btn" 
                                                                data-id="<?php echo $row['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                                                data-desc="<?php echo htmlspecialchars($row['description']); ?>"
                                                                data-quantity="<?php echo $row['quantity']; ?>"
                                                                data-price="<?php echo $row['price']; ?>"
                                                                data-toggle="tooltip" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm delete-btn" 
                                                                data-id="<?php echo $row['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                                                data-toggle="tooltip" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if ($stmt->rowCount() === 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No products found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addProductForm" action="actions/add_product.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editProductForm" action="actions/update_product.php" method="post">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_product_name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_price">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control" id="edit_price" name="price" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="actions/delete_product.php" method="post" style="display: inline-block;">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../templates/footer.php"; ?>
</div>

<!--   Core JS Files   -->
<script src="/ABICO/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/ABICO/assets/js/core/popper.min.js"></script>
<script src="/ABICO/assets/js/core/bootstrap.min.js"></script>
<!-- Datatables -->
<script src="/ABICO/assets/js/plugin/datatables/datatables.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#productTable').DataTable({
            "pageLength": 10,
            "order": [[0, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting on actions column
            ]
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize modals
        const addProductModalEl = document.getElementById('addProductModal');
        const editProductModalEl = document.getElementById('editProductModal');
        const deleteProductModalEl = document.getElementById('deleteModal');
        
        // Initialize Bootstrap modals
        const addProductModal = new bootstrap.Modal(addProductModalEl);
        const editProductModal = new bootstrap.Modal(editProductModalEl);
        const deleteProductModal = new bootstrap.Modal(deleteProductModalEl);
        
        // Reset form when modal is about to be shown
        addProductModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('addProductForm').reset();
        });
        
        // Handle edit button click
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var desc = $(this).data('desc');
            var quantity = $(this).data('quantity');
            var price = $(this).data('price');
            
            $('#edit_id').val(id);
            $('#edit_product_name').val(name);
            $('#edit_description').val(desc);
            $('#edit_quantity').val(quantity);
            $('#edit_price').val(price);
            
            editProductModal.show();
        });
        
        // Handle delete button click
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#delete_id').val(id);
            $('#deleteProductName').text(name);
            
            deleteProductModal.show();
        });
        
                // Handle add product form submission
        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            
            $.ajax({
                url: 'actions/add_product.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Product added successfully!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                            modal.hide();
                            
                            // Refresh the page to show the new product
                            location.reload();
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'An error occurred while adding the product.'
                        });
                        
                        // Re-enable the submit button
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while adding the product. Please try again.'
                    });
                    
                    // Re-enable the submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    console.error('Error:', error);
                }
            });
        });
        
        // Handle edit form submission
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Product updated successfully!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Something went wrong.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update the product. Please try again.'
                    });
                },
                complete: function() {
                    $('#editProductModal').modal('hide');
                }
            });
        });
        
        // Handle delete form submission
        $('#deleteForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Something went wrong.'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to delete the product. Please try again.'
                            });
                        },
                        complete: function() {
                            $('#deleteModal').modal('hide');
                        }
                    });
                }
            });
        });
        
        // Reset form when modal is closed
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form').trigger('reset');
        });
    });
</script>


