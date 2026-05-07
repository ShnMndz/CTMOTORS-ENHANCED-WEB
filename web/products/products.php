<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CITI MOTORS - Products</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../global.css">
<link rel="stylesheet" href="../css/products.css">
</head>

<body>

<?php
session_start();
include '../db.php';
include '../includes/navbar.php';

// Check if admin (optional read-only mode)
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Sanitize filter
$allowed_types  = ['all', 'passenger', 'commercial'];
$filter_type    = isset($_GET['type']) && in_array($_GET['type'], $allowed_types)
                  ? $_GET['type']
                  : 'all';

// Fetch vehicles (using prepared statement)
$base_sql = "SELECT * FROM vehicles v1 
             WHERE image IS NOT NULL AND image != '' 
             AND price IS NOT NULL
             AND v1.id = (SELECT MIN(v2.id) FROM vehicles v2 WHERE v2.model_name = v1.model_name)";

if ($filter_type === 'passenger' || $filter_type === 'commercial') {
    $stmt = $conn->prepare($base_sql . " AND vehicle_type = ? ORDER BY id ASC");
    $stmt->bind_param("s", $filter_type);
    $stmt->execute();
    $vehicles_result = $stmt->get_result();
} else {
    $vehicles_result = $conn->query($base_sql . " ORDER BY id ASC");
}
?>

<?php if ($isAdmin): ?>
<style>
/* Admin read-only — scope tightly to avoid breaking layout */
#productsGrid a,
#productsGrid button,
.product-search-bar button {
    pointer-events: none !important;
    cursor: default !important;
}
</style>
<?php endif; ?>

<!-- Search + Filter -->
<div class="container my-4 product-search-bar">
    <div class="row justify-content-center g-3">
        <div class="col-md-4">
            <input type="text" id="productSearch" class="form-control form-control-lg"
                   placeholder="Search for vehicles..." onkeyup="searchProducts()">
        </div>
        <div class="col-md-3">
            <select id="filterType" class="form-select form-select-lg" onchange="filterType()">
                <option value="all"        <?= $filter_type === 'all'        ? 'selected' : '' ?>>All Types</option>
                <option value="passenger"  <?= $filter_type === 'passenger'  ? 'selected' : '' ?>>Passenger</option>
                <option value="commercial" <?= $filter_type === 'commercial' ? 'selected' : '' ?>>Commercial</option>
            </select>
        </div>
    </div>
</div>

<!-- Products -->
<section class="container my-5">
    <div class="row g-4" id="productsGrid">

        <?php if ($vehicles_result && $vehicles_result->num_rows > 0): ?>

            <?php while ($row = $vehicles_result->fetch_assoc()): ?>

            <div class="col-lg-4 col-md-6 col-sm-12 product-item"
                 data-type="<?= htmlspecialchars($row['vehicle_type']) ?>">

                <a href="product-details.php?id=<?= (int)$row['id'] ?>"
                   style="text-decoration:none; color:inherit;">

                    <img src="../img/<?= htmlspecialchars($row['image']) ?>"
                         alt="<?= htmlspecialchars($row['model_name']) ?>"
                         style="width:100%; height:180px; object-fit:cover; border-radius:8px;">

                    <div class="text-center mt-2">
                        <h5 class="mb-0"><?= htmlspecialchars($row['model_name']) ?></h5>
                    </div>

                </a>

            </div>

            <?php endwhile; ?>

        <?php else: ?>
            <p class="text-center text-muted">No vehicles found.</p>
        <?php endif; ?>

    </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row">

      <div class="col-md-3 footer-column">
        <h3>Main</h3>
        <ul>
          <li><a href="../home.php">Home</a></li>
          <li><a href="../aboutus/aboutus.php">About Us</a></li>
          <li><a href="../news/articles.php">News</a></li>
          <li><a href="../contacts/contacts.php">Contact Us</a></li>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Tools & Service</h3>
        <ul>
          <li><a href="../tools/vehicle_price_list.php">Price List</a></li>
          <li><a href="../tools/compare.php">Compare Vehicles</a></li>
          <li><a href="../tools/testdrive.php">Book a Test Drive</a></li>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Products</h3>
        <ul>
          <li><a href="products.php">All Vehicles</a></li>
          <?php
          $vehicle_links = $conn->query("
              SELECT * FROM vehicles v1 
              WHERE image IS NOT NULL AND image != '' 
              AND price IS NOT NULL
              AND v1.id = (SELECT MIN(v2.id) FROM vehicles v2 WHERE v2.model_name = v1.model_name)
              ORDER BY id ASC LIMIT 5
          ");
          while ($v = $vehicle_links->fetch_assoc()) {
              echo "<li><a href='product-details.php?id=" . (int)$v['id'] . "'>" . htmlspecialchars($v['model_name']) . "</a></li>";
          }
          ?>
        </ul>
      </div>

      <div class="col-md-3 footer-column">
        <h3>Parts & Services</h3>
        <ul>
          <li><a href="../partsandservices/genuine_parts.php">Genuine Parts</a></li>
          <li><a href="../partsandservices/services.php">Services</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom text-center py-3">
      © Disclaimer: This website is made for test only by a student.
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function searchProducts() {
    const input    = document.getElementById("productSearch").value.toLowerCase();
    const products = document.getElementsByClassName("product-item");
    for (let i = 0; i < products.length; i++) {
        const name = products[i].innerText.toLowerCase();
        products[i].style.display = name.includes(input) ? "" : "none";
    }
}

function filterType() {
    const type     = document.getElementById("filterType").value;
    const products = document.getElementsByClassName("product-item");
    for (let i = 0; i < products.length; i++) {
        products[i].style.display =
            (type === 'all' || products[i].getAttribute('data-type') === type)
            ? "" : "none";
    }
}
</script>

</body>
</html>