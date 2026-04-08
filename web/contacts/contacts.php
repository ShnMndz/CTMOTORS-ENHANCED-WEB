<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;

            /* Background Image */
            background: url('../img/bg-contact.jpg') no-repeat center center fixed;
            background-size: cover;

            font-family: Arial, sans-serif;
        }

        /* Overlay */
        .overlay {
            background: rgba(0,0,0,0.5);
            min-height: 100vh;
            padding-top: 80px;
        }

        .contact-box {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .info {
            font-size: 16px;
            margin-bottom: 12px;
        }

        .label {
            font-weight: bold;
            color: #E20000;
        }
    </style>
</head>
<body>

<!-- ✅ Navbar mo (existing) -->
<?php include('../navbar.php'); ?>

<div class="overlay">
    <div class="contact-box">
        <h2>Contact Details</h2>

        <div class="info">
            <span class="label">Company:</span> Citi Motors
        </div>

        <div class="info">
            <span class="label">Address:</span> 123 Main Street, Manila, Philippines
        </div>

        <div class="info">
            <span class="label">Phone:</span> +63 900 000 0000
        </div>

        <div class="info">
            <span class="label">Email:</span> contact@citimotors.com
        </div>

        <div class="info">
            <span class="label">Business Hours:</span> Monday - Saturday, 9:00 AM - 6:00 PM
        </div>
    </div>
</div>

</body>
</html>