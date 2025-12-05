<?php  
session_start();


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Don't include session_manager here to avoid multiple inclusions
// It will be included in individual pages where needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>189 Beauty</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="fade-in">

<header>
    <nav class="navbar">
        <div class="logo shimmer-gold">189 Beauty</div>

        <ul class="nav-links">
    <li><a href="index.php">HOME</a></li>
    <li><a href="products.php">PRODUCTS</a></li>
    <li><a href="services.php">SERVICES</a></li>
    <li><a href="about.php">ABOUT</a></li>
    <li><a href="contact.php">CONTACT</a></li>

    <a href="wishlist.php" class="wishlist-link">♡ Wishlist 
    <?php if(isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0): ?>
        <span class="wishlist-count">(<?= count($_SESSION['wishlist']) ?>)</span>
    <?php endif; ?>
</a>
    <li><a href="cart.php">CART 
        (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)
    </a></li>


    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    <li><a href="account.php">My Account</a></li>
    <li><a href="logout.php">Logout</a></li>
<?php else: ?>
    <li><a href="login.php">Login</a></li>
    <li><a href="register.php">Register</a></li>
<?php endif; ?>
</ul>

    </nav>
</header>

<!-- Floating CTA -->
<a href="products.php" class="floating-cta">SHOP</a>
