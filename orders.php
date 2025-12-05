<?php
// includes/orders.php

function createOrder($user_id, $cart_items, $shipping_address, $billing_address, $payment_method) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['qty'];
        }
        
        // Generate unique order number
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, shipping_address, billing_address, payment_method) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $order_number, $total_amount, $shipping_address, $billing_address, $payment_method]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $product_id => $item) {
            $subtotal = $item['price'] * $item['qty'];
            $stmt->execute([$order_id, $product_id, $item['name'], $item['price'], $item['qty'], $subtotal]);
        }
        
        // Add initial tracking status
        $stmt = $pdo->prepare("
            INSERT INTO order_tracking (order_id, status, description) 
            VALUES (?, 'pending', 'Order placed successfully')
        ");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getUserOrders($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT o.*, 
               (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as current_status
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($order_id, $user_id = null) {
    global $pdo;
    
    $sql = "
        SELECT o.*, 
               ot.status as current_status,
               ot.description as status_description,
               ot.created_at as status_date
        FROM orders o
        LEFT JOIN order_tracking ot ON (
            ot.id = (
                SELECT id FROM order_tracking 
                WHERE order_id = o.id 
                ORDER BY created_at DESC 
                LIMIT 1
            )
        )
        WHERE o.id = ?
    ";
    
    $params = [$order_id];
    
    if ($user_id) {
        $sql .= " AND o.user_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tracking history
        $stmt = $pdo->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC");
        $stmt->execute([$order_id]);
        $order['tracking'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $order;
}

function getOrderStatusBadge($status) {
    $badges = [
        'pending' => '<span class="status-badge pending">Pending</span>',
        'processing' => '<span class="status-badge processing">Processing</span>',
        'shipped' => '<span class="status-badge shipped">Shipped</span>',
        'delivered' => '<span class="status-badge delivered">Delivered</span>',
        'cancelled' => '<span class="status-badge cancelled">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="status-badge pending">Unknown</span>';
}
?>