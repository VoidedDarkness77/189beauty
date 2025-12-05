<?php
include 'includes/header.php';
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        try {
            // First, verify the current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = "User not found.";
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = "Current password is incorrect.";
            } else {
                // Update to new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Luxury Beauty</title>
    <style>
        body {
            background: var(--soft-cream);
            min-height: 100vh;
        }
        
        .password-container {
            max-width: 500px;
            margin: 80px auto 50px;
            padding: 0 20px;
        }
        
        .password-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid var(--warm-gray);
        }
        
        .password-title {
            font-size: 2rem;
            font-weight: 300;
            color: var(--dark-charcoal);
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 1px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-charcoal);
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--warm-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .submit-btn {
            background: var(--primary-gold);
            color: var(--dark-charcoal);
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 1px;
            transition: var(--transition);
            width: 100%;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background: var(--dark-charcoal);
            color: white;
            transform: translateY(-2px);
        }
        
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--dark-charcoal);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .cancel-link:hover {
            color: var(--primary-gold);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            color: #666;
            border: 1px solid #eee;
        }
        
        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: var(--dark-charcoal);
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .password-container {
                margin: 60px auto 30px;
            }
            
            .password-card {
                padding: 30px 20px;
            }
            
            .password-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<!-- Password Change Section -->
<section class="password-container">
    <div class="password-card">
        <h1 class="password-title">Change Password</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="password-requirements">
            <h4>Password Requirements:</h4>
            <ul>
                <li>At least 6 characters long</li>
                <li>Include uppercase and lowercase letters</li>
                <li>Include numbers for better security</li>
                <li>Avoid common words or patterns</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Current Password *</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">New Password *</label>
                <input type="password" name="new_password" class="form-input" required minlength="6">
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm New Password *</label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
            </div>
            
            <button type="submit" class="submit-btn">
                Update Password
            </button>
            
            <a href="account.php" class="cancel-link">
                ← Back to Account
            </a>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    
    form.addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        // Basic validation
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            newPasswordInput.focus();
            return;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match. Please re-enter them.');
            confirmPasswordInput.focus();
            return;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>