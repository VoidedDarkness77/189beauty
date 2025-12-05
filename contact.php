<?php
include 'includes/header.php';

// Configuration - Set your preferred method
$use_formspree = true; // Set to false to use database method
$formspree_endpoint = "https://formspree.io/f/xovglwkv";

$success = '';
$error = '';

// For database method (when $use_formspree is false)
if (!$use_formspree) {
    include 'includes/db.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validation
        if (empty($name) || empty($email) || empty($message)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Use prepared statement for security
            $stmt = $conn->prepare("INSERT INTO messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $message);
            
            if ($stmt->execute()) {
                $success = 'Message sent successfully!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Sorry, there was an error. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>

<section class="contact-section">
    <h2 class="center shimmer-gold">Contact Us</h2>
    
    <div class="contact-container">
        
        <?php if ($error): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form for Formspree -->
        <?php if ($use_formspree): ?>
        <form method="POST" action="<?php echo htmlspecialchars($formspree_endpoint); ?>" id="formspree-form">
            <input type="hidden" name="_subject" value="New Contact Form Submission">
            <input type="hidden" name="_format" value="plain">
            <input type="text" name="_gotcha" style="display:none">
            
            <div class="contact-grid">
                <div class="contact-field">
                    <label for="name">Full Name *</label>
                    <input type="text" 
                           id="name"
                           name="name" 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                           placeholder="Your full name" 
                           required>
                </div>
                
                <div class="contact-field">
                    <label for="email">Email Address *</label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="Your email" 
                           required>
                </div>
            </div>
            
            <div class="contact-field" style="margin-top:20px;">
                <label for="message">Your Message *</label>
                <textarea id="message"
                          name="message" 
                          placeholder="Write your message here..." 
                          required 
                          rows="5"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="contact-btn">Send Message</button>
            
            <!-- Success/Error messages for Formspree (handled by JS) -->
            <div id="formspree-messages" style="display:none; margin-top:15px;"></div>
        </form>
        
        <!-- Form for Database -->
        <?php else: ?>
        <form method="POST" action="">
            <div class="contact-grid">
                <div class="contact-field">
                    <label for="name">Full Name *</label>
                    <input type="text" 
                           id="name"
                           name="name" 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                           placeholder="Your full name" 
                           required>
                </div>
                
                <div class="contact-field">
                    <label for="email">Email Address *</label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="Your email" 
                           required>
                </div>
            </div>
            
            <div class="contact-field" style="margin-top:20px;">
                <label for="message">Your Message *</label>
                <textarea id="message"
                          name="message" 
                          placeholder="Write your message here..." 
                          required 
                          rows="5"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="contact-btn">Send Message</button>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php if ($use_formspree): ?>
<script>
// Handle Formspree submission with AJAX
document.getElementById('formspree-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('.contact-btn');
    const originalBtnText = submitBtn.textContent;
    const messagesDiv = document.getElementById('formspree-messages');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    messagesDiv.style.display = 'none';
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            // Success
            messagesDiv.style.display = 'block';
            messagesDiv.innerHTML = '<div class="alert success">Message sent successfully! We\'ll get back to you soon.</div>';
            form.reset();
        } else {
            // Error
            const data = await response.json();
            let errorMsg = 'Sorry, there was an error.';
            if (data.errors && data.errors.length > 0) {
                errorMsg = data.errors.map(err => err.message).join(', ');
            }
            messagesDiv.style.display = 'block';
            messagesDiv.innerHTML = '<div class="alert error">' + errorMsg + '</div>';
        }
    } catch (error) {
        // Network error
        messagesDiv.style.display = 'block';
        messagesDiv.innerHTML = '<div class="alert error">Network error. Please check your connection.</div>';
    } finally {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});
</script>
<?php endif; ?>

<style>
.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}
.alert.success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}
.alert.error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}
.contact-btn {
    background: #4361ee;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    margin-top: 20px;
    width: 100%;
}
.contact-btn:hover {
    background: #3a56d4;
}
.contact-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}
</style>

<?php include 'includes/footer.php'; ?>