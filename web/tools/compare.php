<?php
// ----------------------------
// Prevent caching (forces reset on refresh/back)
// ----------------------------
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// ----------------------------
// Include DB
// ----------------------------
include '../db.php';

// Fetch all vehicles for dropdowns
$allVehicles = $conn->query("SELECT id, model_name, model_variant FROM vehicles ORDER BY model_name ASC");

// On initial load, ignore GET parameters so page always resets
$id1 = 0;
$id2 = 0;

// Only update $id1/$id2 if the user just submitted the form
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
<style>
body { font-family: 'Poppins', sans-serif; background:#f8fafc; }
.compare-container { max-width: 1200px; margin: 40px auto; }
.compare-table { width: 100%; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.compare-table th, .compare-table td { padding: 15px; text-align: center; vertical-align: top; }
.compare-img { width: 100%; max-width: 250px; height: 150px; object-fit: contain; }
.feature-list { text-align: left; padding-left: 10px; }
.selection-form { margin-bottom: 30px; }
</style>
<script>
function submitCompare() {
    document.getElementById('compareForm').submit();
}
</script>
</head>
<body>
<div class="container compare-container">
<h2 class="text-center mb-4">Vehicle Comparison</h2>

<!-- Dropdowns -->
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
// Only show table if both vehicles are actively selected
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
?>

<table class="table compare-table">
    <tr>
        <th>Feature</th>
        <th><?= htmlspecialchars($v1['model_name']); ?></th>
        <th><?= htmlspecialchars($v2['model_name']); ?></th>
    </tr>

    <tr>
        <td>Image</td>
        <td><img src="../img/<?= htmlspecialchars($v1['image']); ?>" class="compare-img"></td>
        <td><img src="../img/<?= htmlspecialchars($v2['image']); ?>" class="compare-img"></td>
    </tr>

    <tr>
        <td>Variant</td>
        <td><?= htmlspecialchars($v1['model_variant']); ?></td>
        <td><?= htmlspecialchars($v2['model_variant']); ?></td>
    </tr>

    <tr>
        <td>Price</td>
        <td>₱<?= number_format($v1['price'],2); ?></td>
        <td>₱<?= number_format($v2['price'],2); ?></td>
    </tr>

    <tr>
        <td>Key Features</td>
        <td class="feature-list">
            <?php foreach($f1 as $feat): ?>
                <div><?= htmlspecialchars($feat); ?></div>
            <?php endforeach; ?>
        </td>
        <td class="feature-list">
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
</body>
</html>