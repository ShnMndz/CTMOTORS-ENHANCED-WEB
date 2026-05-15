<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'vehicles';

// ---------------------
// ADD / UPDATE VEHICLE
// ---------------------
if(isset($_POST['save_vehicle'])){

    $id            = intval($_POST['vehicle_id'] ?? 0);
    $model_name    = $_POST['model_name'];
    $model_variant = $_POST['model_variant'];
    $vehicle_type  = $_POST['vehicle_type'];
    $price         = floatval($_POST['price']);
    $features      = $_POST['features'];
    $image_file    = null;

    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = time().'_'.basename($_FILES['image_file']['name']);
        move_uploaded_file($_FILES['image_file']['tmp_name'], $target_dir.$image_file);
    }

    if($id > 0){
        if($image_file){
            $sql  = "UPDATE vehicles SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdssi", $model_name, $model_variant, $vehicle_type, $price, $features, $image_file, $id);
        } else {
            $sql  = "UPDATE vehicles SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdsi", $model_name, $model_variant, $vehicle_type, $price, $features, $id);
        }
    } else {
        $sql  = "INSERT INTO vehicles (model_name, model_variant, vehicle_type, price, features, image, created_at) VALUES (?,?,?,?,?,?,NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdss", $model_name, $model_variant, $vehicle_type, $price, $features, $image_file);
    }

    if($stmt->execute()){
        $action = $id > 0 ? 'Updated Vehicle' : 'Added Vehicle';
        logActivity($conn, $_SESSION['user'], $action, "Vehicle: $model_name $model_variant");
        $_SESSION['success'] = $id > 0 ? "Vehicle updated successfully!" : "Vehicle added successfully!";
        header("Location: admin_vehicles.php");
        exit();
    }
}

// ---------------------
// DELETE VEHICLE
// ---------------------
if(isset($_GET['delete_vehicle'])){
    $id = intval($_GET['delete_vehicle']);
    $conn->query("DELETE FROM vehicles WHERE id=$id");
    logActivity($conn, $_SESSION['user'], 'Deleted Vehicle', "Deleted vehicle ID: $id");
    header("Location: admin_vehicles.php");
    exit();
}

// ---------------------
// FETCH VEHICLES
// ---------------------
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$where  = [];
$params = [];
$types  = '';

if($search){
    $where[]  = "model_name LIKE ?";
    $params[] = "%$search%";
    $types   .= 's';
}

if($filter && in_array($filter, ['passenger','commercial'])){
    $where[]  = "vehicle_type=?";
    $params[] = $filter;
    $types   .= 's';
}

$sql = "SELECT * FROM vehicles";
if($where) $sql .= " WHERE ".implode(' AND ', $where);
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Vehicles</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link href="admin_vehicles.css" rel="stylesheet">
<link href="admin_dashboard.css" rel="stylesheet">

<style>
/* ── Mitsubishi theme — content area only ── */

.content {
    background: #080808;
    font-family: 'Inter', sans-serif;
    color: #ccc;
}

.mit-page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 6px;
}
.mit-diamond {
    width: 30px; height: 30px;
    background: #e8001c;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.mit-page-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #fff;
    margin: 0;
}
.mit-red-bar {
    height: 2px;
    background: #e8001c;
    margin: 12px 0 20px;
}

.mit-alert {
    padding: 10px 14px;
    font-size: 13px;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 14px;
    border-left: 3px solid #00c853;
    background: #0a1f0a;
    color: #00c853;
    transition: opacity .5s ease;
}

.mit-toolbar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 16px;
}
.mit-toolbar input[type="text"],
.mit-toolbar select {
    background: #111;
    border: 1px solid #222;
    color: #ccc;
    height: 36px;
    padding: 0 12px;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    outline: none;
    border-radius: 0;
}
.mit-toolbar input[type="text"] { flex: 1; min-width: 160px; }
.mit-toolbar input::placeholder  { color: #444; }
.mit-toolbar input:focus,
.mit-toolbar select:focus         { border-color: #e8001c; }
.mit-toolbar select option        { background: #111; }

.btn-mit {
    height: 36px;
    padding: 0 16px;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 7px 100%, 0 calc(100% - 7px));
    text-decoration: none;
    white-space: nowrap;
    transition: background .12s;
}
.btn-mit.primary       { background: #e8001c; color: #fff; }
.btn-mit.primary:hover { background: #ff1a33; color: #fff; }
.btn-mit.add           { background: #006b2b; color: #fff; margin-left: auto; }
.btn-mit.add:hover     { background: #00a040; color: #fff; }

.mit-table-wrap {
    border: 1px solid #1e1e1e;
    overflow-x: auto;
}

.vehicles-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 780px;
}
.vehicles-table thead tr { background: #111; }
.vehicles-table thead th {
    padding: 10px 12px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #555;
    border-bottom: 2px solid #e8001c;
    text-align: left;
    white-space: nowrap;
}
.vehicles-table tbody tr {
    border-bottom: 1px solid #161616;
    transition: background .1s;
}
.vehicles-table tbody tr:last-child { border-bottom: none; }
.vehicles-table tbody tr:hover { background: #111; }
.vehicles-table td {
    padding: 10px 12px;
    vertical-align: middle;
    color: #aaa;
}

.id-cell {
    font-family: 'Rajdhani', sans-serif;
    color: #444;
    font-size: 12px;
    letter-spacing: .05em;
}
.td-model   { font-weight: 500; color: #e0e0e0; white-space: nowrap; }
.td-variant { color: #666; font-size: 11px; white-space: nowrap; }
.td-price {
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #e0e0e0;
    white-space: nowrap;
}
.td-features {
    font-size: 11px;
    color: #555;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 9px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}
.type-badge::before {
    content: '';
    width: 5px; height: 5px;
    background: currentColor;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.type-passenger  { color: #40c4ff; border: 1px solid #003344; }
.type-commercial { color: #ffa000; border: 1px solid #3d2700; }

.mit-thumb {
    width: 60px; height: 42px;
    background: #161616;
    border: 1px solid #222;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.mit-thumb img  { width: 100%; height: 100%; object-fit: cover; }
.mit-thumb .fa-car { color: #333; font-size: 18px; }

.action-btn {
    width: 28px; height: 28px;
    background: transparent;
    border: 1px solid #222;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #555;
    font-size: 12px;
    text-decoration: none;
    transition: all .12s;
    clip-path: polygon(0 0, calc(100% - 5px) 0, 100% 5px, 100% 100%, 5px 100%, 0 calc(100% - 5px));
}
.action-btn:hover         { background: #1a1a1a; color: #e0e0e0; border-color: #444; }
.action-btn.danger:hover  { background: #1a0005; color: #e8001c; border-color: #3a0010; }
.action-btn.warning:hover { background: #1a1000; color: #ffa000; border-color: #3a2800; }

.mit-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #2a2a2a;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.mit-empty i { display: block; font-size: 28px; margin-bottom: .5rem; color: #1e1e1e; }

/* Modal */
.mit-modal .modal-content {
    background: #111;
    border: 1px solid #1e1e1e;
    border-top: 2px solid #e8001c;
    border-radius: 0;
}
.mit-modal .modal-header {
    background: #111;
    border-bottom: 1px solid #1e1e1e;
    padding: 14px 18px;
}
.mit-modal .modal-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #fff;
}
.mit-modal .btn-close { filter: invert(1) brightness(.5); }
.mit-modal .modal-body { background: #111; padding: 18px; }
.mit-modal .modal-footer {
    background: #111;
    border-top: 1px solid #1e1e1e;
    padding: 14px 18px;
}
.mit-modal .form-label {
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #555;
    margin-bottom: 5px;
}
.mit-modal .form-control,
.mit-modal .form-select {
    background: #0a0a0a;
    border: 1px solid #222;
    color: #ccc;
    border-radius: 0;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
}
.mit-modal .form-control:focus,
.mit-modal .form-select:focus {
    background: #0a0a0a;
    border-color: #e8001c;
    color: #fff;
    box-shadow: none;
}
.mit-modal .form-control::placeholder { color: #444; }
.mit-modal .form-select option { background: #111; }

.mit-preview {
    margin-top: 10px;
    border: 1px solid #222;
    width: 100%;
    display: none;
}

.btn-mit-cancel {
    height: 36px;
    padding: 0 16px;
    background: transparent;
    border: 1px solid #333;
    color: #888;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all .12s;
}
.btn-mit-cancel:hover { border-color: #555; color: #ccc; }

.btn-mit-submit {
    height: 36px;
    padding: 0 20px;
    background: #e8001c;
    color: #fff;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 7px 100%, 0 calc(100% - 7px));
    transition: background .12s;
}
.btn-mit-submit:hover { background: #ff1a33; }
</style>
</head>

<body>

<!-- SIDEBAR — untouched, styled by admin_dashboard.css -->
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_profile.php"><i class="fas fa-user"></i> Your Profile</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="admin_vehicles.php" class="active"><i class="fas fa-car"></i> Manage Vehicles</a>
    <a href="admin_posts.php"><i class="fas fa-newspaper"></i> Posts</a>
    <a href="recent_activity.php"><i class="fas fa-history"></i> Recent Activity</a>
    <a href="admin_test_drives.php"><i class="fas fa-key"></i> Test Drive</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT — Mitsubishi theme -->
<div class="content">

    <div class="mit-page-header">
        <div class="mit-diamond"></div>
        <h2 class="mit-page-title">Manage Vehicles</h2>
    </div>
    <div class="mit-red-bar"></div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="mit-alert auto-fade"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="GET">
    <div class="mit-toolbar">
        <input type="text" name="search" placeholder="Search model…" value="<?= htmlspecialchars($search) ?>">
        <select name="filter">
            <option value="">All Types</option>
            <option value="passenger"  <?= $filter=='passenger'  ?'selected':'' ?>>Passenger</option>
            <option value="commercial" <?= $filter=='commercial' ?'selected':'' ?>>Commercial</option>
        </select>
        <button type="submit" class="btn-mit primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <button type="button" class="btn-mit add" onclick="openModal()">
            <i class="fas fa-plus"></i> Add Vehicle
        </button>
    </div>
    </form>

    <div class="mit-table-wrap">
    <table class="vehicles-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Model</th>
            <th>Variant</th>
            <th>Type</th>
            <th>Price</th>
            <th>Features</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $hasRows = false;
    while($v = $result->fetch_assoc()):
        $hasRows   = true;
        $typeClass = $v['vehicle_type'] === 'passenger' ? 'type-passenger' : 'type-commercial';
    ?>
    <tr
        data-id="<?= $v['id'] ?>"
        data-name="<?= htmlspecialchars($v['model_name'], ENT_QUOTES) ?>"
        data-variant="<?= htmlspecialchars($v['model_variant'], ENT_QUOTES) ?>"
        data-type="<?= $v['vehicle_type'] ?>"
        data-price="<?= $v['price'] ?>"
        data-features="<?= htmlspecialchars($v['features'], ENT_QUOTES) ?>"
        data-image="<?= $v['image'] ?>"
    >
        <td class="id-cell">#<?= $v['id'] ?></td>
        <td class="td-model"><?= htmlspecialchars($v['model_name']) ?></td>
        <td class="td-variant"><?= htmlspecialchars($v['model_variant']) ?></td>
        <td><span class="type-badge <?= $typeClass ?>"><?= ucfirst($v['vehicle_type']) ?></span></td>
        <td class="td-price">&#8369;<?= number_format($v['price'], 2) ?></td>
        <td>
            <div class="td-features" title="<?= htmlspecialchars($v['features']) ?>">
                <?= htmlspecialchars($v['features']) ?>
            </div>
        </td>
        <td>
            <div class="mit-thumb">
                <?php if($v['image']): ?>
                    <img src="../img/<?= $v['image'] ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-car"></i>
                <?php endif; ?>
            </div>
        </td>
        <td>
            <div class="d-flex gap-1">
                <button class="action-btn warning edit-btn" title="Edit">
                    <i class="fas fa-pen"></i>
                </button>
                <a href="?delete_vehicle=<?= $v['id'] ?>"
                   class="action-btn danger"
                   title="Delete"
                   onclick="return confirm('Delete this vehicle?');">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </td>
    </tr>
    <?php endwhile; ?>

    <?php if(!$hasRows): ?>
    <tr>
        <td colspan="8">
            <div class="mit-empty">
                <i class="fas fa-car"></i>
                No vehicles found
            </div>
        </td>
    </tr>
    <?php endif; ?>

    </tbody>
    </table>
    </div>

</div><!-- /.content -->

<!-- MODAL -->
<div class="modal fade mit-modal" id="modal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" enctype="multipart/form-data">

    <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <input type="hidden" name="vehicle_id" id="id">

        <label class="form-label">Model Name</label>
        <input type="text" name="model_name" id="name" class="form-control mb-2" placeholder="e.g. Outlander">

        <label class="form-label">Model Variant</label>
        <input type="text" name="model_variant" id="variant" class="form-control mb-2" placeholder="e.g. GLS Sport">

        <label class="form-label">Vehicle Type</label>
        <select name="vehicle_type" id="type" class="form-select mb-2">
            <option value="passenger">Passenger</option>
            <option value="commercial">Commercial</option>
        </select>

        <label class="form-label">Price (&#8369;)</label>
        <input type="number" name="price" id="price" class="form-control mb-2" placeholder="0.00">

        <label class="form-label">Features</label>
        <textarea name="features" id="features" class="form-control mb-2" rows="3" placeholder="List key features…"></textarea>

        <label class="form-label">Vehicle Image</label>
        <input type="file" name="image_file" class="form-control" onchange="previewImage(event)">
        <img id="image-preview" class="mit-preview">
    </div>

    <div class="modal-footer">
        <button type="button" class="btn-mit-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="save_vehicle" class="btn-mit-submit">Save Vehicle</button>
    </div>

</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id='',name='',variant='',type='',price='',features='',image=''){
    document.getElementById('id').value       = id;
    document.getElementById('name').value     = name;
    document.getElementById('variant').value  = variant;
    document.getElementById('type').value     = type;
    document.getElementById('price').value    = price;
    document.getElementById('features').value = features;
    document.getElementById('modalTitle').textContent = id ? 'Edit Vehicle' : 'Add Vehicle';

    const img = document.getElementById('image-preview');
    if(image){
        img.src           = "../img/" + image;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('modal')).show();
}

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = function(){
        let tr = this.closest('tr');
        openModal(
            tr.dataset.id,
            tr.dataset.name,
            tr.dataset.variant,
            tr.dataset.type,
            tr.dataset.price,
            tr.dataset.features,
            tr.dataset.image
        );
    }
});

function previewImage(event){
    const img         = document.getElementById('image-preview');
    img.src           = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
}

document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll(".auto-fade").forEach(el => {
        setTimeout(() => {
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 500);
        }, 3000);
    });
});
</script>

</body>
</html>