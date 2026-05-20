<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Services - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<link rel="stylesheet" href="../css/services.css">

</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container my-5">

    <!-- ── Why Choose Us ── -->
    <div class="text-center mb-4">
        <span class="oem-badge">Mitsubishi Certified</span>
        <h1 class="section-title">Why Choose Our Service?</h1>
        <p class="section-subtitle">Factory-trained professionals with cutting-edge equipment</p>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon-box">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h6>Factory-Trained Technicians</h6>
                <p>Accredited staff with a wealth of knowledge and expertise.</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon-box">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h6>High Quality Parts</h6>
                <p>We use only genuine and reliable components for your vehicle.</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon-box">
                    <i class="bi bi-cpu"></i>
                </div>
                <h6>Advanced Diagnosis</h6>
                <p>Latest tools to quickly detect and resolve issues.</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card">
                <div class="feature-icon-box">
                    <i class="bi bi-headset"></i>
                </div>
                <h6>24/7 Assistance</h6>
                <p>Roadside and customer support whenever you need it.</p>
            </div>
        </div>
    </div>

    <!-- ── Service Image Cards ── -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="service-card">
                <img src="/citimotorsweb/web/img/service1.png" class="service-img" alt="Mechanical Services">
                <div class="service-card-body">
                    <h5>Mechanical Services</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="service-card">
                <img src="/citimotorsweb/web/img/service2.png" class="service-img" alt="Electrical & Diagnostics">
                <div class="service-card-body">
                    <h5>Electrical &amp; Diagnostics</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="service-card">
                <img src="/citimotorsweb/web/img/service3.png" class="service-img" alt="Body & Paint">
                <div class="service-card-body">
                    <h5>Body &amp; Paint</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stats row ── -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">5-Star</div>
                <div class="stat-label">Service experience</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">6+</div>
                <div class="stat-label">Service types offered</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-val">24/7</div>
                <div class="stat-label">Customer support</div>
            </div>
        </div>
    </div>

    <hr class="section-divider">

    <!-- ── Quote + Description ── -->
    <div class="text-center">
        <div class="quote-mark">"</div>
        <p class="quote-text">Service & Body Shop — 5-Star Service Experience</p>
        <p class="body-copy">
            Our multi-level service shop is staffed with highly-skilled and competent service personnel trained under Mitsubishi Motors standards. We use the latest and most advanced tools and diagnostic equipment to keep your vehicle in tip-top shape. We offer various services such as Mechanical, Electrical, Tinsmith/Painting, Underchassis, Wheel Alignment and Airconditioning repair to name a few. All of our services are available for both personal and warranty repairs. Accurate job estimates from our friendly Service Advisors are available so that you would know how much to pay and when your vehicle would be finished prior to the actual servicing of your vehicle. We also have an insurance section to handle insurance related cases in the event that your vehicle would need body repair.
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