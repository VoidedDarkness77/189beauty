<?php include 'includes/header.php'; ?>

<style>
    /* === SERVICES STYLES === */
    .services-hero {
        background: linear-gradient(135deg, rgba(44, 44, 44, 0.95) 0%, rgba(58, 58, 58, 0.95) 100%), 
                    url('https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        padding: 120px 0 80px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .services-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.08"><circle cx="50" cy="50" r="2"/></svg>') repeat;
        animation: float 20s infinite linear;
    }

    .services-title {
        font-size: 4rem;
        font-weight: 300;
        letter-spacing: 4px;
        margin-bottom: 1.5rem;
        color: #d4af37;
        position: relative;
        display: inline-block;
        text-transform: uppercase;
    }

    .services-title::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: var(--primary-gold);
    }

    .services-subtitle {
        font-size: 1.3rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 300;
        letter-spacing: 2px;
        margin-bottom: 3rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }

    .services-section {
        background: var(--soft-cream);
        padding: 100px 0;
    }

    .services-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .services-intro {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 80px;
        padding: 0 20px;
    }

    .services-intro h2 {
        font-size: 2.5rem;
        color: var(--dark-charcoal);
        margin-bottom: 25px;
        font-weight: 300;
        letter-spacing: 2px;
    }

    .services-intro p {
        font-size: 1.1rem;
        color: #666;
        line-height: 1.8;
        margin-bottom: 0;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 40px;
        margin-bottom: 60px;
    }

    .service-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: 1px solid rgba(232, 229, 222, 0.5);
        position: relative;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .service-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 25px 60px rgba(212, 175, 55, 0.15);
    }

    .service-card:nth-child(1) { animation-delay: 0.1s; }
    .service-card:nth-child(2) { animation-delay: 0.2s; }
    .service-card:nth-child(3) { animation-delay: 0.3s; }

    .service-icon {
        height: 200px;
        background: linear-gradient(135deg, var(--primary-gold) 0%, #f9f295 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .service-icon i {
        font-size: 5rem;
        color: white;
        position: relative;
        z-index: 1;
    }

    .service-icon::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="white" opacity="0.1"><circle cx="50" cy="50" r="3"/></svg>') repeat;
        animation: rotate 20s linear infinite;
    }

    .service-content {
        padding: 40px 35px;
        text-align: center;
    }

    .service-title {
        font-size: 1.8rem;
        color: var(--dark-charcoal);
        margin-bottom: 20px;
        font-weight: 600;
        letter-spacing: 1px;
        position: relative;
        padding-bottom: 15px;
    }

    .service-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 2px;
        background: var(--primary-gold);
    }

    .service-description {
        color: #666;
        line-height: 1.8;
        margin-bottom: 25px;
        font-size: 1.05rem;
    }

    .service-features {
        list-style: none;
        padding: 0;
        margin: 0 0 30px 0;
        text-align: left;
    }

    .service-features li {
        padding: 8px 0;
        color: #555;
        position: relative;
        padding-left: 30px;
        font-size: 0.95rem;
    }

    .service-features li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-gold);
        font-weight: bold;
        font-size: 1.1rem;
    }

    .service-price {
        font-size: 2rem;
        color: var(--primary-gold);
        font-weight: 700;
        margin-bottom: 25px;
        letter-spacing: 1px;
    }

    .service-price span {
        font-size: 1rem;
        color: #999;
        font-weight: 400;
        margin-left: 5px;
    }

    .book-btn {
        background: var(--primary-gold);
        color: var(--dark-charcoal);
        border: none;
        padding: 14px 35px;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
        text-decoration: none;
        text-align: center;
        min-width: 180px;
        font-family: inherit;
    }

    .book-btn:hover {
        background: var(--dark-charcoal);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }

    .book-btn i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .book-btn:hover i {
        transform: translateX(5px);
    }

    .service-highlight {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--dark-charcoal);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Premium Services Section */
    .premium-services {
        background: var(--dark-charcoal);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }

    .premium-services::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    }

    .premium-title {
        text-align: center;
        font-size: 3rem;
        color: var(--primary-gold);
        margin-bottom: 60px;
        font-weight: 300;
        letter-spacing: 3px;
        position: relative;
    }

    .premium-title::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 2px;
        background: var(--primary-gold);
    }

    .premium-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .premium-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 15px;
        padding: 40px 30px;
        text-align: center;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .premium-card:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-10px);
        border-color: var(--primary-gold);
    }

    .premium-icon {
        font-size: 3rem;
        color: var(--primary-gold);
        margin-bottom: 25px;
    }

    .premium-card h4 {
        color: white;
        font-size: 1.4rem;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .premium-card p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* CTA Section */
    .services-cta {
        background: linear-gradient(135deg, var(--primary-gold) 0%, #f9f295 100%);
        padding: 100px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .services-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%232C2C2C" opacity="0.03"><circle cx="50" cy="50" r="3"/></svg>') repeat;
    }

    .cta-content {
        max-width: 700px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }

    .cta-title {
        font-size: 3rem;
        color: var(--dark-charcoal);
        margin-bottom: 25px;
        font-weight: 300;
        letter-spacing: 2px;
    }

    .cta-text {
        font-size: 1.2rem;
        color: var(--dark-charcoal);
        margin-bottom: 40px;
        line-height: 1.8;
        opacity: 0.9;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-btn {
        padding: 16px 40px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-decoration: none;
        transition: all 0.3s ease;
        min-width: 200px;
        text-align: center;
        display: inline-block;
    }

    .cta-btn.primary {
        background: var(--dark-charcoal);
        color: white;
    }

    .cta-btn.primary:hover {
        background: white;
        color: var(--dark-charcoal);
        transform: translateY(-3px);
    }

    .cta-btn.secondary {
        background: transparent;
        color: var(--dark-charcoal);
        border: 2px solid var(--dark-charcoal);
    }

    .cta-btn.secondary:hover {
        background: var(--dark-charcoal);
        color: white;
        transform: translateY(-3px);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        100% { transform: translateY(-100px) rotate(360deg); }
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .services-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .services-title {
            font-size: 2.8rem;
        }
        
        .services-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .premium-title,
        .cta-title {
            font-size: 2.2rem;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .cta-btn {
            width: 100%;
            max-width: 300px;
        }
    }

    @media (max-width: 480px) {
        .services-title {
            font-size: 2.2rem;
        }
        
        .services-subtitle {
            font-size: 1.1rem;
        }
        
        .service-content {
            padding: 30px 20px;
        }
        
        .premium-card {
            padding: 30px 20px;
        }
        
        .book-btn {
            padding: 12px 25px;
            font-size: 0.95rem;
            min-width: 160px;
        }
    }
</style>

<!-- HERO SECTION -->
<section class="services-hero">
    <div class="services-container">
        <h1 class="services-title">LUXURY BEAUTY SERVICES</h1>
        <p class="services-subtitle">Experience personalized beauty consultations and premium skincare analysis designed exclusively for you.</p>
    </div>
</section>

<!-- MAIN SERVICES SECTION -->
<section class="services-section">
    <div class="services-container">
        <div class="services-intro">
            <h2>Elevate Your Beauty Journey</h2>
            <p>Discover our exclusive range of personalized beauty services, crafted by industry experts to help you achieve your most radiant and confident self.</p>
        </div>

        <div class="services-grid">
            <!-- Makeup Consultation -->
            <div class="service-card">
                <div class="service-highlight">Most Popular</div>
                <div class="service-icon">
                    <i>💄</i>
                </div>
                <div class="service-content">
                    <h3 class="service-title">Premium Makeup Consultation</h3>
                    <p class="service-description">Personalized one-on-one cosmetic guidance from our expert artists to discover your perfect look.</p>
                    
                    <ul class="service-features">
                        <li>Personalized color analysis</li>
                        <li>Product recommendations</li>
                        <li>Technique demonstrations</li>
                        <li>Take-home look guide</li>
                    </ul>
                    
                    <div class="service-price">$149<span>/session</span></div>
                    
                    <button class="book-btn" onclick="window.location.href='booking.php?service=makeup'">
                        Book Consultation <i>→</i>
                    </button>
                </div>
            </div>

            <!-- Skincare Analysis -->
            <div class="service-card">
                <div class="service-icon">
                    <i>✨</i>
                </div>
                <div class="service-content">
                    <h3 class="service-title">Comprehensive Skincare Analysis</h3>
                    <p class="service-description">Professional skin evaluation using advanced technology to create your perfect skincare regimen.</p>
                    
                    <ul class="service-features">
                        <li>Skin type analysis</li>
                        <li>Personalized routine creation</li>
                        <li>Product samples included</li>
                        <li>Follow-up consultation</li>
                    </ul>
                    
                    <div class="service-price">$199<span>/analysis</span></div>
                    
                    <button class="book-btn" onclick="window.location.href='booking.php?service=skincare'">
                        Book Analysis <i>→</i>
                    </button>
                </div>
            </div>

            <!-- Virtual Try-On -->
            <div class="service-card">
                <div class="service-highlight">New</div>
                <div class="service-icon">
                    <i>👁️</i>
                </div>
                <div class="service-content">
                    <h3 class="service-title">Virtual Beauty Try-On</h3>
                    <p class="service-description">Experience our cutting-edge AR technology to preview beauty products virtually before you buy.</p>
                    
                    <ul class="service-features">
                        <li>Live AR previews</li>
                        <li>Multiple product testing</li>
                        <li>Save favorite looks</li>
                        <li>Direct purchase links</li>
                    </ul>
                    
                    <div class="service-price">$99<span>/session</span></div>
                    
                    <button class="book-btn" onclick="window.location.href='booking.php?service=virtual'">
                        Try Now <i>→</i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PREMIUM SERVICES -->
<section class="premium-services">
    <div class="services-container">
        <h2 class="premium-title">Additional Premium Services</h2>
        
        <div class="premium-grid">
            <div class="premium-card">
                <div class="premium-icon">👰</div>
                <h4>Bridal Packages</h4>
                <p>Complete bridal beauty packages including trial sessions, day-of services, and touch-up kits.</p>
            </div>
            
            <div class="premium-card">
                <div class="premium-icon">🎨</div>
                <h4>Custom Blending</h4>
                <p>Create your own custom foundation, lipstick, and eyeshadow shades with our experts.</p>
            </div>
            
            <div class="premium-card">
                <div class="premium-icon">🌟</div>
                <h4>Master Classes</h4>
                <p>Learn professional techniques from industry experts through our exclusive master classes.</p>
            </div>
        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="services-cta">
    <div class="cta-content">
        <h2 class="cta-title">Ready to Transform Your Beauty Routine?</h2>
        <p class="cta-text">Book your personalized consultation today and experience luxury beauty services tailored just for you. Our experts are ready to help you discover your perfect look.</p>
        
        <div class="cta-buttons">
            <a href="booking.php" class="cta-btn primary">Book Appointment Now</a>
            <a href="contact.php" class="cta-btn secondary">Contact Our Experts</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effect to service cards
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
    
    // Animate cards on scroll
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    // Observe service cards
    serviceCards.forEach(card => {
        card.style.animationPlayState = 'paused';
        observer.observe(card);
    });
    
    // Add click handlers for booking buttons
    const bookingButtons = document.querySelectorAll('.book-btn');
    bookingButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add loading animation
            const originalText = this.innerHTML;
            this.innerHTML = 'Loading...';
            this.disabled = true;
            
            // Get the service from the onclick handler
            const onclickAttr = this.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('booking.php')) {
                // Extract the service parameter
                const match = onclickAttr.match(/service=(\w+)/);
                if (match) {
                    const service = match[1];
                    
                    // Check if user is logged in
                    fetch('includes/check_login.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.logged_in) {
                                // User is logged in, proceed to booking
                                window.location.href = `booking.php?service=${service}`;
                            } else {
                                // User is not logged in, ask if they want to login or book as guest
                                if (confirm('Would you like to login for faster booking? (You can also book as a guest)')) {
                                    window.location.href = `login.php?redirect=booking.php?service=${service}`;
                                } else {
                                    window.location.href = `booking.php?service=${service}`;
                                }
                            }
                        })
                        .catch(error => {
                            // If check fails, just proceed to booking
                            window.location.href = `booking.php?service=${service}`;
                        });
                }
            }
            
            // Reset button after 2 seconds if nothing happens
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>