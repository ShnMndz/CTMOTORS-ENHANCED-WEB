<?php 
$base = "/citimotorsweb/web"; 
$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-light bg-light shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <a class="navbar-brand" href="<?= $base ?>/home.php">
            <img src="<?= $base ?>/img/logo.png" alt="CITI MOTORS" style="height:50px;">
        </a>

        <!-- Main Links -->
        <div class="d-flex gap-3 align-items-center">

            <!-- Home -->
            <a href="<?= $base ?>/home.php" 
               class="nav-link-custom <?= ($current == 'home.php') ? 'active-link' : '' ?>">
               Home
            </a>

            <!-- About Us -->
            <a href="<?= $base ?>/aboutus/aboutus.php" 
               id="about-us-link"
               class="nav-link-custom <?= ($current == 'aboutus.php') ? 'active-link' : '' ?>">
               About Us
            </a>

            <!-- Customer Tools Dropdown -->
            <div class="dropdown">
                <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Tools and Service</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= $base ?>/tools/vehicle_price_list.php">Price List</a></li>
                    <li><a class="dropdown-item" href="<?= $base ?>/tools/compare.php">Compare Vehicles</a></li>
                </ul>
            </div>

            <!-- Products -->
            <a href="<?= $base ?>/products/products.php" 
               class="nav-link-custom <?= ($current == 'products.php') ? 'active-link' : '' ?>">
               Products
            </a>

            <!-- Parts & Services Dropdown -->
            <div class="dropdown">
                <a class="nav-link-custom dropdown-toggle" href="#" data-bs-toggle="dropdown">Parts & Services</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= $base ?>/partsandservices/partsandservices.html#genuineparts">Genuine Parts</a></li>
                    <li><a class="dropdown-item" href="<?= $base ?>/partsandservices/partsandservices.html#servicebody">Service & Body Shop</a></li>
                </ul>
            </div>

            <!-- Contact Us -->
            <a href="<?= $base ?>/contacts/contactus.html" 
               class="nav-link-custom <?= ($current == 'contactus.html') ? 'active-link' : '' ?>">
               Contact Us
            </a>
        </div>

        <!-- Login / Signup -->
        <div>
        <?php if(isset($_SESSION['user'])): ?>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></span>
            <a href="<?= $base ?>/logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        <?php else: ?>
            <a href="<?= $base ?>/login.php" class="btn btn-outline-danger btn-sm">Login</a>
            <a href="<?= $base ?>/signup.php" class="btn btn-danger btn-sm">Sign Up</a>
        <?php endif; ?>
        </div>

    </div>
</nav>