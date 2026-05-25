<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../db.php';

$allVehicles = $conn->query("SELECT id, model_name, model_variant FROM vehicles ORDER BY model_name ASC");

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compare Vehicles – CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<link rel="stylesheet" href="/citimotorsweb/web/tools/compare.css">
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="compare-page">

<main class="main">

  <div class="page-hero">
    <div>
      <div class="hero-eyebrow">Vehicle Comparison Tool</div>
      <div class="hero-title">
        Compare.<br>
        <em>Decide.</em><br>
        Drive.
      </div>
    </div>

    <div class="hero-desc">
      Select two vehicles from the dropdowns below to see a full spec-by-spec breakdown and find the right match for you.
    </div>
  </div>

  <!-- SELECTOR -->
  <form id="compareForm" method="GET">

    <div class="selector-row">

      <div class="selector-panel">
        <div class="sel-label">
          <span>01</span> — First Vehicle
        </div>

        <select name="id1" onchange="document.getElementById('compareForm').submit()">
          <option value="">Select a vehicle…</option>

          <?php $allVehicles->data_seek(0); while($v = $allVehicles->fetch_assoc()): ?>

            <option
              value="<?= $v['id']; ?>"
              <?= $v['id'] == $id1 ? 'selected' : ''; ?>
              <?= ($id2 && $v['id'] == $id2) ? 'disabled' : ''; ?>>

              <?= htmlspecialchars($v['model_name'].' - '.$v['model_variant']); ?>

            </option>

          <?php endwhile; ?>
        </select>
      </div>

      <div class="vs-center">VS</div>

      <div class="selector-panel">
        <div class="sel-label">
          <span>02</span> — Second Vehicle
        </div>

        <select name="id2" onchange="document.getElementById('compareForm').submit()">
          <option value="">Select a vehicle…</option>

          <?php $allVehicles->data_seek(0); while($v = $allVehicles->fetch_assoc()): ?>

            <option
              value="<?= $v['id']; ?>"
              <?= $v['id'] == $id2 ? 'selected' : ''; ?>
              <?= ($id1 && $v['id'] == $id1) ? 'disabled' : ''; ?>>

              <?= htmlspecialchars($v['model_name'].' - '.$v['model_variant']); ?>

            </option>

          <?php endwhile; ?>
        </select>
      </div>

    </div>

  </form>

  <?php if($id1 && $id2): ?>

    <?php
      $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id IN (?, ?)");
      $stmt->bind_param("ii", $id1, $id2);
      $stmt->execute();

      $result = $stmt->get_result();

      $vehicles = [];

      while($row = $result->fetch_assoc()){
        $vehicles[$row['id']] = $row;
      }

      if(count($vehicles) < 2):
    ?>

      <div class="empty-state">
        <i class="bi bi-exclamation-circle"></i>
        <p>Could not load both vehicles. Please try again.</p>
      </div>

    <?php else:

      $v1 = $vehicles[$id1];
      $v2 = $vehicles[$id2];

      $f1 = array_filter(array_map('trim', explode("\n", $v1['features'])));
      $f2 = array_filter(array_map('trim', explode("\n", $v2['features'])));

      $bestValueId = ($v1['price'] <= $v2['price']) ? $id1 : $id2;

      $savings = abs($v1['price'] - $v2['price']);
    ?>

    <!-- HERO CARDS -->
    <div class="vehicle-heroes">

      <div class="vehicle-card <?= $bestValueId == $id1 ? 'winner' : 'plain'; ?>">

        <?php if($bestValueId == $id1): ?>
          <div class="winner-badge">
            <i class="bi bi-trophy-fill"></i>
            Best Value
          </div>
        <?php endif; ?>

        <div class="card-num">Vehicle 01</div>

        <div class="card-name">
          <?= htmlspecialchars($v1['model_name']); ?>
        </div>

        <div class="card-variant">
          <?= htmlspecialchars($v1['model_variant']); ?>
        </div>

        <div class="card-img">
          <img src="../img/<?= htmlspecialchars($v1['image']); ?>">
        </div>

        <div class="price-lbl">Starting Price</div>

        <div class="price-row">

          <div class="price-tag">
            ₱<?= number_format($v1['price'],2); ?>
          </div>

          <?php if($bestValueId == $id1 && $savings > 0): ?>

            <div class="savings-pill">
              Save ₱<?= number_format($savings,0); ?>
            </div>

          <?php endif; ?>

        </div>

      </div>

      <div class="vehicle-card <?= $bestValueId == $id2 ? 'winner' : 'plain'; ?>">

        <?php if($bestValueId == $id2): ?>
          <div class="winner-badge">
            <i class="bi bi-trophy-fill"></i>
            Best Value
          </div>
        <?php endif; ?>

        <div class="card-num">Vehicle 02</div>

        <div class="card-name">
          <?= htmlspecialchars($v2['model_name']); ?>
        </div>

        <div class="card-variant">
          <?= htmlspecialchars($v2['model_variant']); ?>
        </div>

        <div class="card-img">
          <img src="../img/<?= htmlspecialchars($v2['image']); ?>">
        </div>

        <div class="price-lbl">Starting Price</div>

        <div class="price-row">

          <div class="price-tag">
            ₱<?= number_format($v2['price'],2); ?>
          </div>

          <?php if($bestValueId == $id2 && $savings > 0): ?>

            <div class="savings-pill">
              Save ₱<?= number_format($savings,0); ?>
            </div>

          <?php endif; ?>

        </div>

      </div>

    </div>

    <!-- HEADER -->
    <div class="spec-block">

      <div class="spec-header">

        <div class="spec-header-cell">
          <div class="red-bar"></div>
          <?= htmlspecialchars($v1['model_name']); ?>
        </div>

        <div class="spec-header-cell">
          <div class="red-bar"></div>
          <?= htmlspecialchars($v2['model_name']); ?>
        </div>

      </div>

    </div>

    <!-- FEATURES -->
    <div class="features-block">

      <div class="features-col">

        <div class="feat-hd">
          <div class="acc"></div>
          <?= htmlspecialchars($v1['model_name']); ?> — Features
        </div>

        <?php foreach($f1 as $feat): ?>

          <div class="feat-item">
            <i class="bi bi-check2"></i>
            <span><?= htmlspecialchars($feat); ?></span>
          </div>

        <?php endforeach; ?>

      </div>

      <div class="features-col">

        <div class="feat-hd">
          <div class="acc"></div>
          <?= htmlspecialchars($v2['model_name']); ?> — Features
        </div>

        <?php foreach($f2 as $feat): ?>

          <div class="feat-item">
            <i class="bi bi-check2"></i>
            <span><?= htmlspecialchars($feat); ?></span>
          </div>

        <?php endforeach; ?>

      </div>

    </div>

    <!-- CTA -->
    <div class="cta-bar">

      <a href="inquiry.php?vehicle_id=<?= $id1; ?>" class="cta-btn ghost">
        <i class="bi bi-calendar-check"></i>
        Inquire — <?= htmlspecialchars($v1['model_name']); ?>
      </a>

      <a href="inquiry.php?vehicle_id=<?= $id2; ?>" class="cta-btn solid">
        <i class="bi bi-calendar-check"></i>
        Inquire — <?= htmlspecialchars($v2['model_name']); ?>
      </a>

    </div>

    <?php endif; ?>

  <?php else: ?>

    <div class="empty-state">
      <i class="bi bi-arrow-left-right"></i>
      <p>Select two vehicles above to begin comparing</p>
    </div>

  <?php endif; ?>

</main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>