<?php
include 'includes/header.php';
?>

<style>
/* === HOMEPAGE SPECIFIC STYLES === */
:root {
    --primary-gold: #D4AF37;
    --dark-charcoal: #2C2C2C;
    --soft-cream: #FAF7F2;
    --warm-gray: #E8E5DE;
    --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --shadow-glow: 0 8px 32px rgba(212, 175, 55, 0.15);
}

/* === LUXURY HERO SECTION === */
.luxury-hero {
    background: linear-gradient(135deg, var(--dark-charcoal) 0%, #3A3A3A 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.luxury-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23D4AF37" opacity="0.03"><circle cx="50" cy="50" r="2"/></svg>') repeat;
    animation: float 20s infinite linear;
}

.hero-content {
    text-align: center;
    color: white;
    position: relative;
    z-index: 2;
    max-width: 800px;
    padding: 0 20px;
}

.hero-title {
    font-size: 4.5rem;
    font-weight: 300;
    letter-spacing: 4px;
    margin-bottom: 1.5rem;
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.5s forwards;
}

.hero-subtitle {
    font-size: 1.3rem;
    color: var(--warm-gray);
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 3rem;
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.8s forwards;
}

.cta-container {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    opacity: 0;
    animation: fadeInUp 1s ease-out 1.1s forwards;
}

.cta-btn {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    padding: 15px 35px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    letter-spacing: 2px;
    transition: var(--transition);
    border: 2px solid var(--primary-gold);
}

.cta-btn:hover {
    background: transparent;
    color: var(--primary-gold);
    transform: translateY(-3px);
}

.cta-secondary {
    background: transparent;
    color: var(--primary-gold);
    border: 2px solid var(--primary-gold);
}

.cta-secondary:hover {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
}

/* === FEATURED COLLECTIONS === */
.featured-collections {
    background: var(--soft-cream);
    padding: 100px 0;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 3rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: var(--primary-gold);
}

.collections-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    padding: 0 20px;
}

.collection-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    border: 1px solid var(--warm-gray);
}

.collection-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-glow);
}

.collection-image {
    height: 300px;
    background: var(--soft-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    position: relative;
}

.collection-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: var(--transition);
}

.collection-card:hover .collection-image img {
    transform: scale(1.05);
}

.collection-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--primary-gold);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.collection-info {
    padding: 30px;
    text-align: center;
}

.collection-name {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 15px;
}

.collection-desc {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.collection-btn {
    background: var(--dark-charcoal);
    color: white;
    padding: 12px 25px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    letter-spacing: 1px;
    transition: var(--transition);
    display: inline-block;
}

.collection-btn:hover {
    background: var(--primary-gold);
    color: var(--dark-charcoal);
    transform: translateY(-2px);
}

/* === LUXURY NEW ARRIVALS === */
.luxury-arrivals {
    background: white;
    padding: 100px 0;
    position: relative;
}

.arrivals-header {
    text-align: center;
    margin-bottom: 50px;
}

.arrivals-subtitle {
    color: #666;
    font-size: 1.1rem;
    letter-spacing: 2px;
    margin-top: 10px;
}

.luxury-slider {
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    padding: 0 60px;
}

.slider-container {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
}

.slider-track {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    gap: 0;
}

.slide {
    min-width: 300px;
    background: white;
    border-radius: 16px;
    padding: 30px;
    margin: 0 15px;
    border: 1px solid var(--warm-gray);
    transition: var(--transition);
    position: relative;
}

.slide:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-glow);
    border-color: var(--primary-gold);
}

.slide-image {
    height: 200px;
    background: var(--soft-cream);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    margin-bottom: 20px;
}

.slide-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.slide h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-charcoal);
    margin-bottom: 10px;
}

.slide-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-gold);
    margin-bottom: 15px;
}

.slide-btn {
    background: transparent;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
    display: inline-block;
}

.slide-btn:hover {
    border-color: var(--primary-gold);
    color: var(--primary-gold);
}

.luxury-slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 1px solid var(--warm-gray);
    color: var(--dark-charcoal);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    z-index: 10;
    font-size: 1.2rem;
}

.luxury-slider-btn:hover {
    background: var(--primary-gold);
    color: white;
    border-color: var(--primary-gold);
}

.luxury-slider-btn.prev {
    left: 0;
}

.luxury-slider-btn.next {
    right: 0;
}

/* === BRAND STORY === */
.brand-story {
    background: linear-gradient(135deg, var(--soft-cream) 0%, #f5f2eb 100%);
    padding: 100px 0;
    text-align: center;
}

.story-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.story-title {
    font-size: 2.5rem;
    font-weight: 300;
    letter-spacing: 3px;
    margin-bottom: 2rem;
    color: var(--dark-charcoal);
}

.story-text {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #666;
    margin-bottom: 3rem;
}

.story-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    margin-top: 50px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 3rem;
    font-weight: 300;
    color: var(--primary-gold);
    margin-bottom: 10px;
}

.stat-label {
    font-size: 1rem;
    color: var(--dark-charcoal);
    letter-spacing: 1px;
}

/* === ANIMATIONS === */
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

/* === RESPONSIVE DESIGN === */
@media (max-width: 768px) {
    .hero-title {
        font-size: 3rem;
    }
    
    .cta-container {
        flex-direction: column;
        align-items: center;
    }
    
    .cta-btn {
        width: 200px;
        text-align: center;
    }
    
    .luxury-slider {
        padding: 0 40px;
    }
    
    .slide {
        min-width: 250px;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
</style>

<!-- LUXURY HERO SECTION -->
<section class="luxury-hero">
    <div class="hero-content">
        <h1 class="hero-title">189 BEAUTY</h1>
        <p class="hero-subtitle">WHERE LUXURY MEETS RADIANCE</p>
        <div class="cta-container">
            <a href="products.php" class="cta-btn">EXPLORE COLLECTION</a>
            <a href="#collections" class="cta-btn cta-secondary">DISCOVER LUXURY</a>
        </div>
    </div>
</section>

<!-- FEATURED COLLECTIONS -->
<section class="featured-collections" id="collections">
    <h2 class="section-title shimmer-gold">CURATED COLLECTIONS</h2>
    <div class="collections-grid">
        <div class="collection-card">
            <div class="collection-image">
                <img src="images/Luxury Cosmetics.png" alt="Luxury Cosmetics">
                <div class="collection-badge">PREMIUM</div>
            </div>
            <div class="collection-info">
                <h3 class="collection-name">LUXURY COSMETICS</h3>
                <p class="collection-desc">Elevate your beauty routine with our premium makeup collection designed for flawless, long-lasting wear.</p>
                <a href="products.php" class="collection-btn">Explore Makeup</a>
            </div>
        </div>
        
        <div class="collection-card">
            <div class="collection-image">
                <img src="images/Skincare Elixirs.png" alt="Skincare Essentials">
                <div class="collection-badge">ELITE</div>
            </div>
            <div class="collection-info">
                <h3 class="collection-name">SKINCARE ELIXIRS</h3>
                <p class="collection-desc">Nourish and rejuvenate your skin with our scientifically advanced skincare formulations.</p>
                <a href="products.php" class="collection-btn">Discover Skincare</a>
            </div>
        </div>
        
        <div class="collection-card">
            <div class="collection-image">
                <img src="images/Artisan Tools.png" alt="Beauty Tools">
                <div class="collection-badge">PROFESSIONAL</div>
            </div>
            <div class="collection-info">
                <h3 class="collection-name">ARTISAN TOOLS</h3>
                <p class="collection-desc">Professional-grade tools and accessories for the perfect application every time.</p>
                <a href="products.php" class="collection-btn">View Tools</a>
            </div>
        </div>
    </div>
</section>

<!-- LUXURY NEW ARRIVALS -->
<section class="luxury-arrivals">
    <div class="arrivals-header">
        <h2 class="section-title shimmer-gold">NEW ARRIVALS</h2>
        <p class="arrivals-subtitle">FRESH FROM OUR LABORATORY</p>
    </div>
    
    <div class="luxury-slider">
        <button class="luxury-slider-btn prev">&#10094;</button>
        <div class="slider-container">
            <div class="slider-track">
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Luxury Lipstick.png" alt="Luxury Lipstick">
                    </div>
                    <h3>Luxury Lipstick</h3>
                    <p class="slide-price">$19.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Glow Foundation.png" alt="Glow Foundation">
                    </div>
                    <h3>Glow Foundation</h3>
                    <p class="slide-price">$29.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Silk Eyeshadow Palette.png" alt="Silk Eyeshadow Palette">
                    </div>
                    <h3>Silk Eyeshadow Palette</h3>
                    <p class="slide-price">$39.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Hydrating Face Cream.png" alt="Hydrating Face Cream">
                    </div>
                    <h3>Hydrating Face Cream</h3>
                    <p class="slide-price">$24.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Velvet Blush.png" alt="Velvet Blush">
                    </div>
                    <h3>Velvet Blush</h3>
                    <p class="slide-price">$14.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
                <div class="slide">
                    <div class="slide-image">
                        <img src="images/Anti-Aging Serum.png" alt="Face Serum">
                    </div>
                    <h3>Luxury Face Serum</h3>
                    <p class="slide-price">$45.99</p>
                    <a href="products.php" class="slide-btn">Quick View</a>
                </div>
            </div>
        </div>
        <button class="luxury-slider-btn next">&#10095;</button>
    </div>
</section>

<!-- BRAND STORY -->
<section class="brand-story">
    <div class="story-content">
        <h2 class="story-title">THE 189 BEAUTY LEGACY</h2>
        <p class="story-text">
            Founded on the principles of luxury, innovation, and uncompromising quality, 189 Beauty represents 
            the pinnacle of cosmetic excellence. Each product is meticulously crafted with premium ingredients 
            and backed by scientific research to deliver transformative results.
        </p>
        <div class="story-stats">
            <div class="stat-item">
                <div class="stat-number">100+</div>
                <div class="stat-label">Premium Products</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5★</div>
                <div class="stat-label">Customer Rating</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2024</div>
                <div class="stat-label">Luxury Award Winner</div>
            </div>
        </div>
    </div>
</section>

<script>
// Simplified Luxury Slider
const track = document.querySelector('.slider-track');
const slides = Array.from(track.querySelectorAll('.slide'));
const prevBtn = document.querySelector('.luxury-slider-btn.prev');
const nextBtn = document.querySelector('.luxury-slider-btn.next');

let currentIndex = 0;
const slideWidth = slides[0].offsetWidth + 30; // including margin

function updateSlider() {
    track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateSlider();
}

function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateSlider();
}

// Event Listeners
nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

// Auto-slide
let autoSlide = setInterval(nextSlide, 4000);

// Pause on hover
const slider = document.querySelector('.slider-container');
slider.addEventListener('mouseenter', () => clearInterval(autoSlide));
slider.addEventListener('mouseleave', () => {
    autoSlide = setInterval(nextSlide, 4000);
});

// Touch support for mobile
let startX = 0;
let currentX = 0;

slider.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    clearInterval(autoSlide);
});

slider.addEventListener('touchmove', (e) => {
    currentX = e.touches[0].clientX;
});

slider.addEventListener('touchend', () => {
    const diff = startX - currentX;
    if (Math.abs(diff) > 50) {
        if (diff > 0) {
            nextSlide();
        } else {
            prevSlide();
        }
    }
    autoSlide = setInterval(nextSlide, 4000);
});
</script>

<?php
include 'includes/footer.php';
?>