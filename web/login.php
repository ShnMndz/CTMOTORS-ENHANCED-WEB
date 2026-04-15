<?php 
session_start();
include 'db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - CITI MOTORS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }
    .login-container {
        max-width: 400px;
        margin: 80px auto;
        padding: 30px 25px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    .login-container img {
        width: 120px;
        margin-bottom: 20px;
    }
    .login-container h3 {
        margin-bottom: 25px;
        font-weight: 600;
    }
    .login-container .form-control {
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 12px;
    }
    .login-container .btn-danger {
        border-radius: 8px;
        padding: 10px;
        font-weight: 500;
    }
    .login-container a {
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="login-container">
    <!-- Logo -->
    <img src="img/logo.png" alt="CITI MOTORS">

    <h3>Login to Your Account</h3>

    <form method="POST">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>

        <button name="login" class="btn btn-danger w-100">Login</button>
    </form>

    <p class="mt-3">Don't have an account? <a href="signup.php">Sign Up</a></p>
</div>

</body>
</html>

<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // Store user info in session
            $_SESSION['user'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php"); // <-- admin page
            } else {
                header("Location: home.php"); // <-- normal user page
            }
            exit();
        } else {
            echo "<script>alert('Wrong password');</script>";
        }
    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>

