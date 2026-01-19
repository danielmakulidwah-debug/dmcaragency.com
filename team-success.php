<?php
// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Team! - DM Car Agency</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* === GENERAL STYLES === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1a1a2e;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        html {
            scroll-behavior: smooth;
        }

        /* === BACKGROUND DECORATIONS === */
        .background-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            opacity: 0.08;
            filter: blur(50px);
            border-radius: 40%;
        }

        .blob-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -100px;
            right: -100px;
            animation: blob-animation 8s infinite;
        }

        .blob-2 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -50px;
            left: -50px;
            animation: blob-animation 10s infinite reverse;
        }

        .blob-3 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            right: 10%;
            animation: blob-animation 12s infinite;
        }

        @keyframes blob-animation {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(30px, -50px) scale(1.1);
            }
            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        /* === HEADER === */
        .header {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }

        .logo i {
            font-size: 30px;
        }

        /* === PAGE CONTAINER === */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* === WELCOME SECTION === */
        .welcome-section {
            text-align: center;
            padding: 80px 40px;
            animation: fadeInDown 0.8s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-animation {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease-out;
        }

        .success-animation i {
            color: #27ae60;
            text-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .welcome-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 20px;
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .welcome-message {
            font-size: 16px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* === CONTENT GRID === */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        /* === SECTION TITLES === */
        .how-it-works h2,
        .earning-section h2,
        .benefits-section h2,
        .next-steps h2,
        .whatsapp-section h2,
        .cta-section h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1a1a2e;
        }

        .how-it-works h2 i,
        .earning-section h2 i,
        .benefits-section h2 i,
        .next-steps h2 i {
            color: #667eea;
            font-size: 40px;
        }

        /* === HOW IT WORKS === */
        .how-it-works {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

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

        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border-top: 4px solid #667eea;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: 30px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .step-icon {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 20px;
        }

        .step-card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1a1a2e;
        }

        .step-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* === EARNING SECTION === */
        .earning-section {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .earning-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .earning-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 18px;
            padding: 35px 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .earning-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
        }

        .earning-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 20px;
        }

        .earning-card h3 {
            font-size: 18px;
            margin-bottom: 12px;
            color: #1a1a2e;
        }

        .earning-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        .highlight {
            color: #667eea;
            font-weight: 700;
        }

        .earning-detail {
            font-size: 12px;
            color: #999;
            font-style: italic;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        /* === WHATSAPP SECTION === */
        .whatsapp-section {
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            color: white;
            animation: fadeInUp 0.8s ease-out 0.6s both;
            box-shadow: 0 20px 60px rgba(37, 211, 102, 0.3);
        }

        .whatsapp-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .whatsapp-icon-large {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .whatsapp-section h2 {
            color: white;
            font-size: 40px;
            margin-bottom: 15px;
        }

        .whatsapp-section > p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .group-benefits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            text-align: left;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .benefit i {
            font-size: 20px;
            color: #fff;
        }

        .btn-whatsapp {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #25d366;
            padding: 16px 40px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-whatsapp:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-whatsapp i {
            font-size: 20px;
        }

        .group-note {
            font-size: 12px;
            margin-top: 15px;
            opacity: 0.9;
        }

        /* === BENEFITS SECTION === */
        .benefits-section {
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .benefit-card {
            background: white;
            border-radius: 18px;
            padding: 35px 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 5px solid #667eea;
        }

        .benefit-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
        }

        .benefit-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 20px;
        }

        .benefit-card h3 {
            font-size: 18px;
            margin-bottom: 12px;
            color: #1a1a2e;
        }

        .benefit-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* === NEXT STEPS === */
        .next-steps {
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .steps-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .step-item {
            display: flex;
            gap: 30px;
            padding: 30px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 5px solid #667eea;
        }

        .step-item:hover {
            transform: translateX(10px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.15);
        }

        .step-badge {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .step-content h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #1a1a2e;
        }

        .step-content p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* === CTA SECTION === */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            padding: 80px 40px;
            text-align: center;
            color: white;
            margin: 60px 0;
            animation: fadeInUp 0.8s ease-out 0.9s both;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }

        .cta-section h2 {
            font-size: 40px;
            margin-bottom: 15px;
            color: white;
        }

        .cta-section > p {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .btn-primary-large {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: white;
            color: #667eea;
            padding: 18px 50px;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-primary-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.3);
        }

        .btn-primary-large i {
            font-size: 22px;
        }

        /* === FOOTER === */
        .footer {
            background: #1a1a2e;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 40px;
            font-size: 14px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .welcome-section {
                padding: 40px 20px;
            }

            .welcome-title {
                font-size: 28px;
            }

            .welcome-subtitle {
                font-size: 16px;
            }

            .success-animation {
                font-size: 60px;
            }

            .how-it-works h2,
            .earning-section h2,
            .benefits-section h2,
            .next-steps h2 {
                font-size: 24px;
            }

            .steps-container,
            .earning-cards,
            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .whatsapp-section {
                padding: 40px 20px;
            }

            .group-benefits {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .step-item {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }

            .cta-section {
                padding: 40px 20px;
            }

            .cta-section h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="background-decoration">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-car"></i>
                <span>DM CAR AGENCY</span>
            </div>
        </div>
    </header>

    <div class="page-container">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="success-animation">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="welcome-title">Welcome to the Team! 🎉</h1>
            <p class="welcome-subtitle">Your journey to success starts here</p>
            <p class="welcome-message">
                Congratulations! Your application has been approved. You're now ready to start selling premium vehicles through our agency and earning excellent commissions.
            </p>
        </section>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- How It Works -->
            <section class="how-it-works">
                <h2><i class="fas fa-cogs"></i> How It Works</h2>
                <div class="steps-container">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Join Our WhatsApp Group</h3>
                        <p>Click the button below to join our exclusive WhatsApp group. It's completely FREE and takes just 30 seconds.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Browse Available Cars</h3>
                        <p>Find the perfect vehicles from our extensive inventory listed daily in the group. All premium cars ready for sale.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-megaphone"></i>
                        </div>
                        <h3>Share & Advertise</h3>
                        <p>Share cars on your networks, social media, or directly with potential buyers using our marketing materials.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>Earn Commissions</h3>
                        <p>For every successful sale you close, earn competitive commissions. No limits on your earning potential!</p>
                    </div>
                </div>
            </section>

            <!-- Earning Section -->
            <section class="earning-section">
                <h2><i class="fas fa-money-bill-wave"></i> How You Make Money</h2>
                <div class="earning-cards">
                    <div class="earning-card">
                        <div class="earning-icon">
                            <i class="fas fa-percent"></i>
                        </div>
                        <h3>Commission Per Sale</h3>
                        <p>Earn <span class="highlight">5-15% commission</span> on each vehicle sale you close.</p>
                        <div class="earning-detail">Depending on vehicle type & price</div>
                    </div>

                    <div class="earning-card">
                        <div class="earning-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Unlimited Potential</h3>
                        <p>There's <span class="highlight">no cap</span> on how much you can earn. The more you sell, the more you make.</p>
                        <div class="earning-detail">Scale your income as you grow</div>
                    </div>

                    <div class="earning-card">
                        <div class="earning-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Quick Payouts</h3>
                        <p>Get paid <span class="highlight">within 2 days</span> of sale completion. Fast, reliable, and transparent.</p>
                        <div class="earning-detail">Direct transfer to your account</div>
                    </div>

                    <div class="earning-card">
                        <div class="earning-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h3>Bonuses & Incentives</h3>
                        <p>Earn <span class="highlight">monthly bonuses</span> for top performers and reaching sales targets.</p>
                        <div class="earning-detail">Extra rewards for excellence</div>
                    </div>
                </div>
            </section>

            <!-- WhatsApp Group -->
            <section class="whatsapp-section">
                <div class="whatsapp-content">
                    <div class="whatsapp-icon-large">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h2>Join Our WhatsApp Group</h2>
                    <p>Get instant access to our complete car inventory and connect with the team</p>
                    
                    <div class="group-benefits">
                        <div class="benefit">
                            <i class="fas fa-check"></i>
                            <span>Daily car listings & updates</span>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-check"></i>
                            <span>Direct support from management</span>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-check"></i>
                            <span>Marketing materials & tips</span>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-check"></i>
                            <span>Networking with other agents</span>
                        </div>
                    </div>

                    <a href="https://chat.whatsapp.com/EnlSrBu2kFZ0GuNEo7yIuC" target="_blank" class="btn-whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        Join WhatsApp Group Now
                    </a>
                    <p class="group-note">Free • No obligations • Cancel anytime</p>
                </div>
            </section>

            <!-- Benefits -->
            <section class="benefits-section">
                <h2><i class="fas fa-star"></i> Why Join Our Team?</h2>
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Professional Support</h3>
                        <p>Dedicated team ready to help you succeed with guidance and resources</p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Training & Resources</h3>
                        <p>Access to sales techniques, vehicle knowledge, and marketing strategies</p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Premium Inventory</h3>
                        <p>Work with high-quality vehicles that customers want to buy</p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3>Credibility</h3>
                        <p>Sell under our trusted brand name with proven reputation</p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h3>Network Effect</h3>
                        <p>Access to buyer networks and leads from fellow agents</p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Recognition</h3>
                        <p>Top performers get featured and receive special recognition</p>
                    </div>
                </div>
            </section>

            <!-- Next Steps -->
            <section class="next-steps">
                <h2><i class="fas fa-arrow-right"></i> Next Steps</h2>
                <div class="steps-list">
                    <div class="step-item">
                          <div class="step-badge">1</div>
                        <div class="step-content">
                            <h3>Join WhatsApp Group</h3>
                            <p>Click the button above to join and start viewing available vehicles immediately.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-badge">2</div>
                        <div class="step-content">
                            <h3>Introduce Yourself</h3>
                            <p>Say hello to the team! Share your background and sales goals to get personalized support.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-badge">3</div>
                        <div class="step-content">
                            <h3>Pick Your First Car</h3>
                            <p>Browse the listings and choose vehicles you're confident about selling.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-badge">4</div>
                        <div class="step-content">
                            <h3>Start Advertising</h3>
                            <p>Use our marketing materials to share on social media and with your network.</p>
                        </div>
                        </div>                                   
                        
                        <div class="step-badge">5</div>
                        <div class="step-content">
                            <h3>Close Deals & Earn</h3>
                            <p>Connect buyers with sellers and earn your commission. Keep it up!</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2>Ready to Start Your Journey?</h2>
            <p>Join thousands of successful agents making great money with us</p>
            <a href="https://chat.whatsapp.com/EnlSrBu2kFZ0GuNEo7yIuC" target="_blank" class="btn-primary-large">
                <i class="fab fa-whatsapp"></i>
                Join WhatsApp Group & Start Earning
            </a>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> DM Car Agency. All rights reserved. Welcome to the family!</p>
    </footer>

    <script>
        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Confetti animation on page load
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#25d366', '#f093fb', '#f5576c'];
            const confettiCount = 50;

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-10px';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.pointerEvents = 'none';
                confetti.style.zIndex = '999';
                confetti.style.opacity = '0.8';
                
                document.body.appendChild(confetti);

                const duration = Math.random() * 3 + 2;
                const xMovement = (Math.random() - 0.5) * 200;
                const yMovement = Math.random() * 500 + 300;

                confetti.animate([
                    {
                        transform: `translate(0, 0) rotate(0deg)`,
                        opacity: 1
                    },
                    {
                        transform: `translate(${xMovement}px, ${yMovement}px) rotate(360deg)`,
                        opacity: 0
                    }
                ], {
                    duration: duration * 1000,
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                });

                setTimeout(() => confetti.remove(), duration * 1000);
            }
        }

        // Trigger confetti on page load
        window.addEventListener('load', () => {
            createConfetti();
        });

        // WhatsApp button click tracking
        const whatsappButtons = document.querySelectorAll('.btn-whatsapp, .btn-primary-large');
        whatsappButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('WhatsApp group link clicked');
            });
        });

        console.log('Welcome page loaded successfully!');
    </script>
</body>
</html>
                      