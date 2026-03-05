<?php
// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Start session (for future features)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = "sql105.infinityfree.com";
$db_user = "if0_40484839";
$db_pass = "KQWdyN8caJKhlG2";
$db_name = "if0_40484839_dmcars";

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch featured cars (limit to 6)
$featured_query = "SELECT cl.*, 
        (SELECT image_path FROM car_images WHERE car_id = cl.id ORDER BY display_order LIMIT 1) as main_image,
        (SELECT COUNT(*) FROM car_images WHERE car_id = cl.id) as image_count
        FROM car_listings cl 
        WHERE cl.status = 'approved' AND cl.is_featured = 1 
        ORDER BY cl.created_at DESC 
        LIMIT 6";

$featured_result = $conn->query($featured_query);
$featured_cars = [];

if ($featured_result && $featured_result->num_rows > 0) {
    while($row = $featured_result->fetch_assoc()) {
        $featured_cars[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Malawi's premier car buying and selling platform - Find your dream car today">
    <meta name="keywords" content="car dealership malawi, buy cars malawi, sell cars malawi, used cars, new cars">
    <title>DM Car Agency - Find Your Dream Car in Malawi</title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://images.unsplash.com">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/additional-styles.cs">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
.ai-chat-button {
    bottom: 150px !important;
}

.scroll-top {
    bottom: 50px !important;
}

/* Featured Cars Section Styles */
.featured-cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.featured-car-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s;
    cursor: pointer;
    position: relative;
}

.featured-car-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.featured-car-image {
    width: 100%;
    height: 250px;
    position: relative;
    overflow: hidden;
    background: #f0f0f0;
}

.featured-car-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.featured-car-card:hover .featured-car-image img {
    transform: scale(1.05);
}

.featured-car-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 4rem;
}

/* Badges */
.car-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 2;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    animation: badgeFadeIn 0.3s ease;
}

@keyframes badgeFadeIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.badge-featured {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.badge-new {
    background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.badge-hot {
    background: linear-gradient(135deg, #FF5722 0%, #D32F2F 100%);
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.badge i {
    font-size: 11px;
}

.image-count-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.featured-car-info {
    padding: 1.5rem;
}

.featured-car-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.featured-car-price {
    font-size: 1.6rem;
    font-weight: 700;
    color: #e8491d;
    margin-bottom: 1rem;
}

.featured-car-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.8rem;
    margin-bottom: 1rem;
}

.featured-detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.featured-detail-item i {
    color: #e8491d;
    width: 20px;
}

.featured-car-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #999;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
}

.view-details-btn {
    width: 100%;
    padding: 1rem;
    background: #e8491d;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.view-details-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.no-featured {
    text-align: center;
    padding: 3rem;
    color: #666;
}

@media (max-width: 768px) {
    .featured-cars-grid {
        grid-template-columns: 1fr;
    }
    
    .car-badges {
        top: 10px;
        left: 10px;
        gap: 6px;
    }
    
    .badge {
        padding: 5px 10px;
        font-size: 11px;
    }
}
</style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="nav-container">
                <div id="branding">
                    <h1><span class="highlight">DM</span> CAR AGENCY</h1>
                </div>
                <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <nav>
                    <ul id="nav-menu">
                        <li><a href="#showcase">HOME</a></li>
                        <li><a href="listings.php">INVENTORY</a></li>
                        <li><a href="sell-car.php">SELL CAR</a></li>
                        <li><a href="#why-choose">WHY US</a></li>
                        <li><a href="#contact">CONTACT</a></li>
                        <li><a href="join-team.php">JOIN TEAM</a></li>             
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="showcase">
        <div class="container">
            <h1>Find Your Dream Car in Malawi</h1>
            <p>Quality vehicles, trusted sellers, transparent deals</p>
            <div class="cta-buttons">
                <a href="listings.php" class="btn btn-primary">Browse Cars</a>
                <a href="sell-car.php" class="btn btn-secondary">Sell Your Car</a>
            </div>
        </div>
    </section>

    <!-- Enhanced Search Section -->
    <section id="search-section">
        <div class="container">
            <h2 class="search-title">Search for Your Perfect Vehicle</h2>
            <form class="search-form" id="search-form">
                <input type="text" placeholder="e.g., Toyota Fortuner, SUV, Red car" id="smart-search-input" autocomplete="off">
                <select id="price-range" aria-label="Price range">
                    <option value="">Any Price</option>
                    <option value="0-20000000">Under 20M MWK</option>
                    <option value="20000000-50000000">20M - 50M MWK</option>
                    <option value="50000000-100000000">50M - 100M MWK</option>
                    <option value="100000000-999999999">100M+ MWK</option>
                </select>
                <select id="car-type" aria-label="Car type">
                    <option value="">Any Type</option>
                    <option value="Sedan">Sedan</option>
                    <option value="SUV">SUV</option>
                    <option value="Truck">Truck</option>
                    <option value="Van">Van</option>
                </select>
                <button type="submit"><i class="fas fa-search"></i> Search Now</button>
            </form>
            <div class="search-suggestions" id="search-suggestions"></div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-car"></i>
                    <h3>500+</h3>
                    <p>Cars Available</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>1200+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-handshake"></i>
                    <h3>98%</h3>
                    <p>Satisfaction Rate</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-award"></i>
                    <h3>5 Years</h3>
                    <p>In Business</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cars -->
    <section id="featured-cars">
        <div class="container">
            <h2 class="section-title">Featured Vehicles</h2>
            <p style="text-align: center; margin-bottom: 30px; color: #666;">
                <?php echo !empty($featured_cars) ? 'Handpicked premium vehicles just for you' : 'Browse our complete inventory of quality vehicles'; ?>
            </p>

            <?php if (!empty($featured_cars)): ?>
                <div class="featured-cars-grid">
                    <?php foreach ($featured_cars as $car): ?>
                        <div class="featured-car-card">
                            <div class="featured-car-image">
                                <?php if (!empty($car['main_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($car['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>"
                                         onerror="this.parentElement.innerHTML='<div class=\'no-image\'><i class=\'fas fa-car\'></i></div>'">
                                    <?php if ($car['image_count'] > 1): ?>
                                        <span class="image-count-badge">
                                            <i class="fas fa-images"></i>
                                            <?php echo $car['image_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badges -->
                                <div class="car-badges">
                                    <span class="badge badge-featured">
                                        <i class="fas fa-star"></i> Featured
                                    </span>
                                    
                                    <?php
                                    // Check if car is new (less than 7 days old)
                                    $created = strtotime($car['created_at']);
                                    $days_old = floor((time() - $created) / 86400);
                                    if ($days_old <= 7):
                                    ?>
                                        <span class="badge badge-new">
                                            <i class="fas fa-sparkles"></i> New
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Hot deal for cars under 20M MWK
                                    if ($car['price'] < 20000000):
                                    ?>
                                        <span class="badge badge-hot">
                                            <i class="fas fa-fire"></i> Hot Deal
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="featured-car-info">
                                <h3 class="featured-car-title">
                                    <?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?>
                                </h3>
                                
                                <div class="featured-car-price">
                                    MWK <?php echo number_format($car['price']); ?>
                                </div>

                                <div class="featured-car-details">
                                    <div class="featured-detail-item">
                                        <i class="fas fa-road"></i>
                                        <span><?php echo number_format($car['mileage']); ?> km</span>
                                    </div>
                                    <div class="featured-detail-item">
                                        <i class="fas fa-cog"></i>
                                        <span><?php echo htmlspecialchars($car['transmission']); ?></span>
                                    </div>
                                    <div class="featured-detail-item">
                                        <i class="fas fa-gas-pump"></i>
                                        <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                                    </div>
                                    <div class="featured-detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($car['year']); ?></span>
                                    </div>
                                </div>

                                <div class="featured-car-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($car['location']); ?></span>
                                </div>

                                <a href="listings.php" class="view-details-btn">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-featured">
                    <p>No featured vehicles at the moment. Check back soon!</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 40px;">
                <a href="listings.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 18px;">
                    <i class="fas fa-car"></i> View All Listings
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="why-choose">
        <div class="container">
            <h2 class="section-title" style="color: white;">Why Choose DM Car Agency?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Verified Listings</h3>
                    <p>Every vehicle is inspected and verified for quality and authenticity</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Best Prices</h3>
                    <p>Competitive pricing with transparent and honest valuations</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our team is always ready to assist you with any questions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-file-contract"></i>
                    <h3>Easy Financing</h3>
                    <p>Flexible payment options and financing assistance available</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Excellent service! Found my dream car within a week. The team was professional and helpful throughout."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JM</div>
                        <div>
                            <strong>James Mwale</strong>
                            <p>Bought Toyota Fortuner</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Sold my car quickly at a fair price. Very transparent process and great communication."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">TC</div>
                        <div>
                            <strong>Tina Chirwa</strong>
                            <p>Sold Honda Civic</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Best car dealership in Malawi! Quality vehicles and honest dealings. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">PB</div>
                        <div>
                            <strong>Peter Banda</strong>
                            <p>Bought BMW X5</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <h2 class="section-title">Get in Touch</h2>
            <p>Ready to find your dream car or sell your vehicle? Contact us today!</p>
            <div class="contact-links">
                <a href="https://wa.me/265980717420" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i> WhatsApp Us
                </a>
                <a href="tel:+265980717420">
                    <i class="fas fa-phone"></i> Call Now
                </a>
                <a href="mailto:info@dmcaragency.com">
                    <i class="far fa-envelope"></i> Email Us
                </a>
                <a href="https://www.facebook.com/dmcaragency" target="_blank" rel="noopener">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About DM Car Agency</h3>
                    <p>Malawi's premier destination for buying and selling quality vehicles since 2024.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="listings.php">Browse Inventory</a></li>
                        <li><a href="#why-choose">Why Choose Us</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="sell-car.php">Sell Your Car</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="listings.php">Buy Cars</a></li>
                        <li><a href="sell-car.php">Sell Cars</a></li>
                        <li><a href="join-team.php">Join Team</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Blantyre, Malawi</p>
                    <p><i class="fas fa-phone"></i> +265 980 717 420</p>
                    <p><i class="fas fa-envelope"></i> info@dmcaragency.com</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> DM Car Agency. All Rights Reserved. | Designed for Excellence</p>
            </div>
        </div>
    </footer>

    <!-- AI Chat Assistant -->
    <button class="ai-chat-button" onclick="toggleAIChat()" aria-label="Open AI Assistant">
        <i class="fas fa-robot"></i>
    </button>

    <div class="ai-chat-window" id="ai-chat-window">
        <div class="ai-chat-header">
            <div>
                <i class="fas fa-robot"></i>
                <span>DM Car Assistant</span>
            </div>
            <button onclick="toggleAIChat()" aria-label="Close chat">&times;</button>
        </div>
        <div class="ai-chat-messages" id="ai-chat-messages"></div>
        <form class="ai-chat-input" onsubmit="sendAIMessage(event)">
            <input type="text" id="ai-input" placeholder="Type your message..." autocomplete="off">
            <button type="submit" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop" onclick="scrollToTop()" aria-label="Scroll to top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- JavaScript -->
    <script src="scripts.js"></script>
</body>
</html>
