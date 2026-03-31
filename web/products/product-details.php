<?php
session_start();
include '../db.php';

// Get vehicle ID
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch selected variant
$vehicle_result = $conn->query("SELECT * FROM vehicles WHERE id=$vehicle_id");
if($vehicle_result->num_rows == 0){
    echo "Vehicle not found.";
    exit;
}
$vehicle = $vehicle_result->fetch_assoc();

// Fetch all variants of same model
$variants_result = $conn->query(
    "SELECT * FROM vehicles 
     WHERE model_name='". $conn->real_escape_string($vehicle['model_name']) ."' 
     ORDER BY id ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($vehicle['model_name']); ?> - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- GOOGLE FONT -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- EXTERNAL CSS -->
<link rel="stylesheet" href="product-details.css">
</head>

<body>

<div class="container my-5">

    <!-- MAIN VEHICLE DISPLAY -->
    <div class="vehicle-details">

        <!-- LEFT IMAGE -->
        <div class="vehicle-image">
            <img id="vehicle-image" 
                 src="../img/<?php echo htmlspecialchars($vehicle['image']); ?>">
        </div>

        <!-- RIGHT DETAILS -->
        <div class="vehicle-info">
            <h1 id="vehicle-name"><?php echo htmlspecialchars($vehicle['model_name']); ?></h1>
            <h3 id="vehicle-variant"><?php echo htmlspecialchars($vehicle['model_variant']); ?></h3>

            <div class="vehicle-price" id="vehicle-price">
                ₱<?php echo number_format($vehicle['price'],2); ?>
            </div>

            <div class="features-title">Key Features</div>
            <ul class="features-list" id="vehicle-features">
                <?php foreach(explode("\n",$vehicle['features']) as $f): ?>
                    <li><?php echo htmlspecialchars($f); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

    <!-- VARIANTS -->
    <h4>Available Variants:</h4>
    <div class="row g-3">
        <?php if($variants_result->num_rows>0): while($v = $variants_result->fetch_assoc()): 
            $highlight = ($v['id'] == $vehicle_id) ? 'variant-selected' : '';
        ?>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card shadow-sm text-center h-100 variant-card <?php echo $highlight; ?>"
                 data-variant="<?php echo htmlspecialchars($v['model_variant']); ?>"
                 data-price="<?php echo $v['price']; ?>"
                 data-features="<?php echo htmlspecialchars($v['features'], ENT_QUOTES); ?>"
                 data-image="../img/<?php echo htmlspecialchars($v['image']); ?>">

                <img src="../img/<?php echo htmlspecialchars($v['image']); ?>" 
                     alt="<?php echo htmlspecialchars($v['model_variant']); ?>" 
                     class="card-img-top">

                <div class="card-body">
                    <div class="card-title"><?php echo htmlspecialchars($v['model_variant']); ?></div>
                    <div class="card-price">₱<?php echo number_format($v['price'],2); ?></div>
                </div>

            </div>
        </div>
        <?php endwhile; else: ?>
            <p>No variants available.</p>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Variant switching
document.querySelectorAll('.variant-card').forEach(card => {
    card.addEventListener('click', function(){

        document.querySelectorAll('.variant-card')
            .forEach(c => c.classList.remove('variant-selected'));
        this.classList.add('variant-selected');

        // Variant name
        document.getElementById('vehicle-variant').textContent = this.dataset.variant;

        // Price
        document.getElementById('vehicle-price').textContent =
            "₱" + parseFloat(this.dataset.price)
            .toLocaleString('en-PH', {minimumFractionDigits:2});

        // Features list
        let features = this.dataset.features
            .split("\n")
            .map(f => "<li>" + f + "</li>")
            .join("");

        document.getElementById('vehicle-features').innerHTML = features;

        // Image
        document.getElementById('vehicle-image').src = this.dataset.image;
    });
});
</script>

</body>
</html>