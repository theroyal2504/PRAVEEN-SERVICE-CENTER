<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}


// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Using same md5 as in your database
    
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        redirect('dashboard.php');
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRAVEEN SERVICE CENTER - Your Trusted Bike Repair & Service Partner</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }

        /* Navigation */
        .navbar {
            background: rgba(0, 0, 0, 0.9) !important;
            padding: 15px 0;
            transition: all 0.3s;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar.scrolled {
            background: #000 !important;
            padding: 10px 0;
        }

        .navbar-brand {
            font-size: 28px;
            font-weight: 800;
            color: #ffc107 !important;
            letter-spacing: 1px;
        }

        .navbar-brand i {
            color: #ffc107;
            margin-right: 10px;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 15px;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ffc107;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #ffc107 !important;
            transform: translateY(-2px);
        }

        .login-btn {
            background: #ffc107;
            color: #000 !important;
            padding: 8px 25px !important;
            border-radius: 50px;
            font-weight: 600;
        }

        .login-btn:hover {
            background: #fff;
            color: #000 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,193,7,0.3);
        }

        .login-btn::after {
            display: none;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1558981806-ec527fa84c39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 0;
        }

        .hero-content {
            color: #fff;
        }

        .hero-title {
            font-size: 60px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-title span {
            color: #ffc107;
        }

        .hero-subtitle {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.8;
        }

        .hero-buttons {
            margin-top: 40px;
        }

        .btn-custom {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            margin-right: 20px;
        }

        .btn-primary-custom {
            background: #ffc107;
            color: #000;
            border: 2px solid #ffc107;
        }

        .btn-primary-custom:hover {
            background: transparent;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255,193,7,0.3);
        }

        .btn-outline-custom {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
        }

        .btn-outline-custom:hover {
            background: #fff;
            color: #000;
            transform: translateY(-3px);
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .stat-item {
            text-align: center;
            padding: 30px;
        }

        .stat-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ffc107;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background: #fff;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 800;
            color: #333;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #ffc107;
        }

        .section-title p {
            font-size: 18px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }

        .about-content {
            padding: 30px;
        }

        .about-text h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }

        .about-text p {
            font-size: 16px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .about-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .about-image:hover img {
            transform: scale(1.1);
        }

        /* Services Section */
        .services-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .service-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            background: #ffc107;
            border-radius: 50%;
            opacity: 0.1;
            transition: all 0.5s;
        }

        .service-card:hover::before {
            transform: scale(5);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .service-icon i {
            font-size: 40px;
            color: #fff;
        }

        .service-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .service-card p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .service-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .service-link:hover {
            color: #764ba2;
            transform: translateX(5px);
        }

        /* Tools Section */
        .tools-section {
            padding: 100px 0;
            background: #fff;
        }

        .tool-item {
            text-align: center;
            padding: 30px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
            height: 100%;
        }

        .tool-item:hover {
            background: #ffc107;
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(255,193,7,0.3);
        }

        .tool-item i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .tool-item:hover i {
            color: #000;
        }

        .tool-item h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
            transition: all 0.3s;
        }

        .tool-item:hover h4 {
            color: #000;
        }

        /* Team Section */
        .team-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .team-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .team-image {
            height: 300px;
            overflow: hidden;
            position: relative;
        }

        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .team-card:hover .team-image img {
            transform: scale(1.1);
        }

        .team-info {
            padding: 25px;
            text-align: center;
        }

        .team-info h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }

        .team-info p {
            color: #667eea;
            font-weight: 600;
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
            background: #ffc107;
            color: #000;
            transform: translateY(-3px);
        }

        /* Video Section */
        .video-section {
            padding: 100px 0;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1558981806-ec527fa84c39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
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
            padding: 100px 0;
            background: #fff;
        }

        .testimonial-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 80px;
            color: #ffc107;
            opacity: 0.3;
            font-family: serif;
        }

        .testimonial-content {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .testimonial-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.8;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .author-info h4 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }

        .author-info p {
            font-size: 14px;
            color: #667eea;
            margin: 0;
        }

        .bike-number {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Gallery Section */
        .gallery-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay i {
            font-size: 48px;
            color: #fff;
        }

        /* Contact Section */
        .contact-section {
            padding: 100px 0;
            background: #fff;
        }

        .contact-info {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            height: 100%;
        }

        .contact-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }

        .contact-detail {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .contact-icon i {
            font-size: 24px;
            color: #000;
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
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            min-height: 400px;
        }

        /* Footer */
        .footer {
            background: #000;
            color: #fff;
            padding: 60px 0 30px;
        }

        .footer h5 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #ffc107;
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
            color: #ffc107;
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
            color: #fff;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #ffc107;
            color: #000;
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #999;
        }

        /* Login Modal */
        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 20px 20px 0 0;
            padding: 20px;
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
            border-color: #ffc107;
            box-shadow: none;
        }

        .login-form .btn-login {
            background: #ffc107;
            color: #000;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-form .btn-login:hover {
            background: #000;
            color: #ffc107;
            transform: translateY(-3px);
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

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #ffc107;
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
            border: none;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #000;
            color: #ffc107;
            transform: translateY(-5px);
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 40px;
            }
            
            .navbar-collapse {
                background: rgba(0,0,0,0.95);
                padding: 20px;
                border-radius: 10px;
                margin-top: 10px;
            }
            
            .nav-link {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="bi bi-bicycle"></i> PSC
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
                        <a class="nav-link" href="#about">About</a>
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
                        <a class="nav-link" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Reviews</a>
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
            <div class="row hero-content">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="hero-title">Welcome to <span>PRAVEEN SERVICE CENTER</span></h1>
                    <p class="hero-subtitle">With over 15 years of excellence in bike repair and maintenance, we are your trusted partner for all two-wheeler needs. Our expert team combines traditional craftsmanship with modern technology to deliver the best service in town.</p>
                    <div class="hero-buttons">
                        <a href="#services" class="btn btn-custom btn-primary-custom">Our Services</a>
                        <a href="#contact" class="btn btn-custom btn-outline-custom">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6" data-aos="fade-up">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-bicycle"></i>
                        </div>
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Bikes Repaired</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number">5,000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Tools & Equipment</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>About Us</h2>
                <p>Learn more about our journey and commitment to excellence</p>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1486006920555-c77dc445181e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="About Us">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-content">
                        <h3>Your Trusted Bike Repair Partner Since 2010</h3>
                        <p>PRAVEEN SERVICE CENTER was founded in 2010 with a simple mission: to provide the highest quality bike repair and maintenance services at affordable prices. What started as a small garage has now grown into one of the most trusted service centers in the city.</p>
                        <p>We take pride in our team of expert mechanics who are passionate about bikes and dedicated to their craft. Every bike that comes to us receives personalized attention and care, ensuring it leaves in perfect condition.</p>
                        <p>Our state-of-the-art facility is equipped with the latest diagnostic tools and equipment, allowing us to handle everything from routine maintenance to complex repairs with precision and efficiency.</p>
                        <div class="row mt-4">
                            <div class="col-6">
                                <h4><i class="bi bi-check-circle-fill text-warning"></i> Quality Service</h4>
                                <p>100% satisfaction guaranteed</p>
                            </div>
                            <div class="col-6">
                                <h4><i class="bi bi-clock-history text-warning"></i> Fast Turnaround</h4>
                                <p>Same day service available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Services</h2>
                <p>Comprehensive bike repair and maintenance solutions</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-wrench"></i>
                        </div>
                        <h3>General Repair</h3>
                        <p>Complete bike repair services including brake adjustment, gear tuning, chain cleaning, and general maintenance to keep your bike running smoothly.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <h3>Oil Change</h3>
                        <p>Professional engine oil change service using high-quality oils. We recommend the best oil for your bike's engine for optimal performance and longevity.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-brightness-high"></i>
                        </div>
                        <h3>Electrical Repair</h3>
                        <p>Complete electrical system diagnosis and repair including headlights, indicators, horn, wiring, and battery checks. We fix all electrical issues.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-circle"></i>
                        </div>
                        <h3>Tire & Wheel</h3>
                        <p>Tire replacement, puncture repair, wheel balancing, and alignment services. We stock all major tire brands for all bike models.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Brake Service</h3>
                        <p>Complete brake system inspection, pad replacement, disc cleaning, and brake fluid check. We ensure your safety with proper brake maintenance.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h3>Engine Tuning</h3>
                        <p>Professional engine tuning for better performance, fuel efficiency, and smoother ride. We use advanced diagnostic tools for precise tuning.</p>
                        <a href="#" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tools Section -->
    <section id="tools" class="tools-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Tools & Equipment</h2>
                <p>State-of-the-art equipment for precise diagnostics and repairs</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in">
                    <div class="tool-item">
                        <i class="bi bi-wrench"></i>
                        <h4>Wrench Set</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="50">
                    <div class="tool-item">
                        <i class="bi bi-gear"></i>
                        <h4>Diagnostic Tool</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="tool-item">
                        <i class="bi bi-tools"></i>
                        <h4>Tool Kit</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="150">
                    <div class="tool-item">
                        <i class="bi bi-brightness-high"></i>
                        <h4>Light Tester</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="tool-item">
                        <i class="bi bi-droplet"></i>
                        <h4>Oil Gun</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="250">
                    <div class="tool-item">
                        <i class="bi bi-circle"></i>
                        <h4>Tire Changer</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="tool-item">
                        <i class="bi bi-battery-charging"></i>
                        <h4>Battery Tester</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="350">
                    <div class="tool-item">
                        <i class="bi bi-fan"></i>
                        <h4>Air Compressor</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="tool-item">
                        <i class="bi bi-mic"></i>
                        <h4>Engine Scanner</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="450">
                    <div class="tool-item">
                        <i class="bi bi-rulers"></i>
                        <h4>Alignment Tool</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="500">
                    <div class="tool-item">
                        <i class="bi bi-magnet"></i>
                        <h4>Lifting Jack</h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4" data-aos="zoom-in" data-aos-delay="550">
                    <div class="tool-item">
                        <i class="bi bi-brush"></i>
                        <h4>Cleaning Kit</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="team-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Expert Team</h2>
                <p>Meet the skilled professionals behind our success</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Pradeep Kumar">
                        </div>
                        <div class="team-info">
                            <h3>Pradeep Kumar</h3>
                            <p>Founder & Master Mechanic</p>
                            <p class="text-muted small">15+ years experience in bike repair and restoration. Expert in all bike models.</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" alt="Monu Sharma">
                        </div>
                        <div class="team-info">
                            <h3>Monu Sharma</h3>
                            <p>Senior Service Expert</p>
                            <p class="text-muted small">10+ years experience. Specializes in engine tuning and performance upgrades.</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.unsplash.com/photo-1581299894026-b4c4b5c1aae3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Rahul Verma">
                        </div>
                        <div class="team-info">
                            <h3>Rahul Verma</h3>
                            <p>Diagnostic Specialist</p>
                            <p class="text-muted small">8+ years experience. Expert in electrical systems and computerized diagnostics.</p>
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

    <!-- Gallery Section -->
    <section id="gallery" class="gallery-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Work Gallery</h2>
                <p>See some of the bikes we've worked on</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="zoom-in">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Repair">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1486006920555-c77dc445181e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Service">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1558980664-10a60a8e6c3a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Repair">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1558981359-219d6364c9c8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Service">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1558981001-199536f7c9e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Repair">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="500">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1558980665-10a60a8e6c3a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Bike Service">
                        <div class="gallery-overlay">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section id="videos" class="video-section">
        <div class="container">
            <div class="section-title text-white" data-aos="fade-up">
                <h2 class="text-white">Our Work in Action</h2>
                <p class="text-white-50">Watch how we transform bikes with our expert service</p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-4" data-aos="fade-right">
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Bike Repair Video" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="col-md-6 mb-4" data-aos="fade-left">
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Bike Service Video" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Customer Reviews</h2>
                <p>What our happy customers say about us</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Excellent service! Got my bike repaired within hours. The team was professional and explained everything. Highly recommended!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Rajesh Singh" class="author-image">
                            <div class="author-info">
                                <h4>Rajesh Singh</h4>
                                <p>Happy Customer</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH12AB1234</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Best service center in town! Fair prices and quality work. My bike runs like new after their service. Will visit again."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="Priya Patel" class="author-image">
                            <div class="author-info">
                                <h4>Priya Patel</h4>
                                <p>Regular Customer</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH14CD5678</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Very satisfied with their work. The mechanics are skilled and friendly. They diagnosed the problem quickly and fixed it perfectly."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/3.jpg" alt="Amit Kumar" class="author-image">
                            <div class="author-info">
                                <h4>Amit Kumar</h4>
                                <p>Bike Enthusiast</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH19EF9012</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Great experience! They have all the modern tools and the mechanics are very skilled. My bike runs like new."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/women/4.jpg" alt="Neha Sharma" class="author-image">
                            <div class="author-info">
                                <h4>Neha Sharma</h4>
                                <p>Happy Customer</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH23GH4567</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Professional service and friendly staff. They explained everything before starting the work. Very satisfied!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/5.jpg" alt="Vikram Singh" class="author-image">
                            <div class="author-info">
                                <h4>Vikram Singh</h4>
                                <p>Regular Customer</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH34IJ8901</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Best service in town! They fixed my bike quickly and at a reasonable price. Highly recommended to all bike owners!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/women/6.jpg" alt="Pooja Mehta" class="author-image">
                            <div class="author-info">
                                <h4>Pooja Mehta</h4>
                                <p>Happy Customer</p>
                                <span class="bike-number"><i class="bi bi-bicycle"></i> MH45KL6789</span>
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
            <div class="section-title" data-aos="fade-up">
                <h2>Contact Us</h2>
                <p>Get in touch with us for any queries or service appointments</p>
            </div>
            <div class="row">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="contact-info">
                        <h3>Get In Touch</h3>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Address</h4>
                                <p>123 Bike Street, Auto Nagar,<br>Mumbai - 400001</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Phone</h4>
                                <p>+91 93405 27152</p>
                                <p>+91 98765 43210</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Email</h4>
                                <p>info@praveenservice.com</p>
                                <p>service@praveenservice.com</p>
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
                <div class="col-lg-7" data-aos="fade-left">
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
                <div class="col-lg-4 mb-4">
                    <h5>PRAVEEN SERVICE CENTER</h5>
                    <p>Your trusted partner for all bike repair and maintenance needs. With 15+ years of experience and expert mechanics, we ensure your bike runs perfectly.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                        <a href="#"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#team">Team</a></li>
                        <li><a href="#gallery">Gallery</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
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
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5>Newsletter</h5>
                    <p>Subscribe to get updates on offers and services</p>
                    <form class="mt-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email">
                            <button class="btn btn-warning" type="button">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2026 PRAVEEN SERVICE CENTER. All rights reserved. | Designed with <i class="bi bi-heart-fill text-danger"></i> for bike lovers</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login to Management System</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="login-form">
                        <input type="hidden" name="login" value="1">
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
                        <p><span class="badge bg-primary">Admin</span> Username: <strong>Pradeep</strong> / Password: <strong>admin123</strong></p>
                        <p><span class="badge bg-secondary">Staff</span> Username: <strong>Monu</strong> / Password: <strong>staff123</strong></p>
                        <p class="text-muted small mt-2"><i class="bi bi-database"></i> Database: bike_management_system</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            // Back to top button
            const backToTop = document.querySelector('.back-to-top');
            if (window.scrollY > 500) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.remove('show');
                    }
                }
            });
        });

        // Back to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Counter animation
        function animateCounter(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.innerHTML = Math.floor(progress * (end - start) + start) + '+';
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Trigger counters when stats section is in view
        const statsSection = document.querySelector('.stats-section');
        const statNumbers = document.querySelectorAll('.stat-number');
        let animated = false;

        window.addEventListener('scroll', function() {
            if (!animated && statsSection.getBoundingClientRect().top < window.innerHeight) {
                animated = true;
                statNumbers.forEach((stat, index) => {
                    const value = parseInt(stat.innerHTML);
                    animateCounter(stat, 0, value, 2000);
                });
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 500);
                }
            });
        }, 5000);

        // Show login modal if there's an error
        <?php if (!empty($error)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>