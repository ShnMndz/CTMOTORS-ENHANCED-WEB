<?php
include '../db.php'; // Adjust path if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us - CITI MOTORS</title>

<!-- Bootstrap & Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
/* Page-specific overrides if needed */
.vision-image { background: url('../img/ourvisionpic.png') center/cover no-repeat; }
.mission-image { background: url('../img/mission.png') center/cover no-repeat; }
.history-section { background: url('../img/historypic.png') center/cover no-repeat; }
.history-overlay { background: rgba(0,0,0,0.6); }
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- Vision Section -->
<section class="vision-section d-flex">
    <div class="vision-text col-lg-6">
        <h1>Our Vision</h1>
        <p>To be the most trusted automotive company in the city, delivering excellence in vehicles, service, and customer care.</p>
    </div>
    <div class="vision-image col-lg-6 d-none d-lg-block"></div>
</section>

<!-- Mission Section -->
<section class="mission-section d-flex">
    <div class="mission-text col-lg-6">
        <h1>Our Mission</h1>
        <ol>
            <li>Provide top-quality vehicles that meet our customers’ expectations.</li>
            <li>Offer excellent after-sales service and maintenance support.</li>
            <li>Foster a culture of trust, integrity, and professionalism.</li>
        </ol>
    </div>
    <div class="mission-image col-lg-6 d-none d-lg-block"></div>
</section>

<!-- History Section -->
<section class="history-section position-relative text-center text-white py-5">
    <div class="history-overlay"></div>
    <div class="history-text container position-relative">
        <h2>Our History</h2>
        <p>
            Established in 1995, CITI MOTORS has grown from a small dealership into one of the city's most trusted automotive brands.  
            Our commitment to quality and customer satisfaction has driven us to continuously innovate and expand our services.
        </p>
    </div>
</section>

<!-- Footer -->
<footer class="footer mt-5">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>