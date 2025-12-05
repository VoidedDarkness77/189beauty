<?php
// includes/orders.php

// Prevent multiple declarations
if (!function_exists('getOrderItemCount')) {
    function getOrderItemCount($order_id) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data['total_items'] ?? 0;
    }
}

if (!function_exists('getUserOrders')) {
    function getUserOrders($user_id) {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT o.*, 
                   (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as current_status
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $orders;
    }
}

if (!function_exists('getOrderDetails')) {
    function getOrderDetails($order_id, $user_id = null) {
        global $conn;
        
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
        
        if ($user_id) {
            $sql .= " AND o.user_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        
        if ($user_id) {
            $stmt->bind_param("ii", $order_id, $user_id);
        } else {
            $stmt->bind_param("i", $order_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            // Get order items
            $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order['items'] = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Get tracking history
            $stmt = $conn->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order['tracking'] = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        return $order;
    }
}

if (!function_exists('getOrderStatusBadge')) {
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
}

if (!function_exists('createOrder')) {
    function createOrder($user_id, $cart_items, $shipping_address, $billing_address, $payment_method) {
        global $conn;
        
        try {
            // Calculate total
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['qty'];
            }
            
            // Generate unique order number
            $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, shipping_address, billing_address, payment_method) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isdsss", $user_id, $order_number, $total_amount, $shipping_address, $billing_address, $payment_method);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $product_id => $item) {
                $subtotal = $item['price'] * $item['qty'];
                $stmt->bind_param("iisidi", $order_id, $product_id, $item['name'], $item['price'], $item['qty'], $subtotal);
                $stmt->execute();
            }
            $stmt->close();
            
            // Add initial tracking status
            $stmt = $conn->prepare("
                INSERT INTO order_tracking (order_id, status, description) 
                VALUES (?, 'pending', 'Order placed successfully')
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
            
            return $order_id;
            
        } catch (Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }
}
?>