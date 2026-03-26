<?php
session_start();
include '../db.php';

// Protect page: only admins
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle Add Vehicle form
if(isset($_POST['add_vehicle'])){
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $price = $conn->real_escape_string($_POST['price']);
    $link = $conn->real_escape_string($_POST['link']);

    // Handle file upload
    $target_dir = "../img"; // Folder for uploaded images
    $image_file = $_FILES['image_file']['name'];
    $target_file = $target_dir . basename($image_file);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allowed file types
    $allowed_types = ['jpg','jpeg','png','gif'];

    if(!in_array($imageFileType, $allowed_types)){
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
    } else {
        if(move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)){
            // Save to database
            $sql = "INSERT INTO vehicles (name,type,price,image,link)
                    VALUES ('$name','$type','$price','$image_file','$link')";
            if($conn->query($sql)){
                echo "<script>alert('Vehicle added successfully'); window.location='admin_dashboard.php';</script>";
                exit();
            } else {
                echo "<script>alert('Database error while adding vehicle.');</script>";
            }
        } else {
            echo "<script>alert('Error uploading image.');</script>";
        }
    }
}

// Fetch users and vehicles
$result_users = $conn->query("SELECT id, fullname, email, role, created_at FROM users ORDER BY id DESC");
$total_users = $result_users->num_rows;

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
<a href="../logout.php">Logout</a>

    <hr style="border-color: rgba(255,255,255,0.2);">

    <h5>Add Vehicle</h5>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Vehicle Name" class="form-control mb-2" required>
        <input type="text" name="type" placeholder="Type (commercial/passenger/truck)" class="form-control mb-2" required>
        <input type="text" name="price" placeholder="Price" class="form-control mb-2" required>
        <input type="file" name="image_file" class="form-control mb-2" required>
        <input type="text" name="link" placeholder="Detail Page Link" class="form-control mb-2" required>
        <button name="add_vehicle" class="btn btn-success w-100">Add Vehicle</button>
    </form>
</div>

<!-- Main Content -->
<div class="content">
    <div id="dashboardSection"></div>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?> (Admin)</h2>
    <p>Total Users: <strong><?php echo $total_users; ?></strong></p>
    <p>Total Vehicles: <strong><?php echo $total_vehicles; ?></strong></p>
    <hr>

   <div id="usersSection" style="display:none;">
    <h4>User List</h4>
    <div class="table-responsive mb-4">
        <table class="table table-striped table-hover">
            <thead class="table-danger">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php if($total_users > 0): while($user = $result_users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showSection(section){
    document.getElementById('dashboardSection').style.display = 'none';
    document.getElementById('usersSection').style.display = 'none';

    if(section === 'dashboard'){
        document.getElementById('dashboardSection').style.display = 'block';
    } else {
        document.getElementById('usersSection').style.display = 'block';
    }
}
</script>

    <!-- Vehicles Table -->
    <h4>Vehicle List</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Detail Link</th>
                </tr>
            </thead>
            <tbody>
                <?php if($total_vehicles > 0): while($v = $result_vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $v['id']; ?></td>
                    <td><?php echo htmlspecialchars($v['name']); ?></td>
                    <td><?php echo htmlspecialchars($v['type']); ?></td>
                    <td>₱<?php echo number_format($v['price']); ?></td>
                    <td><img src="../img/<?php echo htmlspecialchars($v['image']); ?>" class="product-img" alt="Vehicle"></td>
                    <td><a href="<?php echo htmlspecialchars($v['link']); ?>" target="_blank">View</a></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center">No vehicles found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>