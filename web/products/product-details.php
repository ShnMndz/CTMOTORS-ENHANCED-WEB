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

// Fetch all variants
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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5f6f8;
}

/* HERO SECTION */
.hero-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 70px 0;
    gap: 40px;
}

/* LEFT */
.hero-text {
    flex: 1;
    max-width: 520px;
}

.hero-text h1 {
    font-size: 60px;
    font-weight: 800;
    margin-bottom: 10px;
}

.hero-sub {
    font-size: 16px;
    color: #555;
    margin-bottom: 10px;
}

.hero-specs {
    font-size: 14px;
    color: #777;
    line-height: 1.6;
}

/* PRICE */
.hero-price {
    font-size: 28px;
    font-weight: 700;
    margin: 20px 0;
}

/* BUTTONS */
.hero-buttons {
    display: flex;
    gap: 15px;
}

.btn-danger {
    background: #d60000;
    border: none;
    padding: 12px 28px;
    font-weight: 600;
    border-radius: 8px;
}

.btn-danger:hover {
    background: #b80000;
}

.btn-outline-dark {
    padding: 12px 28px;
    font-weight: 600;
    border-radius: 8px;
}

/* RIGHT IMAGE */
.hero-image {
    flex: 1;
    text-align: right;
}

.hero-image img {
    width: 100%;
    max-width: 650px;
    transition: 0.4s ease;
}

.hero-image img:hover {
    transform: scale(1.05);
}

/* VARIANTS */
.variant-scroll-container {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding: 20px 0;
}

.variant-card {
    cursor: pointer;
    border: 2px solid transparent;
    padding: 15px 20px;
    border-radius: 10px;
    background: #fff;
    min-width: 140px;
    text-align: center;
    font-weight: 600;
    transition: 0.3s ease;
}

.variant-card:hover {
    border-color: #d60000;
    transform: translateY(-5px);
}

.variant-selected {
    border-color: #d60000;
    background: #fff5f5;
}

/* RESPONSIVE */
@media(max-width: 992px) {
    .hero-section {
        flex-direction: column;
        text-align: center;
    }

    .hero-image {
        text-align: center;
    }

    .hero-text h1 {
        font-size: 40px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container">

    <!-- BACK BUTTON -->
    <a href="javascript:history.back()" class="btn btn-secondary mt-4">
        ← Change Vehicle
    </a>

    <!-- HERO -->
    <div class="hero-section">

        <!-- TEXT -->
        <div class="hero-text">
            <h1><?php echo htmlspecialchars($vehicle['model_name']); ?></h1>

            <p class="hero-sub" id="vehicle-variant">
                <?php echo htmlspecialchars($vehicle['model_variant']); ?>
            </p>

            <p class="hero-specs" id="vehicle-features">
                <?php echo nl2br(htmlspecialchars($vehicle['features'])); ?>
            </p>

            <div class="hero-price" id="vehicle-price">
                From ₱<?php echo number_format($vehicle['price'], 0); ?>
            </div>

            <div class="hero-buttons">
                <a id="testDriveBtn"
                   href="../tools/testdrive.php?id=<?php echo $vehicle['id']; ?>"
                   class="btn btn-danger">
                   Book Test Drive
                </a>

                <a id="configureBtn"
                   href="configure.php?id=<?php echo $vehicle['id']; ?>"
                   class="btn btn-outline-dark">
                   View Model
                </a>
            </div>
        </div>

        <!-- IMAGE -->
        <div class="hero-image">
            <img id="vehicle-image"
                 src="../img/<?php echo htmlspecialchars($vehicle['image'] ?: 'no-image.png'); ?>">
        </div>

    </div>

    <!-- VARIANTS -->
    <h5 class="mt-4">Available Variants</h5>

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

        document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('variant-selected'));
        this.classList.add('variant-selected');

        document.getElementById('vehicle-image').src = this.dataset.image;
        document.getElementById('vehicle-variant').textContent = this.dataset.variant;

        document.getElementById('vehicle-price').textContent =
            "From ₱" + parseFloat(this.dataset.price).toLocaleString('en-PH');

        let features = this.dataset.features
            ? this.dataset.features.replace(/\n/g, "<br>")
            : "No features listed";

        document.getElementById('vehicle-features').innerHTML = features;

        const id = this.dataset.id;
        document.getElementById('testDriveBtn').href = "../tools/testdrive.php?id=" + id;
        document.getElementById('configureBtn').href = "configure.php?id=" + id;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>