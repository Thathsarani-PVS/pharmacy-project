<?php
session_start();
$is_logged_in = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24h Health - Premium Pharmacy</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' fill='%2300a896'/><line x1='12' y1='8' x2='12' y2='16' stroke='white' stroke-width='2' stroke-linecap='round'/><line x1='8' y1='12' x2='16' y2='12' stroke='white' stroke-width='2' stroke-linecap='round'/></svg>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --primary-teal: #050606; --dark-teal: #007a78; --light-teal: #e6fffa; }
        
        body {
            font-family: 'Poppins', sans-serif !important;
            background: #f5fbf7;
            color: #2d3748;
        }

        /* Top Bar for Emergency Hotline */
        .top-emergency-bar {
            background: linear-gradient(135deg, #005f5d, #007a78);
            color: #ffffff;
            font-size: 0.9rem;
            padding: 8px 0;
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .top-emergency-bar a {
            color: #ffffff !important;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .top-emergency-bar a:hover {
            opacity: 0.85;
            text-decoration: underline;
        }

        /* Navbar Custom Styling */
        .navbar-custom { 
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(10px); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .navbar-nav .nav-link {
            font-weight: 600;
            color: #4a5568 !important;
            padding: 8px 16px !important;
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: #00a896 !important;
        }

        /* Typography */
        h1, h2, h3, h4, .fw-bold {
            font-weight: 800 !important;
            letter-spacing: -0.5px;
            color: #1a202c;
        }

        .premium-card { 
            background: white; 
            border-radius: 24px; 
            padding: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
        }
        .premium-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        }
        .premium-card p {
            font-weight: 400;
            line-height: 1.7;
            color: #4a5568;
            font-size: 0.95rem;
        }

        .section-title { 
            font-weight: 800; 
            color: var(--dark-teal); 
            margin-bottom: 10px; 
            font-size: 2.2rem;
        }

        /* Hero Section */
        .hero-section { 
            min-height: 75vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
            color: white; 
            background: linear-gradient(45deg, #00a896, #007a78, #2a9d8f); 
            background-size: 300% 300%; 
            animation: gradientMove 9.5s ease infinite;
            padding: 60px 20px;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 900px;
        }

        .text-slider-wrapper {
            height: 110px;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .text-slider {
            position: relative;
            width: 100%;
        }

        .fade-text {
            position: absolute;
            width: 100%;
            left: 0;
            top: 0;
            font-size: 3.2rem;
            font-weight: 800;
            margin: 0;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeAnimation 9s infinite;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }

        .description {
            font-size: 1.2rem;
            font-weight: 400;
            opacity: 0.95;
            max-width: 700px;
        }

        /* Branded Button */
        .btn-branded {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: #007a78;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .btn-branded:hover {
            background: #005f5d;
            color: #ffffff;
            transform: translateY(-4px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.25);
        }

        .navbar-nav .btn {
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeAnimation {
            0% { opacity: 0; transform: translateY(20px); }
            10% { opacity: 1; transform: translateY(0); }
            33% { opacity: 1; transform: translateY(0); }
            43% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 0; }
        }

        .fade-text:nth-child(1) { animation-delay: 0s; }
        .fade-text:nth-child(2) { animation-delay: 3s; }
        .fade-text:nth-child(3) { animation-delay: 6s; }

        /* Category Card Feature */
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            border: 1px solid rgba(0, 168, 150, 0.1);
        }
        .category-card:hover {
            transform: translateY(-5px);
            background: #e6fffa;
            border-color: #00a896;
        }
        .category-card h5 {
            color: #1a202c;
            font-weight: 700;
            margin-top: 15px;
            font-size: 1.1rem;
        }

        /* Stats Counter Feature */
        .stats-box {
            background: linear-gradient(135deg, #005f5d, #007a78);
            color: white;
            border-radius: 24px;
            padding: 40px 20px;
            margin: 60px 0;
        }
        .stats-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: #ffffff;
        }

        /* Floating Emergency Button */
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #25d366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            z-index: 1000;
            transition: transform 0.3s;
            text-decoration: none;
            font-size: 30px;
        }
        .floating-btn:hover {
            transform: scale(1.1);
            color: white;
        }

        /* Modern Attractive Footer */
        .footer-custom {
            background-color: #1a202c;
            color: #a0aec0;
            padding: 60px 0 20px 0;
            margin-top: 80px;
        }
        .footer-custom h5 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .footer-custom ul li {
            margin-bottom: 12px;
        }
        .footer-custom ul li a {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-custom ul li a:hover {
            color: #00a896;
        }
        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .social-icon:hover {
            background: #00a896;
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

<!-- Emergency Top Bar -->
<div class="top-emergency-bar">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span>Colombo 03, Sri Lanka</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span class="fw-bold">24/7 Emergency Hotline:</span> 
            <a href="tel:+94112345678" class="fw-bold text-white text-decoration-underline">+94 11 234 5678</a>
        </div>
    </div>
</div>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php" style="color: #007a78; font-size: 24px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#00a896" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="#e6fffa"/>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>24h Health</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonials">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                
                <?php if ($is_logged_in): ?>
                    <li class="nav-item ms-lg-3">
                        <a href="logout.php" class="btn btn-outline-danger px-3">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a href="login_form.php" class="btn btn-outline-primary px-3" style="border-color: #00a896; color: #00a896;">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="btn btn-primary px-3 shadow-sm" style="background: #00a896; border: none; color: white;">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div id="home" class="hero-section">
    <div class="hero-content">
        <div class="text-slider-wrapper">
            <div class="text-slider">
                <h1 class="fade-text">24h Service</h1>
                <h1 class="fade-text">Premium Healthcare</h1>
                <h1 class="fade-text">Trusted Consultation</h1>
            </div>
        </div>
    
        <p class="description mt-3 mb-4">Experience top-tier healthcare services and professional consultation right at your fingertips.</p>
    
        <!-- Shop Now Button (Path intact) -->
        <a href="../customer/shop.php" class="btn-branded">Shop Now</a>
    </div>
</div>

<!-- Featured Medical Categories Section (NEW FEATURE) -->
<div id="categories" class="container py-5 mt-3">
    <div class="text-center mb-5">
        <h2 class="section-title">Explore Categories</h2>
        <p class="text-muted">Quickly find what you need from our verified medical categories.</p>
    </div>
    <div class="row g-4">
        <div class="col-6 col-md-3">
            <a href="../customer/shop.php" class="category-card">
                <div class="fs-1 text-teal mb-2" style="color: #00a896;">💊</div>
                <h5>Prescription Meds</h5>
                <p class="text-muted small mb-0">Genuine medicines</p>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="../customer/shop.php" class="category-card">
                <div class="fs-1 text-teal mb-2" style="color: #00a896;">🌿</div>
                <h5>Vitamins & Herbs</h5>
                <p class="text-muted small mb-0">Daily supplements</p>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="../customer/shop.php" class="category-card">
                <div class="fs-1 text-teal mb-2" style="color: #00a896;">🩹</div>
                <h5>First Aid & Care</h5>
                <p class="text-muted small mb-0">Emergency supplies</p>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="../customer/shop.php" class="category-card">
                <div class="fs-1 text-teal mb-2" style="color: #00a896;">👶</div>
                <h5>Baby & Mom</h5>
                <p class="text-muted small mb-0">Safe essentials</p>
            </a>
        </div>
    </div>
</div>

<!-- Statistics Counter Section (NEW FEATURE) -->
<div class="container">
    <div class="stats-box text-center">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stats-number">24/7</div>
                <p class="mb-0 text-light opacity-75">Emergency Service & Support</p>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stats-number">10k+</div>
                <p class="mb-0 text-light opacity-75">Satisfied Customers</p>
            </div>
            <div class="col-md-4">
                <div class="stats-number">100%</div>
                <p class="mb-0 text-light opacity-75">Certified Genuine Products</p>
            </div>
        </div>
    </div>
</div>

<!-- About Us Section with Iconic Cards -->
<div id="about" class="container py-4">
    <div class="text-center mb-5">
        <h2 class="section-title">About Us</h2>
        <p class="text-muted">Dedicated to providing unmatched medical excellence and continuous care.</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="premium-card p-4 h-100 text-center" style="border-top: 5px solid #00a896;">
                <div class="icon-box mb-3" style="background: #e6fffa; color: #00a896; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h4 class="fw-bold mb-3">Our History</h4>
                <p>Established with a vision to provide accessible healthcare, we have grown into a trusted digital pharmacy hub serving thousands daily.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="premium-card p-4 h-100 text-center" style="border-top: 5px solid #00a896;">
                <div class="icon-box mb-3" style="background: #e6fffa; color: #00a896; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                </div>
                <h4 class="fw-bold mb-3">Our Services</h4>
                <p>We offer 24/7 medicine delivery, professional pharmacist consultations, and high-quality genuine medical supplies directly to you.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="premium-card p-4 h-100 text-center" style="border-top: 5px solid #00a896;">
                <div class="icon-box mb-3" style="background: #e6fffa; color: #00a896; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h4 class="fw-bold mb-3">Why Trust Us?</h4>
                <p>Quality is our priority. We are fully certified, ensuring every product you receive meets the highest safety and authenticity standards.</p>
            </div>
        </div>
    </div>
</div>

<!-- Customer Reviews Section -->
<div id="testimonials" class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="text-muted">Real feedback from people who trust our pharmacy services.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="premium-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">★★★★★</div>
                    <p class="small text-muted mb-4">"Very fast delivery service! I ordered my regular prescription medicines late at night, and they delivered them safely within an hour. Highly recommended!"</p>
                </div>
                <div class="d-flex align-items-center gap-3 border-top pt-3">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background: #00a896 !important;">A</div>
                    <div>
                        <h6 class="fw-bold mb-0 small">Asitha Perera</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Colombo</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">★★★★★</div>
                    <p class="small text-muted mb-4">"The 24/7 hotline is a lifesaver. I had an urgent medical requirement and they guided me properly and sent the correct items instantly."</p>
                </div>
                <div class="d-flex align-items-center gap-3 border-top pt-3">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background: #007a78 !important;">F</div>
                    <div>
                        <h6 class="fw-bold mb-0 small">Fathima Ruzaina</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Dehiwala</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">★★★★★</div>
                    <p class="small text-muted mb-4">"Genuine products and friendly customer service support. It's so convenient to order everything from home without any hassle."</p>
                </div>
                <div class="d-flex align-items-center gap-3 border-top pt-3">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background: #2a9d8f !important;">N</div>
                    <div>
                        <h6 class="fw-bold mb-0 small">Nuwan Jayasinghe</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Nugegoda</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div id="faq" class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <p class="text-muted">Find quick answers about our pharmacy services and delivery.</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-3 premium-card shadow-sm" style="padding: 10px;">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button fw-bold bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            How do I order medicines online?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            Simply click on the "Shop Now" button, browse our inventory, add your required products to the cart, and proceed to checkout securely.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 premium-card shadow-sm" style="padding: 10px;">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            Are your medicines 100% genuine and certified?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            Yes, all our medical items and products are sourced directly from certified manufacturers and official pharmaceutical distributors.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 premium-card shadow-sm" style="padding: 10px;">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            Do you offer 24/7 delivery services?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            Yes, our pharmacy operations and emergency delivery support run 24 hours a day, 7 days a week.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Head Office & Contact with Map -->
<div id="contact" class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">Head Office & Contact</h2>
        <p class="text-muted">Get in touch with our team or visit our headquarters.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-5">
            <div class="premium-card p-4 h-100 d-flex flex-column justify-content-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: #e6fffa; padding: 15px; border-radius: 16px; color: #00a896;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Call Support</h6>
                        <p class="text-muted small mb-0">+94 11 234 5678</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: #e6fffa; padding: 15px; border-radius: 16px; color: #00a896;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Head Office Hub</h6>
                        <p class="text-muted small mb-0">No. 45, Healthcare Avenue, Colombo 03, Sri Lanka.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: #e6fffa; padding: 15px; border-radius: 16px; color: #00a896;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Official Dispatch Email</h6>
                        <p class="text-muted small mb-0">orders@24hhealth.com</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="premium-card overflow-hidden h-100 p-0" style="min-height: 350px;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15844.204595821033!2d79.843075!3d6.920153!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2593cf65a1e9d%3A0x53139de6615e9b89!2sColombo!5e0!3m2!1sen!2slk!4v1700000000000" width="100%" height="100%" style="border:0; min-height: 350px;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Floating Emergency Contact Button -->
<a href="tel:+94112345678" class="floating-btn" title="Call Emergency Hotline">
    📞
</a>

<!-- Modern Attractive Footer -->
<footer class="footer-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <a class="d-flex align-items-center gap-2 fw-bold text-white mb-3" href="index.php" style="font-size: 22px; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#00a896" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="#1a202c"/>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <span>24h Health</span>
                </a>
                <p class="small text-muted">Your trusted 24/7 digital healthcare and pharmacy companion, dedicated to keeping you and your family safe and healthy.</p>
            </div>
            <div class="col-md-2 offset-md-1">
                <h5>Quick Links</h5>
                <ul class="list-unstyled small">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#testimonials">Reviews</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h5>Services</h5>
                <ul class="list-unstyled small">
                    <li><a href="#categories">Medicine Delivery</a></li>
                    <li><a href="#about">Consultations</a></li>
                    <li><a href="#about">Certified Medicals</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Connect With Us</h5>
                <p class="small text-muted">Follow us on social media for health updates and exclusive offers.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-icon">🌐</a>
                    <a href="#" class="social-icon">📘</a>
                    <a href="#" class="social-icon">📸</a>
                    <a href="#" class="social-icon">💼</a>
                </div>
            </div>
        </div>
        <hr class="border-secondary mt-5 mb-4">
        <div class="row text-center text-md-start small">
            <div class="col-md-6 text-muted">
                &copy; 2026 24h Health Pharmacy. All rights reserved.
            </div>
            <div class="col-md-6 text-md-end text-muted">
                Designed with precision for better healthcare.
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>