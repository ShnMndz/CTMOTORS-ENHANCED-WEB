<?php
include '../db.php';

$imgPath = "../img/";
$category = trim($_GET['category'] ?? '');

/* =========================
   SAFE CATEGORY
========================= */
$categorySafe = $conn->real_escape_string($category);

/* =========================
   FEATURED POST
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
   QUERY FILTER
========================= */
$categoryFilter = !empty($category)
    ? "AND category = '$categorySafe'"
    : "";

/* =========================
   SIDE POSTS
========================= */
$side = $conn->query("
    SELECT * FROM posts
    WHERE id != $featuredId
    $categoryFilter
    ORDER BY created_at DESC
    LIMIT 4
");

/* =========================
   LATEST POSTS
========================= */
$latest = $conn->query("
    SELECT * FROM posts
    WHERE is_featured != 1
    $categoryFilter
    ORDER BY created_at DESC
    LIMIT 6
");

/* =========================
   SHORT CONTENT
========================= */
function excerpt($text, $limit = 20) {
    $words = explode(' ', strip_tags($text));

    if (count($words) <= $limit) {
        return implode(' ', $words);
    }

    return implode(' ', array_slice($words, 0, $limit)) . '...';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>News - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
body{
    background:#111;
    color:#fff;
    font-family:'Poppins',sans-serif;
}

.tabs a{
    border:1px solid #444;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    color:#ccc;
    text-decoration:none;
    transition:.2s;
}

.tabs a:hover,
.tabs a.active{
    background:red;
    border-color:red;
    color:#fff;
}

.featured{
    position:relative;
    border-radius:12px;
    overflow:hidden;
}

.featured img{
    width:100%;
    height:380px;
    object-fit:cover;
    opacity:.65;
}

.featured-text{
    position:absolute;
    bottom:20px;
    left:20px;
    right:20px;
}

.featured-badge{
    background:red;
    padding:4px 10px;
    border-radius:5px;
    font-size:11px;
}

.side-item{
    border-bottom:1px solid #333;
    padding:14px 0;
    transition:.2s;
}

.side-item:hover{
    padding-left:5px;
}

.card-box{
    background:#1b1b1b;
    border-radius:12px;
    overflow:hidden;
    height:100%;
    display:flex;
    flex-direction:column;
    transition:.25s;
}

.card-box:hover{
    transform:translateY(-4px);
}

.card-box img{
    width:100%;
    height:180px;
    object-fit:cover;
}

.card-box h6{
    padding:10px 12px 0;
    margin:0;
}

.card-box p{
    padding:0 12px 12px;
    font-size:13px;
    color:#aaa;
    margin:0;
}
</style>
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="container mt-5">

    <!-- CATEGORY TABS -->
    <div class="tabs d-flex gap-2 mb-4 flex-wrap">
        <a href="articles.php" class="<?= empty($category) ? 'active' : '' ?>">All</a>
        <a href="?category=Announcement" class="<?= $category == 'Announcement' ? 'active' : '' ?>">Announcements</a>
        <a href="?category=Car News" class="<?= $category == 'Car News' ? 'active' : '' ?>">Car News</a>
        <a href="?category=Promo" class="<?= $category == 'Promo' ? 'active' : '' ?>">Promos</a>
        <a href="?category=Tips and Guides" class="<?= $category == 'Tips and Guides' ? 'active' : '' ?>">Service Tips</a>
    </div>

    <!-- FEATURED + SIDE -->
    <div class="row">

        <!-- FEATURED -->
        <?php if (empty($category) && $featured): ?>
        <div class="col-md-7 mb-4">

            <a href="<?= htmlspecialchars($featured['link'] ?: '#') ?>"
               target="_blank"
               class="text-white text-decoration-none">

                <div class="featured">

                    <?php if (!empty($featured['image'])): ?>
                        <img src="<?= $imgPath . $featured['image'] ?>" alt="">
                    <?php endif; ?>

                    <div class="featured-text">
                        <span class="featured-badge">Featured</span>

                        <h4 class="mt-2">
                            <?= htmlspecialchars($featured['title']) ?>
                        </h4>

                        <small>
                            <?= date("F d, Y", strtotime($featured['created_at'])) ?>
                            •
                            <?= htmlspecialchars($featured['category']) ?>
                        </small>
                    </div>

                </div>

            </a>

        </div>
        <?php endif; ?>

        <!-- SIDE POSTS -->
        <div class="<?= empty($category) ? 'col-md-5' : 'col-md-12' ?>">

            <?php while ($row = $side->fetch_assoc()): ?>

            <a href="<?= htmlspecialchars($row['link'] ?: '#') ?>"
               target="_blank"
               class="text-white text-decoration-none">

                <div class="side-item">

                    <small class="text-danger fw-semibold">
                        <?= strtoupper($row['category']) ?>
                    </small>

                    <div>
                        <?= htmlspecialchars($row['title']) ?>
                    </div>

                    <small>
                        <?= date("F d, Y", strtotime($row['created_at'])) ?>
                    </small>

                </div>

            </a>

            <?php endwhile; ?>

        </div>

    </div>

    <!-- LATEST -->
    <h5 class="mt-5 mb-3">Latest Articles</h5>

    <div class="row">

        <?php while ($row = $latest->fetch_assoc()): ?>

        <div class="col-md-4 mb-4">

            <a href="<?= htmlspecialchars($row['link'] ?: '#') ?>"
               target="_blank"
               class="text-white text-decoration-none">

                <div class="card-box">

                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= $imgPath . $row['image'] ?>" alt="">
                    <?php endif; ?>

                    <span class="badge bg-danger m-2">
                        <?= htmlspecialchars($row['category']) ?>
                    </span>

                    <h6>
                        <?= htmlspecialchars($row['title']) ?>
                    </h6>

                    <?php if (!empty($row['content'])): ?>
                        <p>
                            <?= htmlspecialchars(excerpt($row['content'])) ?>
                        </p>
                    <?php endif; ?>

                </div>

            </a>

        </div>

        <?php endwhile; ?>

    </div>

</div>

<!-- ===== FOOTER ===== -->
<footer class="footer mt-5">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>