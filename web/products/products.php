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

<div class="product-search-bar">
    <div class="container">
        <div class="row align-items-center search-content">

            <!-- LEFT -->
            <div class="col-lg-5 mb-4 mb-lg-0">

                <h1 class="hero-title">
                    Explore Our
                    <span>Vehicles</span>
                </h1>

                <p class="hero-subtitle">
                    Find the perfect Mitsubishi that drives your ambition.
                </p>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-7">

                <div class="row g-3">

                    <div class="col-md-7">
                        <input
                            type="text"
                            id="productSearch"
                            class="form-control"
                            placeholder="Search for vehicles..."
                            onkeyup="searchProducts()"
                        >
                    </div>

                    <div class="col-md-5">
                        <select
                            id="filterType"
                            class="form-select"
                            onchange="filterType()"
                        >
                            <option value="all">All Types</option>
                            <option value="passenger">Passenger</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>

                </div>

            </div>

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