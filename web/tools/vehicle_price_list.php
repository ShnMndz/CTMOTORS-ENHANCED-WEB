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

<style>
body {
    background:#0a0a0a;
    color:#fff;
    font-family:'Poppins',sans-serif;
    overflow-x: hidden;
}

/* ✅ FIX: sariling container (hindi bootstrap) */
.price-container {
    margin-top: 40px;
    max-width: 900px;
}

/* Table */
.price-table {
    background: #111;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.price-header {
    background: #dc3545;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 16px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-bottom:1px solid #222;
    transition:0.2s;
}

.price-row:hover {
    background: #1a1a1a;
}

.model-text {
    font-size: 14px;
    color: #fff;
}

.price {
    color: #ff3b3b;
    font-weight: 600;
    font-size: 14px;
}

.price-row:last-child {
    border-bottom:none;
}

/* Hero */
.tools-hero { 
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('../img/tools-hero-bg.jpg') center/cover no-repeat; 
    min-height: 200px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    text-align: center; 
    color: #fff; 
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.tools-hero h1 { font-size: 2.5rem; font-weight: 700; }
.tools-hero p { font-size: 1.2rem; margin-top: 10px; }
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- Hero -->
<div class="tools-hero">
    <div>
        <h1>Tools & Services</h1>
        <p>Check our Vehicle Price List</p>
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