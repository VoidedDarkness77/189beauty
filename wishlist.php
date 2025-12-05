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

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// ---------------- HANDLE REMOVE FROM WISHLIST ----------------
if (isset($_GET['remove_from_wishlist'])) {
    $id = intval($_GET['remove_from_wishlist']);
    
    if (isset($_SESSION['wishlist'][$id])) {
        $product_name = $_SESSION['wishlist'][$id]['name'];
        unset($_SESSION['wishlist'][$id]);
        $_SESSION['wishlist_success'] = $product_name . " removed from wishlist!";
        autoSaveOnChange();
    }
    
    header("Location: wishlist.php");
    exit;
}

// ---------------- HANDLE MOVE TO CART ----------------
if (isset($_POST['move_to_cart'])) {
    $id = intval($_POST['id']);
    
    if (isset($_SESSION['wishlist'][$id])) {
        $item = $_SESSION['wishlist'][$id];
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add to cart
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] += 1;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'img' => $item['img'],
                'qty' => 1
            ];
        }
        
        // Remove from wishlist
        unset($_SESSION['wishlist'][$id]);
        $_SESSION['wishlist_success'] = $item['name'] . " moved to cart!";
        autoSaveOnChange();
    }
    
    header("Location: wishlist.php");
    exit;
}

// ---------------- HANDLE CLEAR WISHLIST ----------------
if (isset($_GET['clear_wishlist'])) {
    $_SESSION['wishlist'] = [];
    $_SESSION['wishlist_success'] = "Wishlist cleared successfully!";
    autoSaveOnChange();
    header("Location: wishlist.php");
    exit;
}

// Get counts for display
$wishlist_count = count($_SESSION['wishlist']);
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<style>
/* === WISHLIST STYLES === */
.wishlist-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 80px 0 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.wishlist-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    animation: float 20s infinite linear;
}

.wishlist-title {
    font-size: 3rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    color: #d4af37;
    position: relative;
    display: inline-block;
}

.wishlist-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: var(--primary-gold);
}

.wishlist-subtitle {
    font-size: 1.1rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

.wishlist-section {
    background: var(--soft-cream);
    padding: 60px 0;
    min-height: 60vh;
}

.wishlist-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.wishlist-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--warm-gray);
    grid-column: 1 / -1;
}

.wishlist-count {
    font-size: 1.2rem;
    color: var(--dark-charcoal);
    font-weight: 500;
    letter-spacing: 1px;
}

.wishlist-actions {
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
    color: #d4af37;
}

.clear-wishlist {
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

.clear-wishlist:hover {
    background: var(--dark-charcoal);
    color: #d4af37;
    border-color: var(--dark-charcoal);
}

/* Wishlist Items */
.wishlist-items {
    background: white;
    border-radius: 16px;
    padding: 30px;
    border: 1px solid var(--warm-gray);
}

.wishlist-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 20px;
    align-items: center;
    padding: 25px 0;
    border-bottom: 1px solid var(--warm-gray);
    position: relative;
}

.wishlist-item:last-child {
    border-bottom: none;
}

.wishlist-item-image {
    width: 100px;
    height: 100px;
    background: var(--soft-cream);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.wishlist-item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.wishlist-item-info {
    padding-right: 20px;
}

.wishlist-item-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 8px;
    line-height: 1.4;
}

.wishlist-item-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-gold);
}

.wishlist-item-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 120px;
}

.move-to-cart-btn {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
    font-size: 0.9rem;
}

.move-to-cart-btn:hover {
    background: var(--dark-charcoal);
    color: #d4af37;
    transform: translateY(-2px);
}

.remove-btn {
    background: transparent;
    border: 1px solid #d4af37;
    color: #d4af37;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.8rem;
    text-decoration: none;
    text-align: center;
}

.remove-btn:hover {
    background: #d4af37;
    color: white;
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
    font-size: 1.5rem;
}

.remove-item:hover {
    color: #d4af37;
}

/* Quick Actions */
.quick-actions-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    border: 1px solid var(--warm-gray);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.quick-actions-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 25px;
    text-align: center;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--warm-gray);
}

.quick-actions-buttons {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-btn {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    transition: var(--transition);
    font-weight: 500;
    letter-spacing: 1px;
}

.action-btn:hover {
    background: var(--dark-charcoal);
    color: #d4af37;
    transform: translateY(-2px);
}

.action-btn.secondary {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
}

.action-btn.secondary:hover {
    background: var(--dark-charcoal);
    color: #d4af37;
    border-color: var(--dark-charcoal);
}

/* Empty Wishlist */
.wishlist-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--dark-charcoal);
    grid-column: 1 / -1;
}

.wishlist-empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
    color: var(--primary-gold);
}

.wishlist-empty h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 300;
    letter-spacing: 1px;
}

.wishlist-empty p {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 968px) {
    .wishlist-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .quick-actions-card {
        position: static;
    }
}

@media (max-width: 768px) {
    .wishlist-title {
        font-size: 2.5rem;
    }
    
    .wishlist-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .wishlist-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .wishlist-item-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: flex-start;
        margin-top: 10px;
    }
    
    .remove-item {
        top: 15px;
        right: 0;
    }
}

@media (max-width: 480px) {
    .wishlist-item {
        padding: 20px 0;
    }
    
    .wishlist-item-image {
        width: 80px;
        height: 80px;
    }
    
    .wishlist-item-actions {
        flex-direction: column;
    }
}

@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    100% { transform: translateY(-100px) rotate(360deg); }
}
</style>

<!-- HERO SECTION -->
<section class="wishlist-hero">
    <div class="hero-content">
        <h1 class="wishlist-title">MY WISHLIST</h1>
        <p class="wishlist-subtitle">YOUR SAVED BEAUTY ITEMS</p>
    </div>
</section>

<!-- WISHLIST SECTION -->
<section class="wishlist-section">
    <div class="wishlist-container">
        <div class="wishlist-header">
            <div class="wishlist-count">
                <?= $wishlist_count ?> Item<?= $wishlist_count != 1 ? 's' : '' ?> in Wishlist
            </div>
            <div class="wishlist-actions">
                <?php if($wishlist_count > 0): ?>
                <a href="wishlist.php?clear_wishlist=1" class="clear-wishlist" onclick="return confirm('Are you sure you want to clear your entire wishlist?')">
                    Clear Wishlist
                </a>
                <?php endif; ?>
                <a href="products.php" class="continue-shopping">
                    Continue Shopping
                </a>
            </div>
        </div>

        <?php if(!empty($_SESSION['wishlist'])): ?>
            <!-- Wishlist Items -->
            <div class="wishlist-items">
                <?php foreach($_SESSION['wishlist'] as $id => $item): ?>
                <div class="wishlist-item">
                    <div class="wishlist-item-image">
                        <img src="<?= $item['img'] ?>" alt="<?= $item['name'] ?>" loading="lazy">
                    </div>
                    
                    <div class="wishlist-item-info">
                        <h3 class="wishlist-item-name"><?= $item['name'] ?></h3>
                        <p class="wishlist-item-price">$<?= number_format($item['price'], 2) ?></p>
                        <?php if(isset($item['added_at'])): ?>
                            <p style="color: #999; font-size: 0.8rem; margin-top: 5px;">
                                Added: <?= date('M j, Y', strtotime($item['added_at'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="wishlist-item-actions">
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="move_to_cart" value="1">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="move-to-cart-btn">
                                Add to Cart
                            </button>
                        </form>
                        
                        <a href="wishlist.php?remove_from_wishlist=<?= $id ?>" class="remove-btn">
                            Remove
                        </a>
                    </div>
                    
                    <a href="wishlist.php?remove_from_wishlist=<?= $id ?>" class="remove-item" title="Remove Item">
                        ×
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <h2 class="quick-actions-title">Quick Actions</h2>
                <div class="quick-actions-buttons">
                    <a href="products.php" class="action-btn">Continue Shopping</a>
                    <a href="cart.php" class="action-btn secondary">View Cart (<?= $cart_count ?>)</a>
                </div>
            </div>

        <?php else: ?>
            <div class="wishlist-empty">
                <div class="wishlist-empty-icon">❤️</div>
                <h3>Your Wishlist is Empty</h3>
                <p>Discover our luxurious beauty collection and add your favorite products to the wishlist.</p>
                <a href="products.php" class="continue-shopping">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- TOAST NOTIFICATIONS -->
<div class="toast-container">
    <?php if(isset($_SESSION['wishlist_success'])): ?>
        <div class="toast" id="successToast">
            <?= $_SESSION['wishlist_success'] ?>
        </div>
        <?php unset($_SESSION['wishlist_success']); ?>
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

    // Add smooth animations
    const wishlistItems = document.querySelectorAll('.wishlist-item');
    wishlistItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.style.animation = 'slideIn 0.6s ease-out forwards';
    });
});

// Add CSS animation for slideIn
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .wishlist-item {
        opacity: 0;
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>