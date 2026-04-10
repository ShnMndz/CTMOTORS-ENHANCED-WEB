<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Genuine Parts - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<style>

    .text-muted {
    color: #bbb !important;
}

h5 {
    color: #fff;
}

img {
    border-radius: 10px;
}

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

.parts-img {
    width: 100%;
    height: 220px;        /* same height for all */
    object-fit: cover;    /* crop nicely, no distortion */
    border-radius: 10px;
}

/* Button */
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

<div class="container my-5">

    <!-- Title -->
    <h1 class="text-center mb-4" style="color: #E20000;">GENUINE PARTS</h1>

    <!-- 3 Columns -->
    <div class="row text-center">

        <!-- Column 1 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/parts1.png" class="parts-img mb-3" alt="">
            
            <h5 class="fw-bold">
                Affordable Mitsubishi Genuine Parts with Exact Fit and Warranty
            </h5>

            <p class="text-muted">
                Our reasonably-priced Genuine Parts ensures a perfect fit and outstanding quality. 
                Always have peace of mind with the warranty on all Genuine Parts.
            </p>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/part2.png" class="parts-img mb-3" alt="">
            
            <h5 class="fw-bold">
                High-quality Mitsubishi-Approved Paint
            </h5>

            <p class="text-muted">
                To ensure exceptional paint jobs, only the finest paint brands will be used in your vehicle. 
                Achieve the best finish with the best materials available.
            </p>
        </div>

        <!-- Column 3 -->
        <div class="col-md-4 mb-4">
            <img src="/citimotorsweb/web/img/parts3.png" class="parts-img mb-3" alt="">
            
            <h5 class="fw-bold">
                Complete and Advanced Tools/Equipment
            </h5>

            <p class="text-muted">
                Having the right tools and equipment will result to excellent repairs. 
                With the Technician’s skills and complete tools, your vehicle will be restored to its original condition.
            </p>
        </div>

    </div>

    <!-- ✅ Subtitle -->
     <h4 class="text-center mb-4" style="color: #4d4d4d;">
        "Total Quality: Superior Parts from Superior Mitsubishi Motors Technology"
     </h4>


    <!-- ✅ Paragraph -->
    <p class="text-center mt-3 px-3" style="max-width: 800px; margin: auto;">
        Nowadays cheaply-made imitation parts flood the automotive market scene. Many go through these channels to save on costs, only to be spending more in the end from failure and incompatibility of these fake parts.
        Mitsubishi Genuine Parts are produced with unmatched and the strictest quality control. This is your guarantee that every part will bring to your driving experience the highest standards of safety and performance.
        For all your vehicle's scheduled and unscheduled repair and replacement requirements, Citimotors, Inc. has Mitsubishi Motors genuine lubricants, engine and body parts, at very affordable prices.
    </p>

     <button class="btn-custom">Book a Service</button>

</div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>