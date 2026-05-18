<?php
/**
 * Admin Panel Sidebar
 * 
 * HOW TO USE:
 *   1. Set $currentPage before including this file.
 *   2. Include it: <?php include 'sidebar.php'; ?>
 * 
 * PAGE KEYS:
 *   'dashboard', 'profile', 'users', 'vehicles', 'posts', 'recent_activity', 'test_drives'
 * 
 * EXAMPLE (at the top of each admin page):
 *   $currentPage = 'users';
 *   include 'sidebar.php';
 */

// Fetch pending test drives count for the badge
$pending_test_drives_sidebar = 0;
if (isset($conn)) {
    $res_td = $conn->query("SELECT COUNT(*) as total FROM test_drives WHERE status = 'pending'");
    if ($res_td) {
        $pending_test_drives_sidebar = $res_td->fetch_assoc()['total'];
    }
}

// Helper: returns 'active' class if page matches
function sidebarActive(string $page, string $currentPage): string {
    return $page === $currentPage ? 'active' : '';
}
?>

<style>
    /* =========================
   SIDEBAR
========================= */
.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    background:linear-gradient(180deg, #111, #0d0d0d);
    padding:20px;
    border-right:1px solid #222;
    box-shadow: 5px 0 20px rgba(0,0,0,0.6);
}

/* LOGO / TITLE */
.sidebar h4{
    color:#e60012;
    margin-bottom:25px;
    font-weight:bold;
    letter-spacing:1px;
}

/* LINKS */
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 14px;
    color:#bbb;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:8px;
    transition:0.25s ease;
    font-weight:500;
    position:relative;
}

/* HOVER EFFECT */
.sidebar a:hover{
    background:#1a1a1a;
    color:#fff;
    transform:translateX(5px);
}

/* ACTIVE LINK */
.sidebar a.active{
    background:rgba(230,0,18,0.15);
    color:#fff;
    border-left:3px solid #e60012;
    padding-left:11px;
}

/* ICON COLOR */
.sidebar a i{
    color:#e60012;
}

/* HOVER BAR ANIMATION */
.sidebar a::before{
    content:"";
    position:absolute;
    left:0;
    top:50%;
    transform:translateY(-50%);
    width:0;
    height:60%;
    background:#e60012;
    border-radius:4px;
    transition:0.2s;
}

.sidebar a:hover::before{
    width:4px;
}

/* BADGE */
.sidebar .badge-notif{
    margin-left:auto;
    font-size:11px;
}
</style>

<div class="sidebar">

    <h4><i class="fas fa-shield-alt me-2"></i>Admin Panel</h4>

    <a href="admin_dashboard.php" class="<?= sidebarActive('dashboard', $currentPage ?? '') ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_profile.php" class="<?= sidebarActive('profile', $currentPage ?? '') ?>">
        <i class="fas fa-user"></i> Your Profile
    </a>

    <a href="admin_users.php" class="<?= sidebarActive('users', $currentPage ?? '') ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php" class="<?= sidebarActive('vehicles', $currentPage ?? '') ?>">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php" class="<?= sidebarActive('posts', $currentPage ?? '') ?>">
        <i class="fas fa-newspaper"></i> Posts
    </a>

    <a href="recent_activity.php" class="<?= sidebarActive('recent_activity', $currentPage ?? '') ?>">
        <i class="fas fa-history"></i> Recent Activity
    </a>

    <a href="admin_test_drives.php" class="<?= sidebarActive('test_drives', $currentPage ?? '') ?>">
        <i class="fas fa-key"></i> Test Drives
        <?php if ($pending_test_drives_sidebar > 0): ?>
            <span class="badge bg-danger badge-notif"><?= $pending_test_drives_sidebar ?></span>
        <?php endif; ?>
    </a>

    <a href="../logout.php"
       onclick="return confirm('Are you sure you want to logout?')">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>

</div>