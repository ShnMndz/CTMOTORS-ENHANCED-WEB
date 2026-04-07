<?php 
session_start();
$base = "/citimotorsweb/web"; 
?>
<!-- ✅ NAVBAR -->
<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CITI MOTORS - About Us</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FIXED PATH -->
    <link rel="stylesheet" href="<?= $base ?>/css/aboutus.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<!-- Corporate Vision -->
<div id="vision" class="vision-section">
    <div class="container-fluid p-0">
        <div class="row g-0 align-items-center">

            <div class="col-md-6 vision-text">
                <h1>CORPORATE VISION</h1>
                <p>Citimotors, Inc. is a marketing and service company engaged in the Automotive Business committed to customer satisfaction.</p>
                <p>We are our customer's first choice in the delivery of excellent and reliable products and services.</p>
                <p>We have a team of professional employees who are well-cared for and take pride in their work.</p>
                <p>We are recognized as the Industry Leader in growth and people management.</p>
            </div>

            <div class="col-md-6 vision-image"></div>

        </div>
    </div>
</div>

<!-- Mission -->
<div id="mission" class="mission-section">
    <div class="container-fluid p-0">
        <div class="row g-0 align-items-center">

            <div class="col-md-6 mission-image"></div>

            <div class="col-md-6 mission-text">
                <h1 style="color: #000;">MISSION STATEMENT</h1>

                <ol>
                    <li>Operate profitably through hard work and determination.</li>
                    <li>Maintain competence and integrity in competition.</li>
                    <li>Enhance product excellence and customer service.</li>
                    <li>Support employee growth and welfare.</li>
                    <li>Contribute to community development and employment.</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<!-- History -->
<div id="history" class="py-5 text-white history-section">
    <div class="history-overlay"></div>

    <div class="container position-relative text-center">
        <h2 class="fw-bold mb-4">COMPANY HISTORY</h2>

        <div class="mx-auto history-text">
            <p>Citimotors, Inc. was established in 1988 as a Mitsubishi dealer serving Metro Manila.</p>
            <p>It expanded rapidly and now operates branches in Makati, Las Piñas, and Alabang.</p>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer-bottom text-center py-3">
    © Student Project Only
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scroll Highlight for About Us -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const aboutLink = document.getElementById('about-us-link');
    if (!aboutLink) return;

    // Sections to track
    const sections = ['vision', 'mission', 'history'].map(id => document.getElementById(id));

    function highlightAboutUs() {
        const scrollPos = window.scrollY + window.innerHeight / 3; // trigger point
        let active = false;

        for (let sec of sections) {
            if (sec && sec.offsetTop <= scrollPos && (sec.offsetTop + sec.offsetHeight) > scrollPos) {
                aboutLink.classList.add('active-link');
                active = true;
                break;
            }
        }

        if (!active) {
            aboutLink.classList.remove('active-link');
        }
    }

    window.addEventListener('scroll', highlightAboutUs);
    highlightAboutUs(); // initial check

    // Smooth scroll when clicking anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 70, // adjust if navbar height
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>

</body>
</html>