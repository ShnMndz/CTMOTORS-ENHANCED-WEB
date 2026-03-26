<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - CITI MOTORS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }
    .signup-container {
        max-width: 400px;
        margin: 80px auto;
        padding: 30px 25px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    .signup-container img {
        width: 120px;
        margin-bottom: 20px;
    }
    .signup-container h3 {
        margin-bottom: 25px;
        font-weight: 600;
    }
    .signup-container .form-control {
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 12px;
    }
    .signup-container .btn-danger {
        border-radius: 8px;
        padding: 10px;
        font-weight: 500;
    }
    .signup-container a {
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="signup-container">
    <!-- Logo -->
    <img src="img/logo.png" alt="CITI MOTORS">

    <h3>Create Your Account</h3>

    <form method="POST">
        <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>

        <button name="signup" class="btn btn-danger w-100">Sign Up</button>
    </form>

    <p class="mt-3">Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>

<?php
if (isset($_POST['signup'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, password) VALUES ('$name', '$email', '$pass')";

    if ($conn->query($sql)) {
        echo "<script>alert('Account created!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Email already exists!');</script>";
    }
}
?>