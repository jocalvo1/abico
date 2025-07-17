<?php
// Start the session
session_start();

// Check if user is logged in, if not redirect to login page
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('location: /abico/login.php');
    exit;
}

// Include database and classes
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/inventory/product.php';
require_once __DIR__ . '/../includes/ledger/customer_list.php';

// Initialize the database connection
$database = new dbconn();
$db = $database->getConnection();

// Create product object
$product = new Product($db);

// Fetch all products
$products = $product->readAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .cart-item {
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">ABICO Store</h1>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Sales</a>
        </div>

        <div class="row">
            <!-- Left Side -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><?= date('F j, Y') ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customerSelect" class="form-label">Select Customer</label>
                            <div class="d-flex align-items-center gap-1">
                                <select class="form-select flex-grow-1" id="customerSelect">
                                    <option value="">Walk-in Customer</option>
                                    <?php 
                                    $customer = new Customer($db);
                                    $stmt = $customer->readAll();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Properly escape the JSON string
                                        $customerData = htmlspecialchars(json_encode([
                                            'id' => $row['id'],
                                            'name' => $row['customer_name']
                                        ]), ENT_QUOTES, 'UTF-8');
                                        echo "<option value='{$customerData}'>{$row['customer_name']} ({$row['contact']})</option>";
                                    }
                                    ?>
                                </select>
                                <button class="btn btn-outline-success btn-sm" type="button" id="newCustomerBtn" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                    <i class="fas fa-plus me-1"></i>New
                                </button>
                            </div>
                            <input type="hidden" id="customerId" value="">
                            <input type="hidden" id="customerName" value="Walk-in Customer">
                        </div>
                        
                        <div class="table-responsive">
                            <table id="productsTable" class="table table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $products->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td title="<?php echo htmlspecialchars($row['product_name']); ?>">
                                            <?php 
                                            $name = htmlspecialchars($row['product_name']);
                                            echo strlen($name) > 30 ? substr($name, 0, 30) . '...' : $name; 
                                            ?>
                                        </td>
                                        <td title="<?php echo htmlspecialchars($row['unit_value'] . ' ' . $row['unit_type']); ?>">
                                            <?php 
                                            $unit = htmlspecialchars($row['unit_value'] . ' ' . $row['unit_type']);
                                            echo strlen($unit) > 10 ? substr($unit, 0, 10) . '...' : $unit; 
                                            ?>
                                        </td>
                                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                        <td style="width: 120px;">
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary quantity-btn decrease">-</button>
                                                <input type="number" 
                                                       class="form-control text-center product-quantity" 
                                                       value="1" 
                                                       min="1" 
                                                       max="<?php echo $row['stock_quantity']; ?>"
                                                       style="width: 50px;">
                                                <button class="btn btn-outline-secondary quantity-btn increase">+</button>
                                            </div>
                                            <small class="text-muted">In stock: <?php echo $row['stock_quantity']; ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary add-to-cart" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                                    data-price="<?php echo $row['price']; ?>"
                                                    data-stock="<?php echo $row['stock_quantity']; ?>">
                                                <i class="fas fa-plus me-1"></i> Add
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-md-4">
                <!-- Order Summary -->
                <div class="card mb-3" id="orderSummary">
                    <div class="card-header">
                        <h4 class="mb-0">Order Summary</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="cart-items" style="max-height: 300px; overflow-y: auto;">
                            <div id="cartItems" class="p-3">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                    <p>Your cart is empty</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong>Total Items:</strong> <span id="totalItems">0</span>
                                </div>
                                <div>
                                    <strong>Total:</strong> <span id="totalAmount">₱0.00</span>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" id="proceedToPayment" class="btn btn-primary" disabled>
                                    <i class="fas fa-credit-card me-1"></i> Proceed to Payment
                                </button>
                                <button type="button" id="resetCartBtn" class="btn btn-outline-danger">
                                    <i class="fas fa-trash-alt me-1"></i> Reset Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section (Hidden by default) -->
                <div class="card" id="paymentSection" style="display: none;">
                    <div class="card-header">
                        <h4 class="mb-0">Payment</h4>
                    </div>
                    <div class="card-body">
                        <!-- Order Summary in Payment Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">Order Summary</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Items:</span>
                                    <span id="paymentTotalItems" class="fw-medium">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total Amount:</h5>
                                    <h4 class="mb-0 text-primary" id="paymentTotalAmount">₱0.00</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="cashPayment" value="Cash" checked>
                                <label class="form-check-label" for="cashPayment">
                                    <i class="fas fa-money-bill-wave me-1"></i> Cash
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="onlinePayment" value="Online">
                                <label class="form-check-label" for="onlinePayment">
                                    <i class="fas fa-globe me-1"></i> Online
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="chequePayment" value="Cheque">
                                <label class="form-check-label" for="chequePayment">
                                    <i class="fas fa-money-check me-1"></i> Cheque
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="amountReceived" class="form-label">Amount Received</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="amountReceived" placeholder="0.00" step="0.01">
                            </div>
                            <div class="d-flex justify-content-between mb-3 fs-5">
                                <span>Change:</span>
                                <span id="changeAmount" class="fw-bold">₱0.00</span>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" id="processPayment">
                                Process Payment
                            </button>
                            <button class="btn btn-outline-secondary" id="backToCart">
                                Back to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customer Modal -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newCustomerForm">
                        <div class="mb-3">
                            <label for="newCustomerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="newCustomerName" placeholder="Enter full name" required>
                        </div>
                        <div class="mb-3">
                            <label for="newCustomerContact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="newCustomerContact" placeholder="e.g., 09123456789">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Confirm Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Order Summary</h6>
                        <div id="confirmationItems" class="mb-3"></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="confirmationSubtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Method:</span>
                            <span id="confirmationPaymentMethod">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Amount Received:</span>
                            <span id="confirmationAmountReceived">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Change:</span>
                            <span id="confirmationChange">₱0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                        <i class="fas fa-check-circle me-1"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Payment Successful</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                    <h4>Payment Processed Successfully!</h4>
                    <p>Your transaction has been completed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
         $(document).ready(function() {
            // Initialize Select2 for customer search
            $('#customerSelect').select2({
                placeholder: 'Search customer...',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            // Handle customer selection
            $('#customerSelect').on('change', function() {
                const selected = $(this).val();
                if (selected) {
                    try {
                        const customer = JSON.parse(selected);
                        $('#customerId').val(customer.id);
                        $('#customerName').val(customer.name);
                        console.log('Customer selected:', customer); // Debug log
                    } catch (e) {
                        console.error('Error parsing customer data:', e);
                        $('#customerId').val('');
                        $('#customerName').val('Walk-in Customer');
                    }
                } else {
                    $('#customerId').val('');
                    $('#customerName').val('Walk-in Customer');
                }
            });

            // Save new customer
            $('#saveCustomerBtn').click(function() {
                const name = $('#newCustomerName').val().trim();
                const contact = $('#newCustomerContact').val().trim();
                
                if (!name) {
                    alert('Please enter customer name');
                    return;
                }

                $.ajax({
                    url: '../includes/ledger/save_customer.php',
                    type: 'POST',
                    data: {
                        customer_name: name,
                        contact: contact
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add new customer to dropdown
                            const newOption = new Option(
                                `${response.customer.customer_name} (${response.customer.contact || 'No contact'})`,
                                JSON.stringify({
                                    id: response.customer.id,
                                    name: response.customer.customer_name
                                }),
                                true,
                                true
                            );
                            
                            $('#customerSelect').append(newOption).trigger('change');
                            $('#newCustomerModal').modal('hide');
                            $('#newCustomerForm')[0].reset();
                        } else {
                            alert(response.message || 'Error saving customer');
                        }
                    },
                    error: function() {
                        alert('Error saving customer');
                    }
                });
            });
            
            // Reset form when modal is closed
            $('#newCustomerModal').on('hidden.bs.modal', function () {
                $('#newCustomerForm')[0].reset();
            });
            let cart = [];
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));

            // Initialize DataTable
            const table = $('#productsTable').DataTable({
                "pageLength": 5,
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": [3, 4] }
                ]
            });

            // Handle quantity buttons
            $(document).on('click', '.quantity-btn', function() {
                const input = $(this).siblings('input[type="number"]');
                let value = parseInt(input.val());
                
                if ($(this).hasClass('increase')) {
                    input.val(value + 1);
                } else if ($(this).hasClass('decrease') && value > 1) {
                    input.val(value - 1);
                }
            });

            // Add to cart
            $(document).on('click', '.add-to-cart', function() {
                const button = $(this);
                const name = button.data('name');
                const price = parseFloat(button.data('price'));
                const maxStock = parseInt(button.data('stock_quantity'));
                const quantity = parseInt(button.closest('tr').find('.product-quantity').val());
                
                // Check if product is out of stock
                if (maxStock === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Out of Stock',
                        text: 'This product is currently out of stock.',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }
                
                // Validate quantity
                if (quantity < 1 || quantity > maxStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Quantity',
                        text: `Please enter a quantity between 1 and ${maxStock}.`,
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }
                
                // Check if item already in cart
                const existingItem = cart.find(item => item.name === name);
                
                if (existingItem) {
                    const newQuantity = existingItem.quantity + quantity;
                    if (newQuantity > maxStock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Insufficient Stock',
                            text: `Cannot add more than available stock (${maxStock})`,
                            confirmButtonColor: '#0d6efd'
                        });
                        return;
                    }
                    existingItem.quantity = newQuantity;
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    cart.push({
                        id: button.data('id'),
                        name: name,
                        price: price,
                        quantity: quantity,
                        total: price * quantity,
                        stock_quantity: maxStock
                    });
                }
                
                updateCart();
            });

            // Update cart display
            function updateCart() {
                const cartItems = $('#cartItems');
                const totalItems = cart.reduce((sum, item) => sum + parseInt(item.quantity), 0);
                const totalAmount = cart.reduce((sum, item) => sum + parseFloat(item.total), 0);
                
                // Update payment section summary
                $('#paymentTotalItems').text(totalItems);
                $('#paymentTotalAmount').text('₱' + totalAmount.toFixed(2));
                
                if (cart.length === 0) {
                    cartItems.html(`
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                            <p>Your cart is empty</p>
                        </div>
                    `);
                    $('#proceedToPayment').prop('disabled', true);
                } else {
                    let html = '';
                    
                    cart.forEach((item, index) => {
                        html += `
                            <div class="cart-item border-bottom py-2" data-index="${index}">
                                <div class="d-flex justify-content-between">
                                    <div class="me-3">
                                        <h6 class="mb-1">${item.name}</h6>
                                        <small class="text-muted">₱${item.price.toFixed(2)} × ${item.quantity}</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3 fw-bold">₱${item.total.toFixed(2)}</span>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary cart-decrease">-</button>
                                            <button class="btn btn-outline-secondary cart-increase">+</button>
                                            <button class="btn btn-outline-danger cart-remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    cartItems.html(html);
                    $('#proceedToPayment').prop('disabled', false);
                }
                
                updateTotals();
            }

            // Update totals
            function updateTotals() {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                const totalAmount = cart.reduce((sum, item) => sum + item.total, 0);
                
                $('#totalItems').text(totalItems);
                $('#totalAmount').text(`₱${totalAmount.toFixed(2)}`);
            }

            // Cart item actions
            $(document).on('click', '.cart-decrease', function() {
                const index = $(this).closest('.cart-item').data('index');
                if (cart[index].quantity > 1) {
                    cart[index].quantity--;
                    cart[index].total = cart[index].quantity * cart[index].price;
                    updateCart();
                }
            });

            $(document).on('click', '.cart-increase', function() {
                const index = $(this).closest('.cart-item').data('index');
                cart[index].quantity++;
                cart[index].total = cart[index].quantity * cart[index].price;
                updateCart();
            });

            $(document).on('click', '.cart-remove', function() {
                const index = $(this).closest('.cart-item').data('index');
                cart.splice(index, 1);
                updateCart();
            });

            // Proceed to payment
            $('#proceedToPayment').click(function() {
                $('#orderSummary').hide();
                $('#paymentSection').show();
                // Update the total in payment section
                $('#totalInPayment').text($('#totalAmount').text());
                $('#amountReceived').val('').focus();
                calculateChange();
            });

            // Back to cart
            $('#backToCart').click(function() {
                $('#paymentSection').hide();
                $('#orderSummary').show();
            });

            // Calculate change when amount received changes
            function calculateChange() {
                const amountReceived = parseFloat($('#amountReceived').val()) || 0;
                const totalAmount = cart.reduce((sum, item) => sum + parseFloat(item.total), 0);
                const change = amountReceived - totalAmount;
                const changeAmount = Math.max(0, change);
                
                $('#changeAmount').text('₱' + changeAmount.toFixed(2));
                
                // Store the change amount in a data attribute
                $('#amountReceived').data('change-amount', changeAmount);
                
                // Enable/disable process payment button
                $('#processPayment').prop('disabled', change < 0);
                
                return changeAmount;
            }

            // Amount received input
            $('#amountReceived').on('input', calculateChange);

            // Reset cart function
            function resetCart() {
                if (cart.length === 0) return;
                
                Swal.fire({
                    title: 'Clear Cart',
                    text: 'Are you sure you want to clear all items from your cart?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, clear cart',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        cart = [];
                        updateCart();
                        // Reset payment section if visible
                        if ($('#paymentSection').is(':visible')) {
                            $('#paymentSection').hide();
                            $('#orderSummary').show();
                        }
                        // Show success message
                        Swal.fire({
                            title: 'Cart Cleared',
                            text: 'Your cart has been cleared.',
                            icon: 'success',
                            confirmButtonColor: '#0d6efd',
                            timer: 1500,
                            timerProgressBar: true
                        });
                    }
                });
            }

            // Reset cart button click handler
            $(document).on('click', '#resetCartBtn', resetCart);

            // Process payment function
            function processPayment(paymentRequest) {
                const { paymentData, $btn } = paymentRequest;
                
                // Disable button to prevent double submission
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

                // Send data to server
                $.ajax({
                    url: 'process_sale.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(paymentData),
                    success: function(response) {
                        if (response.success) {
                            // Show success message with transaction details
                            const transaction = response.transaction;
                            let itemsList = '';
                            
                            if (transaction.items_list) {
                                itemsList = `<div class="mt-3"><strong>Items:</strong> ${transaction.items_list}</div>`;
                            }
                            
                            $('#successMessage').html(`
                                <h5>Transaction Completed Successfully!</h5>
                                <div class="mt-3">
                                    <p><strong>Transaction ID:</strong> ${transaction.id}</p>
                                    <p><strong>Customer:</strong> ${transaction.customer_name}</p>
                                    <p><strong>Total Amount:</strong> ₱${parseFloat(transaction.total_amount).toFixed(2)}</p>
                                    <p><strong>Payment Method:</strong> ${transaction.payment_method}</p>
                                    <p><strong>Amount Received:</strong> ₱${parseFloat(transaction.amount_received).toFixed(2)}</p>
                                    <p><strong>Change:</strong> ₱${parseFloat(transaction.change_amount).toFixed(2)}</p>
                                    ${itemsList}
                                </div>
                                <div class="mt-3 text-center">
                                    <button type="button" class="btn btn-primary me-2" onclick="window.print()">
                                        <i class="fas fa-print me-1"></i> Print Receipt
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            `);
                            
                            successModal.show();
                            
                            // Reset form and return to cart view
                            cart = [];
                            updateCart();
                            $('input[name="paymentMethod"]').prop('checked', false);
                            $('#amountReceived').val('');
                            $('#changeAmount').text('₱0.00');
                            $('#customerSelect').val('').trigger('change');
                            
                            // Return to cart view
                            $('#paymentSection').hide();
                            $('#orderSummary').show();
                            
                            // Reset payment method to default (Cash)
                            $('#cashPayment').prop('checked', true);
                            
                            // Enable the proceed to payment button
                            $('#proceedToPayment').prop('disabled', true);
                            
                            // Refresh the page after 3 seconds to reset the form
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        } else {
                            alert('Error: ' + (response.message || 'Unknown error occurred'));
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = 'Error processing payment';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                            console.error('Server error:', response);
                        } catch (e) {
                            console.error('Error parsing error response:', e);
                        }
                        alert(errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Process Payment');
                    }
                });
            };
            
            // Process payment button click handler
            $('#processPayment').click(function() {
                // Get customer data
                const customerId = $('#customerId').val();
                let customerName = $('#customerName').val();
                
                // If no customer is selected, use 'Walk-in Customer' as default
                if (!customerId && !customerName) {
                    customerName = 'Walk-in Customer';
                }
                
                // Cache DOM elements
                const $amountReceived = $('#amountReceived');
                const $paymentMethod = $('input[name="paymentMethod"]:checked');
                
                // Calculate totals
                const totalAmount = parseFloat(cart.reduce((sum, item) => sum + parseFloat(item.total), 0).toFixed(2));
                const amountReceived = parseFloat($amountReceived.val()) || 0;
                const changeAmount = calculateChange(); // This ensures we have the latest calculated change
                const paymentMethod = $paymentMethod.val();
                
                // Validate before proceeding
                if (!cart.length) {
                    return alert('Please add items to the cart');
                }

                if (!paymentMethod) {
                    return alert('Please select a payment method');
                }

                if (amountReceived < totalAmount) {
                    return alert('Amount received is less than the total amount');
                }

                // Prepare data for the server
                const paymentData = {
                    customer_id: customerId || null,
                    customer_name: customerName,
                    items: cart.map(({ id, name, price, quantity, total }) => ({
                        product_id: id,
                        product_name: name,
                        price: parseFloat(price),
                        quantity: parseInt(quantity, 10),
                        subtotal: parseFloat(total)
                    })),
                    payment_method: paymentMethod,
                    amount_received: amountReceived,
                    change_amount: changeAmount,
                    total_amount: totalAmount
                };
                
                // Show confirmation modal
                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                
                // Update confirmation modal content
                $('#confirmationItems').html(cart.map(item => `
                    <div class="d-flex justify-content-between mb-2">
                        <span>${item.quantity}x ${item.name}</span>
                        <span>₱${parseFloat(item.total).toFixed(2)}</span>
                    </div>
                `).join(''));
                
                $('#confirmationSubtotal').text(`₱${totalAmount.toFixed(2)}`);
                $('#confirmationPaymentMethod').text(paymentMethod);
                $('#confirmationAmountReceived').text(`₱${amountReceived.toFixed(2)}`);
                $('#confirmationChange').text(`₱${changeAmount.toFixed(2)}`);
                
                // Reset confirm button state
                const $confirmBtn = $('#confirmPaymentBtn');
                $confirmBtn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Confirm Payment');
                
                // Show the modal
                confirmationModal.show();
                
                // Handle confirm button click
                $confirmBtn.off('click').on('click', function() {
                    // Disable confirm button and show processing state
                    $confirmBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                    
                    // Hide the modal
                    confirmationModal.hide();
                    
                    // Process the payment
                    processPayment({
                        paymentData: paymentData,
                        $btn: $('#processPayment')
                    });
                });
            });

            // Initialize cart
            updateCart();
        });
    </script>
    
</body>
</html>