<?php
session_start();

// Clear the cart
$_SESSION['cart'] = [];

include 'includes/header.php';
?>

<div style="max-width:600px;margin:50px auto;text-align:center;">
    <h2 class="shimmer-gold">Order Successful 🎉</h2>
    <p>Thank you for your purchase!</p>
    <a href="index.php" style="display:inline-block;margin-top:20px;padding:12px 18px;background:gold;color:#111;border-radius:10px;text-decoration:none;font-weight:bold;">Return Home</a>
</div>

<?php include 'includes/footer.php'; ?>
