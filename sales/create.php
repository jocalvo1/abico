<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .transaction-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .cart-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            max-height: 400px; /* Adjust this value as needed */
            margin: 10px -15px;
            padding: 0 15px;
        }
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .payment-section {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .btn-checkout {
            width: 100%;
            padding: 10px;
            font-size: 1.1em;
            margin-top: 10px;
        }
        .total-amount {
            font-size: 1.3em;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col-12">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Transactions
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="transaction-container">
                    <h3><i class="fas fa-cash-register me-2"></i>New Sale</h3>
                    <hr>
                    
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Customer's Name</label>
                        <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="productSearch" class="form-label">Search Product</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="productSearch" placeholder="Search product by name or code">
                                <button class="btn btn-outline-secondary" type="button" id="searchProduct">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="productQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="productQuantity" value="1" min="1">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="addToCart">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample product row -->
                                <tr>
                                    <td>Sample Product 1</td>
                                    <td>Sample Product Description</td>
                                    <td>₱100.00</td>
                                    <td><button class="btn btn-sm btn-outline-primary add-product" data-name="Sample Product 1" data-price="100" data-stock="50">Add</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="cart-summary">
                    <h4><i class="fas fa-shopping-cart me-2"></i>Order Summary</h4>
                    <hr>
                    <div class="cart-items-container">
                        <div id="cartItems">
                            <!-- Cart items will be added here dynamically -->
                            <div class="text-muted text-center py-4">
                                <i class="fas fa-shopping-basket fa-3x mb-2"></i>
                                <p>Your cart is empty</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="total-amount text-end">
                            Total: ₱<span id="totalAmount">0.00</span>
                        </div>
                    
                        <div class="d-grid gap-2 pt-3">
                            <button class="btn btn-danger" id="resetCart">
                                <i class="fas fa-trash-alt me-2"></i>Reset
                            </button>
                            <button class="btn btn-success btn-checkout" id="checkoutBtn">
                                <i class="fas fa-credit-card me-2"></i>Checkout
                            </button>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="payment-section" id="paymentSection">
                        <h5><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cheque">Cheque</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cashAmount" class="form-label">Amount Received</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="cashAmount" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount:</span>
                                <span id="paymentTotal">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Change:</span>
                                <span id="changeAmount">₱0.00</span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-secondary" id="backToCart">
                                <i class="fas fa-arrow-left me-2"></i>Back to Cart
                            </button>
                            <button class="btn btn-primary" id="confirmPayment">
                                <i class="fas fa-check-circle me-2"></i>Confirm Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Success!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                    <h4>Transaction Completed</h4>
                    <p>Your sale has been processed successfully!</p>
                    <p>Transaction ID: <strong>#TRX-<span id="transactionId">123456</span></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printReceipt">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cart state
            let cart = [];
            
            // DOM Elements
            const cartItems = document.getElementById('cartItems');
            const totalAmount = document.getElementById('totalAmount');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const resetCartBtn = document.getElementById('resetCart');
            const paymentSection = document.getElementById('paymentSection');
            const backToCartBtn = document.getElementById('backToCart');
            const confirmPaymentBtn = document.getElementById('confirmPayment');
            const cashAmountInput = document.getElementById('cashAmount');
            const paymentTotal = document.getElementById('paymentTotal');
            const changeAmount = document.getElementById('changeAmount');
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            const productQuantity = document.getElementById('productQuantity');
            const productSearch = document.getElementById('productSearch');
            const productsTable = document.querySelector('#productsTable tbody');
            
            // Add product to cart
            function addToCart(productName, price, quantity) {
                // Check if product already in cart
                const existingItem = cart.find(item => item.name === productName);
                
                if (existingItem) {
                    existingItem.quantity += parseInt(quantity);
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    cart.push({
                        name: productName,
                        price: parseFloat(price),
                        quantity: parseInt(quantity),
                        total: parseFloat(price) * parseInt(quantity)
                    });
                }
                
                updateCart();
            }
            
            // Update cart display
            function updateCart() {
                // Clear cart display
                cartItems.innerHTML = '';
                
                if (cart.length === 0) {
                    cartItems.innerHTML = `
                        <div class="text-muted text-center py-4">
                            <i class="fas fa-shopping-basket fa-3x mb-2"></i>
                            <p>Your cart is empty</p>
                        </div>
                    `;
                    totalAmount.textContent = '0.00';
                    checkoutBtn.disabled = true;
                    return;
                }
                
                // Add each item to cart display
                cart.forEach((item, index) => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'cart-item';
                    itemElement.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">${item.name}</h6>
                                <small class="text-muted">₱${item.price.toFixed(2)} x ${item.quantity}</small>
                            </div>
                            <div class="text-end">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-secondary me-2 btn-decrease" data-index="${index}">-</button>
                                    <span class="me-2">${item.quantity}</span>
                                    <button class="btn btn-sm btn-outline-secondary me-2 btn-increase" data-index="${index}">+</button>
                                    <button class="btn btn-sm btn-outline-danger btn-remove" data-index="${index}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="fw-bold mt-1">₱${item.total.toFixed(2)}</div>
                            </div>
                        </div>
                    `;
                    cartItems.appendChild(itemElement);
                });
                
                // Calculate total
                const total = cart.reduce((sum, item) => sum + item.total, 0);
                totalAmount.textContent = total.toFixed(2);
                paymentTotal.textContent = `₱${total.toFixed(2)}`;
                checkoutBtn.disabled = false;
                
                // Add event listeners to quantity buttons
                document.querySelectorAll('.btn-increase').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const index = e.target.getAttribute('data-index');
                        cart[index].quantity++;
                        cart[index].total = cart[index].quantity * cart[index].price;
                        updateCart();
                    });
                });
                
                document.querySelectorAll('.btn-decrease').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const index = e.target.getAttribute('data-index');
                        if (cart[index].quantity > 1) {
                            cart[index].quantity--;
                            cart[index].total = cart[index].quantity * cart[index].price;
                            updateCart();
                        }
                    });
                });
                
                document.querySelectorAll('.btn-remove').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const index = e.target.closest('.btn-remove').getAttribute('data-index');
                        cart.splice(index, 1);
                        updateCart();
                    });
                });
            }
            
            // Reset cart
            function resetCart() {
                cart = [];
                updateCart();
                document.getElementById('customerName').value = '';
                paymentSection.style.display = 'none';
                document.querySelector('.cart-summary h4').textContent = 'Order Summary';
            }
            
            // Calculate change
            function calculateChange() {
                const total = parseFloat(totalAmount.textContent);
                const cashAmount = parseFloat(cashAmountInput.value) || 0;
                const change = cashAmount - total;
                
                changeAmount.textContent = `₱${change >= 0 ? change.toFixed(2) : '0.00'}`;
                
                if (change < 0) {
                    changeAmount.classList.add('text-danger');
                    changeAmount.classList.remove('text-success');
                    confirmPaymentBtn.disabled = true;
                } else {
                    changeAmount.classList.remove('text-danger');
                    changeAmount.classList.add('text-success');
                    confirmPaymentBtn.disabled = false;
                }
            }
            
            // Event Listeners
            document.getElementById('addToCart').addEventListener('click', () => {
                // In a real app, you would search for the product
                const productName = 'Sample Product';
                const price = 100.00;
                const quantity = parseInt(productQuantity.value) || 1;
                
                if (productName && price && quantity > 0) {
                    addToCart(productName, price, quantity);
                    productQuantity.value = 1;
                }
            });
            
            // Add sample product rows
            const sampleProducts = [
                { name: 'Product 1', price: 100.00, stock: 50 },
                { name: 'Product 2', price: 150.00, stock: 30 },
                { name: 'Product 3', price: 200.00, stock: 20 },
                { name: 'Product 4', price: 75.50, stock: 45 },
                { name: 'Product 5', price: 120.00, stock: 15 }
            ];
            
            sampleProducts.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.name}</td>
                    <td>₱${product.price.toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td><button class="btn btn-sm btn-outline-primary add-product" 
                             data-name="${product.name}" 
                             data-price="${product.price}" 
                             data-stock="${product.stock}">Add</button></td>
                `;
                productsTable.appendChild(row);
            });
            
            // Add product from table
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('add-product')) {
                    const button = e.target;
                    const productName = button.getAttribute('data-name');
                    const price = parseFloat(button.getAttribute('data-price'));
                    const stock = parseInt(button.getAttribute('data-stock'));
                    const quantity = parseInt(productQuantity.value) || 1;
                    
                    if (quantity > 0 && quantity <= stock) {
                        addToCart(productName, price, quantity);
                        productQuantity.value = 1;
                    } else {
                        alert(`Invalid quantity. Available stock: ${stock}`);
                    }
                }
            });
            
            // Search products
            productSearch.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = productsTable.getElementsByTagName('tr');
                
                for (let row of rows) {
                    const name = row.cells[0]?.textContent.toLowerCase() || '';
                    if (name.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
            
            // Checkout button
            checkoutBtn.addEventListener('click', () => {
                if (cart.length === 0) return;
                
                document.querySelector('.cart-summary h4').innerHTML = '<i class="fas fa-credit-card me-2"></i>Payment';
                paymentSection.style.display = 'block';
                cashAmountInput.focus();
                
                // Scroll to payment section
                paymentSection.scrollIntoView({ behavior: 'smooth' });
            });
            
            // Back to cart button
            backToCartBtn.addEventListener('click', () => {
                paymentSection.style.display = 'none';
                document.querySelector('.cart-summary h4').textContent = 'Order Summary';
            });
            
            // Reset cart button
            resetCartBtn.addEventListener('click', resetCart);
            
            // Calculate change when cash amount changes
            cashAmountInput.addEventListener('input', calculateChange);
            
            // Confirm payment button
            confirmPaymentBtn.addEventListener('click', () => {
                const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
                const paymentMethod = document.getElementById('paymentMethod').options[document.getElementById('paymentMethod').selectedIndex].text;
                const cashAmount = parseFloat(cashAmountInput.value) || 0;
                const total = parseFloat(totalAmount.textContent);
                const change = cashAmount - total;
                
                if (change < 0) {
                    alert('Insufficient payment amount');
                    return;
                }
                
                // Generate random transaction ID
                const transactionId = 'TRX-' + Math.floor(100000 + Math.random() * 900000);
                document.getElementById('transactionId').textContent = transactionId;
                
                // Show success modal
                successModal.show();
                
                // In a real app, you would send the transaction data to the server here
                console.log('Transaction completed:', {
                    customerName,
                    items: cart,
                    paymentMethod,
                    total,
                    cashAmount,
                    change,
                    transactionId,
                    date: new Date().toISOString()
                });
                
                // Reset the form after a delay
                setTimeout(() => {
                    successModal.hide();
                    resetCart();
                }, 5000);
            });
            
            // Print receipt button
            document.getElementById('printReceipt').addEventListener('click', () => {
                // In a real app, this would open a print dialog with a formatted receipt
                alert('Printing receipt...');
                // window.print(); // Uncomment this in production to actually print
            });
            
            // Initialize cart
            updateCart();
        });
    </script>
</body>
</html>
