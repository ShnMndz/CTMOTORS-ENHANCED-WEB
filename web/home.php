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
/* Hero Section */
.hero-brochure {
    min-height: 80vh;
    background: url('/citimotorsweb/web/img/background.png') center/cover no-repeat;
    position: relative;
    display: flex;
    align-items: center;
}

.hero-brochure::after {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
}

.hero-brochure .container {
    position: relative;
    z-index: 2;
}

/* Footer */
.footer {
    background: #222;
    color: #fff;
    padding: 20px 0;
}

section {
    scroll-margin-top: 80px;
}

h2 {
    font-weight: 700;
}

.bi {
    transition: transform 0.3s ease;
}

.bi:hover {
    transform: scale(1.2);
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

</section> <!-- END HERO -->

<!-- WHY CHOOSE US -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="fw-bold">Why Choose CITI MOTORS?</h2>
        <p class="mt-3">
            We offer top-quality vehicles, flexible financing, and excellent customer service.
        </p>

        <div class="row mt-4">
            <div class="col-md-4">
                <i class="bi bi-car-front display-4 text-danger"></i>
                <h5 class="mt-3">Wide Selection</h5>
                <p>Choose from the latest and most reliable vehicles.</p>
            </div>

            <div class="col-md-4">
                <i class="bi bi-cash-stack display-4 text-danger"></i>
                <h5 class="mt-3">Affordable Prices</h5>
                <p>Competitive pricing and flexible financing options.</p>
            </div>

            <div class="col-md-4">
                <i class="bi bi-award display-4 text-danger"></i>
                <h5 class="mt-3">Trusted Service</h5>
                <p>We prioritize customer satisfaction and reliability.</p>
            </div>
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