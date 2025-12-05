<?php
include 'includes/header.php';

// Configuration - Set this to true to use Formspree
$use_formspree = true; // Set to true for Formspree, false for database
$formspree_endpoint = "https://formspree.io/f/xeoyzkpq"; 

// Check if user is logged in
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$user_id = $logged_in ? $_SESSION['user_id'] : null;

// Get service type from URL parameter
$service_type = isset($_GET['service']) ? $_GET['service'] : '';
$service_names = [
    'makeup' => 'Premium Makeup Consultation',
    'skincare' => 'Comprehensive Skincare Analysis',
    'virtual' => 'Virtual Beauty Try-On'
];

// Set default values if user is logged in
$full_name = '';
$email = '';
$phone = '';

if ($logged_in && $user_id && !$use_formspree) {
    // Only load user data if using database method
    include 'includes/db.php';
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
        $email = $user['email'];
        $phone = $user['phone'] ?? '';
    }
    $stmt->close();
}

// Handle form submission for database method
if (!$use_formspree && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    include 'includes/db.php';
    
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $service_type = $_POST['service_type'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    
    if (empty($booking_date)) {
        $errors[] = "Booking date is required.";
    } elseif (strtotime($booking_date) < strtotime('today')) {
        $errors[] = "Booking date cannot be in the past.";
    }
    
    if (empty($booking_time)) {
        $errors[] = "Booking time is required.";
    }
    
    // Check if time slot is available
    $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE booking_date = ? AND booking_time = ? AND status != 'cancelled'");
    $check_stmt->bind_param("ss", $booking_date, $booking_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 2) {
        $errors[] = "This time slot is fully booked. Please choose another time.";
    }
    $check_stmt->close();
    
    // If no errors, save booking
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_type, full_name, email, phone, booking_date, booking_time, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $service_type, $full_name, $email, $phone, $booking_date, $booking_time, $special_requests);
        
        if ($stmt->execute()) {
            $booking_id = $stmt->insert_id;
            $stmt->close();
            
            $_SESSION['booking_success'] = $booking_id;
            $_SESSION['booking_service'] = $service_type;
            
            header("Location: booking_success.php");
            exit;
        } else {
            $errors[] = "Failed to save booking. Please try again.";
        }
    }
}
?>

<style>
/* ===== BOOKING PAGE STYLES ===== */

/* === HERO SECTION === */
.booking-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    padding: 100px 0 60px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.booking-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.05"><circle cx="50" cy="50" r="2"/></svg>') repeat;
}

.booking-title {
    font-size: 3.5rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 1rem;
    color: #d4af37;
    position: relative;
    display: inline-block;
}

.booking-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: var(--primary-gold);
}

.booking-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 300;
    letter-spacing: 1px;
    margin-bottom: 3rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* === MAIN CONTENT SECTION === */
.booking-section {
    background: var(--soft-cream);
    padding: 80px 0;
    min-height: 80vh;
}

.booking-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 50px;
}

/* === FORM CONTAINER === */
.booking-form-container {
    background: white;
    border-radius: 20px;
    padding: 50px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--warm-gray);
}

.booking-sidebar {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--warm-gray);
    height: fit-content;
    position: sticky;
    top: 20px;
}

/* === FORM ELEMENTS === */
.form-title {
    font-size: 2rem;
    color: var(--dark-charcoal);
    margin-bottom: 30px;
    font-weight: 600;
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
    font-size: 0.95rem;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.form-input {
    width: 100%;
    padding: 14px 18px;
    border: 1px solid var(--warm-gray);
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* === BUTTONS === */
.submit-btn {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    border: none;
    padding: 16px 40px;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 20px;
}

.submit-btn:hover {
    background: var(--dark-charcoal);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
}

.submit-btn:disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* === MESSAGES & ALERTS === */
.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* === FORMSPREE MESSAGES === */
#formspree-messages {
    margin-top: 20px;
}

/* === SERVICE CARD === */
.service-card {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(249, 242, 149, 0.05) 100%);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
}

.service-card h3 {
    color: var(--dark-charcoal);
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.service-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.service-details {
    list-style: none;
    padding: 0;
    margin: 0 0 25px 0;
}

.service-details li {
    padding: 8px 0;
    color: #555;
    position: relative;
    padding-left: 25px;
}

.service-details li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--primary-gold);
    font-weight: bold;
    font-size: 1.2rem;
}

.service-price {
    font-size: 2.2rem;
    color: var(--primary-gold);
    font-weight: 700;
    text-align: center;
    margin-bottom: 25px;
}

.service-price span {
    font-size: 1rem;
    color: #999;
    font-weight: 400;
    margin-left: 5px;
}

/* === AVAILABILITY CALENDAR === */
.availability-calendar {
    background: white;
    border-radius: 15px;
    padding: 25px;
    border: 1px solid var(--warm-gray);
    margin-top: 30px;
}

.availability-title {
    font-size: 1.2rem;
    color: var(--dark-charcoal);
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
}

.availability-slots {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.time-slot {
    background: var(--soft-cream);
    border: 1px solid var(--warm-gray);
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.time-slot:hover {
    background: var(--primary-gold);
    border-color: var(--primary-gold);
    color: var(--dark-charcoal);
}

.time-slot.selected {
    background: var(--primary-gold);
    border-color: var(--primary-gold);
    color: var(--dark-charcoal);
    font-weight: 600;
}

.time-slot.booked {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #adb5bd;
    cursor: not-allowed;
    text-decoration: line-through;
}

/* === LOGIN PROMPT === */
.login-prompt {
    background: rgba(212, 175, 55, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
    text-align: center;
}

.login-prompt p {
    color: var(--dark-charcoal);
    margin-bottom: 15px;
}

.login-btn {
    background: var(--dark-charcoal);
    color: white;
    padding: 10px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    transition: all 0.3s ease;
}

.login-btn:hover {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    transform: translateY(-2px);
}

/* === CONTACT INFO === */
.contact-info {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--warm-gray);
}

.contact-info p {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
}

.contact-info a {
    color: var(--primary-gold);
    text-decoration: none;
    font-weight: 500;
}

.contact-info a:hover {
    text-decoration: underline;
}

/* === RESPONSIVE DESIGN === */
@media (max-width: 968px) {
    .booking-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .booking-sidebar {
        position: static;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .availability-slots {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .booking-hero {
        padding: 80px 0 40px;
    }
    
    .booking-title {
        font-size: 2.5rem;
    }
    
    .booking-subtitle {
        font-size: 1.1rem;
        padding: 0 20px;
    }
    
    .booking-section {
        padding: 60px 0;
    }
    
    .booking-form-container,
    .booking-sidebar {
        padding: 30px;
    }
    
    .form-title {
        font-size: 1.8rem;
    }
    
    .service-card h3 {
        font-size: 1.3rem;
    }
    
    .service-price {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .booking-title {
        font-size: 2rem;
    }
    
    .booking-form-container,
    .booking-sidebar {
        padding: 25px 20px;
    }
    
    .form-title {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    
    .form-input {
        padding: 12px 15px;
    }
    
    .submit-btn {
        padding: 14px 20px;
        font-size: 1rem;
    }
    
    .availability-slots {
        grid-template-columns: 1fr;
    }
    
    .service-card {
        padding: 20px;
    }
}

/* === LOADING STATE === */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-right: 10px;
    vertical-align: middle;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- HERO SECTION -->
<section class="booking-hero">
    <div class="booking-container">
        <div style="grid-column: 1 / -1; text-align: center;">
            <h1 class="booking-title">BOOK YOUR BEAUTY SERVICE</h1>
            <p class="booking-subtitle">
                <?php 
                if ($service_type && isset($service_names[$service_type])) {
                    echo "Reserve your " . htmlspecialchars($service_names[$service_type]) . " with our expert team";
                } else {
                    echo "Schedule your personalized beauty consultation with our expert team";
                }
                ?>
            </p>
        </div>
    </div>
</section>

<!-- BOOKING SECTION -->
<section class="booking-section">
    <div class="booking-container">
        <!-- Booking Form -->
        <div class="booking-form-container">
            <?php if(!$use_formspree && !empty($errors)): ?>
                <div class="alert error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if(!$logged_in && !$use_formspree): ?>
                <div class="login-prompt">
                    <p>Already have an account? <a href="login.php?redirect=booking.php<?= $service_type ? '?service=' . $service_type : '' ?>" class="login-btn">Login</a> to auto-fill your details!</p>
                    <p style="font-size: 0.9rem; color: #666;">Don't have an account? You can still book as a guest.</p>
                </div>
            <?php endif; ?>
            
            <h2 class="form-title">Booking Details</h2>
            
           <!-- FORMSPREE FORM -->
<form method="POST" action="<?php echo htmlspecialchars($formspree_endpoint); ?>" id="bookingForm">
    <input type="hidden" name="_subject" value="New Beauty Service Booking - 189 Beauty">
    <input type="hidden" name="_format" value="plain">
    <input type="hidden" name="_next" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?success=1">
    <input type="text" name="_gotcha" style="display:none">
    
    <div class="form-row">
        <div class="form-group">
            <label class="form-label required">Full Name</label>
            <input type="text" name="Full Name" class="form-input" 
                   value="<?= htmlspecialchars($full_name) ?>" 
                   required 
                   placeholder="Enter your full name">
        </div>
        
        <div class="form-group">
            <label class="form-label required">Email Address</label>
            <input type="email" name="email" class="form-input" 
                   value="<?= htmlspecialchars($email) ?>" 
                   required 
                   placeholder="your@email.com">
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label class="form-label required">Phone Number</label>
            <input type="tel" name="Phone" class="form-input" 
                   value="<?= htmlspecialchars($phone) ?>" 
                   required 
                   placeholder="(123) 456-7890">
        </div>
        
        <div class="form-group">
            <label class="form-label required">Service Type</label>
            <select name="Service Type" class="form-input" required>
                <option value="">Select a service</option>
                <option value="makeup" <?= $service_type == 'makeup' ? 'selected' : '' ?>>Makeup Consultation</option>
                <option value="skincare" <?= $service_type == 'skincare' ? 'selected' : '' ?>>Skincare Analysis</option>
                <option value="virtual" <?= $service_type == 'virtual' ? 'selected' : '' ?>>Virtual Try-On</option>
            </select>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label class="form-label required">Booking Date</label>
            <input type="date" name="Booking Date" class="form-input" 
                   min="<?= date('Y-m-d') ?>" 
                   required 
                   id="bookingDate">
        </div>
        
        <div class="form-group">
            <label class="form-label required">Booking Time</label>
            <select name="Booking Time" class="form-input" required id="bookingTime">
                <option value="">Select a time</option>
                <!-- Times will be populated by JavaScript -->
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label class="form-label">Special Requests (Optional)</label>
        <textarea name="Special Requests" class="form-input" 
                  rows="4" 
                  placeholder="Any specific concerns, preferences, or questions for our beauty expert..."><?= htmlspecialchars($special_requests ?? '') ?></textarea>
    </div>
    
    <button type="submit" class="submit-btn" id="submitBtn">
        <span id="btnText">Confirm Booking</span>
        <span id="btnLoading" style="display: none;">
            <span class="loading-spinner"></span> Sending...
        </span>
    </button>
    
    <!-- Formspree messages will appear here -->
    <div id="formspree-messages"></div>
</form>
        </div>
        
        <!-- Service Details Sidebar -->
        <div class="booking-sidebar">
            <div class="service-card">
                <h3 id="serviceTitle">
                    <?php 
                    if ($service_type && isset($service_names[$service_type])) {
                        echo htmlspecialchars($service_names[$service_type]);
                    } else {
                        echo "Select a Service";
                    }
                    ?>
                </h3>
                
                <div id="serviceDescription">
                    <?php if($service_type == 'makeup'): ?>
                        <p>Personalized one-on-one cosmetic guidance from our expert artists to discover your perfect look.</p>
                        <ul class="service-details">
                            <li>60-minute private session</li>
                            <li>Personalized color analysis</li>
                            <li>Product recommendations</li>
                            <li>Technique demonstrations</li>
                            <li>Take-home look guide</li>
                        </ul>
                        <div class="service-price">$149<span>/session</span></div>
                    <?php elseif($service_type == 'skincare'): ?>
                        <p>Professional skin evaluation using advanced technology to create your perfect skincare regimen.</p>
                        <ul class="service-details">
                            <li>90-minute comprehensive analysis</li>
                            <li>Skin type & condition assessment</li>
                            <li>Personalized routine creation</li>
                            <li>Product samples included</li>
                            <li>Follow-up consultation</li>
                        </ul>
                        <div class="service-price">$199<span>/analysis</span></div>
                    <?php elseif($service_type == 'virtual'): ?>
                        <p>Experience our cutting-edge AR technology to preview beauty products virtually before you buy.</p>
                        <ul class="service-details">
                            <li>45-minute virtual session</li>
                            <li>Live AR previews</li>
                            <li>Multiple product testing</li>
                            <li>Save favorite looks</li>
                            <li>Direct purchase links</li>
                        </ul>
                        <div class="service-price">$99<span>/session</span></div>
                    <?php else: ?>
                        <p>Please select a service type from the form to see details and pricing.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="availability-calendar">
                <h4 class="availability-title">Available Time Slots</h4>
                <div class="availability-slots" id="timeSlots">
                    <!-- Time slots will be populated by JavaScript -->
                </div>
                <p style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 15px;">
                    Select a date first to see available times
                </p>
            </div>
            
            <div class="contact-info">
                <p>
                    <strong>Need help?</strong><br>
                    Call us at <a href="tel:+15551234567">(555) 123-4567</a><br>
                    or email <a href="mailto:bookings@189beauty.com">bookings@189beauty.com</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceTypeSelect = document.querySelector('select[name="Service Type"]');
    const serviceDescription = document.getElementById('serviceDescription');
    const serviceTitle = document.getElementById('serviceTitle');
    const bookingDate = document.getElementById('bookingDate');
    const bookingTime = document.getElementById('bookingTime');
    const timeSlots = document.getElementById('timeSlots');
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    // Service descriptions
    const serviceData = {
        'makeup': {
            title: 'Premium Makeup Consultation',
            description: 'Personalized one-on-one cosmetic guidance from our expert artists to discover your perfect look.',
            details: [
                '60-minute private session',
                'Personalized color analysis',
                'Product recommendations',
                'Technique demonstrations',
                'Take-home look guide'
            ],
            price: '$149',
            duration: '60 min'
        },
        'skincare': {
            title: 'Comprehensive Skincare Analysis',
            description: 'Professional skin evaluation using advanced technology to create your perfect skincare regimen.',
            details: [
                '90-minute comprehensive analysis',
                'Skin type & condition assessment',
                'Personalized routine creation',
                'Product samples included',
                'Follow-up consultation'
            ],
            price: '$199',
            duration: '90 min'
        },
        'virtual': {
            title: 'Virtual Beauty Try-On',
            description: 'Experience our cutting-edge AR technology to preview beauty products virtually before you buy.',
            details: [
                '45-minute virtual session',
                'Live AR previews',
                'Multiple product testing',
                'Save favorite looks',
                'Direct purchase links'
            ],
            price: '$99',
            duration: '45 min'
        }
    };
    
    // Update service details when service type changes
    if (serviceTypeSelect) {
        serviceTypeSelect.addEventListener('change', function() {
            const service = this.value;
            
            if (serviceData[service]) {
                const data = serviceData[service];
                
                serviceTitle.textContent = data.title;
                serviceDescription.innerHTML = `
                    <p>${data.description}</p>
                    <ul class="service-details">
                        ${data.details.map(detail => `<li>${detail}</li>`).join('')}
                    </ul>
                    <div class="service-price">${data.price}<span>/session</span></div>
                `;
                
                // Update URL parameter without page reload
                const url = new URL(window.location);
                url.searchParams.set('service', service);
                window.history.pushState({}, '', url);
            } else {
                serviceTitle.textContent = 'Select a Service';
                serviceDescription.innerHTML = `
                    <p>Please select a service type from the form to see details and pricing.</p>
                `;
            }
        });
    }
    
    // Generate time slots (9 AM to 6 PM, every 30 minutes)
    function generateTimeSlots() {
        const slots = [];
        for (let hour = 9; hour <= 18; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                if (hour === 18 && minute > 0) break;
                const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                slots.push(time);
            }
        }
        return slots;
    }
    
    // Update available time slots when date is selected
    if (bookingDate) {
        bookingDate.addEventListener('change', function() {
            const selectedDate = this.value;
            
            if (!selectedDate) {
                timeSlots.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: #666;">Select a date first</p>';
                if (bookingTime) bookingTime.innerHTML = '<option value="">Select a time</option>';
                return;
            }
            
            // Clear previous options
            if (bookingTime) bookingTime.innerHTML = '<option value="">Select a time</option>';
            timeSlots.innerHTML = '';
            
            // Generate time slots
            const timeOptions = generateTimeSlots();
            const today = new Date();
            const selectedDateObj = new Date(selectedDate);
            const isToday = selectedDateObj.toDateString() === today.toDateString();
            const currentHour = today.getHours();
            const currentMinute = today.getMinutes();
            
            // For demo, randomly mark some slots as booked
            const bookedSlots = [];
            if (Math.random() > 0.7) {
                const randomIndex = Math.floor(Math.random() * timeOptions.length);
                bookedSlots.push(timeOptions[randomIndex]);
            }
            
            timeOptions.forEach(time => {
                const [hour, minute] = time.split(':').map(Number);
                
                // Skip past times for today
                if (isToday && (hour < currentHour || (hour === currentHour && minute <= currentMinute))) {
                    return;
                }
                
                const isBooked = bookedSlots.includes(time);
                
                // Add to dropdown
                if (bookingTime) {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = `${formatTime(time)} ${isBooked ? '(Booked)' : ''}`;
                    option.disabled = isBooked;
                    bookingTime.appendChild(option);
                }
                
                // Add to quick select buttons
                const slotDiv = document.createElement('div');
                slotDiv.className = `time-slot ${isBooked ? 'booked' : ''}`;
                slotDiv.textContent = formatTime(time);
                slotDiv.dataset.time = time;
                
                if (!isBooked) {
                    slotDiv.addEventListener('click', function() {
                        // Remove selected class from all slots
                        document.querySelectorAll('.time-slot').forEach(s => {
                            s.classList.remove('selected');
                        });
                        
                        // Add selected class to clicked slot
                        this.classList.add('selected');
                        
                        // Update dropdown
                        if (bookingTime) {
                            bookingTime.value = this.dataset.time;
                        }
                    });
                }
                
                timeSlots.appendChild(slotDiv);
            });
            
            if (timeSlots.children.length === 0) {
                timeSlots.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: #666;">No available slots for this date</p>';
            }
        });
    }
    
    // Format time for display (12-hour format)
    function formatTime(time24) {
        const [hour, minute] = time24.split(':').map(Number);
        const period = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minute.toString().padStart(2, '0')} ${period}`;
    }
    
    // Form validation and submission for Formspree
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Basic validation - use correct field names
            const fullName = this.querySelector('input[name="Full Name"]').value.trim();
            const email = this.querySelector('input[name="email"]').value.trim();
            const phone = this.querySelector('input[name="Phone"]').value.trim();
            const service = this.querySelector('select[name="Service Type"]').value;
            const date = this.querySelector('input[name="Booking Date"]').value;
            const time = this.querySelector('select[name="Booking Time"]').value;
            
            let errorMsg = '';
            
            if (!fullName) {
                errorMsg = 'Please enter your full name.';
            } else if (!email || !validateEmail(email)) {
                errorMsg = 'Please enter a valid email address.';
            } else if (!phone) {
                errorMsg = 'Please enter your phone number.';
            } else if (!service) {
                errorMsg = 'Please select a service type.';
            } else if (!date) {
                errorMsg = 'Please select a booking date.';
            } else if (!time) {
                errorMsg = 'Please select a booking time.';
            }
            
            if (errorMsg) {
                showMessage('❌ ' + errorMsg, 'error');
                return;
            }
            
            // Show loading state
            if (submitBtn && btnText && btnLoading) {
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
            }
            
            try {
                const formData = new FormData(this);
                
                // Send to Formspree
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Success
                    showMessage('✅ Booking confirmed! We\'ll contact you shortly to confirm your appointment.', 'success');
                    
                    // Clear form
                    this.reset();
                    
                    // Reset UI
                    setTimeout(() => {
                        if (submitBtn && btnText && btnLoading) {
                            submitBtn.disabled = false;
                            btnText.style.display = 'inline';
                            btnLoading.style.display = 'none';
                        }
                    }, 3000);
                    
                } else {
                    // Error
                    const data = await response.json();
                    let errorMsg = 'Sorry, there was an error submitting your booking.';
                    if (data.errors && data.errors.length > 0) {
                        errorMsg = data.errors.map(err => err.message).join(', ');
                    }
                    showMessage('❌ ' + errorMsg, 'error');
                    
                    // Reset button
                    if (submitBtn && btnText && btnLoading) {
                        submitBtn.disabled = false;
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                    }
                }
            } catch (error) {
                // Network error
                showMessage('❌ Network error. Please check your connection and try again.', 'error');
                
                // Reset button
                if (submitBtn && btnText && btnLoading) {
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }
        });
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showMessage(message, type) {
        const messagesDiv = document.getElementById('formspree-messages');
        if (!messagesDiv) return;
        
        // Clear previous messages
        messagesDiv.innerHTML = '';
        
        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert ${type === 'success' ? 'success' : 'error'}`;
        messageDiv.innerHTML = message;
        
        messagesDiv.appendChild(messageDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
    
    // Set minimum date to today
    if (bookingDate) {
        bookingDate.min = new Date().toISOString().split('T')[0];
    }
    
    // If service is pre-selected from URL, trigger change event
    if (serviceTypeSelect && serviceTypeSelect.value && serviceData[serviceTypeSelect.value]) {
        serviceTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>