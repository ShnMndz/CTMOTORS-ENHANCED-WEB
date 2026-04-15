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

<style>

/* DETAILS */
    .service-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 10px;
}

h5 {
    color: #fff;
}

.text-center p {
    color: #ccc;
}
body {
    font-family: 'Poppins', sans-serif;
    background: #0a0a0a;
    color: #fff;
}

.section-box img {
    border-radius: 10px;
}

.section-box h2 {
    color: #E20000;
    margin-bottom: 10px;
}

.subtitle {
    color: #aaa;
    margin-bottom: 15px;
}

/* Book Button */
.btn-custom {
    background: #E20000;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    transition: 0.3s;
}

.btn-custom:hover {
    background: #ff1a1a;
    transform: translateY(-2px);
}

.feature-box {
    background: #111;
    padding: 25px 15px;
    border-radius: 12px;
    transition: 0.3s;
    height: 100%;
}

.feature-box:hover {
    transform: translateY(-5px);
    background: #161616;
}

.feature-box .icon {
    font-size: 40px;
    color: #E20000;
}

.feature-box h6 {
    color: #fff;
}

.feature-box p {
    color: #aaa;
    font-size: 14px;
}
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container">

    <div class="section-box">

    <!-- WHY CHOOSE US -->
<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Why Choose Our Service?</h2>
        <div style="width:60px;height:3px;background:#E20000;margin:10px auto;"></div>
    </div>

    <div class="row text-center">

        <div class="col-md-3 mb-4">
            <div class="feature-box">
                <div class="icon">🔧</div>
                <h6 class="fw-bold mt-3">Factory-Trained Technicians</h6>
                <p>Accredited staff with a wealth of knowledge and expertise.</p>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="feature-box">
                <div class="icon">🧰</div>
                <h6 class="fw-bold mt-3">High Quality Parts</h6>
                <p>We use only genuine and reliable components for your vehicle.</p>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="feature-box">
                <div class="icon">⚙️</div>
                <h6 class="fw-bold mt-3">Advanced Diagnosis</h6>
                <p>Latest tools to quickly detect and resolve issues.</p>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="feature-box">
                <div class="icon">🚗</div>
                <h6 class="fw-bold mt-3">24/7 Assistance</h6>
                <p>Roadside and customer support whenever you need it.</p>
            </div>
        </div>

    </div>
</div>

    <!-- Images + Titles -->
    <div class="row text-center mb-4">

        <!-- Column 1 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/service1.png" class="service-img mb-3" alt="">
            <h5 class="fw-bold">Mechanical Services</h5>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/service2.png" class="service-img mb-3" alt="">
            <h5 class="fw-bold">Electrical & Diagnostics</h5>
        </div>

        <!-- Column 3 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/service3.png" class="service-img mb-3" alt="">
            <h5 class="fw-bold">Body & Paint</h5>
        </div>

    </div>

    <!-- Shared Title -->
    <h2 class="text-center">Service & Body Shop</h2>
    <p class="subtitle text-center">5-Star Service Experience</p>

    <!-- Shared Description -->
    <p class="text-center">
        Our multi-level service shop is staffed with highly-skilled and competent service personnel trained under Mitsubishi Motors standards. We use the latest and most advanced tools and diagnostic equipment
         to keep your vehicle in tip-top shape. We offer various services such as Mechanical, Electrical, Tinsmith/Painting, Underchassis, Wheel Alignment and Airconditioning repair to name a few. All of our services are available
        for both personal and warranty repairs. Accurate job estimates from our friendly Service Advisors are available so that you would know how much to pay and when your vehicle would be finished prior to
        the actual servicing of your vehicle. We also have an insurance section to handle insurance related cases in the event that your vehicle would need body repair.</p>
        <button class="btn-custom">Book a Service</button>
</div>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>