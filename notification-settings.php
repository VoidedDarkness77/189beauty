<?php
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'includes/db.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $order_updates = isset($_POST['order_updates']) ? 1 : 0;
    $promotional_emails = isset($_POST['promotional_emails']) ? 1 : 0;
    $product_updates = isset($_POST['product_updates']) ? 1 : 0;
    
    $success = "Notification settings updated successfully!";
    
    // In a real application, you would save these to the database
    /*
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET email_notifications = ?, order_updates = ?, promotional_emails = ?, product_updates = ? WHERE id = ?");
    $stmt->bind_param("iiiii", $email_notifications, $order_updates, $promotional_emails, $product_updates, $user_id);
    $stmt->execute();
    $stmt->close();
    */
}
?>

<section class="account-hero">
    <div class="hero-content">
        <h1 class="account-title">NOTIFICATION SETTINGS</h1>
        <p class="account-subtitle">MANAGE YOUR COMMUNICATION PREFERENCES</p>
    </div>
</section>

<section class="account-section">
    <div class="account-container">
        <?php include 'includes/account-sidebar.php'; ?>
        <div class="account-main">
            <div class="account-card full-width">
                <div class="card-header">
                    <h3 class="card-title">Notification Preferences</h3>
                    <a href="account.php" class="card-action">← Back to Dashboard</a>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div style="margin-bottom: 30px;">
                        <h4 style="margin-bottom: 15px; color: var(--dark-charcoal);">Email Notifications</h4>
                        
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; padding: 12px; background: var(--soft-cream); border-radius: 8px;">
                            <input type="checkbox" name="email_notifications" checked>
                            <div>
                                <div style="font-weight: 500;">Email notifications</div>
                                <div style="font-size: 0.8rem; color: #666;">Receive important account notifications via email</div>
                            </div>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; padding: 12px; background: var(--soft-cream); border-radius: 8px;">
                            <input type="checkbox" name="order_updates" checked>
                            <div>
                                <div style="font-weight: 500;">Order updates</div>
                                <div style="font-size: 0.8rem; color: #666;">Get notified about your order status and shipping</div>
                            </div>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; padding: 12px; background: var(--soft-cream); border-radius: 8px;">
                            <input type="checkbox" name="promotional_emails">
                            <div>
                                <div style="font-weight: 500;">Promotional emails</div>
                                <div style="font-size: 0.8rem; color: #666;">Receive special offers, discounts, and beauty tips</div>
                            </div>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--soft-cream); border-radius: 8px;">
                            <input type="checkbox" name="product_updates" checked>
                            <div>
                                <div style="font-weight: 500;">New product updates</div>
                                <div style="font-size: 0.8rem; color: #666;">Be the first to know about new beauty products</div>
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <h4 style="margin-bottom: 15px; color: var(--dark-charcoal);">SMS Notifications</h4>
                        <div style="padding: 15px; background: var(--soft-cream); border-radius: 8px;">
                            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                SMS notifications are currently unavailable. We're working on bringing this feature to you soon!
                            </p>
                        </div>
                    </div>
                    
                    <button type="submit" class="action-btn primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            if (this.checked) {
                label.style.background = 'rgba(212, 175, 55, 0.1)';
                label.style.border = '1px solid var(--primary-gold)';
            } else {
                label.style.background = 'var(--soft-cream)';
                label.style.border = 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>