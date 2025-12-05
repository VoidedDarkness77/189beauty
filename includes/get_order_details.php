<?php
// includes/get_order_details.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// Check if orders.php exists and include it
if (file_exists('orders.php')) {
    include 'orders.php';
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    die('Order ID required');
}

// Check if function exists before calling it
if (!function_exists('getOrderDetails')) {
    http_response_code(500);
    die('Orders system not properly configured');
}

$order_id = intval($_GET['order_id']);
$order = getOrderDetails($order_id, $_SESSION['user_id']);

if (!$order) {
    http_response_code(404);
    die('Order not found');
}
?>

<div class="order-info-grid">
    <div class="order-info-card">
        <h3>Order Information</h3>
        <p><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
        <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
        <p><strong>Status:</strong> <?= getOrderStatusBadge($order['current_status']) ?></p>
        <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
    </div>
    
    <div class="order-info-card">
        <h3>Shipping Address</h3>
        <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'Not provided')) ?>
    </div>
    
    <div class="order-info-card">
        <h3>Billing Address</h3>
        <?= nl2br(htmlspecialchars($order['billing_address'] ?? 'Not provided')) ?>
    </div>
    
    <div class="order-info-card">
        <h3>Payment Information</h3>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'Not specified') ?></p>
        <p><strong>Payment Status:</strong> <?= ucfirst($order['payment_status'] ?? 'pending') ?></p>
    </div>
</div>

<div class="order-items">
    <h3 style="margin-bottom: 20px; color: var(--dark-charcoal);">Order Items</h3>
    <?php if (!empty($order['items'])): ?>
        <?php foreach ($order['items'] as $item): ?>
        <div class="order-item">
            <div class="order-item-image">
                <!-- You might want to store product images in your database -->
                <img src="images/products/<?= $item['product_id'] ?>.jpg" alt="<?= htmlspecialchars($item['product_name']) ?>" 
                     onerror="this.src='images/placeholder-product.jpg'">
            </div>
            <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            <div class="order-item-price">
                $<?= number_format($item['product_price'], 2) ?> x <?= $item['quantity'] ?>
            </div>
            <div class="order-item-subtotal">
                $<?= number_format($item['subtotal'], 2) ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No items found for this order.</p>
    <?php endif; ?>
    
    <div class="order-total">
        Total: $<?= number_format($order['total_amount'], 2) ?>
    </div>
</div>

<?php if (!empty($order['tracking'])): ?>
<div class="tracking-timeline">
    <h3 style="margin-bottom: 20px; color: var(--dark-charcoal);">Order Tracking</h3>
    <?php foreach ($order['tracking'] as $index => $tracking): ?>
    <div class="timeline-item">
        <div class="timeline-dot <?= $index === count($order['tracking']) - 1 ? 'completed' : 'pending' ?>">
            <?= $index + 1 ?>
        </div>
        <div class="timeline-content">
            <div class="timeline-status"><?= ucfirst($tracking['status']) ?></div>
            <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($tracking['created_at'])) ?></div>
            <div class="timeline-description"><?= htmlspecialchars($tracking['description']) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>