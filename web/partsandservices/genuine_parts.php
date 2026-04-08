<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Genuine Parts - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #0a0a0a;
    color: #fff;
}

/* Section */
.section-box {
    max-width: 1000px;
    margin: 50px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
}

/* Images */
.section-box img {
    border-radius: 10px;
}

/* Title */
.section-box h2 {
    color: #E20000;
    margin-bottom: 10px;
}

/* Subtitle */
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
                <img src="/citimotorsweb/web/img/parts1.png" class="img-fluid" alt="">
            </div>
            <div class="col-md-4 mb-3">
                <img src="/citimotorsweb/web/img/part2.png" class="img-fluid" alt="">
            </div>
            <div class="col-md-4 mb-3">
                <img src="/citimotorsweb/web/img/parts3.png" class="img-fluid" alt="">
            </div>
        </div>

        <!-- Content -->
        <h2>Genuine Parts</h2>
        <p class="subtitle">Total Quality: Superior Parts from Mitsubishi Technology</p>

        <p>
        Many imitation parts flood the market today. While cheaper, they often fail and cause more damage.
        Mitsubishi Genuine Parts go through strict quality control to ensure safety and performance.
        At CITI MOTORS, we provide original lubricants, engine, and body parts at affordable prices.
        </p>

    </div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>