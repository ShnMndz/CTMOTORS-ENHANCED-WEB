<?php
// Database connection
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to CITI MOTORS</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">

<style>
/* ===== GLOBAL ===== */
body {
    font-family: 'Poppins', sans-serif;
    background: #000;
    color: #fff;
    overflow-x: hidden;
}

* {
    transition: all 0.3s ease;
}

/* ===== HERO SECTION ===== */
.hero-brochure {
    min-height: 90vh;
    background: url('/citimotorsweb/web/img/background.png') center/cover no-repeat;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

/* Dark overlay */
.hero-brochure::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.85));
}

/* Content layering */
.hero-brochure .container {
    position: relative;
    z-index: 2;
    animation: fadeUp 1.2s ease forwards;
}

/* ===== HERO TEXT ===== */
.hero-brochure h1 {
    font-size: 3rem;
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Animated underline */
.hero-brochure h1::after {
    content: "";
    display: block;
    width: 0%;
    height: 3px;
    margin: 10px auto 0;
    background: #dc3545;
    transition: width 0.5s ease;
}

.hero-brochure h1:hover::after {
    width: 60%;
}

/* Subtitle */
.hero-brochure p {
    color: #ccc;
    font-size: 1.2rem;
}

/* ===== BUTTONS ===== */
.btn {
    border-radius: 50px;
    padding: 12px 28px;
    font-weight: 500;
    letter-spacing: 1px;
}

/* Primary button (View Vehicles) */
.btn-danger {
    background: #dc3545;
    border: none;
    box-shadow: 0 0 0 transparent;
}

.btn-danger:hover {
    background: #ff4d5a;
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.7);
}

/* Outline button */
.btn-outline-light {
    border: 2px solid #fff;
}

.btn-outline-light:hover {
    background: #fff;
    color: #000;
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(255,255,255,0.6);
}

/* ===== HERO BACKGROUND ZOOM EFFECT ===== */
.hero-brochure:hover {
    background-size: 110%;
}

/* ===== FADE-IN ANIMATION ===== */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== FOOTER ===== */
.footer {
    background: #111;
    color: #aaa;
    padding: 20px 0;
    font-size: 0.9rem;
}

/* ===== EXTRA: GLOW TEXT EFFECT ===== */
.hero-brochure h1:hover {
    text-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
}
</style>

</head>
<body>

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-brochure d-flex align-items-center text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">THE CITY NEEDS CITI MOTORS</h1>
        <p class="lead mt-3">Premium vehicles. Trusted service. Unmatched value.</p>
        <div class="mt-4">
            <a href="products/products.php" class="btn btn-danger btn-lg me-2">View Vehicles</a>
            <a href="testdrive/test-drive-page.php" class="btn btn-outline-light btn-lg">Book a Test Drive</a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>