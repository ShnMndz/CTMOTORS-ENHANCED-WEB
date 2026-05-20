<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base = "/citimotorsweb/web";
$current = basename($_SERVER['PHP_SELF']);

$navUser = null;
$pic = "/citimotorsweb/web/uploads/default.png";

if (isset($_SESSION['user_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

    $stmt = $conn->prepare("SELECT fullname, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $navUser = $stmt->get_result()->fetch_assoc();

    if (!empty($navUser['profile_pic'])) {
        $pic = "/citimotorsweb/web/uploads/" . $navUser['profile_pic'];
    }
}
?>

<link rel="stylesheet" href="<?= $base ?>/includes/navbar.css">

<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand" href="<?= $base ?>/home.php">
            <img src="<?= $base ?>/img/new-logo.png" class="navbar-logo">
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <!-- CENTER MENU -->
            <ul class="navbar-nav mx-auto text-center">

                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current=='home.php')?'active-link':'' ?>" href="<?= $base ?>/home.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current=='aboutus.php')?'active-link':'' ?>" href="<?= $base ?>/aboutus/aboutus.php">About Us</a>
                </li>

                <li class="nav-item dropdown">
    <a class="nav-link-custom dropdown-toggle" data-bs-toggle="dropdown">Tools & Service</a>

    <ul class="dropdown-menu">

        <li>
            <a class="dropdown-item <?= ($current=='vehicle_price_list.php') ? 'active-link' : '' ?>"
               href="<?= $base ?>/tools/vehicle_price_list.php">
                Price List
            </a>
        </li>

        <li>
            <a class="dropdown-item <?= ($current=='compare.php') ? 'active-link' : '' ?>"
               href="<?= $base ?>/tools/compare.php">
                Compare Vehicles
            </a>
        </li>

        <li>
            <a class="dropdown-item <?= ($current=='testdrive.php') ? 'active-link' : '' ?>"
               href="<?= $base ?>/tools/testdrive.php">
                Book a Test Drive
            </a>
        </li>

    </ul>
</li>

                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current=='products.php')?'active-link':'' ?>" href="<?= $base ?>/products/products.php">Products</a>
                </li>

                <li class="nav-item dropdown">
    <a class="nav-link-custom dropdown-toggle" data-bs-toggle="dropdown">Parts & Services</a>

    <ul class="dropdown-menu">

        <li>
            <a class="dropdown-item <?= ($current=='genuine_parts.php') ? 'active-link' : '' ?>"
               href="<?= $base ?>/partsandservices/genuine_parts.php">
                Genuine Parts
            </a>
        </li>

        <li>
            <a class="dropdown-item <?= ($current=='services.php') ? 'active-link' : '' ?>"
               href="<?= $base ?>/partsandservices/services.php">
                Services
            </a>
        </li>

    </ul>
</li>

                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current=='articles.php')?'active-link':'' ?>" href="<?= $base ?>/news/articles.php">News</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current=='contacts.php')?'active-link':'' ?>" href="<?= $base ?>/contacts/contacts.php">Contact us</a>
                </li>

            </ul>

            <!-- RIGHT SIDE -->
             <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0 justify-content-center justify-content-lg-end">
                <?php if(isset($_SESSION['user_id'])): ?>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/includes/notifications.php"; ?>

<?php endif; ?>

                <?php if(isset($_SESSION['user_id'])): ?>

                    <a href="<?= $base ?>/users/user_dashboard.php"
                       class="d-flex align-items-center gap-2 text-decoration-none">

                        <img src="<?= $pic ?>"
                             class="rounded-circle border"
                             style="width:36px;height:36px;object-fit:cover;border-color:#e60012;">

                        <span class="fw-semibold text-dark">
                            <?= htmlspecialchars($navUser['fullname']) ?>
                        </span>

                    </a>


                   <a href="<?= $base ?>/logout.php" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to logout?')">
    Logout
</a>

                <?php else: ?>

                    <a href="<?= $base ?>/login.php" class="btn btn-outline-danger btn-sm">Login</a>
                    <a href="<?= $base ?>/signup.php" class="btn btn-danger btn-sm">Sign Up</a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>