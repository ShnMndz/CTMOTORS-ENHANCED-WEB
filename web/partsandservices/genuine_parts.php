<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Genuine Parts - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<link rel="stylesheet" href="../css/genuineparts.css">

</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container my-5">

    <!-- Header -->
    <div class="text-center">
        <span class="oem-badge">OEM Certified</span>
        <h1 class="genuine-title">Genuine Parts</h1>
        <p class="genuine-subtitle">Mitsubishi Motors factory-grade quality — built to last</p>
    </div>

    <!-- 3 Cards -->
    <div class="row g-3 mb-4">

        <!-- Card 1 -->
        <div class="col-md-4">
            <div class="part-card">
                <img src="/citimotorsweb/web/img/parts1.png" class="parts-img" alt="Genuine Mitsubishi Parts">
                <div class="card-body-inner">
                    <div class="card-icon-box">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Affordable Mitsubishi Genuine Parts with Exact Fit and Warranty</h5>
                    <p>Our reasonably-priced Genuine Parts ensures a perfect fit and outstanding quality. Always have peace of mind with the warranty on all Genuine Parts.</p>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-4">
            <div class="part-card">
                <img src="/citimotorsweb/web/img/part2.png" class="parts-img" alt="Mitsubishi-Approved Paint">
                <div class="card-body-inner">
                    <div class="card-icon-box">
                        <i class="bi bi-droplet-half"></i>
                    </div>
                    <h5>High-quality Mitsubishi-Approved Paint</h5>
                    <p>To ensure exceptional paint jobs, only the finest paint brands will be used in your vehicle. Achieve the best finish with the best materials available.</p>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-4">
            <div class="part-card">
                <img src="/citimotorsweb/web/img/parts3.png" class="parts-img" alt="Advanced Tools and Equipment">
                <div class="card-body-inner">
                    <div class="card-icon-box">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h5>Complete and Advanced Tools/Equipment</h5>
                    <p>Having the right tools and equipment will result to excellent repairs. With the Technician's skills and complete tools, your vehicle will be restored to its original condition.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">100%</div>
                <div class="stat-label">OEM Certified</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">Warranty</div>
                <div class="stat-label">On all genuine parts</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">Expert</div>
                <div class="stat-label">Certified technicians</div>
            </div>
        </div>
    </div>

    <hr class="section-divider">

    <!-- Quote + Body -->
    <div class="text-center">
        <div class="quote-mark">"</div>
        <p class="quote-text">Total Quality: Superior Parts from Superior Mitsubishi Motors Technology</p>
        <p class="body-copy">
            Nowadays cheaply-made imitation parts flood the automotive market scene. Many go through these channels to save on costs, only to be spending more in the end from failure and incompatibility of these fake parts.
            Mitsubishi Genuine Parts are produced with unmatched and the strictest quality control. This is your guarantee that every part will bring to your driving experience the highest standards of safety and performance.
            For all your vehicle's scheduled and unscheduled repair and replacement requirements, Citimotors, Inc. has Mitsubishi Motors genuine lubricants, engine and body parts, at very affordable prices.
        </p>

        <!-- CTA Buttons -->
        <div class="d-flex gap-3 justify-content-center">
            <button class="btn-custom">Book a Service</button>
            <button class="btn-outline-custom">Learn More</button>
        </div>
    </div>

</div>

<!-- ===== FOOTER ===== -->
<footer class="footer mt-5">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>