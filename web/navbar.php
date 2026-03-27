<?php session_start(); ?>

<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container d-flex justify-content-between">

    <a class="navbar-brand" href="../home.php">CITI MOTORS</a>

    <div class="d-flex align-items-center gap-3">
        <?php if(isset($_SESSION['user'])): ?>
            <span>Welcome, <?php echo $_SESSION['user']; ?></span>

            <?php if($_SESSION['role'] == 'admin'): ?>
                <a href="../admin_dashboard.php" class="btn btn-danger btn-sm">Admin</a>
            <?php endif; ?>

            <a href="../logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        <?php else: ?>
            <a href="../login.php" class="btn btn-outline-primary btn-sm">Login</a>
            <a href="../signup.php" class="btn btn-primary btn-sm">Sign Up</a>
        <?php endif; ?>
    </div>

  </div>
</nav>