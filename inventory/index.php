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
                    <table id="inventoryTable" class="table table-hover" style="width:100%">
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
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                                $stockQuantity = $row['stock_quantity'] ?? $row['quantity'];
                                $lowStockThreshold = $row['low_stock_threshold'] ?? null;
                                $isLowStock = $lowStockThreshold !== null && $stockQuantity > 0 && $stockQuantity <= $lowStockThreshold;
                                $isOutOfStock = $stockQuantity <= 0;
                                $rowClass = $isOutOfStock ? 'table-danger' : ($isLowStock ? 'table-warning' : '');
                            ?>
                            <tr class="<?php echo $rowClass; ?>" data-low-stock-threshold="<?php echo $lowStockThreshold; ?>">
                                <td></td> <!-- Will be populated by DataTables -->
                                <td>
                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                    <?php if ($isOutOfStock): ?>
                                    <span class="badge bg-danger text-white ms-2" data-bs-toggle="tooltip" title="Out of stock!">
                                        <i class="fas fa-times-circle me-1"></i> Out of Stock
                                    </span>
                                    <?php elseif ($isLowStock): ?>
                                    <span class="badge bg-warning text-dark ms-2" data-bs-toggle="tooltip" title="Low stock! Current quantity is below the threshold of <?php echo $lowStockThreshold; ?>">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Low Stock
                                    </span>
                                    <?php endif; ?>
                                </td>
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
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary btn-sm edit-product"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                                data-quantity="<?php echo $row['stock_quantity'] ?? $row['quantity']; ?>"
                                                data-unit-type="<?php echo htmlspecialchars($row['unit_type'] ?? ''); ?>"
                                                data-unit-value="<?php echo $row['unit_value'] ?? '1'; ?>"
                                                data-other-unit-type="<?php echo htmlspecialchars($row['other_unit_type'] ?? ''); ?>"
                                                data-pieces-per-pack="<?php echo $row['pieces_per_pack'] ?? ''; ?>"
                                                data-price="<?php echo $row['price_per_unit'] ?? $row['price']; ?>"
                                                data-low-stock-threshold="<?php echo $lowStockThreshold; ?>"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Edit product"
                                                aria-label="Edit product">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-sm-inline ms-1">Edit</span>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm delete-product" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['product_name']); ?>"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Delete product"
                                                aria-label="Delete product">
                                            <i class="fas fa-trash"></i>
                                            <span class="d-none d-sm-inline ms-1">Delete</span>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-plus-circle me-2"></i>
                    Add New Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/abico/includes/inventory/add_product.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Product Name</label>
                        <input type="text" class="form-control" name="product_name" placeholder="Enter product name" required>
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
                            <div class="col-md-4">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="quantity" placeholder="Enter quantity" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control" name="low_stock_threshold" placeholder="e.g., 10" min="0">
                                <small class="text-muted">Get notified when stock is low</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="price" placeholder="Enter price" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-edit me-2"></i>
                    Edit Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <div class="col-md-4">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" id="editProductQuantity" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control" name="low_stock_threshold" id="editLowStockThreshold" min="0">
                                <small class="text-muted">Get notified when stock is low</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="price_per_unit" id="editProductPrice" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="updateProductBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
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
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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
        const lowStockThreshold = $(this).data('low-stock-threshold');
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
        $('#editLowStockThreshold').val(lowStockThreshold || '');
        
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
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...');
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Product updated successfully',
                        timer: 1500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update product',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the product';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                });
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    // Delete product functionality
    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault();
        
        const $deleteBtn = $(this);
        const productId = $deleteBtn.data('id');
        const productName = $deleteBtn.data('name');
        
        Swal.fire({
            title: 'Delete Product',
            html: `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 4rem;"></i>
                    <h4>Are you sure?</h4>
                    <p>You are about to delete: <strong>${productName}</strong></p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, delete it!',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true,
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '/abico/includes/inventory/delete_product.php',
                    type: 'POST',
                    data: { id: productId },
                    dataType: 'json'
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value && result.value.success) {
                    const response = result.value;
                    // Show success message
                    Swal.fire({
                        title: 'Deleted!',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                <h4>Success!</h4>
                                <p>${response.message || 'The product has been deleted.'}</p>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                } else {
                    let errorMessage = 'An error occurred while deleting the product.';
                    if (result.value && result.value.message) {
                        errorMessage = result.value.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                    });
                }
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: 'Cancelled',
                    text: 'Your product is safe!',
                    icon: 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    timer: 1500
                });
            } else if (result.isDismissed) {
                // Handle other dismissals if needed
                console.log('Delete action was dismissed');
            }
        });
    });
});
</script>

</body>
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