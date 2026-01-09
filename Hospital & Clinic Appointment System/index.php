<?php
session_start();
// Check if user is already logged in
if (isset($_SESSION['user_type']) && isset($_SESSION['user_id'])) {
    $redirects = [
        'admin' => 'admin/dashboard.php',
        'doctor' => 'doctor/dashboard.php',
        'patient' => 'patient/dashboard.php'
    ];
    $target = $redirects[$_SESSION['user_type']] ?? 'login.php';
    header("Location: $target");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horseed Hospital | Excellence in Care</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #004aad;
            --dark-blue: #001d4a;
            --accent-green: #2ecc71;
            --soft-bg: #f8faff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: var(--dark-blue);
            scroll-behavior: smooth;
        }

        /* Navbar */
        .navbar { padding: 20px 0; background: white !important; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; color: var(--primary-blue) !important; }
        .btn-login { border: 2px solid var(--primary-blue); color: var(--primary-blue); border-radius: 10px; font-weight: 600; padding: 8px 20px; transition: 0.3s; }
        .btn-login:hover { background: var(--primary-blue); color: white; }

        /* Sections General */
        section { padding: 100px 0; }
        .section-title { font-weight: 800; font-size: 2.5rem; margin-bottom: 50px; text-align: center; }
        .section-title span { color: var(--primary-blue); }

        /* Hero */
        .hero-container { min-height: 80vh; display: flex; align-items: center; background: radial-gradient(circle at top right, #eef4ff 0%, #ffffff 50%); }
        .hero-content h1 { font-size: 3.5rem; font-weight: 800; }

        /* Services */
        .service-card {
            border: none; padding: 40px; border-radius: 25px; background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; height: 100%;
        }
        .service-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,74,173,0.1); }
        .service-icon { 
            width: 60px; height: 60px; background: #eef4ff; color: var(--primary-blue);
            border-radius: 15px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 20px;
        }

        /* About */
        .about-img { border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }

        /* Modern Footer */
        footer { background: var(--dark-blue); color: #fff; padding: 80px 0 30px; }
        .footer-logo { font-weight: 800; font-size: 1.8rem; color: #fff; margin-bottom: 20px; display: block; text-decoration: none; }
        .footer-links h6 { font-weight: 700; text-transform: uppercase; margin-bottom: 25px; color: var(--accent-green); }
        .footer-links a { color: rgba(255,255,255,0.7); text-decoration: none; display: block; margin-bottom: 12px; transition: 0.3s; }
        .footer-links a:hover { color: #fff; padding-left: 5px; }
        .social-icons a { 
            width: 40px; height: 40px; background: rgba(255,255,255,0.1); display: inline-flex;
            align-items: center; justify-content: center; border-radius: 50%; color: #fff; margin-right: 10px; transition: 0.3s;
        }
        .social-icons a:hover { background: var(--primary-blue); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-heartbeat me-2"></i>HORSEED</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#services">Services</a></li>
                    <li class="nav-item ms-lg-3"><a href="login.php" class="btn btn-login">Login Portal</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="mb-4">Excellence in <span>Healthcare</span> Every Day.</h1>
                    <p class="lead text-muted mb-5">Providing world-class medical services with a team of expert doctors and advanced technology.</p>
                    <a href="login.php" class="btn btn-primary px-5 py-3 fw-bold shadow-lg" style="border-radius:12px; background:var(--primary-blue)">Book Appointment</a>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=800&q=80" class="img-fluid about-img" alt="Medical">
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="bg-light">
        <div class="container">
            <h2 class="section-title">Our Specialized <span>Services</span></h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-microscope"></i></div>
                        <h4>Diagnostics</h4>
                        <p class="text-muted">Advanced laboratory testing and imaging services for accurate results.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-stethoscope"></i></div>
                        <h4>General Care</h4>
                        <p class="text-muted">Routine check-ups and personalized healthcare for your family.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-ambulance"></i></div>
                        <h4>Emergency</h4>
                        <p class="text-muted">24/7 rapid response emergency services for critical conditions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?auto=format&fit=crop&w=800&q=80" class="img-fluid about-img mb-4 mb-lg-0" alt="About">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="fw-800 mb-4">Why Choose <span>Horseed Hospital?</span></h2>
                    <p class="text-muted mb-4">Established in 2020, Horseed Hospital has been a pioneer in digital healthcare. We combine compassion with the latest medical innovations to ensure you get the best treatment.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Expert Specialist Doctors</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Modern Medical Equipment</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Easy Online Booking</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a href="#" class="footer-logo"><i class="fas fa-heartbeat me-2"></i>HORSEED</a>
                    <p class="text-white-50">Providing high-quality healthcare and medical education for a healthier society. Your health is our priority.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 footer-links">
                    <h6>Quick Links</h6>
                    <a href="#">Home</a>
                    <a href="#about">About Us</a>
                    <a href="#services">Services</a>
                    <a href="login.php">Login</a>
                </div>
                <div class="col-lg-3 footer-links">
                    <h6>Our Services</h6>
                    <a href="#">Cardiology</a>
                    <a href="#">Pediatrics</a>
                    <a href="#">Neurology</a>
                    <a href="#">Surgery</a>
                </div>
                <div class="col-lg-3 footer-links">
                    <h6>Contact Us</h6>
                    <a href="#"><i class="fas fa-map-marker-alt me-2"></i> Mogadishu, Somalia</a>
                    <a href="#"><i class="fas fa-phone me-2"></i> +252 61XXXXXXX</a>
                    <a href="#"><i class="fas fa-envelope me-2"></i> info@horseed.com</a>
                </div>
            </div>
            <hr class="mt-5 border-secondary">
            <div class="text-center text-white-50 pt-3">
                <small>&copy; 2025 Horseed Hospital. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>