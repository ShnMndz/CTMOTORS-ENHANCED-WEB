<?php
date_default_timezone_set('Asia/Manila');
?>

<?php
date_default_timezone_set('Asia/Manila');

$currentDay = date('l');
$currentTime = date('H:i');

// Define schedule (24-hour format for logic)
$schedule = [
    "Monday" => ["09:00", "17:00"],
    "Tuesday" => ["09:00", "17:00"],
    "Wednesday" => ["09:00", "17:00"],
    "Thursday" => ["09:00", "17:00"],
    "Friday" => ["09:00", "17:00"],
    "Saturday" => ["09:00", "15:00"],
    "Sunday" => null // closed
];

$isOpen = false;
$closingTime = "";

if ($schedule[$currentDay]) {
    $open = $schedule[$currentDay][0];
    $close = $schedule[$currentDay][1];

    if ($currentTime >= $open && $currentTime < $close) {
        $isOpen = true;
        $closingTime = date("g:i A", strtotime($close));
    }
}
?>

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

        .map-container {
            margin-top: 40px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: none;
            transition: all 0.3s ease;
        }

        .map-container.show {
            display: block;
        }

        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 28px;
            }

            .map-container iframe {
                height: 300px;
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
    <?php if ($isOpen): ?>
        <span style="color:#00ff88;">🟢 Open now</span> · Closes at <?php echo $closingTime; ?>
    <?php else: ?>
        <span style="color:#ff4d4d;">🔴 Closed</span>
    <?php endif; ?>


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
                        <a href="#" id="mapToggle" onclick="toggleMap(event)">View Map →</a>
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

        <!-- MAP SECTION -->
        <div class="map-container">
           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15447.597893179798!2d121.0135519!3d14.5477409!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c94e86c1ab53%3A0xe1e52d406b5ff76b!2sCitimotors%20Inc%20Makati%20(BRAND%20NEW%20UNITS)%20-%20SALES%20DEPARTMENT!5e0!3m2!1sen!2sph!4v1777356564189!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleMap(event) {
        event.preventDefault();
        const mapContainer = document.querySelector('.map-container');
        const mapToggle = document.getElementById('mapToggle');
        
        mapContainer.classList.toggle('show');
        
        // Update text
        if (mapContainer.classList.contains('show')) {
            mapToggle.textContent = 'Hide Map →';
            // Smooth scroll to map
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            mapToggle.textContent = 'View Map →';
        }
    }
</script>

</body>
</html>