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

.vehicle-details { display: flex; flex-wrap: wrap; gap: 30px; }

.vehicle-image img {
    width: 100%;
    max-width: 500px;
    border-radius: 10px;
    transition: 0.3s;
}

.vehicle-image.spotlight img {
    transform: scale(1.1);
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
}

.vehicle-info h1 { font-size: 48px; font-weight: 700; }
.vehicle-info h3 { font-size: 32px; font-weight: 600; }

.vehicle-price {
    font-size: 36px;
    font-weight: 700;
    color: #dc3545;
    margin: 15px 0;
}

.features-title { font-size: 20px; font-weight: 600; margin-top: 25px; }

.variant-card {
    cursor: pointer;
    border: 2px solid transparent;
    padding: 15px;
    border-radius: 8px;
    background: #fff;
    min-width: 120px;
    text-align: center;
    transition: 0.2s;
}

.variant-card:hover { border-color: #dc3545; transform: scale(1.05); }
.variant-selected { border-color: #dc3545; }

.variant-scroll-container {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 10px 0;
}
</style>
</head>
<body>

<div class="container my-5">

    <!-- BACK BUTTON -->
    <a href="javascript:history.back()" class="btn btn-secondary mb-4">
        ← Change Vehicle
    </a>

    <div class="vehicle-details">

        <!-- IMAGE -->
        <div class="vehicle-image">
            <img id="vehicle-image" src="../img/<?php echo htmlspecialchars($vehicle['image'] ?: 'no-image.png'); ?>">
        </div>

        <!-- INFO -->
        <div class="vehicle-info">
            <h1><?php echo htmlspecialchars($vehicle['model_name']); ?></h1>
            <h3 id="vehicle-variant"><?php echo htmlspecialchars($vehicle['model_variant']); ?></h3>

            <div class="vehicle-price" id="vehicle-price">
                ₱<?php echo number_format($vehicle['price'], 2); ?>
            </div>

            <!-- ✅ ACTION BUTTONS -->
            <div class="d-flex gap-3 mt-4">
                <a id="testDriveBtn"
                   href="book_test_drive.php?id=<?php echo $vehicle['id']; ?>"
                   class="btn btn-danger btn-lg">
                   Book Test Drive
                </a>

                <a id="configureBtn"
                   href="configure.php?id=<?php echo $vehicle['id']; ?>"
                   class="btn btn-outline-dark btn-lg">
                   Configure
                </a>
            </div>

            <div class="features-title">Key Features</div>
            <ul id="vehicle-features">
                <?php 
                if (!empty($vehicle['features'])) {
                    foreach (explode("\n", $vehicle['features']) as $f) {
                        if (trim($f)) echo "<li>".htmlspecialchars($f)."</li>";
                    }
                } else {
                    echo "<li>No features listed</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- VARIANTS -->
    <h4 class="mt-5">Available Variants:</h4>
    <div class="variant-scroll-container">

    <?php while ($v = $variants_result->fetch_assoc()): ?>
        <div class="variant-card <?php echo ($v['id']==$vehicle_id?'variant-selected':''); ?>"
             data-id="<?php echo $v['id']; ?>"
             data-variant="<?php echo htmlspecialchars($v['model_variant']); ?>"
             data-price="<?php echo $v['price']; ?>"
             data-features="<?php echo htmlspecialchars($v['features'], ENT_QUOTES); ?>"
             data-image="../img/<?php echo htmlspecialchars($v['image'] ?: 'no-image.png'); ?>">

            <?php echo htmlspecialchars($v['model_variant']); ?>
        </div>
    <?php endwhile; ?>

    </div>

</div>

<script>
// Variant switching
document.querySelectorAll('.variant-card').forEach(card => {
    card.addEventListener('click', function() {

        // Highlight
        document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('variant-selected'));
        this.classList.add('variant-selected');

        // Update content
        document.getElementById('vehicle-image').src = this.dataset.image;
        document.getElementById('vehicle-variant').textContent = this.dataset.variant;

        document.getElementById('vehicle-price').textContent =
            "₱" + parseFloat(this.dataset.price).toLocaleString('en-PH', {minimumFractionDigits: 2});

        // Features
        let features = this.dataset.features
            ? this.dataset.features.split("\n").map(f => "<li>"+f+"</li>").join("")
            : "<li>No features listed</li>";

        document.getElementById('vehicle-features').innerHTML = features;

        // ✅ UPDATE BUTTON LINKS
        const id = this.dataset.id;
        document.getElementById('testDriveBtn').href = "book_test_drive.php?id=" + id;
        document.getElementById('configureBtn').href = "configure.php?id=" + id;

        // Spotlight effect
        const imgBox = document.querySelector('.vehicle-image');
        imgBox.classList.add('spotlight');
        setTimeout(() => imgBox.classList.remove('spotlight'), 1000);
    });
});
</script>

</body>
</html>