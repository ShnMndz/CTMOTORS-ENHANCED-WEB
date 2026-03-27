<?php
session_start();
include '../db.php'; // Database connection

// Determine if the user is admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch all vehicles
$vehicles_result = $conn->query("SELECT * FROM vehicles ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CITI MOTORS - Products</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/products.css">

<style>
body { font-family: 'Poppins', sans-serif; }
.product-item img { max-height: 180px; object-fit: cover; border-radius:8px; }
.footer { background-color: #f8f9fa; padding: 30px 0; margin-top: 50px; }
.footer-column { margin-bottom: 20px; }
.footer-column h3 { font-size: 16px; margin-bottom: 10px; }
.footer-column ul { list-style: none; padding-left: 0; }
.footer-column ul li { margin-bottom: 6px; }
.footer-column ul li a { text-decoration: none; color: #333; }
.footer-bottom { font-size: 13px; color: #666; }

/* Admin read-only styles */
<?php if($isAdmin): ?>
a, button, input {
    pointer-events: none !important;
    cursor: default !important;
}
input { background-color: #f8f9fa !important; }
<?php endif; ?>
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="../home.php">
        <img src="../img/logo.png" alt="CITI MOTORS">
    </a>

    <div class="d-flex gap-3 align-items-center">

        <a href="../home.php" class="nav-link-custom">Home</a>

        <!-- About Us Dropdown -->
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">About Us</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#vision">Corporate Vision</a></li>
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#mission">Mission Statement</a></li>
                <li><a class="dropdown-item" href="../aboutus/aboutus.html#history">Company History</a></li>
            </ul>
        </div>

        <!-- Branches Dropdown -->
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Branches</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../branches/branch.html#makati">Makati (Head Office)</a></li>
                <li><a class="dropdown-item" href="../branches/branch.html#laspinas">Las Piñas Office</a></li>
                <li><a class="dropdown-item" href="../branches/branch.html#alabang">Alabang Office</a></li>
            </ul>
        </div>

        <!-- Vehicles Dropdown -->
        <a class="nav-link-custom active-link" href="products.html">
    Products
</a>

        <!-- Parts & Services Dropdown -->
        <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Parts & Services</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../partsandservices/partsandservices.html#genuineparts">Genuine Parts</a></li>
                <li><a class="dropdown-item" href="../partsandservices/partsandservices.html#servicebody">Service & Body Shop</a></li>
            </ul>
        </div>

        <!-- Jobs Dropdown -->
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
    <div class="row g-4" id="productsGrid">
        <?php if($vehicles_result->num_rows > 0): ?>
            <?php while($row = $vehicles_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 col-sm-12 product-item">
                <div class="card h-100 shadow-sm">
                    <?php if(!empty($row['image'])): ?>
                        <img src="../img/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['model_name']); ?>" style="height:180px; object-fit:cover;">
                    <?php else: ?>
                        <img src="../img/no-image.png" class="card-img-top" alt="No Image" style="height:180px; object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['model_name']); ?> - <?php echo htmlspecialchars($row['model_variant']); ?></h5>
                        <p class="card-subtitle mb-2 text-muted">Type: <?php echo htmlspecialchars($row['model_type']); ?></p>
                        <p class="card-text">
                            <?php echo htmlspecialchars(strlen($row['description']) > 80 ? substr($row['description'],0,80).'...' : $row['description']); ?>
                        </p>
                        <?php if(!empty($row['features'])): ?>
                            <p class="text-small"><strong>Features:</strong> <?php echo htmlspecialchars($row['features']); ?></p>
                        <?php endif; ?>
                        <p class="fw-bold mb-3">₱<?php echo number_format($row['price'],2); ?></p>
                        <a href="product-details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary mt-auto">View Details</a>
                    </div>
                </div>
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
          $vehicle_links = $conn->query("SELECT id, model_name FROM vehicles ORDER BY id ASC");
          while($v = $vehicle_links->fetch_assoc()){
              echo "<li><a href='product-details.php?id=".$v['id']."'>".htmlspecialchars($v['model_name'])."</a></li>";
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