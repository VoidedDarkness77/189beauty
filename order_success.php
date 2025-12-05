<?php
include 'includes/header.php';
include 'includes/session_manager.php';

if (!isset($_SESSION['order_success'])) {
    header("Location: cart.php");
    exit;
}

$order_number = $_SESSION['order_success'];
$transaction_id = $_SESSION['transaction_id'] ?? '';
unset($_SESSION['order_success'], $_SESSION['transaction_id']);
?>

<style>
.success-hero {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    padding: 100px 0 60px;
    text-align: center;
    color: white;
}

.success-title {
    font-size: 3.5rem;
    font-weight: 300;
    margin-bottom: 1rem;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 30px;
}

.success-section {
    background: var(--soft-cream);
    padding: 60px 0;
    min-height: 60vh;
}

.success-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.success-card {
    background: white;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    border: 1px solid var(--warm-gray);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.order-number {
    font-size: 1.8rem;
    color: var(--primary-gold);
    margin: 20px 0;
    font-weight: 700;
}

.transaction-id {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 30px;
}

.success-message {
    color: var(--dark-charcoal);
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 40px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.btn-primary {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
}

.btn-primary:hover {
    background: var(--dark-charcoal);
    color: white;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--dark-charcoal);
    color: white;
    border: 1px solid var(--dark-charcoal);
}

.btn-secondary:hover {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    transform: translateY(-2px);
}
</style>

<!-- SUCCESS HERO -->
<section class="success-hero">
    <div class="hero-content">
        <div class="success-icon">✓</div>
        <h1 class="success-title">ORDER CONFIRMED!</h1>
        <p>Thank you for your purchase</p>
    </div>
</section>

<!-- SUCCESS SECTION -->
<section class="success-section">
    <div class="success-container">
        <div class="success-card">
            <h2>Your order has been placed successfully</h2>
            
            <div class="order-number">
                Order #: <?= htmlspecialchars($order_number) ?>
            </div>
            
            <?php if($transaction_id): ?>
            <div class="transaction-id">
                Transaction ID: <?= htmlspecialchars($transaction_id) ?>
            </div>
            <?php endif; ?>
            
            <div class="success-message">
                <p>A confirmation email has been sent to your email address.</p>
                <p>Your order will be processed and shipped within 1-2 business days.</p>
                <p>You can track your order status in your account dashboard.</p>
            </div>
            
            <div class="action-buttons">
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                <a href="index.php" class="btn btn-secondary">Return to Home</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>