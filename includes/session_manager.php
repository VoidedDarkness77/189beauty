<?php
/**
 * Session Manager for Cart and Wishlist Persistence
 */

/**
 * Save user's cart and wishlist data to database
 */
function saveUserSessionData($user_id) {
    // Include database connection
    include 'db.php';
    
    $cart_data = isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : '[]';
    $wishlist_data = isset($_SESSION['wishlist']) ? json_encode($_SESSION['wishlist']) : '[]';
    
    $stmt = $conn->prepare("UPDATE users SET cart_data = ?, wishlist_data = ? WHERE id = ?");
    $stmt->bind_param("ssi", $cart_data, $wishlist_data, $user_id);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Auto-save session data before logout or session destruction
 */
function autoSaveSessionData() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
        saveUserSessionData($_SESSION['user_id']);
    }
}

/**
 * Clear session data when user logs out
 */
function clearUserSessionData($user_id) {
    // Include database connection
    include 'db.php';
    
    $empty_data = '[]';
    $stmt = $conn->prepare("UPDATE users SET cart_data = ?, wishlist_data = ? WHERE id = ?");
    $stmt->bind_param("ssi", $empty_data, $empty_data, $user_id);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Register shutdown function to auto-save session data
register_shutdown_function('autoSaveSessionData');
?>