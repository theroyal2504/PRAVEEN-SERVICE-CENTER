<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRAVEEN SERVICE CENTER - Professional Bike Repair & Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-repeat: no-repeat;
            background-position: bottom;
            background-size: cover;
            opacity: 0.1;
        }

        /* Navigation */
        .navbar {
            background: transparent !important;
            padding: 20px 0;
            transition: all 0.3s;
        }
        
        .navbar.scrolled {
            background: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
        }
        
        .navbar.scrolled .nav-link {
            color: #333 !important;
        }
        
        .navbar.scrolled .navbar-brand {
            color: #667eea !important;
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: #ffd700 !important;
            transform: translateY(-2px);
        }
        
        .login-btn {
            background: white;
            color: #667eea !important;
            padding: 8px 25px !important;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .login-btn:hover {
            background: #ffd700;
            color: #333 !important;
            transform: translateY(-2px);
        }

        /* Hero Content */
        .hero-content {
            padding: 150px 0 100px;
            color: white;
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }
        
        .hero-subtitle {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }
        
        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            margin-right: 15px;
            transition: all 0.3s;
        }
        
        .btn-primary-custom {
            background: #ffd700;
            color: #333;
            border: 2px solid #ffd700;
        }
        
        .btn-primary-custom:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Hero Image */
        .hero-image {
            animation: float 3s ease-in-out infinite;
        }
        
        .hero-image img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .stat-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 16px;
            color: #666;
        }

        /* Services Section */
        .services-section {
            padding: 80px 0;
            background: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .section-title p {
            font-size: 18px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
            border: 1px solid #f0f0f0;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .service-icon i {
            font-size: 40px;
            color: white;
        }
        
        .service-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .service-card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .service-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .service-link:hover {
            color: #764ba2;
            padding-left: 5px;
        }

        /* Tools Section */
        .tools-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .tool-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .tool-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .tool-item img {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
        }
        
        .tool-item h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        /* Team Section */
        .team-section {
            padding: 80px 0;
            background: white;
        }
        
        .team-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .team-image {
            height: 300px;
            overflow: hidden;
        }
        
        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }
        
        .team-card:hover .team-image img {
            transform: scale(1.1);
        }
        
        .team-info {
            padding: 20px;
            text-align: center;
        }
        
        .team-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .team-info p {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .team-social a {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #f0f0f0;
            border-radius: 50%;
            line-height: 35px;
            text-align: center;
            color: #666;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .team-social a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }

        /* Video Section */
        .video-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 80px 0;
            background: white;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 60px;
            color: #667eea;
            opacity: 0.2;
            font-family: serif;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        
        .author-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .author-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .author-info p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        /* Login Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        
        .modal-header .btn-close {
            color: white;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .login-form .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #f0f0f0;
            transition: all 0.3s;
        }
        
        .login-form .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        .login-form .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .login-form .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .demo-credentials p {
            margin: 5px 0;
            color: #666;
        }

        /* Contact Section */
        .contact-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .contact-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .contact-info h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .contact-detail {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .contact-icon i {
            font-size: 24px;
            color: white;
        }
        
        .contact-text h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .contact-text p {
            color: #666;
            margin: 0;
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer h5 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #999;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links a {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            line-height: 35px;
            text-align: center;
            color: white;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #999;
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
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="bi bi-bicycle"></i> PRAVEEN SERVICE CENTER
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tools">Tools</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#team">Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#videos">Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link login-btn" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center hero-content">
                <div class="col-lg-6">
                    <h1 class="hero-title">Your Trusted Bike Repair & Service Partner</h1>
                    <p class="hero-subtitle">With over 10+ years of experience, we provide professional bike repair, maintenance, and service solutions. Our expert team uses latest tools and technology to keep your bike running smoothly.</p>
                    <div class="hero-buttons">
                        <a href="#services" class="btn btn-custom btn-primary-custom">Our Services</a>
                        <a href="#" class="btn btn-custom btn-outline-custom" data-bs-toggle="modal" data-bs-target="#loginModal">Staff Login</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="https://images.unsplash.com/photo-1486006920555-c77dc445181e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Repair" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-bicycle"></i>
                        </div>
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Bikes Repaired</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Tools & Equipment</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>We offer comprehensive bike repair and maintenance services to keep your ride in perfect condition</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-wrench"></i>
                        </div>
                        <h3>General Repair</h3>
                        <p>Complete bike repair services including brake adjustment, gear tuning, and general maintenance.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <h3>Oil Change</h3>
                        <p>Professional engine oil change service using high-quality oils for better performance.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-brightness-high"></i>
                        </div>
                        <h3>Light & Electrical</h3>
                        <p>Complete electrical system repair including headlights, indicators, and wiring.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-circle"></i>
                        </div>
                        <h3>Tire & Wheel</h3>
                        <p>Tire replacement, puncture repair, wheel balancing, and alignment services.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Brake Service</h3>
                        <p>Complete brake system inspection, pad replacement, and brake fluid check.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h3>Engine Tuning</h3>
                        <p>Professional engine tuning for better performance and fuel efficiency.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tools Section -->
    <section id="tools" class="tools-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Tools & Equipment</h2>
                <p>We use state-of-the-art tools and diagnostic equipment for accurate repairs</p>
            </div>
            <div class="row g-3">
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-wrench display-4 text-primary"></i>
                        <h4>Wrench Set</h4>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-gear display-4 text-success"></i>
                        <h4>Diagnostic Tool</h4>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-tools display-4 text-warning"></i>
                        <h4>Tool Kit</h4>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-brightness-high display-4 text-danger"></i>
                        <h4>Light Tester</h4>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-droplet display-4 text-info"></i>
                        <h4>Oil Gun</h4>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="tool-item">
                        <i class="bi bi-circle display-4 text-secondary"></i>
                        <h4>Tire Changer</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Expert Team</h2>
                <p>Meet our skilled mechanics who ensure your bike gets the best care</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Team Member">
                        </div>
                        <div class="team-info">
                            <h3>Pradeep Kumar</h3>
                            <p>Master Mechanic</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" alt="Team Member">
                        </div>
                        <div class="team-info">
                            <h3>Monu Sharma</h3>
                            <p>Service Expert</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1581299894026-b4c4b5c1aae3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Team Member">
                        </div>
                        <div class="team-info">
                            <h3>Rahul Verma</h3>
                            <p>Diagnostic Specialist</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section id="videos" class="video-section">
        <div class="container">
            <div class="section-title text-white">
                <h2 class="text-white">Our Work in Action</h2>
                <p class="text-white-50">Watch how we transform bikes with our expert service</p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Bike Repair Video" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Bike Service Video" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-title">
                <h2>What Our Customers Say</h2>
                <p>Real feedback from our valued customers</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4">"Best service center in town! They fixed my bike quickly and at a reasonable price. Highly recommended!"</p>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Customer" class="author-image">
                            <div class="author-info">
                                <h4>Rajesh Singh</h4>
                                <p>Happy Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4">"Professional service and friendly staff. They explained everything before starting the work. Very satisfied!"</p>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="Customer" class="author-image">
                            <div class="author-info">
                                <h4>Priya Patel</h4>
                                <p>Regular Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4">"Great experience! They have all the modern tools and the mechanics are very skilled. My bike runs like new."</p>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/3.jpg" alt="Customer" class="author-image">
                            <div class="author-info">
                                <h4>Amit Kumar</h4>
                                <p>Bike Enthusiast</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch with us for any queries or service appointments</p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="contact-info">
                        <h3>Get In Touch</h3>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Address</h4>
                                <p>123 Bike Street, Auto Nagar, Mumbai - 400001</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Phone</h4>
                                <p>+91 93405 27152</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Email</h4>
                                <p>info@praveenservice.com</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Working Hours</h4>
                                <p>Monday - Saturday: 9:00 AM - 8:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3771.4010564446425!2d72.833333!3d19.033333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7ce5a9f3b7a1b%3A0x8a5f5d5a5b5c5d5e!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>PRAVEEN SERVICE CENTER</h5>
                    <p>Your trusted partner for all bike repair and maintenance needs. With years of experience and expert mechanics, we ensure your bike runs perfectly.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#tools">Tools</a></li>
                        <li><a href="#team">Team</a></li>
                        <li><a href="#videos">Videos</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Our Services</h5>
                    <ul class="footer-links">
                        <li><a href="#">General Repair</a></li>
                        <li><a href="#">Oil Change</a></li>
                        <li><a href="#">Brake Service</a></li>
                        <li><a href="#">Engine Tuning</a></li>
                        <li><a href="#">Tire Service</a></li>
                        <li><a href="#">Electrical Repair</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2026 PRAVEEN SERVICE CENTER. All rights reserved. | Designed with <i class="bi bi-heart-fill text-danger"></i> for bike lovers</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login to Management System</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="login-form">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Login</button>
                    </form>
                    
                    <div class="demo-credentials">
                        <p class="mb-2"><strong>Demo Credentials:</strong></p>
                        <p class="mb-1"><i class="bi bi-person-circle"></i> Admin: Pradeep / admin123</p>
                        <p class="mb-0"><i class="bi bi-person-circle"></i> Staff: Monu / staff123</p>
                        <p class="text-muted small mt-2"><i class="bi bi-info-circle"></i> Database: bike_management_system</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);

        // Handle login form submission
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Simple validation
            if (username && password) {
                // Submit the form
                this.submit();
            } else {
                alert('Please enter both username and password');
            }
        });

        // Add animation on scroll
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.service-card, .team-card, .stat-item, .tool-item, .testimonial-card');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementBottom = element.getBoundingClientRect().bottom;
                
                if (elementTop < window.innerHeight - 100 && elementBottom > 0) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };

        // Set initial styles for animation
        document.querySelectorAll('.service-card, .team-card, .stat-item, .tool-item, .testimonial-card').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'all 0.6s ease';
        });

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>