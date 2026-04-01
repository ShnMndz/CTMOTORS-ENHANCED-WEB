<?php
session_start();
include '../db.php';

// Get vehicle ID safely
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch selected vehicle
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle_result = $stmt->get_result();

if ($vehicle_result->num_rows === 0) {
    echo "Vehicle not found.";
    exit;
}

$vehicle = $vehicle_result->fetch_assoc();

// Fetch all variants of the same model
$stmt2 = $conn->prepare("SELECT * FROM vehicles WHERE model_name = ? ORDER BY id ASC");
$stmt2->bind_param("s", $vehicle['model_name']);
$stmt2->execute();
$variants_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($vehicle['model_name']); ?> - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Poppins', sans-serif; background: #f8fafc; }

/* Layout */
.vehicle-details { display: flex; flex-wrap: wrap; gap: 30px; }
.vehicle-image { position: relative; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.vehicle-image img { width: 100%; max-width: 500px; border-radius: 10px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.vehicle-image.spotlight img { transform: scale(1.1); box-shadow: 0 20px 40px rgba(0,0,0,0.25); border-radius: 12px; }

.vehicle-info { flex: 1; }
.vehicle-price { font-size: 24px; font-weight: bold; color: #dc3545; margin: 10px 0; }
.features-title { font-weight: 600; margin-top: 15px; }
.features-list li { margin-bottom: 5px; }

/* Bigger Vehicle Details */
.vehicle-info h1 { font-size: 48px; font-weight: 700; margin-bottom: 10px; }
.vehicle-info h3 { font-size: 32px; font-weight: 600; margin-bottom: 15px; color: #333; }
.vehicle-price { font-size: 36px; font-weight: 700; color: #dc3545; margin: 15px 0; }
.features-title { font-size: 20px; font-weight: 600; margin-top: 25px; }
.features-list li { font-size: 16px; margin-bottom: 8px; }

/* Variants */
.variant-card { 
    cursor: pointer;
    transition: transform 0.2s, border-color 0.2s;
    border: 2px solid transparent;
    border-radius: 8px;
    min-width: 120px;
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 80px;
    background: #fff;
    font-weight: 500;
    font-size: 14px;
    text-align: center;
}
.variant-card:hover { transform: scale(1.05); border-color: #dc3545; }
.variant-selected { border-color: #dc3545; }

/* Scrollable variant container */
.variant-scroll-container { display: flex; gap: 10px; overflow-x: auto; padding: 10px 0; scroll-behavior: smooth; }
.variant-scroll-container::-webkit-scrollbar { height: 8px; }
.variant-scroll-container::-webkit-scrollbar-thumb { background: #dc3545; border-radius: 4px; }
.variant-scroll-container::-webkit-scrollbar-track { background: #f0f0f0; }
</style>
</head>
<body>

<div class="container my-5">

    <!-- RETURN BUTTON -->
    <div class="mb-4">
        <a href="javascript:history.back()" class="btn btn-secondary">
            &larr; Return
        </a>
    </div>

    <!-- MAIN VEHICLE DETAILS -->
    <div class="vehicle-details">

        <!-- IMAGE -->
        <div class="vehicle-image">
            <img id="vehicle-image" src="../img/<?php echo htmlspecialchars($vehicle['image'] ?: 'no-image.png'); ?>" alt="Vehicle Image">
        </div>

        <!-- INFO -->
        <div class="vehicle-info">
            <h1><?php echo htmlspecialchars($vehicle['model_name']); ?></h1>
            <h3 id="vehicle-variant"><?php echo htmlspecialchars($vehicle['model_variant']); ?></h3>

            <div class="vehicle-price" id="vehicle-price">
                ₱<?php echo number_format($vehicle['price'], 2); ?>
            </div>

            <div class="features-title">Key Features</div>
            <ul class="features-list" id="vehicle-features">
                <?php 
                if (!empty($vehicle['features'])) {
                    foreach (explode("\n", $vehicle['features']) as $f) {
                        $f = trim($f);
                        if ($f !== '') echo "<li>".htmlspecialchars($f)."</li>";
                    }
                } else {
                    echo "<li>No features listed</li>";
                }
                ?>
            </ul>
        </div>

    </div>

    <!-- VEHICLE VARIANTS -->
    <h4 class="mt-5">Available Variants:</h4>
    <div class="variant-scroll-container">

    <?php if ($variants_result->num_rows > 0): ?>
        <?php while ($v = $variants_result->fetch_assoc()):
            $highlight = ($v['id'] == $vehicle_id) ? 'variant-selected' : '';
        ?>
        <div class="variant-card <?php echo $highlight; ?>"
             data-variant="<?php echo htmlspecialchars($v['model_variant']); ?>"
             data-price="<?php echo $v['price']; ?>"
             data-features="<?php echo htmlspecialchars($v['features'], ENT_QUOTES); ?>"
             data-image="../img/<?php echo htmlspecialchars($v['image'] ?: 'no-image.png'); ?>">
            <?php echo htmlspecialchars($v['model_variant']); ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No variants available.</p>
    <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Variant switching
document.querySelectorAll('.variant-card').forEach(card => {
    card.addEventListener('click', function() {

        // Highlight selected variant
        document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('variant-selected'));
        this.classList.add('variant-selected');

        // Update main vehicle details
        const vehicleImage = document.getElementById('vehicle-image');
        vehicleImage.src = this.dataset.image;

        document.getElementById('vehicle-variant').textContent = this.dataset.variant;
        document.getElementById('vehicle-price').textContent = "₱" + parseFloat(this.dataset.price)
            .toLocaleString('en-PH', { minimumFractionDigits: 2 });

        let features = this.dataset.features
            ? this.dataset.features.split("\n").map(f => "<li>" + f + "</li>").join("")
            : "<li>No features listed</li>";
        document.getElementById('vehicle-features').innerHTML = features;

        // Spotlight effect
        const container = document.querySelector('.vehicle-image');
        container.classList.add('spotlight');

        // Remove spotlight after 1.2 seconds (temporary zoom effect)
        setTimeout(() => container.classList.remove('spotlight'), 1200);
    });
});
</script>

</body>
</html>