<?php
include '../db.php';

$imgPath = "../img/";
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

/* =========================
   CATEGORY SAFE
========================= */
$categorySafe = $conn->real_escape_string($category);

/* =========================
   FEATURED (ONLY FOR ALL)
========================= */
$featured = null;

if (empty($category)) {
    $featured = $conn->query("
        SELECT * FROM posts 
        WHERE is_featured = 1
        ORDER BY created_at DESC 
        LIMIT 1
    ")->fetch_assoc();
}

$featuredId = $featured['id'] ?? 0;

/* =========================
   SIDE POSTS
========================= */
$side = $conn->query("
    SELECT * FROM posts 
    WHERE id != $featuredId
    " . (!empty($category) ? "AND category = '$categorySafe'" : "") . "
    ORDER BY created_at DESC 
    LIMIT 4
");

/* =========================
   LATEST POSTS
========================= */
$latest = $conn->query("
    SELECT * FROM posts 
    WHERE is_featured != 1
    " . (!empty($category) ? "AND category = '$categorySafe'" : "") . "
    ORDER BY created_at DESC 
    LIMIT 6
");

/* =========================
   FUNCTION: SHORT CONTENT
========================= */
function excerpt($text, $limit = 20) {
    $words = explode(" ", $text);
    return implode(" ", array_slice($words, 0, $limit)) . (count($words) > $limit ? '...' : '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Articles</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
body { background:#111; color:#fff; }

.tabs a {
    border:1px solid #444;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    color:#ccc;
    text-decoration:none;
}
.tabs a.active {
    background:red;
    color:#fff;
    border-color:red;
}

.featured {
    position:relative;
    border-radius:10px;
    overflow:hidden;
}
.featured img {
    width:100%;
    height:380px;
    object-fit:cover;
    opacity:.65;
}
.featured-text {
    position:absolute;
    bottom:20px;
    left:20px;
    right:20px;
}
.featured-badge {
    background:red;
    padding:3px 8px;
    font-size:11px;
    border-radius:4px;
}

.side-item {
    border-bottom:1px solid #333;
    padding:12px 0;
}

.card-box {
    background:#1b1b1b;
    border-radius:10px;
    overflow:hidden;
    height:100%;
    display:flex;
    flex-direction:column;
}
.card-box img {
    width:100%;
    height:180px;
    object-fit:cover;
}
.card-box h6 {
    padding:10px;
    margin:0;
}
.card-box p {
    padding: 0 10px 10px;
    font-size:13px;
    color:#aaa;
}
</style>

</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container mt-5">

<!-- CATEGORY TABS -->
<div class="tabs d-flex gap-2 mb-4 flex-wrap">
    <a href="articles.php" class="<?= empty($category) ? 'active' : '' ?>">All</a>
    <a href="?category=Announcement" class="<?= $category=='Announcement' ? 'active' : '' ?>">Announcements</a>
    <a href="?category=Car News" class="<?= $category=='Car News' ? 'active' : '' ?>">Car News</a>
    <a href="?category=Promo" class="<?= $category=='Promo' ? 'active' : '' ?>">Promos</a>
    <a href="?category=Tips and Guides" class="<?= $category=='Tips and Guides' ? 'active' : '' ?>">Service Tips</a>
</div>

<!-- FEATURED + SIDE -->
<div class="row">

<!-- FEATURED (ONLY ALL) -->
<?php if (empty($category) && $featured): ?>
<div class="col-md-7">

<?php $featLink = !empty($featured['link']) ? $featured['link'] : "#"; ?>

<a href="<?= htmlspecialchars($featLink); ?>" target="_blank" class="text-white text-decoration-none">

<div class="featured">

<?php if ($featured['image']): ?>
<img src="<?= $imgPath . $featured['image']; ?>">
<?php endif; ?>

<div class="featured-text">
<span class="featured-badge">Featured</span>
<h4 class="mt-2"><?= htmlspecialchars($featured['title']); ?></h4>
<small><?= date("F d, Y", strtotime($featured['created_at'])); ?> • <?= $featured['category']; ?></small>
</div>

</div>

</a>

</div>
<?php endif; ?>

<!-- SIDE -->
<div class="<?= empty($category) ? 'col-md-5' : 'col-md-12' ?>">

<?php while($row = $side->fetch_assoc()): ?>
<?php $sideLink = !empty($row['link']) ? $row['link'] : "#"; ?>

<a href="<?= htmlspecialchars($sideLink); ?>" target="_blank" class="text-white text-decoration-none">

<div class="side-item">

<small style="color:red; font-weight:600;">
<?= strtoupper($row['category']); ?>
</small>

<div><?= htmlspecialchars($row['title']); ?></div>

<small><?= date("F d, Y", strtotime($row['created_at'])); ?></small>

</div>

</a>

<?php endwhile; ?>

</div>

</div>

<!-- LATEST -->
<h5 class="mt-5 mb-3">Latest Articles</h5>

<div class="row">

<?php while($row = $latest->fetch_assoc()): ?>
<?php $gridLink = !empty($row['link']) ? $row['link'] : "#"; ?>

<div class="col-md-4 mb-4">

<a href="<?= htmlspecialchars($gridLink); ?>" target="_blank" class="text-white text-decoration-none">

<div class="card-box">

<?php if ($row['image']): ?>
<img src="<?= $imgPath . $row['image']; ?>">
<?php endif; ?>

<span class="badge bg-danger m-2"><?= $row['category']; ?></span>

<h6><?= htmlspecialchars($row['title']); ?></h6>

<?php if (!empty($row['content'])): ?>
<p><?= htmlspecialchars(excerpt($row['content'], 20)); ?></p>
<?php endif; ?>

</div>

</a>

</div>

<?php endwhile; ?>

</div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>