<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* HERO SECTION */
        .hero {
            position: relative;
            height: 100vh;
            background: url('../img/contact-bg.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            color: white;
        }

        /* DARK OVERLAY */
        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(to right, rgba(0,0,0,0.85) 40%, rgba(0,0,0,0.3));
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        /* CONTACT DETAILS */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        .contact-info div {
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .contact-info i {
            color: #E20000;
            margin-right: 10px;
            width: 20px;
        }

        /* BUTTON */
        .btn-custom {
            border: 1px solid white;
            color: white;
            padding: 10px 20px;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background: #E20000;
            border-color: #E20000;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .hero {
                height: auto;
                padding: 80px 20px;
            }

            .hero h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="hero-content">

            <h1>CONTACT CT MOTORS MAKATI</h1>

            <p>
                Ct Motors Philippines welcomes your inquiries. We would be glad to hear from you.
                Should you have questions about our vehicles or services, feel free to contact us.
            </p>

            <!-- CONTACT DETAILS -->
            <div class="contact-info">
                <div><i class="fas fa-map-marker-alt"></i>Don Bosco St. cor. Chino Roces Ave. Formerly Pasong Tamo St. Makati, City</div>
                <div><i class="fas fa-envelope"></i>ctcitimotorsinc.makati@gmail.com</div>
                <div><i class="fas fa-phone"></i> 0955-054-9087</div>
                <div><i class="fas fa-clock"></i>9:00 am to 5:00 pm</div>
            </div>

            <!-- BUTTON -->
            <a href="#" class="btn btn-custom">Find a Dealership</a>

        </div>
    </div>
</section>

</body>
</html>