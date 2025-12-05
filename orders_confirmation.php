<?php
// order_confirmation.php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/orders.php';

if (!isset($_GET['order_id'])) {
    header("Location: account.php?tab=orders");
    exit;
}

$order_id = intval($_GET['order_id']);
$order = getOrderDetails($order_id, $_SESSION['user_id']);

if (!$order) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - 189 Beauty</title>
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: left;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            background: #D4AF37;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px;
        }
        
        .btn:hover {
            background: #b8941f;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="confirmation-container">
        <div class="success-message">
            <h1>🎉 Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order Number:</strong> <?= $order['order_number'] ?></p>
            <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
            <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
            <p><strong>Status:</strong> <?= getOrderStatusBadge($order['current_status']) ?></p>
            
            <h3>Items Ordered:</h3>
            <?php foreach ($order['items'] as $item): ?>
                <p><?= $item['product_name'] ?> - <?= $item['quantity'] ?> x $<?= number_format($item['product_price'], 2) ?></p>
            <?php endforeach; ?>
        </div>
        
        <a href="account.php?tab=orders" class="btn">View All Orders</a>
        <a href="products.php" class="btn">Continue Shopping</a>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>