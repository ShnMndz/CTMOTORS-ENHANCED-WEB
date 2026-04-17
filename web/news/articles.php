<?php
include '../db.php';

$imgPath = "../img/";

$selected = isset($_GET['id']) ? intval($_GET['id']) : 0;

// category filter (TEXT)
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

/* =========================
   SINGLE ARTICLE VIEW
========================= */
if ($selected) {

    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->bind_param("i", $selected);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
}

/* =========================
   GET DISTINCT CATEGORIES
========================= */
$catResult = $conn->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Articles - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
.article-row {
    border-bottom: 1px solid #ddd;
    padding: 30px 0;
}

.article-img {
    width: 100%;
    height: 230px;
    object-fit: cover;
    border-radius: 8px;
}

.article-date {
    font-size: 12px;
    letter-spacing: 2px;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.article-title {
    font-weight: 700;
    font-size: 22px;
    position: relative;
    display: inline-block;
}

.article-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -6px;
    width: 0;
    height: 3px;
    background: #e60012;
    transition: 0.3s;
}

.article-row:hover .article-title::after {
    width: 50px;
}

.article-desc {
    color: #555;
    font-size: 14px;
    margin-bottom: 15px;
}

.article-link {
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid black;
    color: black;
}
</style>

</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">

<?php if ($selected && !empty($post)): ?>

    <!-- SINGLE ARTICLE -->
    <a href="articles.php" class="btn btn-outline-dark btn-sm mb-3">← Back</a>

    <h2><?= htmlspecialchars($post['title']); ?></h2>

    <p class="text-muted small">
        <?= date("F d, Y", strtotime($post['created_at'])); ?> • <?= htmlspecialchars($post['category']); ?>
    </p>

    <?php if (!empty($post['image'])): ?>
        <img src="<?= $imgPath . $post['image']; ?>" class="img-fluid mb-4 rounded">
    <?php endif; ?>

    <div style="line-height:1.8;">
        <?= $post['content']; ?>
    </div>

<?php else: ?>

<?php
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* =========================
   QUERY (FILTER CATEGORY)
========================= */
if (!empty($category)) {
    $stmt = $conn->prepare("
        SELECT * FROM posts 
        WHERE category = ?
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $category, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT * FROM posts 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

/* COUNT */
if (!empty($category)) {
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM posts WHERE category = '$category'");
} else {
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM posts");
}

$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);
?>

<!-- FILTER DROPDOWN -->
<form method="GET" class="mb-3">
    <select name="category" class="form-select w-25" onchange="this.form.submit()">
        <option value="">All Categories</option>

        <?php while($cat = $catResult->fetch_assoc()): ?>
            <option value="<?= $cat['category']; ?>"
                <?= ($category == $cat['category']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category']); ?>
            </option>
        <?php endwhile; ?>

    </select>
</form>

<!-- HEADER -->
<h2 class="fw-bold mb-4">Latest Articles</h2>

<!-- LIST -->
<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>

    <?php
        $readLink = !empty($row['link'])
            ? $row['link']
            : "articles.php?id=" . $row['id'];
    ?>

    <div class="article-row row">
        <div class="col-md-4">
            <?php if (!empty($row['image'])): ?>
                <img src="<?= $imgPath . $row['image']; ?>" class="article-img">
            <?php endif; ?>
        </div>

        <div class="col-md-8">

            <p class="article-date">
                <?= date("F d, Y", strtotime($row['created_at'])); ?>
                • <?= htmlspecialchars($row['category']); ?>
            </p>

            <h3 class="article-title">
                <?= htmlspecialchars($row['title']); ?>
            </h3>

            <p class="article-desc">
                <?= substr(strip_tags($row['content']), 0, 180); ?>...
            </p>

            <a href="<?= htmlspecialchars($readLink); ?>" class="article-link">
                READ ARTICLE
            </a>

        </div>
    </div>

    <?php endwhile; ?>
<?php else: ?>

    <div class="alert alert-secondary text-center">
        No articles found.
    </div>

<?php endif; ?>

<?php endif; ?>

</div>

</body>
</html>