<?php
session_start();
// Include required files
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/inventory/product.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /abico/login.php');
    exit();
}

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize Product object
$product = new product($db);

// Get all products
$stmt = $product->readAll();

// Include template header, sidebar, and navigation
include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>

<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
<<<<<<< HEAD
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0">
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-primary active">
                                <i class="fas fa-users"></i> Inventory
                            </a>
                            <a href="activity_log.php" class="btn btn-outline-secondary">
                                <i class="fas fa-history"></i> Activity Logs
                            </a>
                        </div>
                    </h4>
                </div>

=======
                <h3 class="card-title fw-bold mb-0">Inventory</h3>
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
                <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add new item
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="inventoryTable" class="table table-hover table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>  
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($row['product_name'], 0, 20, "...")); ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 20, "...")); ?></td>
                                <td data-order="<?php echo $row['price']; ?>">₱<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>
                                    <div class="d-flex">
                                    <button class="btn btn-primary btn-sm me-2 edit-product"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                            data-quantity="<?php echo $row['quantity']; ?>"
                                            data-price="<?php echo $row['price']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                        <button class="btn btn-danger btn-sm delete-product" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['product_name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
<<<<<<< HEAD
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">Add New Product</h5>
=======
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/abico/includes/inventory/add_product.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
<<<<<<< HEAD
                        <label class="form-label small fw-medium">Product Name</label>
                        <input type="text" class="form-control form-control-sm" name="product_name" placeholder="e.g., Premium White Sugar" required>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3 pb-1 border-bottom fw-semibold text-uppercase small text-muted">Product Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Type</label>
                                    <select class="form-select" name="product_type" id="productType" required>
                                    <option value="">-- Select Product Type --</option>
                                    <optgroup label="Weight">
                                        <option value="gram">Gram (g)</option>
                                        <option value="kilo">Kilogram (kg)</option>
                                    </optgroup>
                                    <optgroup label="Volume">
                                        <option value="liter">Liter (L)</option>
                                        <option value="ml">Milliliter (ml)</option>
                                    </optgroup>
                                    <optgroup label="Count">
                                        <option value="piece">Piece (pc)</option>
                                        <option value="dozen">Dozen</option>
                                        <option value="set">Set</option>
                                        <option value="tray">Tray</option>
                                    </optgroup>
                                    <optgroup label="Containers">
                                        <option value="bottle">Bottle</option>
                                        <option value="can">Can</option>
                                        <option value="box">Box</option>
                                        <option value="pack">Pack</option>
                                        <option value="sachet">Sachet</option>
                                        <option value="pouch">Pouch</option>
                                        <option value="bar">Bar</option>
                                        <option value="cup">Cup</option>
                                        <option value="roll">Roll</option>
                                        <option value="stick">Stick</option>
                                        <option value="tetra">Tetra Pack</option>
                                    </optgroup>
                                    <option value="other">-- Other --</option>
                                    </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-flex justify-content-between">
                                    <span>Quantity</span>
                                    <span class="text-muted small" id="unitHelp">per item</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="product_quantity" id="productQuantity" min="0.01" step="0.01" value="1" required>
                                    <span class="input-group-text bg-light" id="typeUnit">pc</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div id="otherProductType" class="d-none">
                                    <div class="border-top pt-3">
                                        <label class="form-label">Specify Product Type</label>
                                        <input type="text" class="form-control" name="other_product_type" placeholder="Enter custom product type">
                                    </div>
                                </div>
                                <div id="piecesPerPackContainer" style="display: none;">
                                    <div class="border-top pt-3">
                                        <label class="form-label">Pieces per Pack</label>
                                        <input type="number" class="form-control" name="pieces_per_pack" id="piecesPerPack" min="1" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3 pb-1 border-bottom fw-semibold text-uppercase small text-muted">Inventory Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="quantity" placeholder="Enter quantity" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="price" placeholder="Enter price" min="0" step="0.01" required>
                                </div>
=======
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="product_name" placeholder="Enter product name"     required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Enter description"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" placeholder="Enter quantity" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" placeholder="Enter price" min="0" step="0.01" required>
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
                            </div>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
=======
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" action="/abico/includes/inventory/update_product.php" method="POST">
                <input type="hidden" name="id" id="editProductId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="product_name" id="editProductName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editProductDescription" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="editProductQuantity" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" id="editProductPrice" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js">
</script>

<script>
$(document).ready(function() {
<<<<<<< HEAD
    // Update unit display and handle product type changes
    function updateUnitDisplay() {
        const type = $('#productType').val();
        let unit = 'pc';
        let unitHelp = 'per item';
        
        // Show/hide pieces per pack
        if (type === 'pack') {
            $('#piecesPerPackContainer').show();
            unit = 'pack';
            unitHelp = 'per pack';
        } else {
            $('#piecesPerPackContainer').hide();
            unitHelp = 'per ' + (type || 'item');
        }
        
        // Handle unit display
        switch(type) {
            case 'gram': unit = 'g'; break;
            case 'kilo': unit = 'kg'; break;
            case 'liter': unit = 'L'; break;
            case 'ml': unit = 'ml'; break;
            case 'piece': unit = 'pc'; break;
            case 'other': 
                unit = ''; 
                $('#otherProductType').removeClass('d-none');
                unitHelp = 'per unit';
                break;
            default: 
                if (type !== 'pack') {
                    unit = 'pc';
                }
                $('#otherProductType').addClass('d-none');
        }
        
        $('#typeUnit').text(unit);
        $('#unitHelp').text(unitHelp);
    }
    
    // Initialize unit display
    updateUnitDisplay();
    
    // Update unit when type changes
    $('#productType').on('change', updateUnitDisplay);



    // Initialize DataTable
    var table = $('#inventoryTable').DataTable({
        "pageLength": 10, // Default number of entries
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]], // Entries dropdown
        "order": [[0, 'asc']], // Default sorting by first column
        "responsive": true,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search products...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on Actions column
        ]
    });

=======
    // Initialize DataTable
    var table = $('#inventoryTable').DataTable({
        "pageLength": 10, // Default number of entries
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]], // Entries dropdown
        "order": [[0, 'asc']], // Default sorting by first column
        "responsive": true,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search products...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on Actions column
        ]
    });

>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
    // Add custom search input
    $('.dataTables_filter input').addClass('form-control mb-3');
    
    // Add custom length menu
    $('.dataTables_length select').addClass('form-select mb-3');
    
    // Style pagination
    $('.dataTables_paginate').addClass('mt-3');
    
    // Edit product functionality
    $('.edit-product').on('click', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const productDesc = $(this).data('description');
        const productQty = $(this).data('quantity');
        const productPrice = $(this).data('price');
        
        // Populate the edit form
        $('#editProductId').val(productId);
        $('#editProductName').val(productName);
        $('#editProductDescription').val(productDesc);
        $('#editProductQuantity').val(productQty);
        $('#editProductPrice').val(productPrice);
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        editModal.show();
    });
    
    // Handle edit form submission
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Product updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update product',
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating the product',
                });
            }
        });
    });
    
    // Delete product functionality
    $('.delete-product').on('click', function(e) {
        e.preventDefault();
        
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        
        Swal.fire({
            title: 'Delete Product',
            text: `Are you sure you want to delete "${productName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Send delete request
                $.ajax({
                    url: '/abico/includes/inventory/delete_product.php',
                    type: 'POST',
                    data: { id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message || 'The product has been deleted.',
                                'success'
                            ).then(() => {
                                // Reload the page to reflect changes
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'Failed to delete product.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the product.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>

<?php
// Display SweetAlert if there's a message in the session
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']); // Clear the message after displaying
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '<?php echo addslashes($alert['title']); ?>',
            text: '<?php echo addslashes($alert['message']); ?>',
            icon: '<?php echo $alert['icon']; ?>',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });
    });
    </script>
    <?php
}
?>

</body>
</html>