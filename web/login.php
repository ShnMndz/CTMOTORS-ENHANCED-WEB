<?php 
session_start();
include 'db.php'; 

// Handle login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // Store user info in session
            $_SESSION['user'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php");
                exit();
            } else {
                header("Location: home.php");
                exit();
            }
        } else {
            $login_error = 'Wrong password';
        }
    } else {
        $login_error = 'User not found';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - CITI MOTORS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        margin: 0;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
        background: #eef2f7;
    }
    .page-shell {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        min-height: 100vh;
        overflow: hidden;
    }
    .brand-panel {
        position: relative;
        background: linear-gradient(180deg, rgba(30, 31, 45, 0.6) 0%, rgba(30, 31, 45, 0.6) 100%), url('img/homebg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #fff;
        padding: 48px 40px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .brand-panel::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.05), transparent 28%), radial-gradient(circle at 60% 70%, rgba(255,255,255,0.03), transparent 20%);
        pointer-events: none;
    }
    .brand-panel .brand-inner {
        position: relative;
        z-index: 1;
        max-width: 420px;
        display: none;
    }
    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 30px;
    }
    .brand-badge .brand-logo {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #fff;
    }
    .brand-badge span {
        letter-spacing: 1px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .brand-panel h1 {
        font-size: clamp(2.3rem, 3vw, 3.6rem);
        line-height: 1.05;
        margin: 0 0 22px;
        max-width: 420px;
    }
    .brand-panel p {
        max-width: 380px;
        font-size: 1rem;
        line-height: 1.8;
        opacity: 0.92;
        margin: 0;
    }
    .brand-graphic {
        position: relative;
        width: 100%;
        height: 170px;
        margin-top: 48px;
    }
    .brand-graphic::before,
    .brand-graphic::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,0.08);
    }
    .brand-graphic::before {
        width: 220px;
        height: 64px;
        bottom: 18px;
        left: 24px;
    }
    .brand-graphic::after {
        width: 90px;
        height: 90px;
        bottom: 58px;
        right: 34px;
    }
    .form-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 40px;
    }
    .login-card {
        width: 100%;
        max-width: 420px;
        background: transparent;
        border-radius: 24px;
        box-shadow: none;
        padding: 42px 36px;
    }
    .login-card .logo-circle {
        text-align: center;
        margin: 0 auto 18px;
    }
    .login-card .logo-circle img {
        width: 64px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .login-card h3 {
        text-align: center;
        margin-bottom: 8px;
        font-size: 1.6rem;
        font-weight: 700;
    }
    .login-card .subtitle {
        text-align: center;
        margin-bottom: 30px;
        color: #6b7280;
    }
    .login-card .form-control {
        border-radius: 14px;
        padding: 16px 18px;
        border: 1px solid #e5e7eb;
        background: transparent;
        margin-bottom: 16px;
        color: #111827;
    }
    .login-card .form-control:focus {
        border-color: #d9252b;
        box-shadow: 0 0 0 0.15rem rgba(217,37,43,0.12);
    }
    .login-card .forgot-link {
        display: block;
        text-align: right;
        margin-bottom: 24px;
        color: #d9252b;
        font-weight: 600;
        text-decoration: none;
    }
    .login-card .forgot-link:hover {
        text-decoration: underline;
    }
    .login-card .btn-primary {
        border-radius: 14px;
        padding: 14px 18px;
        background: #d9252b;
        border: none;
        font-size: 1rem;
        font-weight: 700;
    }
    .login-card .btn-primary:hover {
        background: #b01f22;
    }
    .login-card .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 26px 0 22px;
        color: #9ca3af;
        font-size: 0.95rem;
    }
    .login-card .divider::before,
    .login-card .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .login-card .divider::before {
        margin-right: 12px;
    }
    .login-card .divider::after {
        margin-left: 12px;
    }
    .login-card .google-btn {
        width: 100%;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #111827;
        padding: 14px 0;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .login-card .google-btn i {
        color: #ea4335;
    }
    .login-card .signup-link {
        margin-top: 26px;
        text-align: center;
        color: #6b7280;
        font-size: 0.95rem;
    }
    .login-card .signup-link a {
        color: #d9252b;
        font-weight: 700;
        text-decoration: none;
    }
    .login-card .signup-link a:hover {
        text-decoration: underline;
    }
    .login-card .alert {
        border-radius: 14px;
        border: none;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }
    .login-card .alert-danger {
        background-color: #fee2e2;
        color: #7f1d1d;
    }
    @media (max-width: 992px) {
        .page-shell {
            grid-template-columns: 1fr;
        }
        .brand-panel,
        .form-panel {
            padding: 30px 24px;
        }
    }
    @media (max-width: 640px) {
        .brand-panel {
            padding: 24px 20px;
        }
        .login-card {
            padding: 32px 24px;
        }
    }
</style>
</head>
<body>
<div class="page-shell">
    <section class="brand-panel">
        <div class="brand-inner">
            <div class="brand-badge">
                <div class="brand-logo">C</div>
                <span>CITIMOTORS INC.</span>
            </div>
            <h1>Drive your business forward.</h1>
            <p>Your trusted automotive management platform.</p>
        </div>
        <div class="brand-graphic"></div>
    </section>
    <section class="form-panel">
        <div class="login-card">
            <div class="logo-circle">
                <img src="img/logo.png" alt="CITI MOTORS">
            </div>
            <h3>Welcome back</h3>
            <p class="subtitle">Sign in to your account</p>
            <?php if (isset($login_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
            <?php endif; ?>
            <form method="POST">
                <input type="email" name="email" class="form-control" placeholder="Email address" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <a href="#" class="forgot-link">Forgot password?</a>
                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="divider">or</div>
            <button class="google-btn" type="button">
                <i class="fab fa-google"></i> Continue with Google
            </button>
            <p class="signup-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </section>
</div>
</body>
</html>

