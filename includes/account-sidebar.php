<?php
// Check if database connection is available, if not include it
if (!isset($conn)) {
    include 'db.php';
}

// Get user data for sidebar
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    // Fallback if no user session
    $user = ['username' => 'Guest', 'email' => ''];
}

// Get counts safely
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;

// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="account-sidebar">
    <div class="user-profile">
        <div class="user-avatar">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
        </div>
        <h3 class="user-name"><?= htmlspecialchars($user['username']) ?></h3>
        <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
    </div>

    <ul class="account-nav">
        <li><a href="account.php" class="<?= $current_page == 'account.php' ? 'active' : '' ?>"><i>📊</i> Dashboard</a></li>
        <li><a href="orders.php" class="<?= $current_page == 'orders.php' ? 'active' : '' ?>"><i>📦</i> My Orders</a></li>
        <li><a href="wishlist.php" class="<?= $current_page == 'wishlist.php' ? 'active' : '' ?>"><i>❤️</i> Wishlist (<?= $wishlist_count ?>)</a></li>
        <li><a href="cart.php" class="<?= $current_page == 'cart.php' ? 'active' : '' ?>"><i>🛒</i> Shopping Cart (<?= $cart_count ?>)</a></li>
        <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>"><i>👤</i> Profile Settings</a></li>
        <li><a href="addresses.php" class="<?= $current_page == 'addresses.php' ? 'active' : '' ?>"><i>🏠</i> Address Book</a></li>
        <li><a href="payment-methods.php" class="<?= $current_page == 'payment-methods.php' ? 'active' : '' ?>"><i>💳</i> Payment Methods</a></li>
        <li><a href="logout.php"><i>🚪</i> Sign Out</a></li>
    </ul>
</div>