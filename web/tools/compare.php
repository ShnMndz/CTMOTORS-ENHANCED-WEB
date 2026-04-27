<?php
// ----------------------------
// Prevent caching
// ----------------------------
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// ----------------------------
// DB
// ----------------------------
include '../db.php';

// Fetch all vehicles
$allVehicles = $conn->query("SELECT id, model_name, model_variant FROM vehicles ORDER BY model_name ASC");

// IDs
$id1 = 0;
$id2 = 0;

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id1']) && isset($_GET['id2'])){
    $id1 = intval($_GET['id1']);
    $id2 = intval($_GET['id2']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compare Vehicles - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
body {
    background:#f8fafc;
    font-family:'Poppins', sans-serif;
}

.compare-container {
    max-width: 1200px;
    margin: 40px auto;
}

/* TABLE */
.compare-table {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.compare-table th,
.compare-table td {
    padding: 15px;
    text-align: center;
    vertical-align: top;
}

/* IMAGE */
.compare-img {
    width: 100%;
    max-width: 250px;
    height: 150px;
    object-fit: contain;
}

/* FEATURES */
.feature-list {
    text-align: left;
    padding-left: 10px;
}

/* FORM */
.selection-form {
    margin-bottom: 30px;
}

/* 🏆 BADGE */
.best-value-badge {
    display: inline-block;
    margin-bottom: 8px;
}

.best-value-badge img {
    width: 40px;
}

/* 💚 FULL COLUMN HIGHLIGHT */
.best-column {
    background: linear-gradient(180deg, rgba(34,197,94,0.12), rgba(34,197,94,0.04));
    box-shadow: inset 0 0 25px rgba(34,197,94,0.35);
    border-left: 3px solid #22c55e;
    border-right: 3px solid #22c55e;
    transition: 0.3s ease;
}

/* glow effect for images */
.best-column img {
    filter: drop-shadow(0 0 6px rgba(34,197,94,0.6));
}
</style>

<script>
function submitCompare() {
    document.getElementById('compareForm').submit();
}
</script>

</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container compare-container">

    <h2 class="text-center mb-4">Vehicle Comparison</h2>

    <!-- SELECTORS -->
    <form id="compareForm" method="GET" class="selection-form row g-3 justify-content-center mb-4">

        <div class="col-md-5">
            <select name="id1" class="form-select" onchange="submitCompare()">
                <option value="">Select first vehicle</option>
                <?php $allVehicles->data_seek(0); while($v = $allVehicles->fetch_assoc()): ?>
                    <option value="<?= $v['id']; ?>" <?= $v['id']==$id1?'selected':''; ?>
                        <?= ($id2 && $v['id']==$id2)?'disabled':''; ?>>
                        <?= htmlspecialchars($v['model_name'].' - '.$v['model_variant']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-5">
            <select name="id2" class="form-select" onchange="submitCompare()">
                <option value="">Select second vehicle</option>
                <?php $allVehicles->data_seek(0); while($v = $allVehicles->fetch_assoc()): ?>
                    <option value="<?= $v['id']; ?>" <?= $v['id']==$id2?'selected':''; ?>
                        <?= ($id1 && $v['id']==$id1)?'disabled':''; ?>>
                        <?= htmlspecialchars($v['model_name'].' - '.$v['model_variant']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

    </form>

<?php
if($id1 && $id2):

    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id IN (?, ?)");
    $stmt->bind_param("ii", $id1, $id2);
    $stmt->execute();
    $result = $stmt->get_result();

    $vehicles = [];
    while($row = $result->fetch_assoc()) $vehicles[$row['id']] = $row;

    if(count($vehicles)<2){
        echo "<div class='alert alert-danger'>Could not fetch both vehicles.</div>";
    } else {

        $v1 = $vehicles[$id1];
        $v2 = $vehicles[$id2];

        $f1 = array_map('trim', explode("\n", $v1['features']));
        $f2 = array_map('trim', explode("\n", $v2['features']));

        // BEST VALUE (lower price wins)
        $bestValueId = ($v1['price'] <= $v2['price']) ? $id1 : $id2;

        // COLUMN CLASSES
        $col1Class = ($bestValueId == $id1) ? 'best-column' : '';
        $col2Class = ($bestValueId == $id2) ? 'best-column' : '';
?>

<table class="table compare-table">

    <!-- HEADER -->
    <tr>
        <th>Feature</th>

        <th class="<?= $col1Class; ?>">
            <?php if($bestValueId == $id1): ?>
                <div class="best-value-badge">
                    <img src="https://cdn-icons-png.flaticon.com/512/2583/2583344.png">
                    <div><small class="text-success fw-bold">Best Value</small></div>
                </div>
            <?php endif; ?>
            <?= htmlspecialchars($v1['model_name']); ?>
        </th>

        <th class="<?= $col2Class; ?>">
            <?php if($bestValueId == $id2): ?>
                <div class="best-value-badge">
                    <img src="https://cdn-icons-png.flaticon.com/512/2583/2583344.png">
                    <div><small class="text-success fw-bold">Best Value</small></div>
                </div>
            <?php endif; ?>
            <?= htmlspecialchars($v2['model_name']); ?>
        </th>
    </tr>

    <!-- IMAGE -->
    <tr>
        <td>Image</td>
        <td class="<?= $col1Class; ?>">
            <img src="../img/<?= htmlspecialchars($v1['image']); ?>" class="compare-img">
        </td>
        <td class="<?= $col2Class; ?>">
            <img src="../img/<?= htmlspecialchars($v2['image']); ?>" class="compare-img">
        </td>
    </tr>

    <!-- VARIANT -->
    <tr>
        <td>Variant</td>
        <td class="<?= $col1Class; ?>">
            <?= htmlspecialchars($v1['model_variant']); ?>
        </td>
        <td class="<?= $col2Class; ?>">
            <?= htmlspecialchars($v2['model_variant']); ?>
        </td>
    </tr>

    <!-- PRICE -->
    <tr>
        <td>Price</td>
        <td class="<?= $col1Class; ?>">
            ₱<?= number_format($v1['price'],2); ?>
        </td>
        <td class="<?= $col2Class; ?>">
            ₱<?= number_format($v2['price'],2); ?>
        </td>
    </tr>

    <!-- FEATURES -->
    <tr>
        <td>Key Features</td>

        <td class="feature-list <?= $col1Class; ?>">
            <?php foreach($f1 as $feat): ?>
                <div><?= htmlspecialchars($feat); ?></div>
            <?php endforeach; ?>
        </td>

        <td class="feature-list <?= $col2Class; ?>">
            <?php foreach($f2 as $feat): ?>
                <div><?= htmlspecialchars($feat); ?></div>
            <?php endforeach; ?>
        </td>
    </tr>

</table>

<?php
    }
endif;
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>