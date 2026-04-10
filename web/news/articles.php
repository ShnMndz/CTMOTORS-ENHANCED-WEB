<?php
include '../db.php';

// image path
$imgPath = "../img/";

$selected = isset($_GET['id']) ? intval($_GET['id']) : 0;

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Articles - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

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
    margin-bottom: 10px;
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
    letter-spacing: 1px;
}

.article-link:hover {
    opacity: 0.7;
}

/* PAGINATION TOP RIGHT */
.pagination {
    justify-content: flex-end;
}

.pagination-sm .page-link {
    padding: 5px 12px;
    font-size: 13px;
    color: #fff;
    background-color: #111;
    border: 1px solid #333;
    transition: 0.2s ease-in-out;
}

.pagination-sm .page-link:hover {
    background-color: #e60012;
    border-color: #e60012;
    color: #fff;
}

.pagination-sm .page-item.active .page-link {
    background-color: #e60012;
    border-color: #e60012;
    color: #fff;
    font-weight: 600;
}

.pagination-sm .page-item.disabled .page-link {
    background-color: #222;
    color: #666;
    border-color: #333;
}

.pagination-sm .page-link:focus {
    box-shadow: none;
}
</style>

</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">

<?php if ($selected && !empty($post)): ?>

    <!-- FULL ARTICLE -->
    <a href="articles.php" class="btn btn-outline-dark btn-sm mb-3">
        ← Back to Articles
    </a>

    <h2 class="fw-bold"><?= htmlspecialchars($post['title']); ?></h2>

    <p class="text-muted small">
        ARTICLE • <?= date("F d, Y", strtotime($post['created_at'])); ?>
    </p>

    <?php if (!empty($post['image'])): ?>
        <img src="<?= $imgPath . $post['image']; ?>" class="img-fluid mb-4 rounded">
    <?php endif; ?>

    <div style="line-height:1.8;">
        <?= $post['content']; ?>
    </div>

    <?php if (!empty($post['link'])): ?>
        <a href="<?= htmlspecialchars($post['link']); ?>" target="_blank" class="btn btn-dark mt-3">
            External Link
        </a>
    <?php endif; ?>

<?php else: ?>

    <!-- LIST VIEW -->
    <?php
    $limit = 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
        SELECT * FROM posts 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalResult = $conn->query("SELECT COUNT(*) as total FROM posts");
    $totalRow = $totalResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $limit);
    ?>

    <!-- HEADER + PAGINATION -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Latest Articles</h2>

        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm mb-0">

                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">‹</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">›</a>
                    </li>
                <?php endif; ?>

            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- ARTICLES -->
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>

        <?php
            // ✅ NEW LOGIC FOR READ ARTICLE LINK
            $readLink = !empty($row['link']) 
                ? $row['link'] 
                : "articles.php?id=" . $row['id'];
        ?>

        <div class="article-row">
            <div class="row align-items-center">

                <div class="col-md-4">
                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= $imgPath . $row['image']; ?>" class="article-img">
                    <?php endif; ?>
                </div>

                <div class="col-md-8">

                    <p class="article-date">
                        <?= date("F d, Y", strtotime($row['created_at'])); ?>
                    </p>

                    <h3 class="article-title">
                        <?= htmlspecialchars($row['title']); ?>
                    </h3>

                    <p class="article-desc">
                        <?= substr(strip_tags($row['content']), 0, 180); ?>...
                    </p>

                    <!-- ✅ UPDATED -->
                    <a href="<?= htmlspecialchars($readLink); ?>" 
                       class="article-link"
                       <?= !empty($row['link']) ? 'target="_blank"' : '' ?>>
                        READ ARTICLE
                    </a>

                </div>

            </div>
        </div>

        <?php endwhile; ?>
    <?php else: ?>

        <div class="alert alert-secondary text-center">
            No articles available.
        </div>

    <?php endif; ?>

<?php endif; ?>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>