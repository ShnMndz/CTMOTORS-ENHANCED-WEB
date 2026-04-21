<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$imgPath = "../img/";

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!empty($row['image']) && file_exists($imgPath . $row['image'])) {
        unlink($imgPath . $row['image']);
    }

    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin_posts.php");
    exit();
}

/* EDIT FETCH */
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* INSERT / UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'] ?? null;
    $title = htmlspecialchars(trim($_POST['title']));
    $content = htmlspecialchars(trim($_POST['content']));
    $link = htmlspecialchars(trim($_POST['link']));
    $category = $_POST['category'];
    $image = $_POST['old_image'] ?? "";

    if (!empty($_FILES['image']['name'])) {
        $newImage = time() . "_" . basename($_FILES['image']['name']);
        $target = $imgPath . $newImage;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            if (!empty($image) && file_exists($imgPath . $image)) {
                unlink($imgPath . $image);
            }
            $image = $newImage;
        }
    }

    if (!empty($id)) {
        $stmt = $conn->prepare("
            UPDATE posts 
            SET title=?, content=?, image=?, link=?, category=? 
            WHERE id=?
        ");
        $stmt->bind_param("sssssi", $title, $content, $image, $link, $category, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, content, image, link, category, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssss", $title, $content, $image, $link, $category);
    }

    echo $stmt->execute()
        ? "<script>alert('Saved!'); window.location='admin_posts.php';</script>"
        : "<script>alert('Error');</script>";
}

$result = $conn->query("SELECT * FROM posts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Posts</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="admin_dashboard.css">
</head>

<body>

<div class="sidebar">
    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_users.php">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php" class="active">
        <i class="fas fa-newspaper"></i>Posts
    </a>

        <a href="admin_test_drives.php">
        <i class="fas fa-key"></i>Test Drive

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>


<div class="content">

<h2>Articles</h2>

<!-- ✅ BUTTON -->
<button class="btn btn-dark mb-3" onclick="toggleForm()">+ New Article</button>

<!-- ✅ SEARCH + FILTER -->
<div class="d-flex gap-2 mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
    <select id="categoryFilter" class="form-control" style="max-width:200px;">
        <option value="">All</option>
        <option value="Car News">Car News</option>
        <option value="Tips and Guides">Tips and Guides</option>
        <option value="Announcement">Announcement</option>
    </select>
</div>

<!-- ✅ FORM (HIDDEN) -->
<div id="articleForm" style="<?= $editData ? 'display:block;' : 'display:none;' ?>" class="card p-3 mb-4">

<form method="POST" enctype="multipart/form-data">

<?php if ($editData): ?>
<input type="hidden" name="id" value="<?= $editData['id'] ?>">
<input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
<?php endif; ?>

<input type="text" name="title" class="form-control mb-2"
value="<?= htmlspecialchars($editData['title'] ?? '') ?>" placeholder="Title" required>

<input type="url" name="link" class="form-control mb-2"
value="<?= htmlspecialchars($editData['link'] ?? '') ?>" placeholder="Link">

<select name="category" class="form-control mb-2" required>
<option value="">Category</option>
<option value="Car News">Car News</option>
<option value="Tips and Guides">Tips and Guides</option>
<option value="Announcement">Announcement</option>
</select>

<input type="file" name="image" class="form-control mb-2">

<textarea name="content" class="form-control mb-2" rows="4" required><?= htmlspecialchars($editData['content'] ?? '') ?></textarea>

<button class="btn btn-danger w-100"><?= $editData ? "Update" : "Publish" ?></button>

</form>
</div>

<!-- ✅ TABLE -->
<table class="table table-bordered">

<thead>
<tr>
<th>Image</th>
<th>Title</th>
<th>Category</th>
<th>Link</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr class="article-row"
    data-title="<?= strtolower($row['title']); ?>"
    data-category="<?= $row['category']; ?>">

<td>
<?php if ($row['image']): ?>
<img src="<?= $imgPath . $row['image']; ?>" width="60">
<?php endif; ?>
</td>

<td><?= htmlspecialchars($row['title']); ?></td>
<td>
<?php
$cat = $row['category'];

$badgeClass = match($cat) {
    'Car News' => 'bg-primary',
    'Tips and Guides' => 'bg-success',
    'Announcement' => 'bg-warning text-dark',
    default => 'bg-secondary'
};
?>

<span class="badge <?= $badgeClass ?>">
    <?= htmlspecialchars($cat); ?>
</span>
</td>

<td>
<?= $row['link'] ? "<a href='{$row['link']}' target='_blank'>Open</a>" : "N/A"; ?>
</td>

<td><?= date("F d, Y", strtotime($row['created_at'])); ?></td>

<td>
<a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
<a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

<!-- ✅ SCRIPTS -->
<script>
function toggleForm() {
    let form = document.getElementById("articleForm");
    form.style.display = (form.style.display === "none") ? "block" : "none";
}

const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const rows = document.querySelectorAll(".article-row");

function filterTable() {
    let search = searchInput.value.toLowerCase();
    let category = categoryFilter.value;

    rows.forEach(row => {
        let title = row.dataset.title;
        let rowCategory = row.dataset.category;

        let matchSearch = title.includes(search);
        let matchCategory = category === "" || rowCategory === category;

        row.style.display = (matchSearch && matchCategory) ? "" : "none";
    });
}

searchInput.addEventListener("keyup", filterTable);
categoryFilter.addEventListener("change", filterTable);
</script>

</body>
</html>