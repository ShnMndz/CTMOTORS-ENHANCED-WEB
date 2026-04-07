<?php
session_start();
include '../db.php';



// Check if admin (optional read-only mode)
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Optional filter by type
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Only fetch vehicles with image AND price
// Use DISTINCT model_name to show only one card per model
$sql = "SELECT * FROM vehicles v1 
        WHERE image IS NOT NULL AND image != '' AND price IS NOT NULL
        AND v1.id = (SELECT MIN(v2.id) FROM vehicles v2 WHERE v2.model_name = v1.model_name)";

if($filter_type === 'passenger' || $filter_type === 'commercial'){
    $sql .= " AND vehicle_type='$filter_type'";
}

$sql .= " ORDER BY id ASC";
$vehicles_result = $conn->query($sql);
?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CITI MOTORS - Products</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/products.css">
<style>

    
body { font-family: 'Poppins', sans-serif; background:#f8fafc; margin:0; padding:0; }

/* Product grid */
.product-item img { max-height: 180px; object-fit: cover; border-radius:8px; }
.product-item h5 { font-size:1rem; font-weight:600; margin-top:0.5rem; }

/* HIDE PRICE */
.product-item p { display: none; }

/* Footer */
.footer { background-color: #f8f9fa; padding: 30px 0; margin-top: 50px; }
.footer-column { margin-bottom: 20px; }
.footer-column h3 { font-size: 16px; margin-bottom: 10px; }
.footer-column ul { list-style: none; padding-left: 0; }
.footer-column ul li { margin-bottom: 6px; }
.footer-column ul li a { text-decoration: none; color: #333; }
.footer-bottom { font-size: 13px; color: #666; }

/* Admin read-only (optional) */
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



<!-- Search + Filter -->
<div class="container my-4">
    <div class="row justify-content-center g-3">
        <div class="col-md-4">
            <input type="text" id="productSearch" class="form-control form-control-lg"
                   placeholder="Search for vehicles..." onkeyup="searchProducts()">
        </div>
        <div class="col-md-3">
            <select id="filterType" class="form-select form-select-lg" onchange="filterType()">
                <option value="all" <?php if($filter_type==='all') echo 'selected'; ?>>All Types</option>
                <option value="passenger" <?php if($filter_type==='passenger') echo 'selected'; ?>>Passenger</option>
                <option value="commercial" <?php if($filter_type==='commercial') echo 'selected'; ?>>Commercial</option>
            </select>
        </div>
    </div>
</div>

<!-- Vehicles Grid (image + name, price hidden) -->
<section class="container my-5">
    <div class="row g-4" id="productsGrid">
        <?php if($vehicles_result->num_rows > 0): ?>
            <?php while($row = $vehicles_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 col-sm-12 product-item" data-type="<?php echo $row['vehicle_type']; ?>">
                <a href="product-details.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                    <img src="../img/<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="<?php echo htmlspecialchars($row['model_name']); ?>" 
                         style="width:100%; height:180px; object-fit:cover; border-radius:8px;">
                    <div class="text-center mt-2">
                        <h5 class="mb-0"><?php echo htmlspecialchars($row['model_name']); ?></h5>
                    </div>
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
          <li><a href="../branches/branch.html#laspinas">Las Piñas Office</a></li>
          <li><a href="../branches/branch.html#alabang">Alabang Office</a></li>
        </ul>
      </div>
      <div class="col-md-3 footer-column">
        <h3>Product Vehicles</h3>
        <ul>
          <?php
          $vehicle_links = $conn->query("
            SELECT * FROM vehicles v1 
            WHERE image IS NOT NULL AND image != '' AND price IS NOT NULL
            AND v1.id = (SELECT MIN(v2.id) FROM vehicles v2 WHERE v2.model_name = v1.model_name)
            ORDER BY id ASC
          ");
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
// Search filter
function searchProducts() {
    let input = document.getElementById("productSearch").value.toLowerCase();
    let products = document.getElementsByClassName("product-item");
    for (let i = 0; i < products.length; i++) {
        let name = products[i].innerText.toLowerCase();
        products[i].style.display = name.includes(input) ? "" : "none";
    }
}

// Type filter
function filterType() {
    let type = document.getElementById("filterType").value;
    let products = document.getElementsByClassName("product-item");
    for (let i = 0; i < products.length; i++) {
        products[i].style.display = (type === 'all' || products[i].getAttribute('data-type') === type) ? "" : "none";
    }
}
</script>
</body>
</html>