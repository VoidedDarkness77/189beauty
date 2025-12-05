<?php
$pages = [
    'orders.php' => 'MY ORDERS',
    'addresses.php' => 'ADDRESS BOOK', 
    'payment-methods.php' => 'PAYMENT METHODS',
    'change-password.php' => 'CHANGE PASSWORD',
    'notification-settings.php' => 'NOTIFICATION SETTINGS'
];

foreach ($pages as $filename => $title) {
    if (!file_exists($filename)) {
        $content = "<?php
include 'includes/header.php';
if (!isset(\$_SESSION['logged_in'])) { header('Location: login.php'); exit; }
?>

<section class='account-hero'>
    <div class='hero-content'>
        <h1 class='account-title'>$title</h1>
        <p class='account-subtitle'>MANAGE YOUR ACCOUNT PREFERENCES</p>
    </div>
</section>

<section class='account-section'>
    <div class='account-container'>
        <?php include 'includes/account-sidebar.php'; ?>
        <div class='account-main'>
            <div class='account-card full-width'>
                <div class='card-header'>
                    <h3 class='card-title'>$title</h3>
                    <a href='account.php' class='card-action'>← Back to Dashboard</a>
                </div>
                <p style='text-align: center; color: #666; padding: 40px;'>This page is under development.</p>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>";
        
        file_put_contents($filename, $content);
        echo "Created: $filename<br>";
    } else {
        echo "Already exists: $filename<br>";
    }
}

echo "All account pages created!";
?>