<?php
session_start(); // MUST be first
include 'db.php'; // or wherever your DB connection is
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to CITI MOTORS</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/navbar.css">
<link rel="stylesheet" href="home.css">

</head>
<body>
  
<!-- PHP -->
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-brochure d-flex align-items-center text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">THE CITY NEEDS CITI MOTORS</h1>
        <p class="lead mt-3">Premium vehicles. Trusted service. Unmatched value.</p>
        <div class="mt-4">
            <a href="products/products.html" class="btn btn-danger btn-lg me-2">View Vehicles</a>
            <a href="testdrive/test-drive-page.html" class="btn btn-outline-light btn-lg">Book a Test Drive</a>
        </div>
    </div>
</section>

<!-- Floating Sidebar -->
<div class="floating-sidebar">
    <a href="price/price.html" class="sidebar-btn">
        <i class="bi bi-list"></i>
        <span>Price list</span>
    </a>
    <a href="configurator/selection-config.html" class="sidebar-btn">
        <i class="bi bi-tools"></i>
        <span>Vehicle Configurator</span>
    </a>
    <a href="compare/compare.html" class="sidebar-btn">
        <i class="bi bi-car-front-fill"></i>
        <span>Compare vehicles</span>
    </a>
    <a href="testdrive/test-drive-page.html" class="sidebar-btn">
        <i class="bi bi-car-front"></i>
        <span>Book a Test Drive</span>
    </a>
    <a href="#" class="sidebar-btn">
        <i class="bi bi-facebook"></i>
    </a>
</div>

<!-- Featured Vehicles Carousel -->
<section class="container my-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold text-danger">Featured Vehicles</h2>
        <p class="text-muted">Explore our most popular models</p>
    </div>

    <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">

        <div class="carousel-inner">

            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="vehicle-slide">
                    <img src="img/homecar1.png" class="d-block w-100" alt="Triton">

                    <div class="carousel-caption">
                        <h3>Triton</h3>
                        <p>Built for Adventure</p>
                        <a href="products/vehicles/commercial/triton/triton.html"
                           class="btn btn-danger">
                           Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="vehicle-slide">
                    <img src="img/homecar2.png" class="d-block w-100" alt="Montero Sport">

                    <div class="carousel-caption">
                        <h3>Montero Sport</h3>
                        <p>Power & Performance</p>
                        <a href="products/vehicles/commercial/monterosport/monterosport.html"
                           class="btn btn-danger">
                           Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="vehicle-slide">
                    <img src="img/homecar3.png" class="d-block w-100" alt="Mirage G4">

                    <div class="carousel-caption">
                        <h3>Mirage G4</h3>
                        <p>Compact & Efficient</p>
                        <a href="products/vehicles/passenger/mirageg4/mirageg4.html"
                           class="btn btn-danger">
                           Learn More
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>

    </div>

</section>

    <section class="blog-section">
  <div class="container">
    
    <h2 class="section-title">Latest News & Blog</h2>
    <p class="section-subtitle">Stay updated with the latest automotive news, tips, and vehicle reviews.</p>

    <div class="blog-grid">

      <!-- Blog Post 1 -->
      <div class="blog-card">
        <img src="img/blog1.png" alt="Mitsubishi Xpander Review">
        <div class="blog-content">
          <span class="blog-date">February 26, 2026</span>
          <h3>Mitsubishi Xpander Takes the Crown: Philippines’ No. 1 Selling Vehicle of 2025 and Best-Selling MPV for Three Consecutive Years</h3>
          <p>Discover the features, comfort, and performance that make the Xpander a top choice for family driving.</p>
          <a href="https://www.mitsubishi-motors.com.ph/articles/xpander-best-selling-vehicle-2025" class="read-more">Read More →</a>
        </div>
      </div>

      <!-- Blog Post 2 -->
      <div class="blog-card">
        <img src="img/blog2.png" alt="Car Maintenance">
        <div class="blog-content">
          <span class="blog-date">February 28, 2026</span>
          <h3>Basic Car Repair Tips</h3>
          <p>Learn how proper maintenance can extend your car's life and improve performance.</p>
          <a href="https://gomechanic.in/blog/basic-car-repairs-tips"  class="read-more">Read More →</a>
        </div>
      </div>

      <!-- Blog Post 3 -->
      <div class="blog-card">
        <img src="img/blog3.png" alt="SUV Buying Guide">
        <div class="blog-content">
          <span class="blog-date">January 20, 2022</span>
          <h3>Questions to Ask Yourself Before Purchasing a New Car</h3>
          <p>Here are key factors you should consider before making your decision.</p>
          <a href="https://blogs.crossmap.com/stories/questions-to-ask-yourself-before-purchasing-a-new-car-ynDFcZ77HBIvebQBnJmnx?gad_source=1&gad_campaignid=21177565647&gbraid=0AAAAApF8YP7gLfWBJ3Dt68HHEhZ136fql&gclid=Cj0KCQjw37nNBhDkARIsAEBGI8NKjHa0Vkbai3OFQ5pK6ZGvFSDgcZB2Y9Q4TZutK3cHvLx0vKuOqdMaAp4mEALw_wcB" class="read-more">Read More →</a>
        </div>
      </div>

    </div>

  </div>
</section>
</section>


<!-- Footer -->
<footer class="footer">
  <div class="footer-container">
    
    <div class="footer-column">
      <h3>About Us</h3>
      <ul>
        <li><a href="aboutus/aboutus.html#vision">Corporate Vision</a></li>
        <li><a href="aboutus/aboutus.html#mission">Mission Statement</a></li>
        <li><a href="aboutus/aboutus.html#history">Corporate History</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Branches</h3>
      <ul>
        <li><a href="branches/branch.html#makati">Makati Office</a></li>
        <li><a href="branches/branch.html#laspinas">Las Pinas Office</a></li>
        <li><a href="branches/branch.html#alabang">Alabang Office</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Product Vehicles</h3>
      <ul>
        <li><a href="products/vehicles/commercial/l200/l200.html">DESTINATOR</a></li>
        <li><a href="products/vehicles/commercial/l300/l300fb.html">L300 FB EXCEED</a></li>
        <li><a href="products/vehicles/commercial/monterosport/monterosport.html">MONTERO SPORT</a></li>
        <li><a href="products/vehicles/commercial/triton/triton.html">TRITON</a></li>
        <li><a href="products/vehicles/commercial/xforce/xforce.html">XFORCE</a></li>
        <li><a href="products/vehicles/commercial/xpander/xpander.html">XPANDER</a></li>
        <li><a href="products/vehicles/passenger/mirage/mirage.html">MIRAGE</a></li>
        <li><a href="products/vehicles/passenger/mirageg4/mirageg4.html">MIRAGE G4</a></li>
        <li><a href="products/vehicles/trucks/canter.html">CANTER</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Parts and Services</h3>
      <ul>
        <li><a href="partsandservices/partsandservices.html#genuine">Genuine Parts</a></li>
        <li><a href="partsandservices/partsandservices.html#serviceandbodyshop">Service and Body Shop</a></li>
      </ul>
    </div>


  </div>

  <div class="footer-bottom">
    © Disclaimer: This website is made for test only by a student. No copyright
infringement intended
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>