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

    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_special = isset($_POST['is_special']) ? 1 : 0;

    $image_file = null;

    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = time().'_'.basename($_FILES['image_file']['name']);
        move_uploaded_file($_FILES['image_file']['tmp_name'], $target_dir.$image_file);
    }

    // ---------------- UPDATE ----------------
    if($id > 0){

        if($image_file){
            $sql = "UPDATE vehicles 
                    SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=?, image=?, is_new=?, is_special=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssdssiii",
                $model_name, $model_variant, $vehicle_type,
                $price, $features, $image_file,
                $is_new, $is_special, $id
            );

        } else {
            $sql = "UPDATE vehicles 
                    SET model_name=?, model_variant=?, vehicle_type=?, price=?, features=?, is_new=?, is_special=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssdssii",
                $model_name, $model_variant, $vehicle_type,
                $price, $features,
                $is_new, $is_special, $id
            );
        }

    }

    // ---------------- INSERT ----------------
    else {

        $sql = "INSERT INTO vehicles 
        (model_name, model_variant, vehicle_type, price, features, image, is_new, is_special, created_at)
        VALUES (?,?,?,?,?,?,?,?,NOW())";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssdssii",
            $model_name,
            $model_variant,
            $vehicle_type,
            $price,
            $features,
            $image_file,
            $is_new,
            $is_special
        );
    }

    if($stmt->execute()){
    $_SESSION['success'] = $id > 0 ? "Vehicle updated successfully!" : "Vehicle added successfully!";
    header("Location: admin_vehicles.php");
    exit();

    } else {
        echo "<script>alert('Error: ".$stmt->error."');</script>";
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

<style>
.badge-new{
    background:red;
    color:white;
    padding:3px 8px;
    font-size:11px;
    border-radius:10px;
    margin-left:5px;
}
.badge-special{
    background:orange;
    color:white;
    padding:3px 8px;
    font-size:11px;
    border-radius:10px;
    margin-left:5px;
}
</style>

</head>

<body>

<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="admin_vehicles.php" class="active"><i class="fas fa-car"></i> Manage Vehicles</a>
    <a href="admin_posts.php"><i class="fas fa-newspaper"></i> Posts</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
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
data-isnew="<?= $v['is_new'] ?>"
data-isspecial="<?= $v['is_special'] ?>"
>

<td><?= $v['id'] ?></td>

<td>
<?= $v['model_name'] ?>
<?php if($v['is_new']): ?><span class="badge-new">NEW</span><?php endif; ?>
<?php if($v['is_special']): ?><span class="badge-special">PROMO</span><?php endif; ?>
</td>

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
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to delete this vehicle?');">
   Delete
</a>
<button class="btn btn-warning btn-sm edit-btn">Edit</button>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

<!-- MODAL -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST" enctype="multipart/form-data">
<div class="modal-body">

<input type="hidden" name="vehicle_id" id="id">

<input type="text" name="model_name" id="name" class="form-control mb-2">
<input type="text" name="model_variant" id="variant" class="form-control mb-2">

<select name="vehicle_type" id="type" class="form-select mb-2">
<option value="passenger">Passenger</option>
<option value="commercial">Commercial</option>
</select>

<input type="number" name="price" id="price" class="form-control mb-2">

<textarea name="features" id="features" class="form-control mb-2"></textarea>

<label><input type="checkbox" name="is_new" id="is_new"> New Arrival</label><br>
<label><input type="checkbox" name="is_special" id="is_special"> Special Offer</label>

<input type="file" name="image_file" class="form-control mt-2" onchange="previewImage(event)">

<img id="image-preview" style="display:none; width:100%; margin-top:10px;">

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
function openModal(id='',name='',variant='',type='',price='',features='',image='',isnew=0,isspecial=0){

    document.getElementById('id').value=id;
    document.getElementById('name').value=name;
    document.getElementById('variant').value=variant;
    document.getElementById('type').value=type;
    document.getElementById('price').value=price;
    document.getElementById('features').value=features;

    document.getElementById('is_new').checked = (isnew == 1);
    document.getElementById('is_special').checked = (isspecial == 1);

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
            tr.dataset.image,
            tr.dataset.isnew,
            tr.dataset.isspecial
        );
    }
});

function previewImage(event){
    let img=document.getElementById('image-preview');
    img.src=URL.createObjectURL(event.target.files[0]);
    img.style.display='block';
}
</script>

</body>
</html>