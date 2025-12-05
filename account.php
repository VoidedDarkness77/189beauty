<?php 
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'includes/db.php';

// Try to include orders.php, but don't break if it doesn't exist yet
$orders_functions_exist = false;
if (file_exists('includes/orders.php')) {
    include 'includes/orders.php';
    $orders_functions_exist = true;
}

// Get user details from database - UPDATED TO INCLUDE PROFILE AND NOTIFICATION FIELDS
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, first_name, last_name, phone, address, city, state, zip_code, date_of_birth, 
                       email_notifications, order_updates, promotional_emails 
                       FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle notification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $order_updates = isset($_POST['order_updates']) ? 1 : 0;
    $promotional_emails = isset($_POST['promotional_emails']) ? 1 : 0;
    
    $update_stmt = $conn->prepare("UPDATE users SET 
                                  email_notifications = ?, 
                                  order_updates = ?, 
                                  promotional_emails = ?,
                                  notification_updated_at = NOW()
                                  WHERE id = ?");
    $update_stmt->bind_param("iiii", $email_notifications, $order_updates, $promotional_emails, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['notification_success'] = "Notification preferences updated successfully!";
        // Update local user data
        $user['email_notifications'] = $email_notifications;
        $user['order_updates'] = $order_updates;
        $user['promotional_emails'] = $promotional_emails;
    } else {
        $_SESSION['notification_error'] = "Failed to update notification preferences.";
    }
    $update_stmt->close();
    
    // Refresh the page to show updated data
    header("Location: account.php?tab=" . ($_GET['tab'] ?? 'dashboard'));
    exit;
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['password_error'] = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['password_error'] = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['password_error'] = "New password must be at least 6 characters long.";
    } else {
        // Get current password from database using MySQLi
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $db_user = $result->fetch_assoc();
        $stmt->close();
        
        // Check if password field exists and is not null
        if (!$db_user || !isset($db_user['password'])) {
            // If password field doesn't exist or is null, check for other password fields
            $stmt = $conn->prepare("SHOW COLUMNS FROM users LIKE 'password'");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Password column doesn't exist - create it
                $conn->query("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT ''");
            }
            
            // Set a default password if none exists
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['password_success'] = "Password set successfully!";
            } else {
                $_SESSION['password_error'] = "Failed to set password. Please try again.";
            }
            $update_stmt->close();
        } else {
            // Verify current password
            if (password_verify($current_password, $db_user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['password_success'] = "Password updated successfully!";
                } else {
                    $_SESSION['password_error'] = "Failed to update password. Please try again.";
                }
                $update_stmt->close();
            } else {
                $_SESSION['password_error'] = "Current password is incorrect.";
            }
        }
    }
    
    // Refresh the page
    header("Location: account.php?tab=" . ($_GET['tab'] ?? 'dashboard'));
    exit;
}

// Set default values for missing data
$member_since = "January 2024";
$member_duration = "1 year";

// Get cart and wishlist counts
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;

// Get user orders if orders functions exist
$user_orders = [];
$order_count = 0;
$total_spent = 0;

if ($orders_functions_exist && function_exists('getUserOrders')) {
    $user_orders = getUserOrders($user_id);
    $order_count = count($user_orders);
    foreach ($user_orders as $order) {
        $total_spent += $order['total_amount'];
    }
} else {
    // Fallback: if orders system isn't working, use placeholder data
    $order_count = 0;
    $total_spent = 0;
}

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Recent activity
$recent_activity = [
    ['action' => 'Viewed Product', 'item' => 'Luxury Lipstick', 'time' => '2 hours ago'],
    ['action' => 'Added to Wishlist', 'item' => 'Glow Foundation', 'time' => '1 day ago'],
    ['action' => 'Account Created', 'item' => 'Welcome to 189 Beauty', 'time' => 'Recently']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - 189 Beauty</title>
    <style>
        /* === ACCOUNT STYLES - MATCHING BEAUTY THEME === */
        :root {
            --primary-gold: #D4AF37;
            --dark-charcoal: #2C2C2C;
            --soft-cream: #FAF7F2;
            --warm-gray: #E8E5DE;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow-glow: 0 8px 32px rgba(212, 175, 55, 0.15);
        }

        /* Fix for footer and layout */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1 0 auto;
        }

        .account-hero {
            background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
            padding: 80px 0 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .account-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
            animation: float 20s infinite linear;
        }

        .account-title {
            font-size: 3rem;
            font-weight: 300;
            letter-spacing: 3px;
            margin-bottom: 1rem;
            color: #d4af37;
            position: relative;
            display: inline-block;
        }

        .account-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: var(--primary-gold);
        }

        .account-subtitle {
            font-size: 1.1rem;
            color: var(--warm-gray);
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 2rem;
        }

        .account-section {
            background: var(--soft-cream);
            padding: 60px 0;
            min-height: calc(100vh - 300px);
            flex: 1;
        }

        .account-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
            min-height: 500px;
        }

        /* Sidebar Navigation */
        .account-sidebar {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--warm-gray);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .user-profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--warm-gray);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-gold), #f9f295);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--dark-charcoal);
            font-weight: bold;
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 5px;
        }

        .user-email {
            color: #666;
            font-size: 0.9rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--dark-charcoal);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            margin-bottom: 8px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
            font-size: inherit;
        }

        .nav-item:hover,
        .nav-item.active {
            background: var(--primary-gold);
            color: var(--dark-charcoal);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .account-main {
            min-height: 600px;
            display: flex;
            flex-direction: column;
        }

        .tab-content {
            display: none;
            flex: 1;
        }

        .tab-content.active {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .account-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--warm-gray);
            transition: var(--transition);
            opacity: 0;
            animation: slideIn 0.6s ease-out forwards;
        }

        .account-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-glow);
        }

        .account-card.full-width {
            grid-column: 1 / -1;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--warm-gray);
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin: 0;
        }

        .card-subtitle {
            color: #666;
            font-size: 1rem;
            margin: 0;
        }

        .card-action {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .card-action:hover {
            color: var(--dark-charcoal);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: var(--soft-cream);
            border-radius: 12px;
            border: 1px solid var(--warm-gray);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-gold);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Info List */
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--warm-gray);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: var(--dark-charcoal);
            font-weight: 600;
        }

        .info-value.gold {
            color: var(--primary-gold);
        }

        /* Activity List */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--warm-gray);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--soft-cream);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-gold);
            font-size: 1.1rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-action {
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 2px;
        }

        .activity-item-name {
            color: #666;
            font-size: 0.9rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.8rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            background: var(--soft-cream);
            border: 1px solid var(--warm-gray);
            color: var(--dark-charcoal);
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-block;
            cursor: pointer;
            font-family: inherit;
        }

        .action-btn:hover {
            background: var(--primary-gold);
            border-color: var(--primary-gold);
            transform: translateY(-2px);
        }

        .action-btn.primary {
            background: var(--primary-gold);
            border-color: var(--primary-gold);
            font-weight: 600;
        }

        .action-btn.primary:hover {
            background: var(--dark-charcoal);
            color: white;
        }

        /* Loyalty Status */
        .loyalty-status {
            background: linear-gradient(135deg, var(--primary-gold), #f9f295);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            color: var(--dark-charcoal);
            margin-top: 20px;
        }

        .loyalty-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .loyalty-level {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Order Styles */
        .table-responsive {
            flex: 1;
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 800px;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--warm-gray);
        }

        .orders-table th {
            background: var(--dark-charcoal);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr:hover {
            background: var(--soft-cream);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.processing {
            background: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }

        .status-badge.shipped {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-badge.delivered {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .view-order-btn {
            background: var(--primary-gold);
            color: var(--dark-charcoal);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-block;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .view-order-btn:hover {
            background: var(--dark-charcoal);
            color: white;
            transform: translateY(-1px);
        }

        /* Order Details Modal */
        .order-details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .order-details-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .order-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--warm-gray);
        }

        .order-details-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-charcoal);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            color: #999;
            cursor: pointer;
            transition: var(--transition);
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: var(--dark-charcoal);
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .order-info-card {
            background: var(--soft-cream);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--warm-gray);
        }

        .order-info-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-charcoal);
        }

        .order-info-card p {
            margin: 8px 0;
            color: #666;
            line-height: 1.5;
        }

        .order-items {
            margin-bottom: 30px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 60px 1fr auto auto;
            gap: 15px;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--warm-gray);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .order-item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .order-item-name {
            font-weight: 500;
            color: var(--dark-charcoal);
        }

        .order-item-price {
            color: var(--primary-gold);
            font-weight: 600;
        }

        .order-item-subtotal {
            font-weight: 600;
            color: var(--dark-charcoal);
        }

        .order-total {
            text-align: right;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--warm-gray);
        }

        .tracking-timeline {
            margin-top: 30px;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            bottom: -20px;
            width: 2px;
            background: var(--warm-gray);
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .timeline-dot.completed {
            background: var(--primary-gold);
        }

        .timeline-dot.pending {
            background: #ccc;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-status {
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 5px;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #999;
        }

        .timeline-description {
            color: #666;
            font-size: 0.9rem;
        }

        .no-orders {
            text-align: center;
            padding: 80px 20px;
            color: var(--dark-charcoal);
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }

        .no-orders-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
            color: var(--primary-gold);
        }

        .no-orders h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .no-orders p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Security Notice */
        .security-notice {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #065f46;
            text-align: center;
        }

        .security-notice i {
            color: #10b981;
            margin-right: 5px;
        }

        /* Setup Prompt */
        .setup-prompt {
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 0.8rem;
            color: #8B4513;
            text-align: center;
        }

        .setup-prompt a {
            color: var(--primary-gold);
            font-weight: 600;
            text-decoration: none;
        }

        .setup-prompt a:hover {
            text-decoration: underline;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-gold);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        /* Inline form styles */
        .inline-form {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .inline-form h4 {
            margin-top: 0;
            color: var(--dark-charcoal);
            margin-bottom: 15px;
        }

        .form-group-inline {
            margin-bottom: 15px;
        }

        .form-group-inline label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-input-inline {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .form-input-inline:focus {
            outline: none;
            border-color: var(--primary-gold);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .account-container {
                grid-template-columns: 1fr;
                gap: 30px;
                min-height: auto;
            }
            
            .account-sidebar {
                position: static;
            }
            
            .order-info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .order-item {
                grid-template-columns: 50px 1fr;
                gap: 10px;
            }
            
            .order-item-price,
            .order-item-subtotal {
                grid-column: 1 / -1;
                text-align: left;
                margin-top: 5px;
            }
            
            .order-details-content {
                padding: 20px;
                width: 95%;
            }
        }

        @media (max-width: 768px) {
            .account-title {
                font-size: 2.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
            
            .account-section {
                min-height: calc(100vh - 250px);
                padding: 40px 0;
            }
        }

        @media (max-width: 480px) {
            .account-title {
                font-size: 2rem;
            }
            
            .account-card {
                padding: 20px;
            }
            
            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .account-section {
                padding: 30px 0;
            }
            
            .no-orders {
                padding: 60px 20px;
                min-height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- ACCOUNT HERO SECTION -->
    <section class="account-hero">
        <div class="hero-content">
            <h1 class="account-title">MY ACCOUNT</h1>
            <p class="account-subtitle">MANAGE YOUR BEAUTY PROFILE AND PREFERENCES</p>
        </div>
    </section>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-content">
        <!-- ACCOUNT SECTION -->
        <section class="account-section">
            <div class="account-container">
                <!-- Sidebar Navigation -->
                <div class="account-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <h3 class="user-name">
                            <?= !empty($user['first_name']) && !empty($user['last_name']) 
                                ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) 
                                : htmlspecialchars($user['username']) ?>
                        </h3>
                        <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <nav class="sidebar-nav">
                        <a href="account.php?tab=dashboard" class="nav-item <?= $active_tab == 'dashboard' ? 'active' : '' ?>">
                            <i>📊</i> Dashboard
                        </a>
                        <a href="account.php?tab=orders" class="nav-item <?= $active_tab == 'orders' ? 'active' : '' ?>">
                            <i>📦</i> My Orders
                        </a>
                        <a href="account.php?tab=profile" class="nav-item <?= $active_tab == 'profile' ? 'active' : '' ?>">
                            <i>👤</i> Profile Settings
                        </a>
                        <a href="wishlist.php" class="nav-item">
                            <i>❤️</i> Wishlist
                        </a>
                        <a href="cart.php" class="nav-item">
                            <i>🛒</i> Shopping Cart
                        </a>
                        <a href="logout.php" class="nav-item">
                            <i>🚪</i> Sign Out
                        </a>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="account-main">
                    <!-- Dashboard Tab -->
                    <div class="tab-content <?= $active_tab == 'dashboard' ? 'active' : '' ?>" id="dashboard-tab">
                        <!-- Account Overview -->
                        <div class="account-card">
                            <div class="card-header">
                                <h3 class="card-title">Account Overview</h3>
                                <a href="profile.php" class="card-action">Edit Profile</a>
                            </div>
                            
                            <div class="setup-prompt">
                                💡 <strong>Complete your profile!</strong> 
                                <a href="profile.php">Add more details</a> to unlock all features.
                            </div>
                            
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number"><?= $order_count ?></div>
                                    <div class="stat-label">Orders</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= $wishlist_count ?></div>
                                    <div class="stat-label">Wishlist Items</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= $cart_count ?></div>
                                    <div class="stat-label">Cart Items</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">$<?= number_format($total_spent, 2) ?></div>
                                    <div class="stat-label">Total Spent</div>
                                </div>
                            </div>

                            <ul class="info-list">
                                <li class="info-item">
                                    <span class="info-label">Member Since</span>
                                    <span class="info-value"><?= $member_since ?></span>
                                </li>
                                <li class="info-item">
                                    <span class="info-label">Membership Duration</span>
                                    <span class="info-value"><?= $member_duration ?></span>
                                </li>
                                <li class="info-item">
                                    <span class="info-label">Email Status</span>
                                    <span class="info-value gold">Verified ✓</span>
                                </li>
                                <li class="info-item">
                                    <span class="info-label">Account Status</span>
                                    <span class="info-value gold">Active</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Recent Activity -->
                        <div class="account-card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Activity</h3>
                                <a href="activity.php" class="card-action">View All</a>
                            </div>
                            
                            <ul class="activity-list">
                                <?php foreach($recent_activity as $activity): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <?= 
                                            $activity['action'] === 'Viewed Product' ? '👀' :
                                            ($activity['action'] === 'Added to Wishlist' ? '❤️' :
                                            ($activity['action'] === 'Account Created' ? '🎉' : '📝'))
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-action"><?= $activity['action'] ?></div>
                                        <div class="activity-item-name"><?= $activity['item'] ?></div>
                                    </div>
                                    <div class="activity-time"><?= $activity['time'] ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Quick Actions -->
                        <div class="account-card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            
                            <div class="quick-actions">
                                <a href="products.php" class="action-btn primary">Continue Shopping</a>
                                <a href="wishlist.php" class="action-btn">View Wishlist (<?= $wishlist_count ?>)</a>
                                <a href="cart.php" class="action-btn">View Cart (<?= $cart_count ?>)</a>
                                <a href="profile.php" class="action-btn">Complete Profile</a>
                            </div>
                        </div>

                        <!-- Loyalty Program -->
                        <div class="account-card">
                            <div class="card-header">
                                <h3 class="card-title">Loyalty Status</h3>
                                <a href="loyalty.php" class="card-action">Learn More</a>
                            </div>
                            
                            <div class="loyalty-status">
                                <div class="loyalty-title">Beauty Insider</div>
                                <div class="loyalty-level">Gold Member</div>
                            </div>
                            
                            <ul class="info-list">
                                <li class="info-item">
                                    <span class="info-label">Points Balance</span>
                                    <span class="info-value gold">1,250 points</span>
                                </li>
                                <li class="info-item">
                                    <span class="info-label">Next Reward</span>
                                    <span class="info-value">250 points to next tier</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Security & Preferences -->
                        <div class="account-card full-width">
                            <div class="card-header">
                                <h3 class="card-title">Security & Preferences</h3>
                            </div>
                            
                            <div class="quick-actions">
                                <button onclick="openChangePassword()" class="action-btn">Change Password</button>
                                <a href="profile.php" class="action-btn">Update Profile</a>
                                <button onclick="openNotifications()" class="action-btn">Notifications</button>
                                <a href="logout.php" class="action-btn primary">Sign Out</a>
                            </div>
                            
                            <!-- Password Change Form (Hidden by default) -->
                            <div id="passwordSection" class="inline-form" style="display: none;">
                                <h4>Change Password</h4>
                                <?php if(isset($_SESSION['password_success'])): ?>
                                    <div class="message success"><?= $_SESSION['password_success'] ?></div>
                                    <?php unset($_SESSION['password_success']); ?>
                                <?php endif; ?>
                                
                                <?php if(isset($_SESSION['password_error'])): ?>
                                    <div class="message error"><?= $_SESSION['password_error'] ?></div>
                                    <?php unset($_SESSION['password_error']); ?>
                                <?php endif; ?>
                                
                                <form id="passwordForm" method="POST">
                                    <input type="hidden" name="change_password" value="1">
                                    <div class="form-group-inline">
                                        <label>Current Password *</label>
                                        <input type="password" name="current_password" class="form-input-inline" required>
                                    </div>
                                    <div class="form-group-inline">
                                        <label>New Password *</label>
                                        <input type="password" name="new_password" class="form-input-inline" required minlength="6">
                                    </div>
                                    <div class="form-group-inline">
                                        <label>Confirm New Password *</label>
                                        <input type="password" name="confirm_password" class="form-input-inline" required minlength="6">
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="action-btn primary">Update Password</button>
                                        <button type="button" class="action-btn" onclick="closeChangePassword()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Notifications Settings Form (Hidden by default) -->
                            <div id="notificationsSection" class="inline-form" style="display: none;">
                                <h4>Notification Settings</h4>
                                <?php if(isset($_SESSION['notification_success'])): ?>
                                    <div class="message success"><?= $_SESSION['notification_success'] ?></div>
                                    <?php unset($_SESSION['notification_success']); ?>
                                <?php endif; ?>
                                
                                <?php if(isset($_SESSION['notification_error'])): ?>
                                    <div class="message error"><?= $_SESSION['notification_error'] ?></div>
                                    <?php unset($_SESSION['notification_error']); ?>
                                <?php endif; ?>
                                
                                <form id="notificationsForm" method="POST">
                                    <input type="hidden" name="update_notifications" value="1">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="email_notifications" id="email_notifications" 
                                            <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                        <label for="email_notifications">Email Notifications</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="order_updates" id="order_updates" 
                                            <?= ($user['order_updates'] ?? 1) ? 'checked' : '' ?>>
                                        <label for="order_updates">Order Updates</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="promotional_emails" id="promotional_emails" 
                                            <?= ($user['promotional_emails'] ?? 1) ? 'checked' : '' ?>>
                                        <label for="promotional_emails">Promotional Emails</label>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="action-btn primary">Save Preferences</button>
                                        <button type="button" class="action-btn" onclick="closeNotifications()">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="security-notice">
                                <i>🔒</i> Welcome to your account! • 
                                <a href="profile.php" style="color: #065f46; text-decoration: underline;">Complete your profile setup</a>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-content <?= $active_tab == 'orders' ? 'active' : '' ?>" id="orders-tab">
                        <div class="account-card full-width">
                            <div class="card-header">
                                <h2 class="card-title">My Orders</h2>
                                <p class="card-subtitle">Track and manage your orders</p>
                            </div>
                            
                            <?php if ($orders_functions_exist && !empty($user_orders)): ?>
                                <div class="table-responsive">
                                    <table class="orders-table">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_orders as $order): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                                <td>
                                                    <?php
                                                    $item_count = $orders_functions_exist ? getOrderItemCount($order['id']) : 0;
                                                    echo $item_count . ' item' . ($item_count != 1 ? 's' : '');
                                                    ?>
                                                </td>
                                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                                <td><?= $orders_functions_exist ? getOrderStatusBadge($order['current_status']) : '<span class="status-badge pending">Unknown</span>' ?></td>
                                                <td>
                                                    <a href="account.php?tab=orders&view_order=<?= $order['id'] ?>" 
                                                       class="view-order-btn" 
                                                       onclick="openOrderModal(<?= $order['id'] ?>); return false;">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif (!$orders_functions_exist): ?>
                                <div class="no-orders">
                                    <div class="no-orders-icon">🔧</div>
                                    <h3>Orders System Setup Required</h3>
                                    <p>The orders functionality is being set up. Please check back later or contact support.</p>
                                    <a href="products.php" class="action-btn primary">Continue Shopping</a>
                                </div>
                            <?php else: ?>
                                <div class="no-orders">
                                    <div class="no-orders-icon">📦</div>
                                    <h3>No Orders Yet</h3>
                                    <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                                    <a href="products.php" class="action-btn primary">Start Shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Settings Tab -->
                    <div class="tab-content <?= $active_tab == 'profile' ? 'active' : '' ?>" id="profile-tab">
                        <div class="account-card full-width">
                            <div class="card-header">
                                <h2 class="card-title">Profile Settings</h2>
                                <p class="card-subtitle">Manage your personal information and preferences</p>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                                <!-- Personal Information -->
                                <div>
                                    <h3 style="margin-bottom: 20px; color: var(--dark-charcoal);">Personal Information</h3>
                                    
                                    <div class="info-list">
                                        <div class="info-item">
                                            <span class="info-label">Full Name</span>
                                            <span class="info-value">
                                                <?= !empty($user['first_name']) && !empty($user['last_name']) 
                                                    ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) 
                                                    : 'Not set' ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email</span>
                                            <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Phone</span>
                                            <span class="info-value">
                                                <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not set' ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Date of Birth</span>
                                            <span class="info-value">
                                                <?= !empty($user['date_of_birth']) 
                                                    ? date('F j, Y', strtotime($user['date_of_birth'])) 
                                                    : 'Not set' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address Information -->
                                <div>
                                    <h3 style="margin-bottom: 20px; color: var(--dark-charcoal);">Address Information</h3>
                                    
                                    <div class="info-list">
                                        <div class="info-item">
                                            <span class="info-label">Address</span>
                                            <span class="info-value">
                                                <?= !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not set' ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">City</span>
                                            <span class="info-value">
                                                <?= !empty($user['city']) ? htmlspecialchars($user['city']) : 'Not set' ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">State</span>
                                            <span class="info-value">
                                                <?= !empty($user['state']) ? htmlspecialchars($user['state']) : 'Not set' ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">ZIP Code</span>
                                            <span class="info-value">
                                                <?= !empty($user['zip_code']) ? htmlspecialchars($user['zip_code']) : 'Not set' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-actions" style="margin-top: 30px;">
                                <a href="profile.php" class="action-btn primary">Edit Profile</a>
                                <button onclick="openChangePassword()" class="action-btn">Change Password</button>
                            </div>
                            
                            <div class="setup-prompt" style="margin-top: 20px;">
                                💡 <strong>Complete your profile!</strong> 
                                Add your personal details to enhance your shopping experience.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div> <!-- End .main-content -->

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="order-details-modal">
        <div class="order-details-content">
            <div class="order-details-header">
                <h2 class="order-details-title">Order Details</h2>
                <button class="close-modal" onclick="closeOrderModal()">&times;</button>
            </div>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <script>
    // Order modal functionality
    function openOrderModal(orderId) {
        event.preventDefault();
        
        // Show loading
        document.getElementById('orderDetailsContent').innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="loading-spinner"></div>
                <p>Loading order details...</p>
            </div>
        `;
        
        // Show modal
        document.getElementById('orderDetailsModal').style.display = 'block';
        
        // Load order details via AJAX
        fetch(`includes/get_order_details.php?order_id=${orderId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('orderDetailsContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('orderDetailsContent').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <p>Error loading order details. Please try again.</p>
                    </div>
                `;
            });
    }

    function closeOrderModal() {
        document.getElementById('orderDetailsModal').style.display = 'none';
    }

    // Security & Preferences Functions
    function openChangePassword() {
        closeNotifications();
        document.getElementById('passwordSection').style.display = 'block';
        document.getElementById('notificationsSection').style.display = 'none';
        // Scroll to the form
        document.getElementById('passwordSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeChangePassword() {
        document.getElementById('passwordSection').style.display = 'none';
        document.getElementById('passwordForm').reset();
        // Clear messages
        const messages = document.querySelectorAll('#passwordSection .message');
        messages.forEach(msg => msg.style.display = 'none');
    }

    function openNotifications() {
        closeChangePassword();
        document.getElementById('notificationsSection').style.display = 'block';
        document.getElementById('passwordSection').style.display = 'none';
        // Scroll to the form
        document.getElementById('notificationsSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeNotifications() {
        document.getElementById('notificationsSection').style.display = 'none';
        // Clear messages
        const messages = document.querySelectorAll('#notificationsSection .message');
        messages.forEach(msg => msg.style.display = 'none');
    }

    // Close modal when clicking outside
    document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderModal();
        }
    });

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderModal();
        }
    });

    // Enhanced account page functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add loading states to action buttons
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
            if (!button.hasAttribute('type') || button.getAttribute('type') !== 'submit') {
                button.addEventListener('click', function(e) {
                    // Don't apply loading to form submit buttons
                    if (this.type === 'submit' || this.closest('form')) return;
                    
                    // Add loading animation for any button click
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span>Loading...</span>';
                    this.style.opacity = '0.7';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.opacity = '1';
                    }, 500);
                });
            }
        });
        
        // Add smooth animations to cards
        const cards = document.querySelectorAll('.account-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.style.animation = 'slideIn 0.6s ease-out forwards';
        });
        
        // Add tab switching functionality
        const tabLinks = document.querySelectorAll('.nav-item[href*="tab="]');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Remove active class from all tabs
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show the corresponding tab content
                const tabName = this.getAttribute('href').split('tab=')[1];
                const tabContent = document.getElementById(tabName + '-tab');
                if (tabContent) {
                    tabContent.classList.add('active');
                }
            });
        });
        
        // Password Form Submission - Real validation
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const currentPassword = this.querySelector('input[name="current_password"]').value;
                const newPassword = this.querySelector('input[name="new_password"]').value;
                const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
                
                // Client-side validation
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match!');
                    return;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters!');
                    return;
                }
            });
        }
        
        // Auto-close success messages after 5 seconds
        setTimeout(() => {
            const successMessages = document.querySelectorAll('.message.success');
            successMessages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
        
        // Close forms when clicking outside (optional enhancement)
        document.addEventListener('click', function(e) {
            const passwordSection = document.getElementById('passwordSection');
            const notificationsSection = document.getElementById('notificationsSection');
            
            // Check if click is outside both forms and not on the buttons that open them
            if (passwordSection && passwordSection.style.display === 'block' && 
                !passwordSection.contains(e.target) && 
                !e.target.closest('.action-btn[onclick*="openChangePassword"]')) {
                closeChangePassword();
            }
            
            if (notificationsSection && notificationsSection.style.display === 'block' && 
                !notificationsSection.contains(e.target) && 
                !e.target.closest('.action-btn[onclick*="openNotifications"]')) {
                closeNotifications();
            }
        });
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>