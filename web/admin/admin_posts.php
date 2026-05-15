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

    logActivity($conn, $_SESSION['user'], 'Deleted Post', "Deleted post ID: $id");

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

    $id      = $_POST['id'] ?? null;
    $title   = htmlspecialchars(trim($_POST['title']));
    $content = htmlspecialchars(trim($_POST['content']));
    $link    = htmlspecialchars(trim($_POST['link']));
    $category = $_POST['category'];

    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image       = $_POST['old_image'] ?? "";
    $posted_by   = $_SESSION['user'];

    if ($is_featured) {
        $conn->query("UPDATE posts SET is_featured = 0");
    }

    if (!empty($_FILES['image']['name'])) {
        $newImage = time() . "_" . basename($_FILES['image']['name']);
        $target   = $imgPath . $newImage;

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
            SET title=?, content=?, image=?, link=?, category=?, is_featured=?, posted_by=?
            WHERE id=?
        ");
        $stmt->bind_param("sssssisi", $title, $content, $image, $link, $category, $is_featured, $posted_by, $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, content, image, link, category, is_featured, posted_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssssis", $title, $content, $image, $link, $category, $is_featured, $posted_by);
    }

    if ($stmt->execute()) {
        $action = !empty($id) ? 'Updated Post' : 'Added Post';
        logActivity($conn, $_SESSION['user'], $action, "Post: $title");
        echo "<script>alert('Saved!'); window.location='admin_posts.php';</script>";
    } else {
        echo "<script>alert('Error saving post');</script>";
    }
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
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

<!-- Original dashboard CSS (sidebar styles live here — untouched) -->
<link rel="stylesheet" href="admin_dashboard.css">

<style>
/* ── Mitsubishi theme scoped to .content only ── */

.content {
    background: #080808;
    font-family: 'Inter', sans-serif;
    color: #ccc;
}

/* Page header */
.mit-page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 6px;
}
.mit-diamond {
    width: 30px;
    height: 30px;
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

/* New article button */
.btn-mit {
    height: 36px;
    padding: 0 18px;
    background: #e8001c;
    color: #fff;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
    text-decoration: none;
    margin-bottom: 16px;
}
.btn-mit:hover { background: #ff1a33; color: #fff; text-decoration: none; }

/* Toolbar */
.mit-toolbar {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    flex-wrap: wrap;
    align-items: center;
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
.mit-toolbar input[type="text"]::placeholder { color: #444; }
.mit-toolbar input[type="text"]:focus,
.mit-toolbar select:focus { border-color: #e8001c; }
.mit-toolbar select option { background: #111; }

/* Article form */
.mit-form-card {
    background: #111;
    border: 1px solid #1e1e1e;
    border-top: 2px solid #e8001c;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.mit-form-card .form-control,
.mit-form-card select.form-control {
    background: #0a0a0a;
    border: 1px solid #222;
    color: #ccc;
    border-radius: 0;
    font-size: 13px;
}
.mit-form-card .form-control:focus,
.mit-form-card select.form-control:focus {
    background: #0a0a0a;
    border-color: #e8001c;
    color: #fff;
    box-shadow: none;
}
.mit-form-card .form-control::placeholder { color: #444; }
.mit-form-card .form-check-label { color: #888; font-size: 13px; }

.btn-publish {
    width: 100%;
    height: 38px;
    background: #e8001c;
    color: #fff;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
}
.btn-publish:hover { background: #ff1a33; }

/* Table wrapper */
.mit-table-wrap {
    border: 1px solid #1e1e1e;
    overflow: hidden;
}

/* Table */
.posts-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    table-layout: fixed;
}
.posts-table thead tr { background: #111; }
.posts-table thead th {
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
    overflow: hidden;
}
.posts-table thead th:nth-child(1) { width: 60px; }
.posts-table thead th:nth-child(2) { width: 26%; }
.posts-table thead th:nth-child(3) { width: 22%; }
.posts-table thead th:nth-child(4) { width: 15%; }
.posts-table thead th:nth-child(5) { width: 8%;  }
.posts-table thead th:nth-child(6) { width: 12%; }
.posts-table thead th:nth-child(7) { width: 10%; }

.posts-table tbody tr {
    border-bottom: 1px solid #161616;
    transition: background .1s;
}
.posts-table tbody tr:last-child { border-bottom: none; }
.posts-table tbody tr:hover { background: #111; }
.posts-table td {
    padding: 10px 12px;
    vertical-align: middle;
    color: #aaa;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Thumbnail */
.mit-thumb {
    width: 44px; height: 32px;
    background: #161616;
    border: 1px solid #222;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.mit-thumb img { width: 100%; height: 100%; object-fit: cover; }
.mit-thumb .fa-image { color: #333; font-size: 14px; }

/* Post title */
.post-title {
    font-weight: 500;
    color: #e0e0e0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Category badges */
.cat-badge {
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
.cat-badge::before {
    content: '';
    width: 5px; height: 5px;
    background: currentColor;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.cat-news         { color: #e8001c; border: 1px solid #3a0008; }
.cat-tips         { color: #00c853; border: 1px solid #003318; }
.cat-announcement { color: #ffa000; border: 1px solid #3d2700; }
.cat-promo        { color: #40c4ff; border: 1px solid #003344; }
.cat-default      { color: #888;    border: 1px solid #222;    }

.featured-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    background: #1a0a00;
    border: 1px solid #3a2000;
    color: #ffa000;
    font-size: 10px;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-left: 5px;
    vertical-align: middle;
}

/* Author chip */
.author-chip { display: inline-flex; align-items: center; gap: 7px; }
.author-avatar {
    width: 24px; height: 24px;
    background: #1a0005;
    border: 1px solid #3a0010;
    color: #e8001c;
    font-size: 9px;
    font-weight: 700;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: .05em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.author-name { color: #666; font-size: 11px; }

/* Link */
.open-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #40c4ff;
    font-size: 11px;
    text-decoration: none;
}
.open-link:hover { color: #80d8ff; }
.no-link { color: #2a2a2a; }

/* Date */
.date-cell {
    color: #444;
    font-size: 11px;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: .04em;
}

/* Action buttons */
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
.action-btn:hover { background: #1a1a1a; color: #e0e0e0; border-color: #444; }
.action-btn.danger:hover { background: #1a0005; color: #e8001c; border-color: #3a0010; }

/* Empty state */
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
</style>
</head>

<body>

<!-- SIDEBAR — completely untouched, styled by admin_dashboard.css -->
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_profile.php"><i class="fas fa-user"></i> Your Profile</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="admin_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a>
    <a href="admin_posts.php" class="active"><i class="fas fa-newspaper"></i> Posts</a>
    <a href="recent_activity.php"><i class="fas fa-history"></i> Recent Activity</a>
    <a href="admin_test_drives.php"><i class="fas fa-key"></i> Test Drive</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT — Mitsubishi theme applied here only -->
<div class="content">

    <div class="mit-page-header">
        <div class="mit-diamond"></div>
        <h2 class="mit-page-title">Articles Management</h2>
    </div>
    <div class="mit-red-bar"></div>

    <button class="btn-mit" onclick="toggleForm()">
        <i class="fas fa-plus"></i> New Article
    </button>

    <div class="mit-toolbar">
        <input type="text" id="searchInput" placeholder="Search articles…">
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <option value="Car News">Car News</option>
            <option value="Tips and Guides">Tips and Guides</option>
            <option value="Announcement">Announcement</option>
            <option value="Promo">Promo</option>
        </select>
    </div>

    <!-- Form -->
    <div id="articleForm" style="<?= $editData ? 'display:block;' : 'display:none;' ?>" class="mit-form-card">
    <form method="POST" enctype="multipart/form-data">

        <?php if ($editData): ?>
            <input type="hidden" name="id"        value="<?= $editData['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
        <?php endif; ?>

        <input
            type="text" name="title"
            class="form-control mb-2"
            value="<?= htmlspecialchars($editData['title'] ?? '') ?>"
            placeholder="Title" required
        >

        <input
            type="url" name="link"
            class="form-control mb-2"
            value="<?= htmlspecialchars($editData['link'] ?? '') ?>"
            placeholder="Link (optional)"
        >

        <?php
        $categories = ['Car News', 'Tips and Guides', 'Announcement', 'Promo'];
        $currentCat = $editData['category'] ?? '';
        ?>

        <select name="category" class="form-control mb-2" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $catOption): ?>
                <option value="<?= $catOption ?>" <?= $currentCat === $catOption ? 'selected' : '' ?>>
                    <?= $catOption ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php $isFeatured = $editData['is_featured'] ?? 0; ?>
        <div class="form-check mb-2">
            <input
                type="checkbox" name="is_featured" value="1"
                class="form-check-input"
                <?= $isFeatured ? 'checked' : '' ?>
            >
            <label class="form-check-label">&#9670; Featured Post</label>
        </div>

        <input type="file" name="image" class="form-control mb-2">

        <textarea name="content" class="form-control mb-2" rows="4" required><?= htmlspecialchars($editData['content'] ?? '') ?></textarea>

        <button type="submit" class="btn-publish">
            <?= $editData ? "Update Article" : "Publish Article" ?>
        </button>

    </form>
    </div>

    <!-- Table -->
    <div class="mit-table-wrap">
    <table class="posts-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Category</th>
            <th>Posted By</th>
            <th>Link</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="tableBody">

    <?php
    $hasRows = false;
    while ($row = $result->fetch_assoc()):
        $hasRows = true;

        $catClass = match($row['category']) {
            'Car News'        => 'cat-news',
            'Tips and Guides' => 'cat-tips',
            'Announcement'    => 'cat-announcement',
            'Promo'           => 'cat-promo',
            default           => 'cat-default'
        };

        $parts    = explode(' ', trim($row['posted_by']));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        if (strlen($initials) < 2) $initials = strtoupper(substr($row['posted_by'], 0, 2));
    ?>

    <tr class="article-row"
        data-title="<?= strtolower(htmlspecialchars($row['title'])) ?>"
        data-category="<?= htmlspecialchars($row['category']) ?>">

        <td>
            <div class="mit-thumb">
                <?php if (!empty($row['image'])): ?>
                    <img src="<?= $imgPath . htmlspecialchars($row['image']) ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-image"></i>
                <?php endif; ?>
            </div>
        </td>

        <td>
            <div class="post-title" title="<?= htmlspecialchars($row['title']) ?>">
                <?= htmlspecialchars($row['title']) ?>
            </div>
        </td>

        <td>
            <span class="cat-badge <?= $catClass ?>">
                <?= htmlspecialchars($row['category']) ?>
            </span>
            <?php if (!empty($row['is_featured'])): ?>
                <span class="featured-pill">&#9670; Featured</span>
            <?php endif; ?>
        </td>

        <td>
            <div class="author-chip">
                <div class="author-avatar"><?= htmlspecialchars($initials) ?></div>
                <span class="author-name"><?= htmlspecialchars($row['posted_by']) ?></span>
            </div>
        </td>

        <td>
            <?php if (!empty($row['link'])): ?>
                <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" class="open-link">
                    <i class="fas fa-arrow-up-right-from-square" style="font-size:10px;"></i> Open
                </a>
            <?php else: ?>
                <span class="no-link">—</span>
            <?php endif; ?>
        </td>

        <td class="date-cell"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>

        <td>
            <div class="d-flex gap-1">
                <a href="?edit=<?= $row['id'] ?>" class="action-btn" title="Edit">
                    <i class="fas fa-pen"></i>
                </a>
                <a href="?delete=<?= $row['id'] ?>" class="action-btn danger" title="Delete"
                   onclick="return confirm('Delete this article?');">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </td>

    </tr>

    <?php endwhile; ?>

    <?php if (!$hasRows): ?>
    <tr>
        <td colspan="7">
            <div class="mit-empty">
                <i class="fas fa-newspaper"></i>
                No articles yet — click "New Article" to get started
            </div>
        </td>
    </tr>
    <?php endif; ?>

    </tbody>
    </table>

    <div id="emptySearch" class="mit-empty" style="display:none;">
        <i class="fas fa-search"></i>
        No articles match your search
    </div>

    </div><!-- /.mit-table-wrap -->

</div><!-- /.content -->

<script>
function toggleForm() {
    const form = document.getElementById("articleForm");
    form.style.display = (form.style.display === "none") ? "block" : "none";
}

const searchInput    = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const rows           = document.querySelectorAll(".article-row");
const emptySearch    = document.getElementById("emptySearch");

function filterTable() {
    const s   = searchInput.value.toLowerCase();
    const cat = categoryFilter.value;
    let visible = 0;

    rows.forEach(row => {
        const matchTitle = row.dataset.title.includes(s);
        const matchCat   = !cat || row.dataset.category === cat;
        const show       = matchTitle && matchCat;
        row.style.display = show ? "" : "none";
        if (show) visible++;
    });

    emptySearch.style.display = (visible === 0 && rows.length > 0) ? "block" : "none";
}

searchInput.addEventListener("keyup", filterTable);
categoryFilter.addEventListener("change", filterTable);
</script>

</body>
</html>
