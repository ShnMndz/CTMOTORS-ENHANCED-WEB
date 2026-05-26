<?php
session_start();
include '../db.php';

$sql = "SELECT model_name, model_variant, price 
        FROM vehicles 
        WHERE price IS NOT NULL
        ORDER BY model_name ASC, price ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Price List - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<link rel="stylesheet" href="vehicle_price_list.css">

</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- Hero -->
<div class="tools-hero">
    <div class="hero-content">
        <div class="hero-sub">CITI MOTORS</div>
        <h1>Tools & Services</h1>
        <p>Check our latest Mitsubishi vehicle price list</p>
    </div>
</div>

<!-- ✅ FIXED CONTAINER -->
<div class="container price-container">
    <div class="price-table">
        <div class="price-header">Model</div>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="price-row">
                <div class="model-text">
                    <?= htmlspecialchars($row['model_name'].' '.$row['model_variant']); ?>
                </div>
                <div class="price">
                    ₱<?= number_format($row['price'],2); ?>
                </div>
            </div>
        <?php endwhile; ?>

    </div>
</div>

<!-- Footer -->
<footer class="footer mt-5">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>