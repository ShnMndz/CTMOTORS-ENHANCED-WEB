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

    $id = intval($_POST['vehicle_id'] ?? 0);
    $model_name = $_POST['model_name'];
    $model_variant = $_POST['model_variant'];
    $vehicle_type = $_POST['vehicle_type'];
    $price = floatval($_POST['price']);
    $features = $_POST['features'];

    $image_file = null;

    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = time().'_'.basename($_FILES['image_file']['name']);
        move_uploaded_file($_FILES['image_file']['tmp_name'], $target_dir.$image_file);
    }

    if($id > 0){

        if($image_file){
            $sql = "UPDATE vehicles 
                    SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=?, image=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssdssi",
                $model_name,
                $model_variant,
                $vehicle_type,
                $price,
                $features,
                $image_file,
                $id
            );

        } else {
            $sql = "UPDATE vehicles 
                    SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssdsi",
                $model_name,
                $model_variant,
                $vehicle_type,
                $price,
                $features,
                $id
            );
        }

    } else {

        $sql = "INSERT INTO vehicles 
        (model_name, model_variant, vehicle_type, price, features, image, created_at)
        VALUES (?,?,?,?,?,?,NOW())";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssdss",
            $model_name,
            $model_variant,
            $vehicle_type,
            $price,
            $features,
            $image_file
        );
    }

    if($stmt->execute()){
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
    header("Location: admin_vehicles.php");
    exit();
}

// ---------------------
// FETCH VEHICLES
// ---------------------
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$where = [];
$params = [];
$types = '';

if($search){
    $where[] = "model_name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if($filter && in_array($filter, ['passenger','commercial'])){
    $where[] = "vehicle_type=?";
    $params[] = $filter;
    $types .= 's';
}

$sql = "SELECT * FROM vehicles";

if($where){
    $sql .= " WHERE ".implode(' AND ', $where);
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if($params){
    $stmt->bind_param($types, ...$params);
}

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
<link href="admin_vehicles.css" rel="stylesheet">
<link href="admin_dashboard.css" rel="stylesheet">

<!-- ONLY ADDITION -->
<style>
.alert{
    transition: opacity 0.5s ease;
}
</style>

</head>

<body>

<div class="sidebar">
    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_profile.php"><i class="fas fa-user"></i>Your Profile</a>

    <a href="admin_users.php">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php" class="active">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php">
        <i class="fas fa-newspaper"></i>Posts
    </a>

        <a href="admin_test_drives.php">
        <i class="fas fa-key"></i>Test Drive

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
<div class="content">

<h2>Vehicles</h2>

<div class="mb-3 d-flex gap-2">

<form class="d-flex gap-2" method="GET">
<input type="text" name="search" class="form-control" placeholder="Search Model..." value="<?= htmlspecialchars($search) ?>">
<select name="filter" class="form-select">
<option value="">All Types</option>
<option value="passenger" <?= $filter=='passenger'?'selected':'' ?>>Passenger</option>
<option value="commercial" <?= $filter=='commercial'?'selected':'' ?>>Commercial</option>
</select>
<button class="btn btn-primary">Filter</button>
</form>

<button class="btn btn-success ms-auto" onclick="openModal()">+ Add Vehicle</button>

</div>

<!-- ONLY CHANGE HERE: added auto-fade class -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success auto-fade alert-dismissible fade show">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php unset($_SESSION['success']); endif; ?>

<table class="table table-bordered">

<thead class="table-success">
<tr>
<th>ID</th>
<th>Model</th>
<th>Variant</th>
<th>Type</th>
<th>Price</th>
<th>Features</th>
<th>Image</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($v=$result->fetch_assoc()): ?>

<tr
data-id="<?= $v['id'] ?>"
data-name="<?= htmlspecialchars($v['model_name'],ENT_QUOTES) ?>"
data-variant="<?= htmlspecialchars($v['model_variant'],ENT_QUOTES) ?>"
data-type="<?= $v['vehicle_type'] ?>"
data-price="<?= $v['price'] ?>"
data-features="<?= htmlspecialchars($v['features'],ENT_QUOTES) ?>"
data-image="<?= $v['image'] ?>"
>

<td><?= $v['id'] ?></td>
<td><?= $v['model_name'] ?></td>
<td><?= $v['model_variant'] ?></td>
<td><?= $v['vehicle_type'] ?></td>
<td>₱<?= number_format($v['price'],2) ?></td>
<td><?= nl2br(htmlspecialchars($v['features'])) ?></td>

<td>
<?php if($v['image']): ?>
<img src="../img/<?= $v['image'] ?>" class="product-img">
<?php endif; ?>
</td>

<td>
<a href="?delete_vehicle=<?= $v['id'] ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this vehicle?');"
title="Delete">
<i class="fas fa-trash"></i>
</a>

<button class="btn btn-sm btn-warning edit-btn" title="Edit">
<i class="fas fa-gear"></i>
</button>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

<!-- MODAL (UNCHANGED) -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST" enctype="multipart/form-data">
<div class="modal-body">

<input type="hidden" name="vehicle_id" id="id">

<label class="form-label">Model Name</label>
<input type="text" name="model_name" id="name" class="form-control mb-2">

<label class="form-label">Model Variant</label>
<input type="text" name="model_variant" id="variant" class="form-control mb-2">

<label class="form-label">Vehicle Type</label>
<select name="vehicle_type" id="type" class="form-select mb-2">
<option value="passenger">Passenger</option>
<option value="commercial">Commercial</option>
</select>

<label class="form-label">Price</label>
<input type="number" name="price" id="price" class="form-control mb-2">

<label class="form-label">Features</label>
<textarea name="features" id="features" class="form-control mb-2"></textarea>

<label class="form-label">Vehicle Image</label>
<input type="file" name="image_file" class="form-control" onchange="previewImage(event)">

<img id="image-preview" style="display:none;width:100%;margin-top:10px;">

</div>

<div class="modal-footer">
<button name="save_vehicle" class="btn btn-primary">Save</button>
</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// UNCHANGED JS
function openModal(id='',name='',variant='',type='',price='',features='',image=''){

    document.getElementById('id').value=id;
    document.getElementById('name').value=name;
    document.getElementById('variant').value=variant;
    document.getElementById('type').value=type;
    document.getElementById('price').value=price;
    document.getElementById('features').value=features;

    const img=document.getElementById('image-preview');
    if(image){
        img.src="../img/"+image;
        img.style.display='block';
    } else {
        img.style.display='none';
    }

    new bootstrap.Modal(document.getElementById('modal')).show();
}

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.onclick=function(){
        let tr=this.closest('tr');
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
    let img=document.getElementById('image-preview');
    img.src=URL.createObjectURL(event.target.files[0]);
    img.style.display='block';
}

// ONLY ADDITION: AUTO FADE ALERT
document.addEventListener("DOMContentLoaded", function () {
    const alerts = document.querySelectorAll(".auto-fade");

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});
</script>

</body>
</html>