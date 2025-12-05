<?php
// Remove the vendor autoload line since we're not using external libraries
// require_once 'vendor/autoload.php';
include 'includes/header.php';
include 'includes/db.php'; // This should create $pdo variable
include 'includes/session_manager.php';

// Include the simple payment processor
require_once 'includes/payment_processor_simple.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Calculate totals (same as cart.php)
$subtotal = 0;
$shipping = 0;
$tax_rate = 0.08;
$tax = 0;
$total = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$shipping = ($subtotal > 50) ? 0 : 5.99;
$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create payment processor instance
    $payment_processor = new SimplePaymentProcessor();
    
    // Process checkout and payment
    $shipping_data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip_code' => $_POST['zip_code'],
        'country' => $_POST['country']
    ];
    
    $payment_data = [
        'method' => $_POST['payment_method'],
        'card_number' => $_POST['card_number'] ?? '',
        'expiry_date' => $_POST['expiry_date'] ?? '',
        'cvv' => $_POST['cvv'] ?? '',
        'name_on_card' => $_POST['name_on_card'] ?? '',
        'paypal_email' => $_POST['paypal_email'] ?? ''
    ];
    
    // Validate and process payment
    $payment_result = null;
    
    switch ($payment_data['method']) {
        case 'credit_card':
            $payment_result = $payment_processor->processCreditCardPayment($payment_data, $total);
            break;
            
        case 'paypal':
            $payment_result = $payment_processor->processPayPalPayment($payment_data['paypal_email'], $total);
            break;
            
        case 'apple_pay':
            $payment_result = $payment_processor->processApplePayPayment($total);
            break;
            
        default:
            $error = "Invalid payment method selected";
            break;
    }
    
    // If payment was successful
    if ($payment_result && $payment_result['success']) {
        // Save order to database
        $order_id = saveOrderToDatabase($pdo, $_SESSION['user_id'] ?? null, $shipping_data, $payment_data, $payment_result, $total);
        
        // Clear cart and redirect to success page
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = $order_id;
        $_SESSION['transaction_id'] = $payment_result['transaction_id'];
        header("Location: order_success.php");
        exit;
    } elseif (isset($payment_result)) {
        $error = "Payment failed: " . ($payment_result['error'] ?? 'Please try again.');
    } else {
        $error = "Payment processing failed. Please try again.";
    }
}

function saveOrderToDatabase($pdo, $user_id, $shipping_data, $payment_data, $payment_result, $total_amount) {
    // Generate order number
    $order_number = 'ORD-' . strtoupper(uniqid());
    
    // If no database connection, just return the order number
    if (!$pdo) {
        return $order_number;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if orders table exists
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE 'orders'");
            $stmt->execute();
            $table_exists = $stmt->rowCount() > 0;
            
            if (!$table_exists) {
                // Table doesn't exist, just return order number
                $pdo->commit();
                return $order_number;
            }
        } catch (Exception $e) {
            // Error checking table, just return order number
            return $order_number;
        }
        
        // Insert order if table exists
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, payment_method, transaction_id, created_at)
            VALUES (?, ?, ?, 'completed', ?, ?, ?, NOW())
        ");
        
        $shipping_json = json_encode($shipping_data);
        $stmt->execute([
            $order_number,
            $user_id,
            $total_amount,
            $shipping_json,
            $payment_data['method'],
            $payment_result['transaction_id']
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items if table exists
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE 'order_items'");
            $stmt->execute();
            $items_table_exists = $stmt->rowCount() > 0;
            
            if ($items_table_exists) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                // Use the global $_SESSION['cart'] since it's available in this scope
                global $_SESSION;
                
                foreach ($_SESSION['cart'] as $id => $item) {
                    $item_total = $item['price'] * $item['qty'];
                    $stmt->execute([
                        $order_id,
                        $id,
                        $item['name'],
                        $item['qty'],
                        $item['price'],
                        $item_total
                    ]);
                }
            }
        } catch (Exception $e) {
            // Error with order_items table, continue anyway
            error_log("Order items error: " . $e->getMessage());
        }
        
        $pdo->commit();
        return $order_number;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (Exception $rollback_ex) {
                // Ignore rollback errors
            }
        }
        // Log error but still return order number for user experience
        error_log("Order save error: " . $e->getMessage());
        return $order_number;
    }
}
?>

<style>
/* === CHECKOUT STYLES === */
.checkout-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 80px 0 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.checkout-title {
    font-size: 3rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    color: #d4af37;
    position: relative;
    display: inline-block;
}

.checkout-subtitle {
    font-size: 1.1rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

.checkout-section {
    background: var(--soft-cream);
    padding: 60px 0;
    min-height: 80vh;
}

.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.checkout-form {
    background: white;
    border-radius: 16px;
    padding: 40px;
    border: 1px solid var(--warm-gray);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--warm-gray);
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark-charcoal);
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--warm-gray);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 25px;
}

.payment-method {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid var(--warm-gray);
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
}

.payment-method:hover {
    border-color: var(--primary-gold);
}

.payment-method.selected {
    border-color: var(--primary-gold);
    background: rgba(212, 175, 55, 0.05);
}

.payment-method input[type="radio"] {
    margin-right: 15px;
}

.payment-method-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-icon {
    font-size: 1.5rem;
    width: 40px;
    text-align: center;
}

.payment-details {
    margin-top: 15px;
    padding: 20px;
    background: var(--soft-cream);
    border-radius: 8px;
    display: none;
}

.payment-details.active {
    display: block;
}

.card-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Order Summary */
.order-summary {
    background: white;
    border-radius: 16px;
    padding: 30px;
    border: 1px solid var(--warm-gray);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.order-items {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--warm-gray);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-info {
    flex: 1;
}

.order-item-name {
    font-weight: 500;
    color: var(--dark-charcoal);
    margin-bottom: 5px;
}

.order-item-details {
    font-size: 0.9rem;
    color: #666;
}

.order-item-total {
    font-weight: 600;
    color: var(--dark-charcoal);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--warm-gray);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-label {
    color: #666;
    font-weight: 500;
}

.summary-value {
    font-weight: 600;
    color: var(--dark-charcoal);
}

.summary-total {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-gold);
}

.place-order-btn {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 1px;
    width: 100%;
    margin-top: 20px;
}

.place-order-btn:hover {
    background: var(--dark-charcoal);
    color: white;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 968px) {
    .checkout-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .order-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .checkout-title {
        font-size: 2.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .card-row {
        grid-template-columns: 1fr;
    }
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #fcc;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
}
</style>

<!-- HERO SECTION -->
<section class="checkout-hero">
    <div class="hero-content">
        <h1 class="checkout-title">CHECKOUT</h1>
        <p class="checkout-subtitle">COMPLETE YOUR PURCHASE</p>
    </div>
</section>

<!-- CHECKOUT SECTION -->
<section class="checkout-section">
    <div class="checkout-container">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <?php if(isset($_SESSION['cart_success'])): ?>
                <div class="success-message">
                    <?= $_SESSION['cart_success'] ?>
                    <?php unset($_SESSION['cart_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkout-form">
                <!-- Shipping Information -->
                <div class="form-section">
                    <h2 class="section-title">Shipping Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-input" required value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-input" required value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-input" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address *</label>
                            <input type="text" name="address" class="form-input" required value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-input" required value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-input" required value="<?= isset($_POST['state']) ? htmlspecialchars($_POST['state']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ZIP Code *</label>
                            <input type="text" name="zip_code" class="form-input" required value="<?= isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" class="form-input" value="United States" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="form-section">
                    <h2 class="section-title">Payment Method</h2>
                    
                    <div class="payment-methods">
                        <!-- Credit Card -->
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="credit_card" required <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'checked' : '' ?>>
                            <div class="payment-method-content">
                                <div class="payment-icon">💳</div>
                                <div>
                                    <strong>Credit/Debit Card</strong>
                                    <div style="font-size: 0.9rem; color: #666;">Visa, Mastercard, American Express</div>
                                </div>
                            </div>
                        </label>

                        <!-- PayPal -->
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="paypal" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'checked' : '' ?>>
                            <div class="payment-method-content">
                                <div class="payment-icon">🔵</div>
                                <div>
                                    <strong>PayPal</strong>
                                    <div style="font-size: 0.9rem; color: #666;">Secure online payments</div>
                                </div>
                            </div>
                        </label>

                        <!-- Apple Pay -->
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="apple_pay" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'apple_pay') ? 'checked' : '' ?>>
                            <div class="payment-method-content">
                                <div class="payment-icon">🍎</div>
                                <div>
                                    <strong>Apple Pay</strong>
                                    <div style="font-size: 0.9rem; color: #666;">Fast and secure</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Credit Card Details -->
                    <div class="payment-details" id="credit-card-details">
                        <div class="form-group">
                            <label class="form-label">Name on Card *</label>
                            <input type="text" name="name_on_card" class="form-input" value="<?= isset($_POST['name_on_card']) ? htmlspecialchars($_POST['name_on_card']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Card Number *</label>
                            <input type="text" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19" value="<?= isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : '' ?>">
                        </div>
                        
                        <div class="card-row">
                            <div class="form-group">
                                <label class="form-label">Expiry Date *</label>
                                <input type="text" name="expiry_date" class="form-input" placeholder="MM/YY" maxlength="5" value="<?= isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CVV *</label>
                                <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="4" value="<?= isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Details -->
                    <div class="payment-details" id="paypal-details">
                        <div class="form-group">
                            <label class="form-label">PayPal Email *</label>
                            <input type="email" name="paypal_email" class="form-input" value="<?= isset($_POST['paypal_email']) ? htmlspecialchars($_POST['paypal_email']) : '' ?>">
                        </div>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                            Secure payment through PayPal.
                        </p>
                    </div>

                    <!-- Apple Pay Details -->
                    <div class="payment-details" id="apple-pay-details">
                        <p style="color: #666; font-size: 0.9rem;">
                            Fast and secure payment with Apple Pay.
                        </p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2 class="section-title">Order Summary</h2>
            
            <div class="order-items">
                <?php foreach($_SESSION['cart'] as $id => $item): 
                    $item_total = $item['price'] * $item['qty'];
                ?>
                    <div class="order-item">
                        <div class="order-item-info">
                            <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="order-item-details">
                                Qty: <?= $item['qty'] ?> × $<?= number_format($item['price'], 2) ?>
                            </div>
                        </div>
                        <div class="order-item-total">
                            $<?= number_format($item_total, 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">$<?= number_format($subtotal, 2) ?></span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Shipping</span>
                <span class="summary-value">
                    $<?= number_format($shipping, 2) ?>
                    <?php if($shipping == 0 && $subtotal > 0): ?>
                        <div style="font-size: 0.8rem; color: #28a745;">Free Shipping!</div>
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Tax (8%)</span>
                <span class="summary-value">$<?= number_format($tax, 2) ?></span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Total</span>
                <span class="summary-value summary-total">$<?= number_format($total, 2) ?></span>
            </div>

            <?php if($subtotal > 0 && $subtotal < 50): ?>
                <div style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9rem;">
                    Add $<?= number_format(50 - $subtotal, 2) ?> more for free shipping!
                </div>
            <?php endif; ?>

            <button type="submit" form="checkout-form" class="place-order-btn">
                Place Order - $<?= number_format($total, 2) ?>
            </button>
            
            <p style="text-align: center; margin-top: 15px; color: #666; font-size: 0.85rem;">
                <em>Note: This is a demo. No real payments are processed.</em>
            </p>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetails = document.querySelectorAll('.payment-details');
    
    // Function to show/hide payment details
    function updatePaymentDetails() {
        // Hide all payment details
        paymentDetails.forEach(detail => {
            detail.classList.remove('active');
        });
        
        // Find selected payment method
        let selectedMethod = '';
        paymentMethods.forEach(method => {
            if (method.checked) {
                selectedMethod = method.value;
            }
        });
        
        // Show selected payment details
        if (selectedMethod) {
            const detailsElement = document.getElementById(selectedMethod + '-details');
            if (detailsElement) {
                detailsElement.classList.add('active');
            }
        }
        
        // Update payment method labels
        document.querySelectorAll('.payment-method').forEach(label => {
            label.classList.remove('selected');
        });
        
        const selectedLabel = document.querySelector('input[name="payment_method"]:checked');
        if (selectedLabel) {
            selectedLabel.closest('.payment-method').classList.add('selected');
        }
    }
    
    // Show/hide payment details based on selected method
    paymentMethods.forEach(method => {
        method.addEventListener('change', updatePaymentDetails);
    });
    
    // Initialize on page load
    updatePaymentDetails();
    
    // Format card number
    const cardNumberInput = document.querySelector('input[name="card_number"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = value.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];
            
            for (let i = 0, len = match.length; i < len; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                e.target.value = parts.join(' ');
            } else {
                e.target.value = value;
            }
        });
    }
    
    // Format expiry date
    const expiryInput = document.querySelector('input[name="expiry_date"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
        });
    }
    
    // Form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedPayment) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
            
            // Additional validation based on payment method
            if (selectedPayment.value === 'credit_card') {
                const cardNumber = document.querySelector('input[name="card_number"]').value.replace(/\s/g, '');
                const expiry = document.querySelector('input[name="expiry_date"]').value;
                const cvv = document.querySelector('input[name="cvv"]').value;
                const nameOnCard = document.querySelector('input[name="name_on_card"]').value;
                
                if (cardNumber.length < 13) {
                    e.preventDefault();
                    alert('Please enter a valid card number (13-16 digits).');
                    return;
                }
                
                if (!expiry || expiry.length !== 5 || !expiry.includes('/')) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY).');
                    return;
                }
                
                if (!cvv || cvv.length < 3) {
                    e.preventDefault();
                    alert('Please enter a valid CVV (3-4 digits).');
                    return;
                }
                
                if (!nameOnCard || nameOnCard.trim().length < 2) {
                    e.preventDefault();
                    alert('Please enter the name on card.');
                    return;
                }
            }
            
            if (selectedPayment.value === 'paypal') {
                const paypalEmail = document.querySelector('input[name="paypal_email"]').value;
                if (!paypalEmail || !paypalEmail.includes('@')) {
                    e.preventDefault();
                    alert('Please enter a valid PayPal email.');
                    return;
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>