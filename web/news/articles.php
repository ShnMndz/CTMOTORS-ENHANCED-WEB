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
    LIMIT 3
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<link rel="stylesheet" href="articles.css">
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="hero">
    <div class="hero-pattern"></div>
    <div class="hero-accent"></div>
    
    <div class="hero-content">
        <div class="container">
        
            <!-- CATEGORY TABS -->
            <div class="hero-nav">
                <a href="articles.php" class="nav-pill <?= empty($category) ? 'active' : '' ?>">All</a>
                <a href="?category=Announcement" class="nav-pill <?= $category == 'Announcement' ? 'active' : '' ?>">Announcements</a>
                <a href="?category=Car News" class="nav-pill <?= $category == 'Car News' ? 'active' : '' ?>">Car News</a>
                <a href="?category=Promo" class="nav-pill <?= $category == 'Promo' ? 'active' : '' ?>">Promos</a>
                <a href="?category=Tips and Guides" class="nav-pill <?= $category == 'Tips and Guides' ? 'active' : '' ?>">Service Tips</a>
            </div>
            
            <!-- FEATURED + SIDEBAR -->
            <div class="hero-main">
                
                <!-- FEATURED -->
                <?php if (empty($category) && $featured): ?>
                <div class="hero-featured">
                    <a href="<?= htmlspecialchars($featured['link'] ?: '#') ?>" target="_blank" style="text-decoration:none;">
                        <div class="hero-thumb">
                            <div class="hero-badge">Featured</div>
                            <?php if (!empty($featured['image'])): ?>
                                <img src="<?= $imgPath . htmlspecialchars($featured['image']) ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="hero-meta">
                            <div class="hero-diamond"></div>
                            <span class="hero-tag"><?= htmlspecialchars($featured['category']) ?></span>
                            <span class="hero-date"><?= date("M d, Y", strtotime($featured['created_at'])) ?></span>
                        </div>
                        <h1 class="hero-title"><?= htmlspecialchars($featured['title']) ?></h1>
                        <?php if (!empty($featured['content'])): ?>
                            <p class="hero-excerpt"><?= htmlspecialchars(excerpt($featured['content'], 25)) ?></p>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- SIDEBAR -->
                <div class="hero-sidebar">
                    <?php while ($row = $side->fetch_assoc()): ?>
                    <a href="<?= htmlspecialchars($row['link'] ?: '#') ?>" target="_blank" class="side-item">
                        <div class="side-tag"><?= htmlspecialchars($row['category']) ?></div>
                        <div class="side-title"><?= htmlspecialchars($row['title']) ?></div>
                        <div class="side-date"><?= date("M d, Y", strtotime($row['created_at'])) ?></div>
                    </a>
                    <?php endwhile; ?>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<!-- LATEST ARTICLES -->
<div class="articles">
    <div class="container">
        
        <div class="articles-head">
            <div class="articles-diamond"></div>
            <h2 class="articles-title">Latest articles</h2>
        </div>
        
        <div class="row g-4">
            <?php while ($row = $latest->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6">
                <a href="<?= htmlspecialchars($row['link'] ?: '#') ?>" target="_blank" class="article-card">
                    <div class="article-img">
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= $imgPath . htmlspecialchars($row['image']) ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="article-body">
                        <div class="article-tag"><?= htmlspecialchars($row['category']) ?></div>
                        <h3 class="article-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <?php if (!empty($row['content'])): ?>
                            <p class="article-excerpt"><?= htmlspecialchars(excerpt($row['content'])) ?></p>
                        <?php endif; ?>
                        <div class="article-meta"><?= date("M d, Y", strtotime($row['created_at'])) ?></div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="container text-center">
        <p>© DISCLAIMER: THIS WEBSITE IS MADE FOR TEST ONLY BY A STUDENT. NO COPYRIGHT INFRINGEMENT INTENDED.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>