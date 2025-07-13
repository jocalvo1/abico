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

// Get all products, ordered by created_at descending
$stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");

// Include template header, sidebar, and navigation
include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>

<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0">
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-primary active">
                                <i class="fas fa-boxes"></i> Inventory
                            </a>
                            <a href="activity_log.php" class="btn btn-outline-secondary">
                                <i class="fas fa-history"></i> Activity Logs
                            </a>
                        </div>
                    </h4>
                </div>

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
                                <th>Product Name</th>
                                <th>Units</th>
                                <th>Stock Quantity</th>
                                <th>Price</th>
                                <th>Actions</th>
                                <th class="d-none">Created At</th> <!-- Hidden column for sorting -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td></td> <!-- Will be populated by DataTables -->
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td>
                                    <?php 
                                    $unitValue = $row['unit_value'] ?? 1;
                                    $unitType = $row['unit_type'] ?? '';
                                    
                                    $unitMap = [
                                        'g' => 'Grams',
                                        'kg' => 'Kilograms',
                                        'L' => 'Liters',
                                        'ml' => 'Milliliters',
                                        'pc' => 'Pieces',
                                        'doz' => 'Dozen',
                                        'set' => 'Set',
                                        'tray' => 'Tray',
                                        'pack' => 'Pack' . (!empty($row['pieces_per_pack']) ? ' (' . $row['pieces_per_pack'] . ' pcs)' : ''),
                                        'other' => !empty($row['other_unit_type']) ? htmlspecialchars($row['other_unit_type']) : 'Other'
                                    ];
                                    
                                    $displayUnit = $unitMap[$unitType] ?? ucfirst($unitType);
                                    echo $unitValue . ' ' . $displayUnit . ($unitValue != 1 && !in_array($unitType, ['g', 'kg', 'L', 'ml', 'pc', 'doz', 'set', 'tray', 'pack', 'other']) ? 's' : '');
                                    ?>
                                </td>
                                <td><?php echo $row['stock_quantity'] ?? $row['quantity']; ?></td>
                                <td data-order="<?php echo $row['price_per_unit'] ?? $row['price']; ?>">₱<?php echo number_format(($row['price_per_unit'] ?? $row['price']), 2); ?></td>
                                <td class="d-none"><?php echo $row['created_at']; ?></td>
                                <td>
                                    <div class="d-flex">
                                    <button class="btn btn-primary btn-sm me-2 edit-product"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                            data-quantity="<?php echo $row['stock_quantity'] ?? $row['quantity']; ?>"
                                            data-unit-type="<?php echo htmlspecialchars($row['unit_type'] ?? ''); ?>"
                                            data-unit-value="<?php echo $row['unit_value'] ?? '1'; ?>"
                                            data-other-unit-type="<?php echo htmlspecialchars($row['other_unit_type'] ?? ''); ?>"
                                            data-pieces-per-pack="<?php echo $row['pieces_per_pack'] ?? ''; ?>"
                                            data-price="<?php echo $row['price_per_unit'] ?? $row['price']; ?>">
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
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/abico/includes/inventory/add_product.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Product Name</label>
                        <input type="text" class="form-control form-control-sm" name="product_name" placeholder="e.g., Premium White Sugar" required>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3 pb-1 border-bottom fw-semibold text-uppercase small text-muted">Unit Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Unit Type</label>
                                <select class="form-select" name="unit_type" id="unitType" required>
                                    <option value="">-- Select Unit Type --</option>
                                    <optgroup label="Weight">
                                        <option value="g">Gram (g)</option>
                                        <option value="kg">Kilogram (kg)</option>
                                    </optgroup>
                                    <optgroup label="Volume">
                                        <option value="L">Liter (L)</option>
                                        <option value="ml">Milliliter (ml)</option>
                                    </optgroup>
                                    <optgroup label="Count">
                                        <option value="pc" selected>Piece (pc)</option>
                                        <option value="doz">Dozen</option>
                                        <option value="set">Set</option>
                                        <option value="tray">Tray</option>
                                        <option value="pack">Pack</option>
                                    </optgroup>
                                    <option value="other">-- Other --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit Value</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="unit_value" id="unitValue" min="0.01" step="0.01" value="1" required>
                                    <span class="input-group-text bg-light" id="unitDisplay">pc</span>
                                </div>
                                <small class="text-muted">The quantity per unit (e.g., 1, 12 for dozen, etc.)</small>
                            </div>
                            <div class="col-12">
                                <div id="otherUnitTypeContainer" class="d-none">
                                    <label class="form-label">Custom Unit Type</label>
                                    <input type="text" class="form-control" name="other_unit_type" id="otherUnitType" placeholder="e.g., bundle, carton, etc.">
                                </div>
                                <div id="piecesPerPackContainer" class="d-none">
                                    <label class="form-label">Pieces per Pack</label>
                                    <input type="number" class="form-control" name="pieces_per_pack" id="piecesPerPack" min="1" value="1" placeholder="e.g., 12">
                                    <small class="text-muted">Number of individual pieces in one pack</small>
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
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
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

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3 pb-1 border-bottom fw-semibold text-uppercase small text-muted">Unit Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Unit Type</label>
                                <select name="unit_type" id="editUnitType" class="form-select">
                                    <option value="">-- Select Unit Type --</option>
                                    <optgroup label="Weight">
                                        <option value="g">Gram (g)</option>
                                        <option value="kg">Kilogram (kg)</option>
                                    </optgroup>
                                    <optgroup label="Volume">
                                        <option value="L">Liter (L)</option>
                                        <option value="ml">Milliliter (ml)</option>
                                    </optgroup>
                                    <optgroup label="Count">
                                        <option value="pc">Piece (pc)</option>
                                        <option value="doz">Dozen</option>
                                        <option value="set">Set</option>
                                        <option value="tray">Tray</option>
                                        <option value="pack">Pack</option>
                                    </optgroup>
                                    <option value="other">-- Other --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit Value</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="unit_value" id="editUnitValue" min="0.01" step="0.01" value="1" required>
                                    <span class="input-group-text bg-light" id="editUnitDisplay">pc</span>
                                </div>
                                <small class="text-muted">The quantity per unit (e.g., 1, 12 for dozen, etc.)</small>
                            </div>
                            <div class="col-12">
                                <div id="editOtherUnitTypeContainer" class="d-none">
                                    <label class="form-label">Custom Unit Type</label>
                                    <input type="text" class="form-control" name="other_unit_type" id="editOtherUnitType" placeholder="e.g., bundle, carton, etc.">
                                </div>
                                <div id="editPiecesPerPackContainer" class="d-none">
                                    <label class="form-label">Pieces per Pack</label>
                                    <input type="number" class="form-control" name="pieces_per_pack" id="editPiecesPerPack" min="1" value="1" placeholder="e.g., 12">
                                    <small class="text-muted">Number of individual pieces in one pack</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3 pb-1 border-bottom fw-semibold text-uppercase small text-muted">Inventory Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" id="editProductQuantity" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="price_per_unit" id="editProductPrice" min="0" step="0.01" required>
                                </div>
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
    // Update unit display based on selected unit type
    function updateUnitDisplay() {
        const unitType = $('#unitType').val();
        let unitDisplay = '';
        
        // Show/hide pieces per pack field
        if (unitType === 'pack') {
            $('#piecesPerPackContainer').removeClass('d-none');
            $('#piecesPerPack').prop('required', true);
        } else {
            $('#piecesPerPackContainer').addClass('d-none');
            $('#piecesPerPack').prop('required', false);
        }
        
        // Update the unit display text
        switch(unitType) {
            case 'g': unitDisplay = 'g'; break;
            case 'kg': unitDisplay = 'kg'; break;
            case 'L': unitDisplay = 'L'; break;
            case 'ml': unitDisplay = 'ml'; break;
            case 'pc': unitDisplay = 'pc'; break;
            case 'doz': unitDisplay = 'doz'; break;
            case 'set': unitDisplay = 'set'; break;
            case 'tray': unitDisplay = 'tray'; break;
            case 'pack': unitDisplay = 'pack'; break;
            case 'other': 
                unitDisplay = 'unit';
                $('#otherUnitTypeContainer').removeClass('d-none');
                break;
            default: 
                unitDisplay = 'pc';
                $('#otherUnitTypeContainer').addClass('d-none');
        }
        
        // Update the display and required attributes
        $('#unitDisplay').text(unitDisplay);
        
        // If not 'other', clear any value in other_unit_type
        if (unitType !== 'other') {
            $('#otherUnitType').val('');
        } else {
            $('#otherUnitType').prop('required', true);
        }
    }
    
    // Initialize unit display
    updateUnitDisplay();
    
    // Update unit when type changes
    $('#unitType').on('change', updateUnitDisplay);
    
    // Form validation for other_unit_type when unit type is 'other'
    $('form').on('submit', function(e) {
        if ($('#unitType').val() === 'other' && !$('#otherUnitType').val().trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please specify a custom unit type',
                confirmButtonText: 'OK'
            });
        }
    });


    // Initialize DataTable with custom sorting
    const table = $('#inventoryTable').DataTable({
        responsive: true,
        // Disable initial sort to prevent auto-sorting by the first column
        order: [],
        // Add created_at column as hidden for sorting
        columnDefs: [
            {
                targets: 0, // # column
                orderable: false, // Disable sorting for the # column
                render: function(data, type, row, meta) {
                    // Just show the row number (1-based)
                    return meta.row + 1;
                },
                className: 'text-center'
            },
            { 
                targets: -1, // Actions column
                orderable: false,
                searchable: false
            },
            {
                targets: 1, // Product Name
                type: 'string'
            },
            // Add hidden column for created_at
            {
                targets: 6, // This will be a hidden column
                visible: false,
                data: 'created_at'
            }
        ],
        // Initial sort by created_at in descending order
        order: [[6, 'desc']],
        // Row callback for custom numbering
        rowCallback: function(row, data, index) {
            var api = this.api();
            var page = api.page();
            var pageInfo = api.page.info();
            // Calculate row number based on current page and position
            var rowNum = pageInfo.start + index + 1;
            $(row).find('td:eq(0)').html(rowNum);
        },
        displayStart: 0,
        columnDefs: [
            {
                targets: 0, // # column
                data: null,
                render: function(data, type, row, meta) {
                    // For display, show the row number (1-based index)
                    if (type === 'display') {
                        return meta.row + 1;
                    }
                    // For sorting, use the row index
                    return meta.row;
                },
                type: 'num',
                orderSequence: ['asc', 'desc']
            },
            { 
                orderable: false, 
                targets: [5], // Disable sorting for Actions column
                searchable: false
            },
            {
                targets: 1, // Product Name (alphabetical sorting)
                type: 'string'
            },
            {
                targets: 2, // Units (sort by unit type first, then by value)
                type: 'string',
                render: function(data, type, row) {
                    if (type === 'sort') {
                        // Return an array for multi-column sorting [unit_type, numeric_value]
                        const matches = data.match(/^([\d.]+)\s+(.+)$/);
                        if (matches) {
                            const numericValue = parseFloat(matches[1]) || 0;
                            const unitType = matches[2] || '';
                            // Return an array for DataTables to sort by both values
                            return [unitType.toLowerCase(), numericValue];
                        }
                        return ['', 0];
                    }
                    return data;
                }
            },
            {
                targets: 3, // Stock Quantity (numeric sorting)
                type: 'num'
            },
            {
                targets: 4, // Price (numeric sorting, handle currency symbol)
                type: 'num',
                render: function(data, type, row) {
                    if (type === 'sort') {
                        return parseFloat(data.replace(/[^0-9.-]+/g,""));
                    }
                    return data;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search products...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 10,  
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]]
    });

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
        const productQty = $(this).data('quantity');
        const productPrice = $(this).data('price');
        let unitType = $(this).data('unit-type');
        const unitValue = $(this).data('unit-value');
        const otherUnitType = $(this).data('other-unit-type');
        const piecesPerPack = $(this).data('pieces-per-pack');
        
        // Check if the current unit type is a custom type (exists in other_unit_type)
        const standardUnitTypes = ['g', 'kg', 'L', 'ml', 'pc', 'doz', 'set', 'tray', 'pack'];
        const isCustomType = unitType && !standardUnitTypes.includes(unitType);
        
        // If it's a custom type, set unitType to 'other' and populate the other_unit_type field
        if (isCustomType) {
            $('#editOtherUnitType').val(unitType);
            unitType = 'other';
        }
        
        // Populate the edit form
        $('#editProductId').val(productId);
        $('#editProductName').val(productName);
        $('#editProductQuantity').val(productQty);
        $('#editProductPrice').val(productPrice);
        
        // Handle unit type and value
        $('#editUnitType').val(unitType);
        $('#editUnitValue').val(unitValue);
        
        // Handle other unit type if applicable
        if (unitType === 'other') {
            $('#editOtherUnitTypeContainer').removeClass('d-none');
            // If we have a custom type from the database, use it
            if (otherUnitType) {
                $('#editOtherUnitType').val(otherUnitType);
            }
        } else {
            $('#editOtherUnitTypeContainer').addClass('d-none');
        }
        
        // Handle pieces per pack if applicable
        if (unitType === 'pack' && piecesPerPack) {
            $('#editPiecesPerPack').val(piecesPerPack);
        }
        
        // Update unit display
        updateEditUnitDisplay();
        
        // Show the modal
        $('#editProductModal').modal('show');
    });
    
    // Function to update unit display in edit modal
    function updateEditUnitDisplay() {
        const unitType = $('#editUnitType').val();
        let unitDisplay = '';
        
        // Show/hide pieces per pack field in edit modal
        if (unitType === 'pack') {
            $('#editPiecesPerPackContainer').removeClass('d-none');
            $('#editPiecesPerPack').prop('required', true);
        } else {
            $('#editPiecesPerPackContainer').addClass('d-none');
            $('#editPiecesPerPack').prop('required', false);
        }
        
        switch(unitType) {
            case 'g': unitDisplay = 'g'; break;
            case 'kg': unitDisplay = 'kg'; break;
            case 'L': unitDisplay = 'L'; break;
            case 'ml': unitDisplay = 'ml'; break;
            case 'pc': unitDisplay = 'pc'; break;
            case 'doz': unitDisplay = 'doz'; break;
            case 'set': unitDisplay = 'set'; break;
            case 'tray': unitDisplay = 'tray'; break;
            case 'pack': unitDisplay = 'pack'; break;
            case 'other': unitDisplay = $('#editOtherUnitType').val() || ''; break;
            default: unitDisplay = 'unit';
        }
        
        $('#editUnitDisplay').text(unitDisplay);
    }
    
    // Update unit display when unit type changes in edit modal
    $('#editUnitType').on('change', function() {
        if ($(this).val() === 'other') {
            $('#editOtherUnitTypeContainer').removeClass('d-none');
        } else {
            $('#editOtherUnitTypeContainer').addClass('d-none');
        }
        updateEditUnitDisplay();
    });
    
    // Update unit display when other unit type changes in edit modal
    $('#editOtherUnitType').on('input', function() {
        if ($('#editUnitType').val() === 'other') {
            updateEditUnitDisplay();
        }
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
            html: '<?php echo addslashes($alert['message']); ?>',
            icon: '<?php echo $alert['icon']; ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4e73df',
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                confirmButton: 'btn btn-primary',
                popup: 'swal2-popup-custom'
            },
            buttonsStyling: false
        });
    });
    </script>
    <style>
    .swal2-popup-custom {
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .swal2-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .swal2-html-container {
        font-size: 1.1rem;
    }
    .swal2-styled.swal2-confirm {
        padding: 0.5rem 2rem;
        font-size: 1rem;
        font-weight: 500;
    }
    <?php
}
?>

</body>
</html>