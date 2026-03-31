<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// ---------------------
// HANDLE ADD/UPDATE VEHICLE
// ---------------------
if(isset($_POST['save_vehicle'])){
    $id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
    $model_name = $conn->real_escape_string($_POST['model_name']);
    $model_variant = $conn->real_escape_string($_POST['model_variant']);
    $vehicle_type = $conn->real_escape_string($_POST['vehicle_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $features = $conn->real_escape_string($_POST['features']);

    $image_file = null;
    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = basename($_FILES['image_file']['name']);
        $target_file = $target_dir . $image_file;
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if(!in_array($ext, $allowed)){
            $image_file = null;
            echo "<script>alert('Invalid image type.');</script>";
        } elseif(!move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)){
            $image_file = null;
            echo "<script>alert('Error uploading image.');</script>";
        }
    }

    if($id > 0){
        // Update vehicle
        $sql = $image_file ?
            "UPDATE vehicles SET model_name='$model_name', model_variant='$model_variant', vehicle_type='$vehicle_type', description='$description', price='$price', features='$features', image='$image_file' WHERE id=$id" :
            "UPDATE vehicles SET model_name='$model_name', model_variant='$model_variant', vehicle_type='$vehicle_type', description='$description', price='$price', features='$features' WHERE id=$id";
    } else {
        // Add new vehicle
        $sql = "INSERT INTO vehicles (model_name, model_variant, vehicle_type, description, price, features, image)
                VALUES ('$model_name','$model_variant','$vehicle_type','$description','$price','$features','$image_file')";
    }

    if($conn->query($sql)){
        header("Location: vehicles.php");
        exit();
    } else {
        echo "<script>alert('Database error: ".$conn->error."');</script>";
    }
}

// ---------------------
// DELETE VEHICLE
// ---------------------
if(isset($_GET['delete_vehicle'])){
    $id = intval($_GET['delete_vehicle']);
    $conn->query("DELETE FROM vehicles WHERE id=$id");
    header("Location: vehicles.php");
    exit();
}

// ---------------------
// FETCH VEHICLES
// ---------------------
$result_vehicles = $conn->query("SELECT * FROM vehicles ORDER BY id DESC");
$total_vehicles = $result_vehicles->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicles - Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f8f9fa; }
.sidebar { height:100vh;width:240px;position:fixed;top:0;left:0;background:#dc3545;color:#fff;padding:20px; }
.sidebar h4{text-align:center;margin-bottom:15px;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:10px;margin-bottom:5px;border-radius:6px;}
.sidebar a:hover{background:#c82333;}
.content{margin-left:260px;padding:20px;}
.table th,.table td{vertical-align:middle;}
.form-control,.btn{border-radius:6px;}
.product-img{max-width:80px; max-height:60px; object-fit:contain;}
</style>
</head>
<body>
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="users.php">Users</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="content">
    <h2>Vehicle Management</h2>
    <button class="btn btn-success mb-3" onclick="openEditModal();">+ Add Vehicle</button>
    <p>Total Vehicles: <?php echo $total_vehicles; ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-success">
                <tr>
                    <th>#</th><th>Name</th><th>Variant</th><th>Type</th><th>Description</th>
                    <th>Price</th><th>Features</th><th>Image</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total_vehicles>0): while($v=$result_vehicles->fetch_assoc()): ?>
                <tr 
                    data-id="<?php echo $v['id']; ?>"
                    data-name="<?php echo htmlspecialchars($v['model_name'], ENT_QUOTES); ?>"
                    data-variant="<?php echo htmlspecialchars($v['model_variant'], ENT_QUOTES); ?>"
                    data-type="<?php echo htmlspecialchars($v['vehicle_type'], ENT_QUOTES); ?>"
                    data-desc="<?php echo htmlspecialchars($v['description'], ENT_QUOTES); ?>"
                    data-price="<?php echo $v['price']; ?>"
                    data-features="<?php echo htmlspecialchars($v['features'], ENT_QUOTES); ?>"
                    data-image="<?php echo htmlspecialchars($v['image'], ENT_QUOTES); ?>"
                >
                    <td><?php echo $v['id']; ?></td>
                    <td><?php echo htmlspecialchars($v['model_name']); ?></td>
                    <td><?php echo htmlspecialchars($v['model_variant']); ?></td>
                    <td><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($v['description']); ?></td>
                    <td>₱<?php echo number_format($v['price'],2); ?></td>
                    <td><?php echo htmlspecialchars($v['features']); ?></td>
                    <td><?php echo $v['image'] ? '<img src="../img/'.$v['image'].'" class="product-img">' : 'N/A'; ?></td>
                    <td>
                        <a href="vehicles.php?delete_vehicle=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this vehicle?')">Delete</a>
                        <button class="btn btn-warning btn-sm edit-btn">Edit</button>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="9" class="text-center">No vehicles found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="vehicle_id" id="modal_vehicle_id">

            <label>Model Name</label>
            <input type="text" name="model_name" id="modal_model_name" class="form-control mb-2" placeholder="Model Name (use existing to add variant)" required>

            <label>Variant</label>
            <input type="text" name="model_variant" id="modal_model_variant" class="form-control mb-2" placeholder="Variant (e.g., LX, EX)" required>

            <label>Type</label>
            <select name="vehicle_type" id="modal_vehicle_type" class="form-select mb-2" required>
                <option value="passenger">Passenger</option>
                <option value="commercial">Commercial</option>
            </select>

            <label>Description</label>
            <textarea name="description" id="modal_description" class="form-control mb-2" placeholder="Description" required></textarea>

            <label>Price</label>
            <input type="number" step="0.01" name="price" id="modal_price" class="form-control mb-2" placeholder="Price" required>

            <label>Features</label>
            <textarea name="features" id="modal_features" class="form-control mb-2" placeholder="Features"></textarea>

            <label>Current Image</label>
            <img id="modal_current_image" style="max-width:80px; max-height:60px; margin-bottom:5px; display:none;">
            <input type="file" name="image_file" class="form-control mb-2">
        </div>
        <div class="modal-footer">
          <button name="save_vehicle" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(id='',name='',variant='',type='',desc='',price='',features='',img=''){
    document.getElementById('modal_vehicle_id').value = id;
    document.getElementById('modal_model_name').value = name;
    document.getElementById('modal_model_variant').value = variant;
    document.getElementById('modal_description').value = desc;
    document.getElementById('modal_price').value = price;
    document.getElementById('modal_features').value = features;

    // Pre-select vehicle type
    const typeSelect = document.getElementById('modal_vehicle_type');
    typeSelect.value = type || 'passenger';

    // Image preview
    const imgEl = document.getElementById('modal_current_image');
    if(img){ 
        imgEl.src='../img/'+img; 
        imgEl.style.display='block'; 
    } else { 
        imgEl.style.display='none'; 
    }

    new bootstrap.Modal(document.getElementById('editVehicleModal')).show();
}

// EDIT BUTTONS USING DATA ATTRIBUTES
document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
        const tr = this.closest('tr');
        openEditModal(
            tr.dataset.id,
            tr.dataset.name,
            tr.dataset.variant,
            tr.dataset.type,
            tr.dataset.desc,
            tr.dataset.price,
            tr.dataset.features,
            tr.dataset.image
        );
    });
});
</script>
</body>
</html>