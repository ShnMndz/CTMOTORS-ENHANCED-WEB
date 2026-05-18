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
 */

$pending_test_drives_sidebar = 0;
if (isset($conn)) {
    $res_td = $conn->query("SELECT COUNT(*) as total FROM test_drives WHERE status = 'pending'");
    if ($res_td) {
        $pending_test_drives_sidebar = $res_td->fetch_assoc()['total'];
    }
}

function sidebarActive(string $page, string $currentPage): string {
    return $page === $currentPage ? 'active' : '';
}
?>

<style>
    /* =========================
   SIDEBAR
========================= */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    background: linear-gradient(180deg, #111, #0d0d0d);
    padding: 20px;
    border-right: 1px solid #222;
    box-shadow: 5px 0 20px rgba(0,0,0,0.6);
    transition: width 0.3s ease, padding 0.3s ease;
    overflow: hidden;
    z-index: 100;
}

/* COLLAPSED STATE */
.sidebar.collapsed {
    width: 70px;
    padding: 20px 10px;
}

/* TOGGLE BUTTON */
.sidebar-toggle {
    position: absolute;
    top: 18px;
    right: 14px;
    background: none;
    border: 1px solid #333;
    color: #e60012;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.2s;
    flex-shrink: 0;
}

.sidebar-toggle:hover {
    background: rgba(230,0,18,0.15);
    border-color: #e60012;
}

/* When collapsed, center the toggle button */
.sidebar.collapsed .sidebar-toggle {
    right: 50%;
    transform: translateX(50%);
}

/* TITLE */
.sidebar-title {
    color: #e60012;
    margin-bottom: 25px;
    margin-top: 8px;
    font-weight: bold;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
    padding-top: 20px;
}

/* LINKS */
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    color: #bbb;
    text-decoration: none;
    border-radius: 10px;
    margin-bottom: 8px;
    transition: 0.25s ease;
    font-weight: 500;
    position: relative;
    white-space: nowrap;
    overflow: hidden;
}

/* HOVER */
.sidebar a:hover {
    background: #1a1a1a;
    color: #fff;
    transform: translateX(5px);
}

/* Disable translateX when collapsed so icons stay centered */
.sidebar.collapsed a:hover {
    transform: none;
}

/* ACTIVE */
.sidebar a.active {
    background: rgba(230,0,18,0.15);
    color: #fff;
    border-left: 3px solid #e60012;
    padding-left: 11px;
}

/* ICON */
.sidebar a i {
    color: #e60012;
    min-width: 18px;
    text-align: center;
    flex-shrink: 0;
}

/* HOVER BAR ANIMATION */
.sidebar a::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 60%;
    background: #e60012;
    border-radius: 4px;
    transition: 0.2s;
}

.sidebar a:hover::before {
    width: 4px;
}

/* HIDE LABELS WHEN COLLAPSED */
.sidebar.collapsed .sidebar-label,
.sidebar.collapsed .badge-notif {
    display: none;
}

/* CENTER ICONS WHEN COLLAPSED */
.sidebar.collapsed a {
    justify-content: center;
    padding: 12px 0;
}

.sidebar.collapsed .sidebar-title {
    justify-content: center;
}

/* BADGE */
.sidebar .badge-notif {
    margin-left: auto;
    font-size: 11px;
}

/* TOOLTIP on collapsed icons (uses title attr via CSS) */
.sidebar.collapsed a:hover::after {
    content: attr(title);
    position: absolute;
    left: 70px;
    background: #1a1a1a;
    color: #fff;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 13px;
    white-space: nowrap;
    border: 1px solid #333;
    z-index: 200;
    pointer-events: none;
}

/* =========================
   CONTENT SHIFT
========================= */
.content {
    margin-left: 260px;
    transition: margin-left 0.3s ease;
    padding: 25px;
}

.content.sidebar-collapsed {
    margin-left: 70px;
}
</style>

<div class="sidebar" id="adminSidebar">

    <!-- TOGGLE BUTTON -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Collapse sidebar">
        <i class="fas fa-angles-left" id="toggleIcon"></i>
    </button>

    <h4 class="sidebar-title">
        <i class="fas fa-shield-alt"></i>
        <span class="sidebar-label">Admin Panel</span>
    </h4>

    <a href="admin_dashboard.php" class="<?= sidebarActive('dashboard', $currentPage ?? '') ?>" title="Dashboard">
        <i class="fas fa-chart-line"></i>
        <span class="sidebar-label">Dashboard</span>
    </a>

    <a href="admin_profile.php" class="<?= sidebarActive('profile', $currentPage ?? '') ?>" title="Your Profile">
        <i class="fas fa-user"></i>
        <span class="sidebar-label">Your Profile</span>
    </a>

    <a href="admin_users.php" class="<?= sidebarActive('users', $currentPage ?? '') ?>" title="Manage Users">
        <i class="fas fa-users"></i>
        <span class="sidebar-label">Manage Users</span>
    </a>

    <a href="admin_vehicles.php" class="<?= sidebarActive('vehicles', $currentPage ?? '') ?>" title="Manage Vehicles">
        <i class="fas fa-car"></i>
        <span class="sidebar-label">Manage Vehicles</span>
    </a>

    <a href="admin_posts.php" class="<?= sidebarActive('posts', $currentPage ?? '') ?>" title="Posts">
        <i class="fas fa-newspaper"></i>
        <span class="sidebar-label">Posts</span>
    </a>

    <a href="recent_activity.php" class="<?= sidebarActive('recent_activity', $currentPage ?? '') ?>" title="Recent Activity">
        <i class="fas fa-history"></i>
        <span class="sidebar-label">Recent Activity</span>
    </a>

    <a href="admin_test_drives.php" class="<?= sidebarActive('test_drives', $currentPage ?? '') ?>" title="Test Drives">
        <i class="fas fa-key"></i>
        <span class="sidebar-label">Test Drives</span>
        <?php if ($pending_test_drives_sidebar > 0): ?>
            <span class="badge bg-danger badge-notif"><?= $pending_test_drives_sidebar ?></span>
        <?php endif; ?>
    </a>

    <a href="../logout.php"
       onclick="return confirm('Are you sure you want to logout?')"
       title="Logout">
        <i class="fas fa-sign-out-alt"></i>
        <span class="sidebar-label">Logout</span>
    </a>

</div>

<script>
(function () {
    const sidebar = document.getElementById('adminSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    const icon    = document.getElementById('toggleIcon');
    const content = document.querySelector('.content');

    // Restore saved state immediately (prevents flash on page load)
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        if (content) content.classList.add('sidebar-collapsed');
        icon.classList.replace('fa-angles-left', 'fa-angles-right');
    }

    toggle.addEventListener('click', function () {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        if (content) content.classList.toggle('sidebar-collapsed', isCollapsed);

        icon.classList.replace(
            isCollapsed ? 'fa-angles-left'  : 'fa-angles-right',
            isCollapsed ? 'fa-angles-right' : 'fa-angles-left'
        );

        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
})();
</script>