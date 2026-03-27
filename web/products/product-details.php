<?php
include '../db.php';

// Get vehicle ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch vehicle
$result = $conn->query("SELECT * FROM vehicles WHERE id = $id");

if ($result->num_rows == 0) {
    die("Vehicle not found");
}

$vehicle = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($vehicle['model_name']); ?> | CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5f7fb;
}

.container-box {
    margin-top: 50px;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.car-img {
    width: 100%;
    border-radius: 10px;
}

.price {
    font-size: 30px;
    font-weight: 600;
    color: #0d6efd;
}

.features {
    margin-top: 15px;
}
</style>
</head>

<body>

<div class="container">

    <a href="products.php" class="btn btn-outline-secondary mt-4">← Back to Products</a>

    <div class="container-box row mt-3">

        <!-- IMAGE -->
        <div class="col-md-6">
            <?php if(!empty($vehicle['image'])): ?>
                <img src="../img/<?php echo htmlspecialchars($vehicle['image']); ?>" class="car-img">
            <?php else: ?>
                <div class="text-center text-muted" style="padding:50px; border:1px dashed #ccc;">No Image Available</div>
            <?php endif; ?>
        </div>

        <!-- DETAILS -->
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($vehicle['model_name']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($vehicle['model_type']); ?></p>

            <div class="price">₱<?php echo number_format($vehicle['price'], 2); ?></div>

            <p class="mt-3"><?php echo nl2br(htmlspecialchars($vehicle['description'])); ?></p>

            <?php if(!empty($vehicle['features'])): ?>
            <div class="features">
                <h5>Features:</h5>
                <ul>
                    <?php
                    $features = explode(',', $vehicle['features']);
                    foreach($features as $f) {
                        echo '<li>' . htmlspecialchars(trim($f)) . '</li>';
                    }
                    ?>
                </ul>
            </div>
            <?php endif; ?>

        </div>

    </div>
</div>

</body>
</html>