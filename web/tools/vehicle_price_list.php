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

<style>
body {
    background: #0a0a0a;
    font-family: 'Poppins', sans-serif;
    color: #fff;
}

/* Container */
.container {
    margin-top: 40px;
}

/* TITLE */
.page-title {
    text-align: center;
    margin-bottom: 30px;
}

.page-title h1 {
    font-weight: 700;
    font-size: 36px;
    letter-spacing: 2px;
}

.page-title span {
    color: #dc3545; /* red accent */
}

/* Table */
.price-table {
    background: #111;
    border-radius: 6px;
    overflow: hidden;
}

/* Header */
.price-header {
    background: #dc3545;
    padding: 12px 20px;
    font-weight: 600;
}

/* Row */
.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid #222;
    transition: 0.2s;
}

.price-row:hover {
    background: #1a1a1a;
}

/* Left text */
.model-text {
    font-size: 14px;
    color: #fff;
}

/* Price */
.price {
    color: #ff3b3b;
    font-weight: 600;
    font-size: 14px;
}

/* Remove last border */
.price-row:last-child {
    border-bottom: none;
}
</style>
</head>

<body>

<div class="container">

    <!-- 🔥 TITLE -->
    <div class="page-title">
        <h1>VEHICLE <span>PRICE LIST</span></h1>
    </div>

    <div class="price-table">

        <!-- HEADER -->
        <div class="price-header">
            Model
        </div>

        <!-- DATA -->
        <?php while ($row = $result->fetch_assoc()): ?>

            <div class="price-row">

                <div class="model-text">
                    <?php 
                        echo htmlspecialchars($row['model_name']) . " " . 
                             htmlspecialchars($row['model_variant']); 
                    ?>
                </div>

                <div class="price">
                    ₱<?php echo number_format($row['price'], 2); ?>
                </div>

            </div>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>