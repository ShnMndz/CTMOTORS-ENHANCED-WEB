<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// image folder
$imgPath = "../img/";

/* =========================
   DELETE POST
========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if (!empty($row['image']) && file_exists($imgPath . $row['image'])) {
        unlink($imgPath . $row['image']);
    }

    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin_posts.php");
    exit();
}

/* =========================
   EDIT FETCH
========================= */
$editData = null;

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* =========================
   INSERT / UPDATE
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'] ?? null;
    $title = htmlspecialchars(trim($_POST['title']));
    $content = htmlspecialchars(trim($_POST['content']));
    $link = htmlspecialchars(trim($_POST['link']));
    $image = $_POST['old_image'] ?? "";

    /* IMAGE UPLOAD */
    if (!empty($_FILES['image']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {

            $newImage = time() . "_" . basename($_FILES['image']['name']);
            $target = $imgPath . $newImage;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {

                // delete old image
                if (!empty($image) && file_exists($imgPath . $image)) {
                    unlink($imgPath . $image);
                }

                $image = $newImage;
            }
        }
    }

    if (!empty($id)) {
        $stmt = $conn->prepare("
            UPDATE posts 
            SET title=?, content=?, image=?, link=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $title, $content, $image, $link, $id);

    } else {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, content, image, link, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $title, $content, $image, $link);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Saved successfully!'); window.location='admin_posts.php';</script>";
    } else {
        echo "<script>alert('Error saving post');</script>";
    }
}

/* =========================
   FETCH POSTS
========================= */
$result = $conn->query("SELECT * FROM posts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Posts</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="dashboard.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_users.php"><i class="fas fa-users"></i>Manage Users</a>
    <a href="admin_vehicles.php"><i class="fas fa-car"></i>Manage Vehicles</a>
    <a href="admin_posts.php" class="active"><i class="fas fa-newspaper"></i>Posts(News/Articles)</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

<h2 class="mb-4">Articles</h2>

<!-- FORM -->
<div class="card p-3 mb-4 shadow-sm">
<h5><?= $editData ? "Edit Article" : "Create New Article" ?></h5>

<form method="POST" enctype="multipart/form-data">

<?php if ($editData): ?>
    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
<?php endif; ?>

<input type="text" name="title" class="form-control mb-2"
       value="<?= htmlspecialchars($editData['title'] ?? '') ?>"
       placeholder="Title" required>

<input type="url" name="link" class="form-control mb-2"
       value="<?= htmlspecialchars($editData['link'] ?? '') ?>"
       placeholder="External Link (optional)">

<input type="file" name="image" class="form-control mb-2">

<textarea name="content" class="form-control mb-2" rows="4" required><?= htmlspecialchars($editData['content'] ?? '') ?></textarea>

<button class="btn btn-danger w-100">
    <?= $editData ? "Update" : "Publish" ?>
</button>

</form>
</div>

<!-- TABLE -->
<table class="table table-hover table-bordered">

<thead>
<tr>
    <th>Image</th>
    <th>Title</th>
    <th>Link</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr>

<td>
<?php if (!empty($row['image'])): ?>
    <img src="<?= $imgPath . $row['image']; ?>" width="80">
<?php else: ?>
    No image
<?php endif; ?>
</td>

<td><?= htmlspecialchars($row['title']); ?></td>

<td>
<?php if (!empty($row['link'])): ?>
    <a href="<?= htmlspecialchars($row['link']); ?>" target="_blank">Open</a>
<?php else: ?>
    N/A
<?php endif; ?>
</td>

<td>
<?= !empty($row['created_at']) 
    ? date("F d, Y", strtotime($row['created_at'])) 
    : 'No date'; ?>
</td>

<td>
    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
       onclick="return confirm('Delete this article?')">Delete</a>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>