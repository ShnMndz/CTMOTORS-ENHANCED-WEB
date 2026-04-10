<?php
include '../db.php'; // Adjust path if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us - CITI MOTORS</title>

<!-- Bootstrap & Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
/* ===== GLOBAL DARK THEME ===== */
body {
    background-color: #000;
    color: #fff;
    font-family: 'Poppins', sans-serif;
}

/* ===== VISION SECTION ===== */
.vision-section {
    background-color: #000;
}

.vision-image {
    background: url('../img/ourvisionpic.png') center/cover no-repeat;
    height: 400px;
    border-radius: 10px;
}

/* ===== MISSION SECTION ===== */
.mission-section {
    background-color: #111; /* slight contrast */
}

.mission-image {
    background: url('../img/missionpic.png') center/cover no-repeat;
    height: 400px;
    border-radius: 10px;
}

/* ===== HISTORY SECTION ===== */
.history-section {
    background: url('../img/historypic.png') center/cover no-repeat;
    position: relative;
    min-height: 400px;
    display: flex;
    align-items: center;
}

.history-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.6);
}

.history-section .container {
    position: relative;
    z-index: 2;
}

/* ===== TEXT STYLING ===== */
h1, h2 {
    font-weight: 600;
}

p, li {
    color: #ccc;
}

/* ===== FOOTER ===== */
.footer {
    background: #000;
    color: #777;
    padding: 20px 0;
}
</style>
</head>
<body>

<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- ===== VISION SECTION ===== -->
<section class="vision-section py-5">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- Text LEFT -->
            <div class="col-lg-6">
                <h1>CORPORATE VISION</h1>
                <p>
                    Citimotors, Inc. is a marketing and service company engaged in the Automotive Business committed to customer satisfaction.
                    We are our customer's first choice in the delivery of excellent and reliable products and services. Have a team of professional employees who
                    are well-cared for and continue to take pride in the work that they do. Are recognized as the Industry Leader in growth and people management.
                </p>
            </div>

            <!-- Image RIGHT -->
            <div class="col-lg-6">
                <div class="vision-image w-100"></div>
            </div>

        </div>
    </div>
</section>

<!-- ===== MISSION SECTION ===== -->
<section class="mission-section py-5">
    <div class="container">
        <div class="row align-items-center">

            <!-- Image LEFT -->
            <div class="col-lg-6 order-lg-1 order-2">
                <div class="mission-image w-100"></div>
            </div>

            <!-- Text RIGHT -->
            <div class="col-lg-6 order-lg-2 order-1">
                <h1>MISSION STATEMENT</h1>
                <ol>
                    <li>The need to operate on a highly profitable level and insure that optimium profits are gained through perseverance, hard work, and determination to maintain a stable and financially-viable business atmosphere;</li>
                    <li>The need to maintain competence, integrity and alertness in dealing successfully with competition, so it can rise above all odds to utlimately become the leader in the industry;</li>
                    <li>The need to embark on a lasting program, marked by driving energy and competitive readiness, to enhance product excellence and reliability, efficient and prompt service, with the goal of attaining the highest level of customer service;</li>
                    <li>The need to look after the professional growth and personal welfare of all employees in the Organization, that they will be financially comfortable, and thus strive to remain decent and responsible citizens; and</li>
                    <li>The need to support worthwhile projects that are designed for the upliftment and improvement of the community and its people, to share in the fulfillment of social obligations of the nation by giving employement and livelihood opportunities to hundreds of families, and paying the appropriate taxes due.</li>
                </ol>
            </div>

        </div>
    </div>
</section>

<!-- ===== HISTORY SECTION ===== -->
<section class="history-section text-center text-white">
    <div class="container py-5">
        <h2>COMPANY HISTORY</h2>
        <p>
         To uphold a competitive edge in the automotive industry, Mitsubishi Motors Philippines Corporation (MMPC) -- then called Philippine Automotive Manufacturing Corporation (PAMCOR), local assembler and distributor of Mitsubishi passenger cars, light commercial vehicles, and trucks and buses, declared in the latter part of 1987 the need to appoint a third franchise dealer for Metro Manila (with the City of Makati as the base of operation), with the intention of strengthening its dealership network in the National Capital Region.
         MMPC chose the group represented by Fulgencio "Paul" N. Ching and Escorton "Gordon" Teng for their technical skills and vast automotive marketing expertise.</p>
         <p>On March 11, 1988, Citimotors, Inc. was established and incorporated under SEC Registration Certificate No. 14322, with an authorized capital stock of Php50-million. The authorized capital stock was fully paid by the stockholders after only four years of operation. Subsequently, the authorized capital stock was increased to P130-million which was also fully paid-up.
         Its territorial jurisdiction, as distinguished from the other Metro Manila dealers of MMPC, covers the entire area situated south of the Pasig River -- Makati, Pasay, Taguig, Pateros, Paranaque, Muntinlupa, and Las Pinas in Rizal; San Pedro and Binan in Laguna; and Bacoor, Imus, Kawit, Noveleta, Cavite City, Rosario, Dasmarinas, Silang and Carmona in Cavite.
         Citimotors, Inc. now has three outlets strategically located within its area of responsibility: Makati, Las Pinas and Alabang. Its Makati office, which was the first to be established, serves as the company headquarters.</p>
        
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer mt-5">
    <div class="footer-container text-center">
        <p>© Disclaimer: This website is made for test only by a student. No copyright infringement intended.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>