<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Services - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

.section-box {
    max-width: 1000px;
    margin: 50px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
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
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container">

    <div class="section-box">

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
         to keep your vehicle in tip-top shape. </p>
    <p>We offer various services such as Mechanical, Electrical, Tinsmith/Painting, Underchassis, Wheel Alignment and Airconditioning repair to name a few. All of our services are available
        for both personal and warranty repairs. Accurate job estimates from our friendly Service Advisors are available so that you would know how much to pay and when your vehicle would be finished prior to
        the actual servicing of your vehicle.</p>
        <p>We also have an insurance section to handle insurance related cases in the event that your vehicle would need body repair.</p>

        <button class="btn-custom">Book a Service</button>
</div>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>