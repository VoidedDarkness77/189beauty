<?php 
include 'includes/header.php';

// Initialize variables
$error = '';
$username = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';

    // Sanitize and validate input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username)) {
        $error = "Username is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, email, cart_data, wishlist_data FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For demo purposes - remove this in production and use proper password hashing
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Load user's saved cart and wishlist data
                loadUserCartData($user, $conn);
                loadUserWishlistData($user, $conn);
                
                // Redirect to intended page or home
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                
                header("Location: " . $redirect);
                exit;
            } else {
                $error = "Incorrect username or password";
            }
        } else {
            $error = "Incorrect username or password";
        }
        
        $stmt->close();
    }
    
    // Close connection
    $conn->close();
}

/**
 * Load user's saved cart data from database
 */
function loadUserCartData($user, $conn = null) {
    if (!empty($user['cart_data'])) {
        $cart_data = json_decode($user['cart_data'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($cart_data)) {
            $_SESSION['cart'] = $cart_data;
        }
    }
    
    // Initialize empty cart if none exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Load user's saved wishlist data from database
 */
function loadUserWishlistData($user, $conn = null) {
    if (!empty($user['wishlist_data'])) {
        $wishlist_data = json_decode($user['wishlist_data'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($wishlist_data)) {
            $_SESSION['wishlist'] = $wishlist_data;
        }
    }
    
    // Initialize empty wishlist if none exists
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
}
?>



<style>
/* === LOGIN STYLES - MATCHING BEAUTY THEME === */
:root {
    --primary-gold: #D4AF37;
    --dark-charcoal: #2C2C2C;
    --soft-cream: #FAF7F2;
    --warm-gray: #E8E5DE;
    --deep-burgundy: #8B4513;
    --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --shadow-glow: 0 8px 32px rgba(212, 175, 55, 0.15);
}

.login-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 80px 0 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.login-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    animation: float 20s infinite linear;
}

.login-title {
    font-size: 3rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    color: #d4af37;
    position: relative;
    display: inline-block;
}

.login-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: var(--primary-gold);
}

.login-subtitle {
    font-size: 1.1rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

.login-section {
    background: var(--soft-cream);
    padding: 60px 0;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-container {
    max-width: 450px;
    width: 100%;
    margin: 0 auto;
    padding: 0 20px;
}

.login-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: var(--shadow-glow);
    border: 1px solid var(--warm-gray);
    position: relative;
    overflow: hidden;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-gold), #f9f295, var(--primary-gold));
}

.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.login-card-title {
    font-size: 1.8rem;
    font-weight: 300;
    background: linear-gradient(135deg, var(--primary-gold), #f9f295);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
}

.login-card-subtitle {
    color: #666;
    font-size: 0.95rem;
}

.login-form {
    width: 100%;
}

.form-group {
    margin-bottom: 25px;
    position: relative;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--dark-charcoal);
    font-weight: 500;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 14px 16px;
    background: var(--soft-cream);
    border: 1px solid var(--warm-gray);
    border-radius: 10px;
    color: var(--dark-charcoal);
    font-size: 1rem;
    transition: var(--transition);
    font-family: inherit;
}

.form-input::placeholder {
    color: #999;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
    background: white;
    transform: translateY(-2px);
}

.form-group.error .form-input {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 38px;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--primary-gold);
}

.error-message {
    color: #dc3545;
    font-size: 0.8rem;
    margin-top: 5px;
    display: block;
    font-weight: 500;
    text-align: center;
    padding: 10px;
    background: rgba(220, 53, 69, 0.05);
    border-radius: 6px;
    border: 1px solid rgba(220, 53, 69, 0.1);
}

.login-btn {
    background: linear-gradient(135deg, var(--primary-gold), #f9f295);
    color: var(--dark-charcoal);
    border: none;
    padding: 16px 32px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.login-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
}

.login-btn:active {
    transform: translateY(-1px);
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.login-btn:hover::before {
    left: 100%;
}

.login-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.login-footer {
    text-align: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid var(--warm-gray);
}

.login-footer p {
    color: #666;
    margin-bottom: 15px;
}

.register-link {
    color: var(--primary-gold);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    display: inline-block;
    padding: 10px 20px;
    border: 1px solid var(--primary-gold);
    border-radius: 8px;
}

.register-link:hover {
    background: var(--primary-gold);
    color: white;
    transform: translateY(-2px);
}

/* Additional Options */
.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 0.85rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.remember-me input {
    margin: 0;
}

.forgot-password {
    color: var(--primary-gold);
    text-decoration: none;
    transition: var(--transition);
}

.forgot-password:hover {
    color: var(--dark-charcoal);
}

/* Demo Credentials */
.demo-credentials {
    background: rgba(212, 175, 55, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 0.8rem;
    color: #666;
}

.demo-title {
    font-weight: 600;
    color: var(--primary-gold);
    margin-bottom: 5px;
}

/* Animation Keyframes */
@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    100% { transform: translateY(-100px) rotate(360deg); }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-title {
        font-size: 2.5rem;
    }
    
    .login-card {
        padding: 30px 25px;
    }
    
    .login-options {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .login-title {
        font-size: 2rem;
    }
    
    .login-card {
        padding: 25px 20px;
    }
    
    .form-input {
        padding: 12px 14px;
    }
}

/* Security Notice */
.security-notice {
    font-size: 0.7rem;
    color: #999;
    text-align: center;
    margin-top: 15px;
    line-height: 1.4;
}
</style>

<!-- LOGIN HERO SECTION -->
<section class="login-hero">
    <div class="hero-content">
        <h1 class="login-title">WELCOME BACK</h1>
        <p class="login-subtitle">SIGN IN TO YOUR BEAUTY ACCOUNT</p>
    </div>
</section>

<!-- LOGIN SECTION -->
<section class="login-section">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2 class="login-card-title">Member Login</h2>
                <p class="login-card-subtitle">Access your personalized beauty dashboard</p>
            </div>

            <!-- Demo Credentials (Remove in production) -->
            <div class="demo-credentials">
                <div class="demo-title">Demo Credentials:</div>
                <div>Username: demo</div>
                <div>Password: demo123</div>
            </div>

            <form method="POST" class="login-form" novalidate>
                <!-- Username Field -->
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        value="<?= htmlspecialchars($username) ?>" 
                        placeholder="Enter your username" 
                        required
                        class="form-input"
                        autocomplete="username"
                    >
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Enter your password" 
                        required
                        class="form-input"
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        👁️
                    </button>
                </div>

                <!-- Additional Options -->
                <div class="login-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">
                        Forgot Password?
                    </a>
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="login-btn">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>Don't have an account?</p>
                <a href="register.php" class="register-link">
                    Create Your Beauty Account
                </a>
            </div>

            <div class="security-notice">
                Your login information is encrypted and secure
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced form functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    // Focus on username field on page load
    usernameInput.focus();
    
    // Real-time validation
    const inputs = form.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        if (field.name === 'username' && !value) {
            isValid = false;
            showFieldError(field, 'Username is required');
        } else if (field.name === 'password' && !value) {
            isValid = false;
            showFieldError(field, 'Password is required');
        } else {
            clearFieldError(field);
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        field.classList.add('error');
        // Remove existing error message
        const existingError = field.parentElement.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message field-error';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentElement.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});

// Password visibility toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = '🔒';
    } else {
        passwordInput.type = 'password';
        toggleButton.textContent = '👁️';
    }
}

// Add enter key support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const focused = document.activeElement;
        if (focused && focused.form && focused.form.classList.contains('login-form')) {
            // Let the form handle the submission
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>