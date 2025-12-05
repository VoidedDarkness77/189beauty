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

// Get current user data
$stmt = $conn->prepare("SELECT username, email, first_name, last_name, phone, address, city, state, zip_code, date_of_birth FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ?, date_of_birth = ? WHERE id = ?");
$stmt->bind_param("sssssssssi", $first_name, $last_name, $email, $phone, $address, $city, $state, $zip_code, $date_of_birth, $user_id);
            
            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
                // Refresh user data
                $stmt = $conn->prepare("SELECT username, email, first_name, last_name, phone, address, city, state, zip_code, date_of_birth FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - 189 Beauty</title>
    <style>
        :root {
            --primary-gold: #D4AF37;
            --dark-charcoal: #2C2C2C;
            --soft-cream: #FAF7F2;
            --warm-gray: #E8E5DE;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow-glow: 0 8px 32px rgba(212, 175, 55, 0.15);
        }

        .profile-hero {
            background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
            padding: 80px 0 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
            animation: float 20s infinite linear;
        }

        .profile-title {
            font-size: 3rem;
            font-weight: 300;
            letter-spacing: 3px;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            display: inline-block;
        }

        .profile-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: var(--primary-gold);
        }

        .profile-subtitle {
            font-size: 1.1rem;
            color: var(--warm-gray);
            font-weight: 300;
            letter-spacing: 2px;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
            background: var(--soft-cream);
            min-height: 80vh;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
        }

        .profile-sidebar {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--warm-gray);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .user-avatar-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-gold), #f9f295);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--dark-charcoal);
            font-weight: bold;
        }

        .user-info-sidebar {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--warm-gray);
        }

        .user-name-sidebar {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 5px;
        }

        .user-email-sidebar {
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
        }

        .nav-item:hover,
        .nav-item.active {
            background: var(--primary-gold);
            color: var(--dark-charcoal);
        }

        .profile-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            border: 1px solid var(--warm-gray);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--warm-gray);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary-gold);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-charcoal);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--warm-gray);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .btn {
            background: var(--primary-gold);
            color: var(--dark-charcoal);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            background: var(--dark-charcoal);
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--warm-gray);
            color: var(--dark-charcoal);
        }

        .btn-secondary:hover {
            background: var(--dark-charcoal);
            color: white;
            border-color: var(--dark-charcoal);
        }

        .alert {
            padding: 15px 20px;
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

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--soft-cream);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--warm-gray);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-gold);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.8rem;
            font-weight: 500;
        }

        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .profile-sidebar {
                position: static;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-title {
                font-size: 2.5rem;
            }
            
            .profile-content {
                padding: 25px;
            }
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- PROFILE HERO -->
    <section class="profile-hero">
        <div class="hero-content">
            <h1 class="profile-title">PROFILE SETTINGS</h1>
            <p class="profile-subtitle">MANAGE YOUR PERSONAL INFORMATION</p>
        </div>
    </section>

    <!-- PROFILE CONTAINER -->
    <section class="profile-container">
        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="user-info-sidebar">
                    <div class="user-avatar-large">
                        <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <h3 class="user-name-sidebar"><?= htmlspecialchars($user['first_name'] && $user['last_name'] ? $user['first_name'] . ' ' . $user['last_name'] : ($user['username'] ?? 'User')) ?></h3>
                    <p class="user-email-sidebar"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>

                <nav class="sidebar-nav">
                    <a href="account.php" class="nav-item">
                        <i>📊</i> Account Dashboard
                    </a>
                    <a href="profile.php" class="nav-item active">
                        <i>👤</i> Profile Settings
                    </a>
                    <a href="change-password.php" class="nav-item">
                        <i>🔒</i> Change Password
                    </a>
                    <a href="orders.php" class="nav-item">
                        <i>📦</i> My Orders
                    </a>
                    <a href="wishlist.php" class="nav-item">
                        <i>❤️</i> Wishlist
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i>🚪</i> Sign Out
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <h2 class="section-title">Personal Information</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Stats -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">Member</div>
                        <div class="stat-label">Status</div>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-input" 
                                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-input" 
                                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-input" required
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-input" 
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address" class="form-input" 
                                   value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-input" 
                                   value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="state" class="form-label">State</label>
                            <input type="text" name="state" id="state" class="form-input" 
                                   value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="zip_code" class="form-label">ZIP Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-input" 
                                   value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-input" 
                                   value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn">Update Profile</button>
                        <a href="account.php" class="btn btn-secondary">Back to Account</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add loading state to form submission
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Updating...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>