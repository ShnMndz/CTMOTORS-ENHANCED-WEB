<?php
include '../db.php'; // Database connection

// Fetch all vehicles
$vehicles_result = $conn->query("SELECT * FROM vehicles ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CITI MOTORS - Products</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/products.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    .product-item img { max-height: 180px; object-fit: contain; }
    .footer { background-color: #f8f9fa; padding: 30px 0; margin-top: 50px; }
    .footer-column { margin-bottom: 20px; }
    .footer-column h3 { font-size: 16px; margin-bottom: 10px; }
    .footer-column ul { list-style: none; padding-left: 0; }
    .footer-column ul li { margin-bottom: 6px; }
    .footer-column ul li a { text-decoration: none; color: #333; }
    .footer-bottom { font-size: 13px; color: #666; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="../home.html">
        <img src="../img/logo.png" alt="CITI MOTORS">
    </a>
    <div class="d-flex gap-3 align-items-center">
        <a href="../home.html" class="nav-link-custom">Home</a>
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">About Us</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#vision">Corporate Vision</a></li>
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#mission">Mission Statement</a></li>
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#history">Company History</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Branches</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../branches/branch.html#makati">Makati (Head Office)</a></li>
                <li><a class="dropdown-item" href="../branches/branch.html#laspinas">Las Piñas Office</a></li>
                <li><a class="dropdown-item" href="../branches/branch.html#alabang">Alabang Office</a></li>
            </ul>
        </div>
        <a class="nav-link-custom active-link" href="products.php">Products</a>
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Parts & Services</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../partsandservices/partsandservices.html#genuineparts">Genuine Parts</a></li>
                <li><a class="dropdown-item" href="../partsandservices/partsandservices.html#servicebody">Service & Body Shop</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Jobs</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../jobs/jobs.html#accounting">Accounting Clerk</a></li>
                <li><a class="dropdown-item" href="../jobs/jobs.html#credit">Credit & Collection Clerk</a></li>
                <li><a class="dropdown-item" href="../jobs/jobs.html#insurance">Insurance Assistance Coordinator</a></li>
                <li><a class="dropdown-item" href="../jobs/jobs.html#purchase">Purchasing Clerk</a></li>
                <li><a class="dropdown-item" href="../jobs/jobs.html#sales">Sales Executives</a></li>
                <li><a class="dropdown-item" href="../jobs/jobs.html#secretary">Secretary</a></li>
            </ul>
        </div>
        <a href="../contacts/contactus.html" class="nav-link-custom">Contact Us</a>
    </div>
  </div>
</nav>

<!-- Search Bar -->
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <input type="text" id="productSearch" class="form-control form-control-lg"
                   placeholder="Search for vehicles..." onkeyup="searchProducts()">
        </div>
    </div>
</div>

<!-- Vehicles Grid -->
<section class="container my-5">
    <div class="row text-center g-4" id="productsGrid">
        <?php if($vehicles_result->num_rows > 0): ?>
            <?php while($row = $vehicles_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 col-sm-12 product-item">
                <a href="<?php echo htmlspecialchars($row['link']); ?>" class="text-decoration-none text-dark">
                    <img src="../img/<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h5 class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="text-muted">From ₱<?php echo number_format($row['price']); ?></p>
                </a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No vehicles found.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-md-3 footer-column">
        <h3>About Us</h3>
        <ul>
          <li><a href="../aboutus/aboutus.html#vision">Corporate Vision</a></li>
          <li><a href="../aboutus/aboutus.html#mission">Mission Statement</a></li>
          <li><a href="../aboutus/aboutus.html#history">Company History</a></li>
        </ul>
      </div>
      <div class="col-md-3 footer-column">
        <h3>Branches</h3>
        <ul>
          <li><a href="../branches/branch.html#makati">Makati Office</a></li>
          <li><a href="../branches/branch.html#laspinas">Las Pinas Office</a></li>
          <li><a href="../branches/branch.html#alabang">Alabang Office</a></li>
        </ul>
      </div>
      <div class="col-md-3 footer-column">
        <h3>Product Vehicles</h3>
        <ul>
          <?php
          $vehicle_links = $conn->query("SELECT name, link FROM vehicles ORDER BY id ASC");
          while($v = $vehicle_links->fetch_assoc()){
              echo "<li><a href='${v['link']}'>".htmlspecialchars($v['name'])."</a></li>";
          }
          ?>
        </ul>
      </div>
      <div class="col-md-3 footer-column">
        <h3>Parts & Services</h3>
        <ul>
          <li><a href="../partsandservices/partsandservices.html#genuine">Genuine Parts</a></li>
          <li><a href="../partsandservices/partsandservices.html#service">Service and Body Shop</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom text-center py-3">
      © Disclaimer: This website is made for test only by a student. No copyright infringement intended
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function searchProducts() {
    let input = document.getElementById("productSearch").value.toLowerCase();
    let products = document.getElementsByClassName("product-item");
    for (let i = 0; i < products.length; i++) {
        let name = products[i].innerText.toLowerCase();
        products[i].style.display = name.includes(input) ? "" : "none";
    }
}
</script>
</body>
</html>