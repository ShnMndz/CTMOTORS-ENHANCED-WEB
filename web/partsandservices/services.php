<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Services - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">



<style>
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
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container">

    <div class="section-box">

        <!-- Images -->
        <div class="row text-center mb-4">
            <div class="col-md-4 mb-3">
                <img src="/citimotorsweb/web/img/service1.png" class="img-fluid" alt="">
            </div>
            <div class="col-md-4 mb-3">
                <img src="/citimotorsweb/web/img/service2.png" class="img-fluid" alt="">
            </div>
            <div class="col-md-4 mb-3">
                <img src="/citimotorsweb/web/img/service3.png" class="img-fluid" alt="">
            </div>
        </div>

        <!-- Content -->
        <h2>Service & Body Shop</h2>
        <p class="subtitle">5-Star Service Experience</p>

        <p>
        Our service shop is handled by highly trained Mitsubishi technicians.
        We use advanced tools and diagnostics to maintain your vehicle’s condition.
        Services include Mechanical, Electrical, Painting, Wheel Alignment,
        Airconditioning repair, and more.
        </p>

    </div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>