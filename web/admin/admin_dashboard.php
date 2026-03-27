<?php
session_start();
include '../db.php';

// Protect page: only admins
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Update user role
if(isset($_POST['update_role'])){
    $id = intval($_POST['id']);
    $role = $conn->real_escape_string($_POST['role']);
    $conn->query("UPDATE users SET role='$role' WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// Delete user
if(isset($_GET['delete_user'])){
    $id = intval($_GET['delete_user']);
    if($id == $_SESSION['user_id']){
        echo "<script>alert('You cannot delete your own account');</script>";
    } else {
        $conn->query("DELETE FROM users WHERE id=$id");
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Delete vehicle
if(isset($_GET['delete_vehicle'])){
    $id = intval($_GET['delete_vehicle']);
    $conn->query("DELETE FROM vehicles WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// Handle Add Vehicle form
if(isset($_POST['add_vehicle'])){
    $model_name = $conn->real_escape_string($_POST['model_name']);
    $model_variant = $conn->real_escape_string($_POST['model_variant']);
    $model_type = $conn->real_escape_string($_POST['model_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $features = $conn->real_escape_string($_POST['features']);

    // Handle image upload
    $image_file = null;
    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = basename($_FILES['image_file']['name']);
        $target_file = $target_dir . $image_file;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg','jpeg','png','gif'];

        if(in_array($imageFileType, $allowed_types)){
            if(!move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)){
                $image_file = null;
                echo "<script>alert('Error uploading image.');</script>";
            }
        } else {
            $image_file = null;
            echo "<script>alert('Invalid image type. Only JPG, JPEG, PNG, GIF allowed.');</script>";
        }
    }

    $sql = "INSERT INTO vehicles (model_name, model_variant, model_type, description, price, features, image)
            VALUES ('$model_name','$model_variant','$model_type','$description','$price','$features','$image_file')";
    
    if($conn->query($sql)){
        echo "<script>alert('Vehicle added successfully'); window.location='admin_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Database error while adding vehicle.');</script>";
    }
}

// Handle Update Vehicle form
if(isset($_POST['update_vehicle'])){
    $id = intval($_POST['vehicle_id']);
    $model_name = $conn->real_escape_string($_POST['model_name']);
    $model_variant = $conn->real_escape_string($_POST['model_variant']);
    $model_type = $conn->real_escape_string($_POST['model_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $features = $conn->real_escape_string($_POST['features']);

    // Handle image upload
    $image_file = null;
    if(!empty($_FILES['image_file']['name'])){
        $target_dir = "../img/";
        $image_file = basename($_FILES['image_file']['name']);
        $target_file = $target_dir . $image_file;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg','jpeg','png','gif'];

        if(in_array($imageFileType, $allowed_types)){
            if(!move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)){
                $image_file = null;
                echo "<script>alert('Error uploading image.');</script>";
            }
        } else {
            $image_file = null;
            echo "<script>alert('Invalid image type.');</script>";
        }
    }

    if($image_file){
        $sql = "UPDATE vehicles SET model_name='$model_name', model_variant='$model_variant', model_type='$model_type', description='$description', price='$price', features='$features', image='$image_file' WHERE id=$id";
    } else {
        $sql = "UPDATE vehicles SET model_name='$model_name', model_variant='$model_variant', model_type='$model_type', description='$description', price='$price', features='$features' WHERE id=$id";
    }

    if($conn->query($sql)){
        echo "<script>alert('Vehicle updated successfully'); window.location='admin_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Database error while updating vehicle.');</script>";
    }
}

// Fetch users
$result_users = $conn->query("SELECT id, fullname, email, role, created_at FROM users ORDER BY id DESC");
$total_users = $result_users->num_rows;

// Fetch vehicles
$result_vehicles = $conn->query("SELECT * FROM vehicles ORDER BY id DESC");
$total_vehicles = $result_vehicles->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - CITI MOTORS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.sidebar { height: 100vh; width: 240px; position: fixed; top:0; left:0; background:#dc3545; color:#fff; padding:20px; }
.sidebar h4 { text-align:center; margin-bottom:15px; }
.sidebar a { display:block; color:#fff; text-decoration:none; padding:10px; margin-bottom:5px; border-radius:6px; }
.sidebar a:hover { background:#c82333; }
.content { margin-left:260px; padding:20px; }
.table th, .table td { vertical-align: middle; }
.form-control, .btn { border-radius:6px; }
.product-img { max-width:80px; max-height:60px; object-fit:contain; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="#" onclick="showSection('dashboard')">Dashboard</a>
    <a href="#" onclick="showSection('users')">Users</a>
    <a href="#" onclick="showSection('vehicles')">Vehicles</a>
    <a href="#" onclick="showSection('add_vehicle')">Add Vehicle</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <!-- Dashboard -->
    <div id="dashboardSection">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?> (Admin)</h2>
        <p>Total Users: <strong><?php echo $total_users; ?></strong></p>
        <p>Total Vehicles: <strong><?php echo $total_vehicles; ?></strong></p>
        <hr>
    </div>

    <!-- Users Section -->
    <div id="usersSection" style="display:none;">
        <h4>User List</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-danger">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_users > 0): while($user = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="form-select form-select-sm">
                                    <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                                    <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <button name="update_role" class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center">No users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vehicles Section -->
    <div id="vehiclesSection" style="display:none;">
        <h4>Vehicle List</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Model Name</th>
                        <th>Variant</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Features</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_vehicles > 0): while($v = $result_vehicles->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $v['id']; ?></td>
                        <td><?php echo htmlspecialchars($v['model_name']); ?></td>
                        <td><?php echo htmlspecialchars($v['model_variant']); ?></td>
                        <td><?php echo htmlspecialchars($v['model_type']); ?></td>
                        <td><?php echo htmlspecialchars($v['description']); ?></td>
                        <td>₱<?php echo number_format($v['price'],2); ?></td>
                        <td><?php echo htmlspecialchars($v['features']); ?></td>
                        <td>
                            <?php if(!empty($v['image'])): ?>
                                <img src="../img/<?php echo htmlspecialchars($v['image']); ?>" class="product-img" alt="Vehicle">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?delete_vehicle=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this vehicle?')">Delete</a>
                            <a href="#" class="btn btn-warning btn-sm" 
                               onclick="openEditModal(
                                   '<?php echo $v['id']; ?>', 
                                   '<?php echo htmlspecialchars($v['model_name'], ENT_QUOTES); ?>', 
                                   '<?php echo htmlspecialchars($v['model_variant'], ENT_QUOTES); ?>', 
                                   '<?php echo htmlspecialchars($v['model_type'], ENT_QUOTES); ?>', 
                                   '<?php echo htmlspecialchars($v['description'], ENT_QUOTES); ?>', 
                                   '<?php echo $v['price']; ?>', 
                                   '<?php echo htmlspecialchars($v['features'], ENT_QUOTES); ?>', 
                                   '<?php echo htmlspecialchars($v['image'], ENT_QUOTES); ?>'
                               )">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="9" class="text-center">No vehicles found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Vehicle Section -->
    <div id="add_vehicleSection" style="display:none;">
        <h4>Add Vehicle</h4>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="model_name" placeholder="Model Name" class="form-control mb-2" required>
            <input type="text" name="model_variant" placeholder="Model Variant" class="form-control mb-2" required>
            <input type="text" name="model_type" placeholder="Model Type" class="form-control mb-2" required>
            <textarea name="description" placeholder="Description" class="form-control mb-2" required></textarea>
            <input type="text" name="price" placeholder="Price" class="form-control mb-2" required>
            <textarea name="features" placeholder="Features (comma separated)" class="form-control mb-2" required></textarea>
            <input type="file" name="image_file" class="form-control mb-2">
            <button name="add_vehicle" class="btn btn-success w-100">Add Vehicle</button>
        </form>
    </div>

</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editVehicleLabel">Edit Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="vehicle_id" id="modal_vehicle_id">
            <div class="mb-2">
                <label>Model Name</label>
                <input type="text" name="model_name" id="modal_model_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Model Variant</label>
                <input type="text" name="model_variant" id="modal_model_variant" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Model Type</label>
                <input type="text" name="model_type" id="modal_model_type" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Description</label>
                <textarea name="description" id="modal_description" class="form-control" required></textarea>
            </div>
            <div class="mb-2">
                <label>Price</label>
                <input type="text" name="price" id="modal_price" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Features (comma separated)</label>
                <textarea name="features" id="modal_features" class="form-control" required></textarea>
            </div>
            <div class="mb-2">
                <label>Current Image</label>
                <img id="modal_current_image" src="" style="max-width:80px; max-height:60px; display:block; margin-bottom:5px;">
                <input type="file" name="image_file" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_vehicle" class="btn btn-primary">Update Vehicle</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showSection(section){
    const sections = ['dashboardSection','usersSection','vehiclesSection','add_vehicleSection'];
    sections.forEach(s => document.getElementById(s).style.display = 'none');

    if(section === 'dashboard') document.getElementById('dashboardSection').style.display = 'block';
    if(section === 'users') document.getElementById('usersSection').style.display = 'block';
    if(section === 'vehicles') document.getElementById('vehiclesSection').style.display = 'block';
    if(section === 'add_vehicle') document.getElementById('add_vehicleSection').style.display = 'block';
}

// Show dashboard by default
showSection('dashboard');

// Populate and show edit modal
function openEditModal(id, name, variant, type, description, price, features, image){
    document.getElementById('modal_vehicle_id').value = id;
    document.getElementById('modal_model_name').value = name;
    document.getElementById('modal_model_variant').value = variant;
    document.getElementById('modal_model_type').value = type;
    document.getElementById('modal_description').value = description;
    document.getElementById('modal_price').value = price;
    document.getElementById('modal_features').value = features;
    
    if(image){
        document.getElementById('modal_current_image').src = '../img/' + image;
        document.getElementById('modal_current_image').style.display = 'block';
    } else {
        document.getElementById('modal_current_image').style.display = 'none';
    }

    var myModal = new bootstrap.Modal(document.getElementById('editVehicleModal'), {});
    myModal.show();
}
</script>
</body>
</html>