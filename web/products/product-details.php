<?php
session_start();
include '../db.php';

$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle_result = $stmt->get_result();

if ($vehicle_result->num_rows === 0) {
    echo "Vehicle not found.";
    exit;
}

$vehicle = $vehicle_result->fetch_assoc();

$stmt2 = $conn->prepare("SELECT * FROM vehicles WHERE model_name = ? ORDER BY id ASC");
$stmt2->bind_param("s", $vehicle['model_name']);
$stmt2->execute();
$variants_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($vehicle['model_name']); ?> - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f4f6f9;
}

/* PAGE LAYOUT */
.product-wrapper{
    display:flex;
    gap:40px;
    align-items:flex-start;
}

/* IMAGE SECTION */
.image-box{
    flex:1;
    position:sticky;
    top:100px;
}

.image-box img{
    width:100%;
    border-radius:18px;
    transition:.4s ease;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.image-box.active img{
    transform:scale(1.03);
}

/* INFO SECTION */
.info-box{
    flex:1;
    background:#fff;
    padding:30px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
}

h1{
    font-size:38px;
    font-weight:700;
    margin-bottom:5px;
}

.variant-text{
    font-size:22px;
    font-weight:500;
    color:#555;
}

.price{
    font-size:34px;
    font-weight:700;
    color:#dc3545;
    margin:15px 0;
}

/* BUTTONS */
.actions{
    display:flex;
    gap:15px;
    margin-top:20px;
}

.actions a{
    flex:1;
    padding:12px;
    font-weight:600;
    border-radius:12px;
}

/* FEATURES */
.features{
    margin-top:25px;
}

.features li{
    list-style:none;
    background:#f1f3f6;
    padding:8px 12px;
    border-radius:10px;
    margin-bottom:8px;
    font-size:14px;
}

/* VARIANTS */
.variant-title{
    margin-top:40px;
    font-weight:600;
}

.variant-container{
    display:flex;
    gap:10px;
    overflow-x:auto;
    padding:10px 0;
}

.variant-card{
    min-width:140px;
    padding:12px;
    border-radius:12px;
    background:#fff;
    border:1px solid #ddd;
    cursor:pointer;
    text-align:center;
    transition:.2s;
}

.variant-card:hover{
    border-color:#dc3545;
    transform:translateY(-2px);
}

.variant-active{
    border:2px solid #dc3545;
    background:#fff5f5;
}

/* MOBILE */
@media(max-width:992px){
    .product-wrapper{
        flex-direction:column;
    }

    .image-box{
        position:relative;
        top:auto;
    }
}
</style>
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container my-5">

    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">
        ← Back
    </a>

    <div class="product-wrapper">

        <!-- IMAGE -->
        <div class="image-box">
            <img id="vehicle-image" src="../img/<?= htmlspecialchars($vehicle['image'] ?: 'no-image.png'); ?>">
        </div>

        <!-- INFO -->
        <div class="info-box">

            <h1><?= htmlspecialchars($vehicle['model_name']); ?></h1>
            <div class="variant-text" id="vehicle-variant">
                <?= htmlspecialchars($vehicle['model_variant']); ?>
            </div>

            <div class="price" id="vehicle-price">
                ₱<?= number_format($vehicle['price'], 2); ?>
            </div>

            <div class="actions">
                <a id="testDriveBtn"
                   href="../tools/testdrive.php?id=<?= $vehicle['id']; ?>"
                   class="btn btn-danger">
                   Book Test Drive
                </a>

                <a id="configureBtn"
                   href="configure.php?id=<?= $vehicle['id']; ?>"
                   class="btn btn-outline-dark">
                   Configure
                </a>
            </div>

            <div class="features">
                <h5 class="mt-4">Key Features</h5>
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

            <div class="variant-title">Available Variants</div>
            <div class="variant-container">

                <?php while ($v = $variants_result->fetch_assoc()): ?>
                    <div class="variant-card <?= ($v['id']==$vehicle_id?'variant-active':''); ?>"
                         data-id="<?= $v['id']; ?>"
                         data-variant="<?= htmlspecialchars($v['model_variant']); ?>"
                         data-price="<?= $v['price']; ?>"
                         data-features="<?= htmlspecialchars($v['features'], ENT_QUOTES); ?>"
                         data-image="../img/<?= htmlspecialchars($v['image'] ?: 'no-image.png'); ?>">

                        <?= htmlspecialchars($v['model_variant']); ?>
                    </div>
                <?php endwhile; ?>

            </div>

        </div>

    </div>
</div>

<script>
document.querySelectorAll('.variant-card').forEach(card => {
    card.addEventListener('click', function(){

        document.querySelectorAll('.variant-card')
            .forEach(c => c.classList.remove('variant-active'));

        this.classList.add('variant-active');

        document.getElementById('vehicle-image').src = this.dataset.image;
        document.getElementById('vehicle-variant').textContent = this.dataset.variant;

        document.getElementById('vehicle-price').textContent =
            "₱" + parseFloat(this.dataset.price)
            .toLocaleString('en-PH', {minimumFractionDigits:2});

        let features = this.dataset.features
            ? this.dataset.features.split("\n").map(f => "<li>"+f+"</li>").join("")
            : "<li>No features listed</li>";

        document.getElementById('vehicle-features').innerHTML = features;

        const id = this.dataset.id;
        document.getElementById('testDriveBtn').href = "../tools/testdrive.php?id=" + id;
        document.getElementById('configureBtn').href = "configure.php?id=" + id;

        const box = document.querySelector('.image-box');
        box.classList.add('active');
        setTimeout(() => box.classList.remove('active'), 500);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>