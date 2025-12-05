<?php
include 'includes/header.php';
include 'includes/db.php';
// Include session manager
include 'includes/session_manager.php';

// Simple auto-save function
function autoSaveOnChange() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
        saveUserSessionData($_SESSION['user_id']);
    }
}

// After quantity updates
if (isset($_POST['update_quantity'])) {
    // ... your existing logic ...
    autoSaveOnChange();
}

// After removing items
if (isset($_GET['remove_from_cart'])) {
    // ... your existing logic ...
    autoSaveOnChange();
}

// After clearing cart
if (isset($_GET['clear_cart'])) {
    // ... your existing logic ...
    autoSaveOnChange();
}

// TEMPORARY DEBUG - REMOVE AFTER FIXING
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "<!-- DEBUG CART CONTENTS: ";
    foreach ($_SESSION['cart'] as $id => $item) {
        echo "Item $id: {$item['name']} - Price: {$item['price']} - Qty: {$item['qty']} | ";
    }
    echo " -->";
}

// ---------------- HANDLE QUANTITY UPDATES ----------------
if (isset($_POST['update_quantity'])) {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);
    
    if (isset($_SESSION['cart'][$id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['qty'] = $quantity;
        }
        $_SESSION['cart_success'] = "Cart updated successfully!";
    }
    
    header("Location: cart.php");
    exit;
}

// ---------------- HANDLE REMOVE ITEM ----------------
if (isset($_GET['remove_from_cart'])) {
    $id = intval($_GET['remove_from_cart']);
    
    if (isset($_SESSION['cart'][$id])) {
        $product_name = $_SESSION['cart'][$id]['name'];
        unset($_SESSION['cart'][$id]);
        $_SESSION['cart_success'] = $product_name . " removed from cart!";
    }
    
    header("Location: cart.php");
    exit;
}

// ---------------- HANDLE CLEAR CART ----------------
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_success'] = "Cart cleared successfully!";
    header("Location: cart.php");
    exit;
}

// ---------------- CALCULATE TOTALS ----------------
$subtotal = 0;
$shipping = 0;
$tax_rate = 0.08; // 8% tax
$tax = 0;
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    
    // Calculate shipping (free over $50, otherwise $5.99)
    $shipping = ($subtotal > 50 || $subtotal == 0) ? 0 : 5.99;
    
    // Calculate tax
    $tax = $subtotal * $tax_rate;
    
    // Calculate total
    $total = $subtotal + $shipping + $tax;
}
?>

<style>
/* === CART STYLES === */
.cart-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 80px 0 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cart-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    animation: float 20s infinite linear;
}

.cart-title {
    font-size: 3rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    color: #d4af37;
    position: relative;
    display: inline-block;
}

.cart-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: var(--primary-gold);
}

.cart-subtitle {
    font-size: 1.1rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

.cart-section {
    background: var(--soft-cream);
    padding: 60px 0;
    min-height: 60vh;
}

.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--warm-gray);
    grid-column: 1 / -1;
}

.cart-count {
    font-size: 1.2rem;
    color: var(--dark-charcoal);
    font-weight: 500;
    letter-spacing: 1px;
}

.cart-actions {
    display: flex;
    gap: 15px;
}

.continue-shopping {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: var(--transition);
    font-weight: 500;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.continue-shopping:hover {
    background: var(--dark-charcoal);
    transform: translateY(-2px);
    color: white;
}

.clear-cart {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 1px;
    text-decoration: none;
    display: inline-block;
}

.clear-cart:hover {
    background: var(--dark-charcoal);
    color: #d4af37;
    border-color: var(--dark-charcoal);
}

/* Cart Items */
.cart-items {
    background: white;
    border-radius: 16px;
    padding: 30px;
    border: 1px solid var(--warm-gray);
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 20px;
    align-items: center;
    padding: 25px 0;
    border-bottom: 1px solid var(--warm-gray);
    position: relative;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    background: var(--soft-cream);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.cart-item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.cart-item-info {
    padding-right: 20px;
}

.cart-item-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 8px;
    line-height: 1.4;
}

.cart-item-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-gold);
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-input {
    width: 60px;
    padding: 8px 12px;
    border: 1px solid var(--warm-gray);
    border-radius: 6px;
    text-align: center;
    font-size: 0.9rem;
}

.update-btn {
    background: var(--primary-gold);
    color: #d4af37;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.8rem;
    font-weight: 500;
}

.update-btn:hover {
    background: var(--dark-charcoal);
}

.cart-item-total {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark-charcoal);
    min-width: 100px;
    text-align: right;
}

.remove-item {
    position: absolute;
    top: 25px;
    right: 0;
    background: transparent;
    border: none;
    color: #999;
    cursor: pointer;
    transition: var(--transition);
    padding: 5px;
}

.remove-item:hover {
    color: #dc3545;
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

.order-summary-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 25px;
    text-align: center;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--warm-gray);
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

.shipping-note {
    font-size: 0.8rem;
    color: #999;
    margin-top: 5px;
    text-align: right;
}

.checkout-btn {
    background: var(--primary-gold);
    color: #d4af37;
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

.checkout-btn:hover {
    background: var(--dark-charcoal);
    transform: translateY(-2px);
}

.checkout-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

/* Empty Cart */
.cart-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--dark-charcoal);
    grid-column: 1 / -1;
}

.cart-empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
    color: var(--primary-gold);
}

.cart-empty h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 300;
    letter-spacing: 1px;
}

.cart-empty p {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 968px) {
    .cart-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .order-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-title {
        font-size: 2.5rem;
    }
    
    .cart-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .cart-item-quantity,
    .cart-item-total {
        grid-column: 1 / -1;
        justify-self: start;
        margin-top: 10px;
    }
    
    .remove-item {
        top: 15px;
        right: 0;
    }
}

@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    100% { transform: translateY(-100px) rotate(360deg); }
}
</style>

<!-- HERO SECTION -->
<section class="cart-hero">
    <div class="hero-content">
        <h1 class="cart-title">SHOPPING CART</h1>
        <p class="cart-subtitle">REVIEW YOUR LUXURY SELECTIONS</p>
    </div>
</section>

<!-- CART SECTION -->
<section class="cart-section">
    <div class="cart-container">
        <div class="cart-header">
            <div class="cart-count">
                <?php 
                $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                echo $cart_count . ' Item' . ($cart_count != 1 ? 's' : '') . ' in Cart';
                ?>
            </div>
            <div class="cart-actions">
                <?php if($cart_count > 0): ?>
                <a href="cart.php?clear_cart=1" class="clear-cart" onclick="return confirm('Are you sure you want to clear your entire cart?')">
                    Clear Cart
                </a>
                <?php endif; ?>
                <a href="products.php" class="continue-shopping">
                    Continue Shopping
                </a>
            </div>
        </div>

        <?php if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach($_SESSION['cart'] as $id => $item): 
                    $item_total = $item['price'] * $item['qty'];
                ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="<?= $item['img'] ?>" alt="<?= $item['name'] ?>">
                    </div>
                    <div class="cart-item-info">
                        <h3 class="cart-item-name"><?= $item['name'] ?></h3>
                        <p class="cart-item-price">$<?= number_format($item['price'], 2) ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <form method="POST" action="cart.php" class="quantity-form">
                            <input type="hidden" name="update_quantity" value="1">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="number" name="quantity" value="<?= $item['qty'] ?>" min="0" max="99" class="quantity-input">
                            <button type="submit" class="update-btn">Update</button>
                        </form>
                    </div>
                    <div class="cart-item-total">
                        $<?= number_format($item_total, 2) ?>
                    </div>
                    <a href="cart.php?remove_from_cart=<?= $id ?>" class="remove-item" title="Remove Item">
                        ×
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="order-summary-title">Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">$<?= number_format($subtotal, 2) ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">
                        $<?= number_format($shipping, 2) ?>
                        <?php if($shipping == 0 && $subtotal > 0): ?>
                            <div class="shipping-note">Free Shipping!</div>
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
                <div class="shipping-note" style="text-align: center; margin-top: 10px;">
                    Add $<?= number_format(50 - $subtotal, 2) ?> more for free shipping!
                </div>
                <?php endif; ?>

                <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                    Proceed to Checkout
                </button>
            </div>

        <?php else: ?>
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <h3>Your Cart is Empty</h3>
                <p>Discover our luxurious beauty collection and add your favorite products to the cart.</p>
                <a href="products.php" class="continue-shopping">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- TOAST NOTIFICATIONS -->
<div class="toast-container">
    <?php if(isset($_SESSION['cart_success'])): ?>
        <div class="toast" id="successToast">
            <?= $_SESSION['cart_success'] ?>
        </div>
        <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>
</div>

<script>
// Auto-hide toast notifications
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    });
});

// Quantity input validation
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value < 0) this.value = 0;
        if (this.value > 99) this.value = 99;
    });
});
</script>

<?php include 'includes/footer.php'; ?>