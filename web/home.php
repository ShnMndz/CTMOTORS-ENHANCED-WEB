<?php
session_start();
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
/* Hero Section 1 */
.hero-brochure {
    min-height: 80vh;
    background: url('/citimotorsweb/web/img/herobackground.png') center/cover no-repeat;
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


/* Hero Section 2 (Brochure Style) */
.hero-brochure-2 {
    min-height: 85vh;
    background: url('/citimotorsweb/web/img/monterobackground.png') right center/cover no-repeat;
    position: relative;
    display: flex;
}

/* Dark gradient overlay */
.hero-brochure-2::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to right,
        rgba(0,0,0,0.8) 35%,
        rgba(0,0,0,0.4) 65%,
        rgba(0,0,0,0.1) 100%
    );
    z-index: 1;
}


.hero-brochure-2::after {
    content: "";
    position: absolute;
    inset: 0;

    background: linear-gradient(
        to bottom,
        #000 0%,                
        rgba(0,0,0,0) 15%,     
        rgba(0,0,0,0) 75%,      
        #f8f9fa 100%            
    );

    z-index: 1;
}

.hero-brochure-2 .container {
    position: relative;
    z-index: 2;
}

.hero-brochure-2 h1 {
    line-height: 1.2;
    font-weight: 300;
}

.hero-brochure-2 p {
    max-width: 500px;
    color: #ddd;
}

.hero-brochure-2 .btn {
    border-radius: 50px;
    letter-spacing: 1px;
}


/* Footer */
.footer {
    background: #222;
    color: #fff;
    padding: 30px 0;
    margin-top: 50px;
}
.footer-column {
    margin-bottom: 20px;
}
.footer-column h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #fff;
}
.footer-column ul {
    list-style: none;
    padding-left: 0;
}
.footer-column ul li {
    margin-bottom: 6px;
}
.footer-column ul li a {
    text-decoration: none;
    color: #ccc;
}
.footer-column ul li a:hover {
    color: #fff;
}
.footer-bottom {
    font-size: 13px;
    color: #666;
}


/* General */
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
            <a href="tools/testdrive.php" class="btn btn-outline-light btn-lg">Book a Test Drive</a>
        </div>
    </div>
</section>

</section> <!-- END HERO -->

<section class="hero-brochure-2 d-flex align-items-center text-white">
    <div class="container">
        <div class="row align-items-center">

            <!-- LEFT TEXT -->
            <div class="col-lg-6">
                <h1 class="display-4 fw-light">
                    Drive <br>
                    Beyond  Limits <br>
                </h1>

                <p class="mt-4">
                    Powered by Mitsubishi’s reliable engineering, this vehicle is
                    designed for strength, efficiency, and all-terrain capability. 
                    Take on any journey with confidence, comfort, and control.
                </p>

                <a href="#" class="btn btn-outline-light mt-3 px-4 py-2">
                    <i class="bi bi-plus"></i> READ MORE
                </a>
            </div>

            <!-- RIGHT SIDE (empty, image handles it) -->
            <div class="col-lg-6"></div>

        </div>
    </div>
</section>

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
  <div class="container">
    <div class="row">

      <div class="col-md-3 footer-column">
        <h3>Main</h3>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="aboutus/aboutus.php">About Us</a></li>
          <li><a href="news/articles.php">News</a></li>
          <li><a href="contacts/contacts.php">Contact Us</a></li>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Tools & Service</h3>
        <ul>
          <li><a href="tools/vehicle_price_list.php">Price List</a></li>
          <li><a href="tools/compare.php">Compare Vehicles</a></li>
          <li><a href="tools/testdrive.php">Book a Test Drive</a></li>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Products</h3>
        <ul>
          <li><a href="products/products.php">All Vehicles</a></li>
          <?php
          $vehicle_links = $conn->query("
            SELECT * FROM vehicles v1 
            WHERE image IS NOT NULL AND image != '' 
            AND price IS NOT NULL
            AND v1.id = (SELECT MIN(v2.id) FROM vehicles v2 WHERE v2.model_name = v1.model_name)
            ORDER BY id ASC LIMIT 5
          ");
          while($v = $vehicle_links->fetch_assoc()){
              echo "<li><a href='products/product-details.php?id=".$v['id']."'>".htmlspecialchars($v['model_name'])."</a></li>";
          }
          ?>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Parts & Services</h3>
        <ul>
          <li><a href="partsandservices/genuine_parts.php">Genuine Parts</a></li>
          <li><a href="partsandservices/services.php">Services</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom text-center py-3">
      © Disclaimer: This website is made for test only by a student.
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>