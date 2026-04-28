<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact CT Motors Makati</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="/citimotorsweb/web/global.css">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0d0d0d;
            color: #fff;
        }

        .contact-hero {
            padding: 80px 0;
        }

        h1 {
            font-size: 42px;
            font-weight: 700;
        }

        .text-danger {
            color: #e20000 !important;
        }

        .subtext {
            max-width: 600px;
            opacity: 0.8;
        }

        .status {
            margin-top: 10px;
            font-size: 14px;
            color: #ccc;
        }

        .open-dot {
            height: 8px;
            width: 8px;
            background: #00ff88;
            display: inline-block;
            border-radius: 50%;
            margin-right: 6px;
        }

        .action-buttons .btn {
            margin: 5px 5px 0 0;
            border-radius: 20px;
            font-size: 13px;
        }

        .btn-danger {
            background: #e20000;
            border: none;
        }

        .btn-outline-danger {
            border-color: #e20000;
            color: #e20000;
        }

        .btn-outline-danger:hover {
            background: #e20000;
            color: #fff;
        }

        .section-title {
            color: #888;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .info-card {
            display: flex;
            gap: 15px;
            background: #161616;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
            align-items: center;
        }

        .info-card i {
            color: #e20000;
            font-size: 18px;
        }

        .info-card p {
            margin: 0;
            font-size: 14px;
        }

        .info-card a {
            color: #e20000;
            font-size: 13px;
            text-decoration: none;
        }

        .hours-card {
            background: #161616;
            padding: 15px;
            border-radius: 10px;
        }

        .day {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #222;
        }

        .day:last-child {
            border-bottom: none;
        }

        .day.active {
            color: #ff4d4d;
        }

        .day.closed span {
            color: #777;
        }

        .socials .btn {
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR (Optional PHP include) -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<section class="contact-hero">
    <div class="container">

        <!-- LABEL -->
        <span class="badge bg-danger mb-3">MAKATI BRANCH</span>

        <!-- TITLE -->
        <h1>Contact <span class="text-danger">CT Motors</span> Makati</h1>

        <!-- DESCRIPTION -->
        <p class="subtext">
            We'd be glad to hear from you. Reach us for vehicle inquiries,
            test drives, parts, or service appointments.
        </p>

        <!-- STATUS -->
        <div class="status">
            <span class="open-dot"></span> Open now · Closes at 5:00 PM
            <span class="ms-3">Response time ~ 15 minutes</span>
        </div>

        <!-- BUTTONS -->
        <div class="action-buttons mt-3">
            <a class="btn btn-outline-danger">Book test drive</a>
        </div>

        <!-- CONTENT -->
        <div class="row mt-5">

            <!-- LEFT SIDE -->
            <div class="col-lg-6">

                <h6 class="section-title">GET IN TOUCH</h6>

                <div class="info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Address</strong>
                        <p>Don Bosco St. cor. Chino Roces Ave., Makati</p>
                        <a href="#">Map →</a>
                    </div>
                </div>

                <div class="info-card">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Phone / Viber</strong>
                        <p>0955-054-9087</p>
                        <a href="tel:09550549087">Call →</a>
                    </div>
                </div>

                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <p>ctcitimotorsinc.makati@gmail.com</p>
                        <a href="mailto:ctcitimotorsinc.makati@gmail.com">Send →</a>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-6">

                <h6 class="section-title">BRANCH HOURS</h6>

               <?php
$days = [
    "Monday" => "9:00 AM – 5:00 PM",
    "Tuesday" => "9:00 AM – 5:00 PM",
    "Wednesday" => "9:00 AM – 5:00 PM",
    "Thursday" => "9:00 AM – 5:00 PM",
    "Friday" => "9:00 AM – 5:00 PM",
    "Saturday" => "9:00 AM – 3:00 PM",
    "Sunday" => "Closed"
];

$today = date('l'); // gets current day like "Tuesday"
?>

<div class="hours-card">
<?php foreach ($days as $day => $hours): ?>
    <div class="day 
        <?php echo ($day == $today) ? 'active' : ''; ?> 
        <?php echo ($hours == 'Closed') ? 'closed' : ''; ?>">
        
        <?php echo $day; ?>
        <?php if ($day == $today) echo " (Today)"; ?>
        
        <span><?php echo $hours; ?></span>
    </div>
<?php endforeach; ?>
</div>
                </div>

                <h6 class="section-title mt-4">FOLLOW US</h6>

                <div class="socials">
                    <a class="btn btn-outline-light btn-sm">Facebook</a>
                    <a class="btn btn-outline-light btn-sm">Instagram</a>
                    <a class="btn btn-outline-light btn-sm">WhatsApp</a>
                </div>

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>