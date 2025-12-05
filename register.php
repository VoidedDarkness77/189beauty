<?php

include 'includes/header.php';

// Initialize variables
$username = $email = '';
$errors = [];
$success = false;

// Password requirements
$password_requirements = [
    'min_length' => 8,
    'needs_uppercase' => true,
    'needs_lowercase' => true,
    'needs_number' => true,
    'needs_special' => false
];

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';
    
    // Sanitize and validate inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Username must be at least 3 characters";
    } elseif (strlen($username) > 30) {
        $errors['username'] = "Username must be less than 30 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address";
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } else {
        // Check password strength
        if (strlen($password) < $password_requirements['min_length']) {
            $errors['password'] = "Password must be at least {$password_requirements['min_length']} characters";
        }
        if ($password_requirements['needs_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors['password'] = "Password must contain at least one uppercase letter";
        }
        if ($password_requirements['needs_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors['password'] = "Password must contain at least one lowercase letter";
        }
        if ($password_requirements['needs_number'] && !preg_match('/[0-9]/', $password)) {
            $errors['password'] = "Password must contain at least one number";
        }
        if ($password_requirements['needs_special'] && !preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors['password'] = "Password must contain at least one special character";
        }
    }
    
    // Confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    // Validate terms
    if (!$terms) {
        $errors['terms'] = "You must agree to the Terms of Service";
    }
    
    // Check if username/email already exists (only if no validation errors)
    if (empty($errors)) {
        // Check username availability
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['username'] = "Username is already taken";
        }
        $stmt->close();
        
        // Check email availability
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['email'] = "Email is already registered";
        }
        $stmt->close();
    }
    
    // If no errors, create account
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if table has verification columns
        $has_verification = false;
        try {
            $check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_token'");
            if ($check_columns) {
                $has_verification = $check_columns->num_rows > 0;
            }
        } catch (Exception $e) {
            // Table doesn't have verification columns
            $has_verification = false;
        }
        
        if ($has_verification) {
            // Table has verification columns
            $verification_token = bin2hex(random_bytes(32));
            $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, verification_token, verification_expires, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $username, $email, $hashedPassword, $verification_token, $verification_expires);
        } else {
            // Table doesn't have verification columns - use basic insert
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
        }
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();
            
            // Store user data in session
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            
            if ($has_verification) {
                $_SESSION['verification_token'] = $verification_token;
            }
            
            // Redirect to success page or dashboard
            header("Location: index.php");
            exit();
        } else {
            $errors['general'] = "Registration failed. Please try again. Error: " . $conn->error;
        }
    }
}
?>

<!-- HERO SECTION -->
<section class="register-hero">
    <div class="hero-content">
        <h1 class="hero-title">Join <span class="highlight">189 Beauty</span></h1>
        <p class="hero-subtitle">Create your account to unlock personalized beauty recommendations and exclusive offers</p>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
        </svg>
    </div>
</section>

<!-- REGISTRATION SECTION -->
<section class="register-section">
    <div class="container">
        <div class="register-grid">
            <!-- Registration Form -->
            <div class="register-form-container">
                <div class="form-header">
                    <h2 class="form-title">Create Your Account</h2>
                    <p class="form-subtitle">Join our community of beauty enthusiasts</p>
                </div>
                
                <?php if(!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="icon-error"></i> <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registrationForm" class="register-form">
                    <!-- Username Field -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            Username <span class="required">*</span>
                            <span class="hint">(3-30 characters, letters, numbers, underscores only)</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="icon-user"></i>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-input <?php echo isset($errors['username']) ? 'error' : ''; ?>"
                                   value="<?php echo htmlspecialchars($username); ?>"
                                   placeholder="Choose your username"
                                   required
                                   minlength="3"
                                   maxlength="30"
                                   pattern="[a-zA-Z0-9_]+">
                        </div>
                        <?php if(isset($errors['username'])): ?>
                            <div class="error-message">
                                <i class="icon-error"></i> <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="hint-message" id="usernameHint"></div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email Address <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="icon-email"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   placeholder="your@email.com"
                                   required>
                        </div>
                        <?php if(isset($errors['email'])): ?>
                            <div class="error-message">
                                <i class="icon-error"></i> <?php echo htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="hint-message" id="emailHint"></div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password <span class="required">*</span>
                            <span class="hint">(At least <?php echo $password_requirements['min_length']; ?> characters)</span>
                        </label>
                        <div class="input-with-icon password-field">
                            <i class="icon-lock"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                                   placeholder="Create a strong password"
                                   required
                                   minlength="<?php echo $password_requirements['min_length']; ?>">
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="icon-eye"></i>
                            </button>
                        </div>
                        <?php if(isset($errors['password'])): ?>
                            <div class="error-message">
                                <i class="icon-error"></i> <?php echo htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Password Strength Meter -->
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <div class="strength-labels">
                                <span class="strength-label" data-strength="0">Very Weak</span>
                                <span class="strength-label" data-strength="1">Weak</span>
                                <span class="strength-label" data-strength="2">Fair</span>
                                <span class="strength-label" data-strength="3">Good</span>
                                <span class="strength-label" data-strength="4">Strong</span>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="requirements-list">
                            <div class="requirement" id="reqLength">
                                <i class="icon-check"></i> At least <?php echo $password_requirements['min_length']; ?> characters
                            </div>
                            <?php if($password_requirements['needs_uppercase']): ?>
                                <div class="requirement" id="reqUppercase">
                                    <i class="icon-check"></i> One uppercase letter
                                </div>
                            <?php endif; ?>
                            <?php if($password_requirements['needs_lowercase']): ?>
                                <div class="requirement" id="reqLowercase">
                                    <i class="icon-check"></i> One lowercase letter
                                </div>
                            <?php endif; ?>
                            <?php if($password_requirements['needs_number']): ?>
                                <div class="requirement" id="reqNumber">
                                    <i class="icon-check"></i> One number
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            Confirm Password <span class="required">*</span>
                        </label>
                        <div class="input-with-icon password-field">
                            <i class="icon-lock"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                   placeholder="Re-enter your password"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="icon-eye"></i>
                            </button>
                        </div>
                        <?php if(isset($errors['confirm_password'])): ?>
                            <div class="error-message">
                                <i class="icon-error"></i> <?php echo htmlspecialchars($errors['confirm_password']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="hint-message" id="confirmHint"></div>
                    </div>
                    
                    <!-- Terms Agreement -->
                    <div class="form-group terms-group">
                        <div class="checkbox-container">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   class="checkbox-input"
                                   <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>
                                   required>
                            <label for="terms" class="checkbox-label">
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        <?php if(isset($errors['terms'])): ?>
                            <div class="error-message">
                                <i class="icon-error"></i> <?php echo htmlspecialchars($errors['terms']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-spinner hidden">
                            <i class="icon-spinner"></i>
                        </span>
                    </button>
                </form>
                
                <!-- Login Link -->
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </div>
            
            <!-- Benefits Sidebar -->
            <div class="benefits-sidebar">
                <div class="benefits-card">
                    <h3 class="benefits-title">Members Enjoy</h3>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="icon-percent"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Exclusive Discounts</h4>
                            <p>Members-only sales and early access to promotions</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="icon-heart"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Personalized Recommendations</h4>
                            <p>AI-powered beauty suggestions based on your preferences</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="icon-star"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Rewards Program</h4>
                            <p>Earn points with every purchase, redeem for free products</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="icon-truck"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Free Shipping</h4>
                            <p>Free standard shipping on all orders over $50</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="icon-calendar"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Priority Booking</h4>
                            <p>Early access to beauty consultations and appointments</p>
                        </div>
                    </div>
                    
                    <div class="benefits-footer">
                        <div class="trust-badge">
                            <i class="icon-shield"></i>
                            <span>100% Secure Registration</span>
                        </div>
                        <div class="trust-badge">
                            <i class="icon-lock"></i>
                            <span>Your Data is Protected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* === REGISTRATION PAGE STYLES === */
    :root {
        --primary-gold: #D4AF37;
        --dark-charcoal: #2C2C2C;
        --soft-cream: #FAF7F0;
        --warm-gray: #E8E2D9;
        --success-green: #28a745;
        --error-red: #dc3545;
        --warning-orange: #ffc107;
        --light-gold: rgba(212, 175, 55, 0.1);
    }
    
    /* HERO SECTION */
    .register-hero {
        background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
        padding: 120px 0 60px;
        color: white;
        position: relative;
        text-align: center;
    }
    
    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 300;
        margin-bottom: 1rem;
        letter-spacing: 1px;
    }
    
    .hero-title .highlight {
        color: var(--primary-gold);
        font-weight: 600;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 300;
        line-height: 1.6;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .hero-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }
    
    .hero-wave svg {
        position: relative;
        display: block;
        width: calc(100% + 1.3px);
        height: 60px;
        fill: var(--soft-cream);
    }
    
    /* REGISTRATION SECTION */
    .register-section {
        background: var(--soft-cream);
        padding: 80px 0;
        min-height: calc(100vh - 300px);
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .register-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 50px;
    }
    
    /* FORM CONTAINER */
    .register-form-container {
        background: white;
        border-radius: 20px;
        padding: 50px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--warm-gray);
    }
    
    .form-header {
        margin-bottom: 40px;
        text-align: center;
    }
    
    .form-title {
        font-size: 2.2rem;
        color: var(--dark-charcoal);
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .form-subtitle {
        color: #666;
        font-size: 1.1rem;
    }
    
    /* FORM ELEMENTS */
    .form-group {
        margin-bottom: 30px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark-charcoal);
        font-size: 1rem;
    }
    
    .form-label .required {
        color: var(--error-red);
    }
    
    .form-label .hint {
        font-weight: 400;
        color: #888;
        font-size: 0.85rem;
        margin-left: 5px;
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 1.1rem;
    }
    
    .input-with-icon.password-field i.icon-lock {
        left: 18px;
    }
    
    .form-input {
        width: 100%;
        padding: 16px 18px 16px 50px;
        border: 2px solid var(--warm-gray);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--primary-gold);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    }
    
    .form-input.error {
        border-color: var(--error-red);
        background: rgba(220, 53, 69, 0.02);
    }
    
    .form-input.error:focus {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    
    .toggle-password {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 5px;
    }
    
    .toggle-password:hover {
        color: var(--dark-charcoal);
    }
    
    /* ERROR MESSAGES */
    .error-message {
        color: var(--error-red);
        font-size: 0.9rem;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .hint-message {
        font-size: 0.85rem;
        margin-top: 5px;
        color: #666;
    }
    
    /* ALERT BOXES */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    
    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: var(--error-red);
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        border: 1px solid rgba(40, 167, 69, 0.3);
        color: var(--success-green);
    }
    
    /* PASSWORD STRENGTH */
    .password-strength {
        margin-top: 15px;
    }
    
    .strength-meter {
        height: 6px;
        background: #eee;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .strength-bar {
        height: 100%;
        width: 0%;
        background: #ddd;
        border-radius: 3px;
        transition: all 0.3s ease;
    }
    
    .strength-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #999;
    }
    
    .strength-label {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }
    
    .strength-label.active {
        opacity: 1;
        font-weight: 600;
    }
    
    /* PASSWORD REQUIREMENTS */
    .requirements-list {
        margin-top: 15px;
    }
    
    .requirement {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        color: #999;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }
    
    .requirement.valid {
        color: var(--success-green);
    }
    
    .requirement i {
        font-size: 0.8rem;
    }
    
    /* TERMS CHECKBOX */
    .terms-group {
        margin-top: 30px;
    }
    
    .checkbox-container {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .checkbox-input {
        margin-top: 5px;
        accent-color: var(--primary-gold);
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-label {
        flex: 1;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.5;
        cursor: pointer;
    }
    
    .checkbox-label a {
        color: var(--primary-gold);
        text-decoration: none;
        font-weight: 500;
    }
    
    .checkbox-label a:hover {
        text-decoration: underline;
    }
    
    /* SUBMIT BUTTON */
    .submit-btn {
        width: 100%;
        padding: 18px;
        background: var(--primary-gold);
        color: var(--dark-charcoal);
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .submit-btn:hover:not(:disabled) {
        background: #c9a430;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }
    
    .submit-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-spinner {
        animation: spin 1s linear infinite;
    }
    
    .hidden {
        display: none;
    }
    
    /* LOGIN LINK */
    .login-link {
        text-align: center;
        padding-top: 25px;
        border-top: 1px solid var(--warm-gray);
        color: #666;
        font-size: 1rem;
    }
    
    .login-link a {
        color: var(--primary-gold);
        text-decoration: none;
        font-weight: 600;
    }
    
    .login-link a:hover {
        text-decoration: underline;
    }
    
    /* BENEFITS SIDEBAR */
    .benefits-sidebar {
        position: sticky;
        top: 20px;
        height: fit-content;
    }
    
    .benefits-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--warm-gray);
    }
    
    .benefits-title {
        font-size: 1.8rem;
        color: var(--dark-charcoal);
        margin-bottom: 30px;
        text-align: center;
        font-weight: 600;
    }
    
    .benefit-item {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .benefit-icon {
        width: 40px;
        height: 40px;
        background: var(--light-gold);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-gold);
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .benefit-content h4 {
        font-size: 1.1rem;
        color: var(--dark-charcoal);
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .benefit-content p {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.5;
    }
    
    .benefits-footer {
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid var(--warm-gray);
    }
    
    .trust-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 10px;
    }
    
    .trust-badge i {
        color: var(--primary-gold);
    }
    
    /* ANIMATIONS */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* RESPONSIVE DESIGN */
    @media (max-width: 992px) {
        .register-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        
        .benefits-sidebar {
            position: static;
        }
        
        .hero-title {
            font-size: 2.8rem;
        }
    }
    
    @media (max-width: 768px) {
        .register-form-container {
            padding: 30px 25px;
        }
        
        .benefits-card {
            padding: 30px 25px;
        }
        
        .form-title {
            font-size: 1.8rem;
        }
        
        .hero-title {
            font-size: 2.3rem;
        }
    }
    
    @media (max-width: 576px) {
        .register-hero {
            padding: 80px 0 40px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1rem;
        }
        
        .register-section {
            padding: 40px 0;
        }
        
        .register-form-container {
            padding: 25px 20px;
        }
    }
    
    /* ICONS */
    .icon-user::before { content: '👤'; }
    .icon-email::before { content: '✉️'; }
    .icon-lock::before { content: '🔒'; }
    .icon-eye::before { content: '👁️'; }
    .icon-check::before { content: '✓'; }
    .icon-error::before { content: '⚠️'; }
    .icon-spinner::before { content: '⏳'; }
    .icon-percent::before { content: '％'; }
    .icon-heart::before { content: '❤️'; }
    .icon-star::before { content: '★'; }
    .icon-truck::before { content: '🚚'; }
    .icon-calendar::before { content: '📅'; }
    .icon-shield::before { content: '🛡️'; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registrationForm');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const strengthBar = document.getElementById('strengthBar');
        const strengthLabels = document.querySelectorAll('.strength-label');
        
        // Password strength calculation
        function calculatePasswordStrength(password) {
            let score = 0;
            
            // Length check
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Character variety checks
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            // Cap at 4
            return Math.min(score, 4);
        }
        
        // Update password strength meter
        function updateStrengthMeter(password) {
            const strength = calculatePasswordStrength(password);
            const percentage = (strength / 4) * 100;
            
            // Update bar width and color
            strengthBar.style.width = `${percentage}%`;
            
            if (strength <= 1) {
                strengthBar.style.backgroundColor = '#dc3545'; // Red
            } else if (strength === 2) {
                strengthBar.style.backgroundColor = '#ffc107'; // Yellow
            } else if (strength === 3) {
                strengthBar.style.backgroundColor = '#28a745'; // Green
            } else {
                strengthBar.style.backgroundColor = '#20c997'; // Teal
            }
            
            // Update labels
            strengthLabels.forEach((label, index) => {
                if (index === strength) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });
        }
        
        // Validate password requirements
        function validatePasswordRequirements(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            // Update requirement indicators
            const reqLength = document.getElementById('reqLength');
            const reqUppercase = document.getElementById('reqUppercase');
            const reqLowercase = document.getElementById('reqLowercase');
            const reqNumber = document.getElementById('reqNumber');
            
            if (reqLength) reqLength.classList.toggle('valid', requirements.length);
            if (reqUppercase) reqUppercase.classList.toggle('valid', requirements.uppercase);
            if (reqLowercase) reqLowercase.classList.toggle('valid', requirements.lowercase);
            if (reqNumber) reqNumber.classList.toggle('valid', requirements.number);
            
            return requirements;
        }
        
        // Real-time password validation
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Update strength meter
            updateStrengthMeter(password);
            
            // Update requirement indicators
            validatePasswordRequirements(password);
            
            // Real-time confirm password validation
            if (confirmInput.value) {
                validateConfirmPassword();
            }
        });
        
        // Confirm password validation
        function validateConfirmPassword() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const hint = document.getElementById('confirmHint');
            
            if (confirm === '') {
                if (hint) hint.textContent = '';
                return;
            }
            
            if (hint) {
                if (password === confirm) {
                    hint.textContent = '✓ Passwords match';
                    hint.style.color = '#28a745';
                    confirmInput.classList.remove('error');
                } else {
                    hint.textContent = '✗ Passwords do not match';
                    hint.style.color = '#dc3545';
                    confirmInput.classList.add('error');
                }
            }
        }
        
        if (confirmInput) {
            confirmInput.addEventListener('input', validateConfirmPassword);
        }
        
        // Username validation
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                const username = this.value;
                const hint = document.getElementById('usernameHint');
                
                if (username.length === 0) {
                    if (hint) hint.textContent = '';
                    return;
                }
                
                if (hint) {
                    if (username.length < 3) {
                        hint.textContent = 'Username must be at least 3 characters';
                        hint.style.color = '#dc3545';
                    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                        hint.textContent = 'Only letters, numbers, and underscores allowed';
                        hint.style.color = '#dc3545';
                    } else {
                        hint.textContent = '✓ Valid username format';
                        hint.style.color = '#28a745';
                    }
                }
            });
        }
        
        // Email validation
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const hint = document.getElementById('emailHint');
                
                if (email.length === 0) {
                    if (hint) hint.textContent = '';
                    return;
                }
                
                if (hint) {
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        hint.textContent = 'Please enter a valid email address';
                        hint.style.color = '#dc3545';
                    } else {
                        hint.textContent = '✓ Valid email format';
                        hint.style.color = '#28a745';
                    }
                }
            });
        }
        
        // Form submission - SIMPLIFIED VERSION
        if (form) {
            form.addEventListener('submit', function(e) {
                const password = passwordInput ? passwordInput.value : '';
                const confirm = confirmInput ? confirmInput.value : '';
                const username = usernameInput ? usernameInput.value : '';
                const email = emailInput ? emailInput.value : '';
                const terms = document.getElementById('terms') ? document.getElementById('terms').checked : false;
                
                // Basic validation
                if (!terms) {
                    e.preventDefault();
                    alert('Please agree to the Terms of Service');
                    return;
                }
                
                if (password && password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters');
                    return;
                }
                
                if (password !== confirm) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
                
                // Show loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const btnText = document.querySelector('.btn-text');
                    const btnSpinner = document.querySelector('.btn-spinner');
                    
                    if (btnText) btnText.classList.add('hidden');
                    if (btnSpinner) btnSpinner.classList.remove('hidden');
                }
                
                // Allow form to submit normally
                return true;
            });
        }
        
        // Toggle password visibility
        window.togglePassword = function(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            const toggleBtn = field.parentNode.querySelector('.toggle-password i');
            if (!toggleBtn) return;
            
            if (field.type === 'password') {
                field.type = 'text';
                toggleBtn.textContent = '👁️‍🗨️';
            } else {
                field.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        };
        
        // Initialize strength meter
        if (passwordInput && strengthBar) {
            updateStrengthMeter(passwordInput.value);
            validatePasswordRequirements(passwordInput.value);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>