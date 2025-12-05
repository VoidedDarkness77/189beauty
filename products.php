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

// After adding to cart
if (isset($_POST['add_to_cart'])) {
    // ... your existing cart logic ...
    
    // Auto-save after cart change
    autoSaveOnChange();
}

// After adding to wishlist
if (isset($_POST['add_to_wishlist'])) {
    // ... your existing wishlist logic ...
    
    // Auto-save after wishlist change
    autoSaveOnChange();
}

// After updating quantity
if (isset($_POST['update_quantity'])) {
    // ... your existing quantity update logic ...
    
    // Auto-save after cart change
    autoSaveOnChange();
}

// After removing from cart
if (isset($_GET['remove_from_cart'])) {
    // ... your existing remove logic ...
    
    // Auto-save after cart change
    autoSaveOnChange();
}

// ---------------- HANDLE WISHLIST ----------------
if (isset($_POST['add_to_wishlist'])) {
    // DEBUG: See what we're receiving
    error_log("WISHLIST POST DATA: " . print_r($_POST, true));
    
    $id    = intval($_POST['id']);
    $name  = $_POST['name'];
    $img   = $_POST['img'];
    
    // SIMPLE PRICE HANDLING - NO VALIDATION
    $price = floatval($_POST['price']);
    error_log("Wishlist - ID: $id, Price: $price");
    
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }

    if (!isset($_SESSION['wishlist'][$id])) {
        $_SESSION['wishlist'][$id] = [
            'name'  => $name,
            'price' => $price,
            'img'   => $img,
            'added_at' => date('Y-m-d H:i:s')
        ];
        $_SESSION['wishlist_success'] = $name . " added to wishlist!";
    } else {
        $_SESSION['wishlist_error'] = $name . " is already in your wishlist!";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// ---------------- HANDLE ADD TO CART ----------------
if (isset($_POST['add_to_cart'])) {
    // DEBUG: See what we're receiving
    error_log("CART POST DATA: " . print_r($_POST, true));
    
    $id    = intval($_POST['id']);
    $name  = $_POST['name'];
    $img   = $_POST['img'];
    
    // SIMPLE PRICE HANDLING - NO VALIDATION
    $price = floatval($_POST['price']);
    error_log("Cart - ID: $id, Price: $price");
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += 1;
    } else {
        $_SESSION['cart'][$id] = [
            'name'  => $name,
            'price' => $price,
            'img'   => $img,
            'qty'   => 1
        ];
    }

    $_SESSION['cart_success'] = $name . " added to cart!";
    header("Location: cart.php");
    exit;
}

// ---------------- PRODUCT DATA WITH TYPES ----------------
$products = [
    [
        'id'=>1,
        'name'=>'Luxury Lipstick',
        'price'=>19.99,
        'img'=>'images/Luxury Lipstick.png',
        'description'=>'A creamy, long-lasting luxury lipstick with a hydrating formula.',
        'created_at'=>'2025-11-25 10:00:00',
        'type'=>'cosmetics'
    ],
    [
        'id'=>2,
        'name'=>'Glow Foundation',
        'price'=>29.99,
        'img'=>'images/Glow Foundation.png',
        'description'=>'Full coverage foundation with a radiant, luminous finish.',
        'created_at'=>'2025-11-20 12:00:00',
        'type'=>'cosmetics'
    ],
    [
        'id'=>3,
        'name'=>'Silk Eyeshadow Palette',
        'price'=>39.99,
        'img'=>'images/Silk Eyeshadow Palette.png',
        'description'=>'12 luxurious shades with smooth blendable pigments.',
        'created_at'=>'2025-11-18 15:00:00',
        'type'=>'cosmetics'
    ],
    [
        'id'=>4,
        'name'=>'Hydrating Face Cream',
        'price'=>24.99,
        'img'=>'images/Hydrating Face Cream.png',
        'description'=>'Lightweight cream that deeply hydrates and rejuvenates skin.',
        'created_at'=>'2025-11-22 09:30:00',
        'type'=>'skincare'
    ],
    [
        'id'=>5,
        'name'=>'Velvet Blush',
        'price'=>14.99,
        'img'=>'images/Velvet Blush.png',
        'description'=>'Soft, buildable blush that adds a natural glow to cheeks.',
        'created_at'=>'2025-11-23 11:00:00',
        'type'=>'cosmetics'
    ],
    [
        'id'=>6,
        'name'=>'Anti-Aging Serum',
        'price'=>45.99,
        'img'=>'images/Anti-Aging Serum.png',
        'description'=>'Powerful serum that reduces fine lines and wrinkles.',
        'created_at'=>'2025-11-21 14:00:00',
        'type'=>'skincare'
    ],
    [
        'id'=>7,
        'name'=>'Makeup Brushes Set',
        'price'=>49.99,
        'img'=>'images/Makeup Brushes Set.png',
        'description'=>'8 professional brushes for flawless application.',
        'created_at'=>'2025-11-19 16:00:00',
        'type'=>'tools'
    ],
    [
        'id'=>8,
        'name'=>'Jade Face Roller',
        'price'=>32.99,
        'img'=>'images/Jade Face Roller.png',
        'description'=>'Jade roller for facial massage and depuffing.',
        'created_at'=>'2025-11-17 13:00:00',
        'type'=>'tools'
    ],
    [
        'id'=>9,
        'name'=>'Vitamin C Cream',
        'price'=>41.99,
        'img'=>'images/Vitamin C Cream.png',
        'description'=>'Brightening cream with stabilized vitamin C.',
        'created_at'=>'2025-11-16 11:00:00',
        'type'=>'skincare'
    ],
    [
        'id'=>10,
        'name'=>'Luxury Brush Set',
        'price'=>89.99,
        'img'=>'images/Luxury Brush Set.png',
        'description'=>'12 premium synthetic brushes with rose gold handles.',
        'created_at'=>'2025-11-15 10:00:00',
        'type'=>'tools'
    ]
];

// Define collection types with local images
$collection_types = [
    'cosmetics' => [
        'name' => 'Premium Luxury Cosmetics',
        'badge' => 'PREMIUM',
        'description' => 'Elevate your beauty routine with our premium makeup collection',
        'image' => 'images/Luxury Set.png',
        'alt' => 'Luxury makeup products on display'
    ],
    'skincare' => [
        'name' => 'Elite Skincare Elixirs', 
        'badge' => 'ELITE',
        'description' => 'Nourish and rejuvenate your skin with our scientifically advanced formulations',
        'image' => 'images/',
        'alt' => 'Skincare products with natural ingredients'
    ],
    'tools' => [
        'name' => 'Professional Artisan Tools',
        'badge' => 'PROFESSIONAL',
        'description' => 'Professional-grade tools crafted for precision and perfect application',
        'image' => 'images/',
        'alt' => 'Professional makeup brushes and tools'
    ]
];
// ---------------- HANDLE FILTERING ----------------
$filtered_products = $products;
$active_filter = '';

if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    if (array_key_exists($filter, $collection_types)) {
        $filtered_products = array_filter($products, fn($p) => $p['type'] == $filter);
        $active_filter = $filter;
    }
}

// ---------------- HANDLE SORTING ----------------
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_asc')
        usort($filtered_products, fn($a, $b) => $a['price'] <=> $b['price']);
    elseif ($_GET['sort'] == 'price_desc')
        usort($filtered_products, fn($a, $b) => $b['price'] <=> $a['price']);
    elseif ($_GET['sort'] == 'newest')
        usort($filtered_products, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
}
?>
<style>
/* === MODERN VARIABLES === */
:root {
    --primary-gold: #D4AF37;
    --dark-charcoal: #2C2C2C;
    --soft-cream: #FAF7F2;
    --warm-gray: #E8E5DE;
    --deep-burgundy: #8B4513;
    --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --shadow-glow: 0 8px 32px rgba(212, 175, 55, 0.15);
}

/* === COLLECTION FILTERS === */
.collection-filters {
    background: var(--soft-cream);
    padding: 30px 0;
    border-bottom: 1px solid var(--warm-gray);
}

.filters-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
}

.filters-title {
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--dark-charcoal);
    margin-bottom: 20px;
    letter-spacing: 1px;
}

.filters-grid {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-btn {
    background: white;
    border: 2px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
    letter-spacing: 1px;
    text-decoration: none;
    display: inline-block;
}

.filter-btn:hover {
    border-color: var(--primary-gold);
    color: var(--primary-gold);
    transform: translateY(-2px);
}

.filter-btn.active {
    background: var(--primary-gold);
    border-color: var(--primary-gold);
    color: white;
}

.filter-badge {
    background: var(--dark-charcoal);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.7rem;
    margin-left: 8px;
    font-weight: 600;
}

/* === CURATED COLLECTIONS === */
.curated-collections {
    background: var(--soft-cream);
    padding: 80px 0;
}

.collections-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    padding: 0 20px;
}

.collection-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    border: 1px solid var(--warm-gray);
}

.collection-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-glow);
}

.collection-image {
    height: 250px;
    background: var(--soft-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    position: relative;
}

.collection-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: var(--transition);
}

.collection-card:hover .collection-image img {
    transform: scale(1.05);
}

.collection-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--primary-gold);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.collection-info {
    padding: 30px;
    text-align: center;
}

.collection-name {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 15px;
}

.collection-desc {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.collection-btn {
    background: var(--dark-charcoal);
    color: white;
    padding: 12px 25px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    letter-spacing: 1px;
    transition: var(--transition);
    display: inline-block;
}

.collection-btn:hover {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    transform: translateY(-2px);
}

/* === PRODUCTS HERO SECTION === */
.products-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 80px 0 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.products-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    100% { transform: translateY(-100px) rotate(360deg); }
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

.hero-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: var(--primary-gold);
}

.hero-subtitle {
    font-size: 1.1rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

/* === FILTERS & SORTING === */
.products-controls {
    background: var(--soft-cream);
    padding: 30px 0;
    border-bottom: 1px solid var(--warm-gray);
}

.controls-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    padding: 0 20px;
}

.sort-form {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sort-form label {
    font-weight: 500;
    color: var(--dark-charcoal);
    letter-spacing: 1px;
}

.sort-form select {
    padding: 12px 20px;
    border: 1px solid var(--warm-gray);
    background: white;
    border-radius: 8px;
    font-size: 0.9rem;
    min-width: 200px;
    transition: var(--transition);
    cursor: pointer;
}

.sort-form select:focus {
    outline: none;
    border-color: var(--primary-gold);
    box-shadow: var(--shadow-glow);
}

.products-stats {
    color: var(--dark-charcoal);
    font-weight: 500;
    letter-spacing: 1px;
}

.active-filter-display {
    background: var(--primary-gold);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.clear-filter {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
}

.clear-filter:hover {
    background: var(--dark-charcoal);
    color: white;
}

/* === PRODUCT GRID === */
.products-section {
    background: white;
    padding: 60px 0;
}

.product-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    padding: 0 20px;
}

.product-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    border: 1px solid var(--warm-gray);
    cursor: pointer;
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    opacity: 0;
    transition: var(--transition);
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-glow);
    border-color: var(--primary-gold);
}

.product-card:hover::before {
    opacity: 1;
}

.product-image {
    position: relative;
    overflow: hidden;
    background: var(--soft-cream);
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: var(--transition);
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--primary-gold);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.product-info {
    padding: 25px;
    position: relative;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 8px;
    line-height: 1.4;
}

.product-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-gold);
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Wishlist Form Styles */
.wishlist-form {
    display: inline-block;
}

.wishlist-form button.wishlist-btn {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 10px 12px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1.1rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wishlist-form button.wishlist-btn:hover {
    background: var(--primary-gold);
    color: white;
    border-color: var(--primary-gold);
    transform: scale(1.1);
}

.quick-view-btn {
    background: var(--dark-charcoal);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 1px;
}

.quick-view-btn:hover {
    background: var(--primary-gold);
    transform: translateY(-2px);
}

/* === MODAL STYLES === */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    position: relative;
    animation: modalSlideIn 0.4s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.close-modal {
    position: absolute;
    right: 25px;
    top: 25px;
    font-size: 2rem;
    color: var(--dark-charcoal);
    cursor: pointer;
    z-index: 10;
    transition: var(--transition);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    background: var(--warm-gray);
    color: var(--primary-gold);
}

.modal-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.modal-image {
    background: var(--soft-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    border-radius: 20px 0 0 20px;
}

.modal-image img {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.modal-info {
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.modal-price {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-gold);
    margin: 10px 0;
}

.modal-desc {
    color: #666;
    line-height: 1.6;
    margin: 20px 0;
    font-size: 0.95rem;
}

.modal-addcart-btn {
    background: var(--primary-gold);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    margin-bottom: 15px;
    letter-spacing: 1px;
}

.modal-addcart-btn:hover {
    background: var(--dark-charcoal);
    transform: translateY(-2px);
}

.modal-wishlist-btn {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
}

.modal-wishlist-btn:hover {
    border-color: var(--primary-gold);
    color: var(--primary-gold);
}

/* === RESPONSIVE DESIGN === */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .controls-container {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .modal-body {
        grid-template-columns: 1fr;
    }
    
    .modal-image {
        border-radius: 20px 20px 0 0;
        padding: 30px;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .collections-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-grid {
        flex-direction: column;
        align-items: center;
    }
    
    .filter-btn {
        width: 200px;
        text-align: center;
    }
}

/* === TOAST NOTIFICATIONS === */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1001;
}

.toast {
    background: var(--primary-gold);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    margin-bottom: 10px;
    animation: slideInRight 0.3s ease-out;
    box-shadow: var(--shadow-glow);
    font-weight: 500;
}

.toast.error {
    background: #dc3545;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>

<!-- HERO SECTION -->
<section class="products-hero">
    <div class="hero-content">
        <h1 class="hero-title shimmer-gold">BEAUTY COLLECTION</h1>
        <p class="hero-subtitle">CURATED LUXURY FOR THE MODERN SOUL</p>
    </div>
</section>

<!-- COLLECTION FILTERS -->
<section class="collection-filters">
    <div class="filters-container">
        <div class="filters-title">FILTER BY COLLECTION</div>
        <div class="filters-grid">
            <a href="?filter=cosmetics" class="filter-btn <?= $active_filter == 'cosmetics' ? 'active' : '' ?>">
                Premium Cosmetics
                <span class="filter-badge"><?= count(array_filter($products, fn($p) => $p['type'] == 'cosmetics')) ?></span>
            </a>
            <a href="?filter=skincare" class="filter-btn <?= $active_filter == 'skincare' ? 'active' : '' ?>">
                Skincare Elixirs
                <span class="filter-badge"><?= count(array_filter($products, fn($p) => $p['type'] == 'skincare')) ?></span>
            </a>
            <a href="?filter=tools" class="filter-btn <?= $active_filter == 'tools' ? 'active' : '' ?>">
                Artisan Tools
                <span class="filter-badge"><?= count(array_filter($products, fn($p) => $p['type'] == 'tools')) ?></span>
            </a>
            <?php if ($active_filter): ?>
                <a href="products.php" class="clear-filter">Clear Filter</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CURATED COLLECTIONS -->
<section class="curated-collections">
    <h2 class="section-title shimmer-gold" style="text-align: center; margin-bottom: 50px;">CURATED COLLECTIONS</h2>
    <div class="collections-grid">
        <?php foreach($collection_types as $type => $collection): ?>
        <div class="collection-card">
            <div class="collection-image">
                <img src="images/<?= $type == 'cosmetics' ? 'lipstick' : ($type == 'skincare' ? 'facecream' : 'brushes-set') ?>.jpg" alt="<?= $collection['name'] ?>">
                <div class="collection-badge"><?= $collection['badge'] ?></div>
            </div>
            <div class="collection-info">
                <h3 class="collection-name"><?= $collection['name'] ?></h3>
                <p class="collection-desc"><?= $collection['description'] ?></p>
                <a href="?filter=<?= $type ?>" class="collection-btn">Explore Collection</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CONTROLS SECTION -->
<section class="products-controls">
    <div class="controls-container">
        <div class="products-stats">
            <?php if ($active_filter): ?>
                <span class="active-filter-display">
                    <?= $collection_types[$active_filter]['name'] ?> 
                    (<?= count($filtered_products) ?> products)
                </span>
            <?php else: ?>
                <?= count($filtered_products) ?> Premium Products
            <?php endif; ?>
        </div>
        <form method="GET" class="sort-form">
            <?php if ($active_filter): ?>
                <input type="hidden" name="filter" value="<?= $active_filter ?>">
            <?php endif; ?>
            <label for="sort">REFINE COLLECTION</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="">CURATE SELECTION</option>
                <option value="price_asc" <?= (($_GET['sort'] ?? '') == 'price_asc') ? 'selected':'' ?>>PRICE: LOW TO HIGH</option>
                <option value="price_desc" <?= (($_GET['sort'] ?? '') == 'price_desc') ? 'selected':'' ?>>PRICE: HIGH TO LOW</option>
                <option value="newest" <?= (($_GET['sort'] ?? '') == 'newest') ? 'selected':'' ?>>NEWEST ARRIVALS</option>
            </select>
        </form>
    </div>
</section>

<!-- PRODUCTS GRID -->
<section class="products-section">
    <div class="product-grid">
        <?php foreach($filtered_products as $p): 
            $collection_info = $collection_types[$p['type']];
        ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" loading="lazy">
                <div class="product-badge"><?= $collection_info['badge'] ?></div>
            </div>
            <div class="product-info">
                <h3 class="product-name"><?= $p['name'] ?></h3>
                <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
                <div class="product-actions">
                    <!-- WISHLIST FORM -->
                    <form method="POST" action="products.php" class="wishlist-form">
                        <input type="hidden" name="add_to_wishlist" value="1">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="name" value="<?= $p['name'] ?>">
                        <input type="hidden" name="price" value="<?= $p['price'] ?>">
                        <input type="hidden" name="img" value="<?= $p['img'] ?>">
                        <button type="submit" class="wishlist-btn" title="Add to Wishlist" onclick="event.stopPropagation()">
                            ♡
                        </button>
                    </form>
                    
                    <button class="quick-view-btn" onclick="openModal(event, <?= $p['id'] ?>,'<?= $p['name'] ?>','<?= $p['price'] ?>','<?= $p['img'] ?>','<?= $p['description'] ?>')">
                        QUICK VIEW
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PRODUCT MODAL -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-body">
            <div class="modal-image">
                <img id="modal-img" src="" alt="">
            </div>

            <div class="modal-info">
                <h2 id="modal-name" class="shimmer-gold"></h2>
                <p id="modal-price" class="modal-price"></p>
                <p id="modal-desc" class="modal-desc"></p>

                <!-- ADD TO COLLECTION (CART) -->
                <form id="modalCartForm" method="POST" action="products.php">
                    <input type="hidden" name="add_to_cart" value="1">
                    <input type="hidden" name="id" id="modal-id">
                    <input type="hidden" name="name" id="modal-name-input">
                    <input type="hidden" name="price" id="modal-price-input">
                    <input type="hidden" name="img" id="modal-img-input">

                    <button type="submit" class="modal-addcart-btn">
                        ADD TO COLLECTION
                    </button>
                </form>

                <!-- ADD TO WISHLIST -->
                <form id="modalWishlistForm" method="POST" action="products.php">
                    <input type="hidden" name="add_to_wishlist" value="1">
                    <input type="hidden" name="id" id="modal-wishlist-id">
                    <input type="hidden" name="name" id="modal-wishlist-name">
                    <input type="hidden" name="price" id="modal-wishlist-price">
                    <input type="hidden" name="img" id="modal-wishlist-img">

                    <button type="submit" class="modal-wishlist-btn">
                        ♡ ADD TO WISH LIST
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ENHANCED SCRIPT -->
<script>
let modal = document.getElementById("productModal");

function openModal(event, id, name, price, img, desc) {
    event.stopPropagation();

    modal.style.display = "block";
    document.body.style.overflow = "hidden";

    const numericPrice = parseFloat(price);

    // Fill modal
    document.getElementById("modal-img").src = img;
    document.getElementById("modal-name").innerText = name;
    document.getElementById("modal-price").innerText = "$" + numericPrice.toFixed(2);
    document.getElementById("modal-desc").innerText = desc;

    // CART HIDDEN FIELDS
    document.getElementById("modal-id").value = id;
    document.getElementById("modal-name-input").value = name;
    document.getElementById("modal-price-input").value = numericPrice;
    document.getElementById("modal-img-input").value = img;

    // WISHLIST HIDDEN FIELDS
    document.getElementById("modal-wishlist-id").value = id;
    document.getElementById("modal-wishlist-name").value = name;
    document.getElementById("modal-wishlist-price").value = numericPrice;
    document.getElementById("modal-wishlist-img").value = img;
}

function closeModal() {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
}

document.querySelector(".close-modal").onclick = closeModal;

window.onclick = e => {
    if (e.target == modal) closeModal();
};

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

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
</script>

<!-- TOAST NOTIFICATIONS -->
<div class="toast-container">
    <?php if(isset($_SESSION['wishlist_success'])): ?>
        <div class="toast" id="successToast">
            <?= $_SESSION['wishlist_success'] ?>
        </div>
        <?php unset($_SESSION['wishlist_success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['wishlist_error'])): ?>
        <div class="toast error" id="errorToast">
            <?= $_SESSION['wishlist_error'] ?>
        </div>
        <?php unset($_SESSION['wishlist_error']); ?>
    <?php endif; ?>
</div>

<?php