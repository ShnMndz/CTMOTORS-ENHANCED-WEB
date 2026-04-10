<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base = "/citimotorsweb/web";
$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand" href="<?= $base ?>/home.php">
            <img src="<?= $base ?>/img/logo.png" alt="CITI MOTORS" class="navbar-logo">
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <!-- CENTER MENU -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">

            <!-- HOME -->
                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current == 'home.php')?'active-link':'' ?>" href="<?= $base ?>/home.php">Home</a>
                </li>

            <!-- ABOUT US -->
                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current == 'aboutus.php')?'active-link':'' ?>" href="<?= $base ?>/aboutus/aboutus.php">About Us</a>
                </li>

           <!-- TOOLS AND SERVICE -->
                <li class="nav-item dropdown">
                    <a class="nav-link-custom dropdown-toggle <?= in_array($current, ['vehicle_price_list.php','compare.php'])?'active-link':'' ?>" href="#" data-bs-toggle="dropdown">
                        Tools & Service
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?= ($current=='vehicle_price_list.php')?'active-link':'' ?>" href="<?= $base ?>/tools/vehicle_price_list.php">Price List</a></li>
                        <li><a class="dropdown-item <?= ($current=='compare.php')?'active-link':'' ?>" href="<?= $base ?>/tools/compare.php">Compare Vehicles</a></li>
                    </ul>
                </li>

           <!-- PRODUCTS -->
                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current == 'products.php')?'active-link':'' ?>" href="<?= $base ?>/products/products.php">Products</a>
                </li>

                <!-- PARTS AND SERVICES -->
                <li class="nav-item dropdown">
    <a class="nav-link-custom dropdown-toggle <?= in_array($current, ['genuine_parts.php','services.php'])?'active-link':'' ?>" href="#" data-bs-toggle="dropdown">
        Parts & Services
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="<?= $base ?>/partsandservices/genuine_parts.php">Genuine Parts</a></li>
        <li><a class="dropdown-item" href="<?= $base ?>/partsandservices/services.php">Services</a></li>
    </ul>
</li>


<!-- NEWS -->
<li class="nav-item">
    <a class="nav-link-custom <?= ($current == 'articles.php')?'active-link':'' ?>" 
       href="<?= $base ?>/news/articles.php">
        News
    </a>
</li>

               <!-- CONTACT US -->
                <li class="nav-item">
                    <a class="nav-link-custom <?= ($current == 'contacts.php')?'active-link':'' ?>" href="<?= $base ?>/contacts/contacts.php">Contact Us</a>
                </li>

            </ul>

            <!-- RIGHT SIDE -->
            <div class="d-flex align-items-center gap-2">

                <?php if(isset($_SESSION['user'])): ?>
                    <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']) ?></span>
                    <a href="<?= $base ?>/logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
                <?php else: ?>
                    <a href="<?= $base ?>/login.php" class="btn btn-outline-danger btn-sm">Login</a>
                    <a href="<?= $base ?>/signup.php" class="btn btn-danger btn-sm">Sign Up</a>
                <?php endif; ?>

            </div>

        </div>
    </div>
</nav>

<style>
/* ================== Navbar ================== */
.custom-navbar {
    position: sticky;
    top: 0;
    z-index: 9999;
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.95);
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

/* FIX: Logo size */
.navbar-logo {
    height: 55px;
    transition: transform 0.3s ease;
}
.navbar-logo:hover { transform: scale(1.05); }

/* CENTER MENU FIX */
.navbar-nav {
    align-items: center;
    gap: 6px;
}

/* Links */
.nav-link-custom {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.nav-link-custom:hover {
    color: #E20000;
    background: rgba(226,0,0,0.08);
}

.active-link {
    color: #E20000 !important;
    font-weight: 600;
    background: rgba(226,0,0,0.1);
}

/* Dropdown FIX (important) */
.dropdown-menu {
    border: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-radius: 12px;
    overflow: hidden;
    z-index: 9999;
}

/* Dropdown items */
.dropdown-item {
    padding: 10px 16px;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background: #E20000;
    color: #fff;
}

.dropdown-item.active-link {
    background: rgba(226,0,0,0.1);
    color: #E20000;
    font-weight: 600;
}

/* Welcome text */
.welcome-text {
    font-size: 14px;
    margin-right: 5px;
}

/* Buttons spacing fix */
.btn {
    border-radius: 6px;
}

/* Mobile fix */
@media (max-width: 991px) {
    .navbar-nav {
        margin-top: 10px;
    }

    .nav-link-custom {
        display: block;
        text-align: center;
    }

    .d-flex {
        justify-content: center;
        margin-top: 10px;
    }
}
</style>