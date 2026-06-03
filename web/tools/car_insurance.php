<?php
session_start();

// car_insurance.php - Citimotors Car Insurance Page
// Include your existing header here if needed:
// include('includes/header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CITIMOTORS - Car Insurance</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="car_insurance.css" />
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <p class="hero-eyebrow">Citimotors &mdash; Protection &amp; Peace of Mind</p>
    <h1>Car <span>Insurance</span><br>Coverage</h1>
    <p class="hero-sub">Drive confidently with comprehensive protection designed for every road condition. Here's what your Citimotors insurance covers.</p>
  </div>
</section>

<!-- COVERAGE -->
<div class="section">
  <p class="section-label">What's Covered</p>
  <h2 class="section-title">Your Coverage Benefits</h2>

  <div class="coverage-grid">
    <?php
    $coverages = [
      ['icon' => '🌧️', 'title' => 'Acts of Nature',                        'desc'  => 'Coverage for damages caused by typhoons, floods, earthquakes, and other natural calamities.'],
      ['icon' => '🚗', 'title' => 'Own Damage / Theft',                     'desc'  => 'Protects your vehicle against accidental damage, collision, or theft of the insured unit.'],
      ['icon' => '🧑‍⚕️','title' => 'Third-Party Bodily Injury',             'desc'  => 'Covers medical expenses or liability for injury caused to another person in an accident.'],
      ['icon' => '🚧', 'title' => 'Third-Party Property Damage',             'desc'  => 'Covers the cost of repairing or replacing another person\'s property damaged in an accident.'],
      ['icon' => '🧑‍🤝‍🧑','title'=> 'Unnamed Passenger Personal Accident',  'desc'  => 'Provides personal accident benefits for passengers inside the vehicle at the time of an incident.'],
      ['icon' => '📋', 'title' => 'Faster, Easy & Higher Coverage Claims',   'desc'  => 'Streamlined claims process with higher coverage limits so you get back on the road faster.'],
      ['icon' => '🚛', 'title' => '24/7 Nationwide Towing Assistance',       'desc'  => 'Round-the-clock towing service available anywhere in the Philippines whenever you need it.'],
    ];
    foreach ($coverages as $item): ?>
    <div class="coverage-card">
      <div class="coverage-icon"><?= $item['icon'] ?></div>
      <div class="coverage-info">
        <h3><?= htmlspecialchars($item['title']) ?></h3>
        <p><?= htmlspecialchars($item['desc']) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- TIPS STRIP -->
<div class="tips-strip">
  <div class="tips-inner">
    <p class="section-label">How to Get Insured</p>
    <h2 class="section-title">Steps to Get Your Insurance</h2>

    <div class="tips-grid">
      <?php
      $tips = [
        ['title' => 'Talk to Your Dealer',       'desc' => 'Ask your Citimotors sales representative about partner insurance providers during your purchase.'],
        ['title' => 'Compare Providers',          'desc' => 'Get quotes from multiple insurers and compare coverage limits, exclusions, and premiums.'],
        ['title' => 'Prepare Your Documents',     'desc' => 'Have your OR/CR, valid driver\'s license, and vehicle details (make, model, plate number) ready.'],
        ['title' => 'Apply & Get Covered',        'desc' => 'Apply online or in person. Most insurers process applications within 1–2 business days.'],
        ['title' => 'Renew Before It Expires',    'desc' => 'Renew at least one week before expiry to avoid coverage gaps and qualify for no-claim discounts.'],
      ];
      foreach ($tips as $i => $tip): ?>
      <div class="tip-item">
        <div class="tip-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
        <div class="tip-title"><?= htmlspecialchars($tip['title']) ?></div>
        <div class="tip-desc"><?= htmlspecialchars($tip['desc']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- CTA -->
<div class="cta-strip">
  <h2>Ready to Get Protected?</h2>
  <p>Visit your nearest Citimotors branch or reach out to our team today.</p>
  <a href="/contact" class="cta-btn">Get a Quote</a>
</div>

<!-- NOTE -->
<p class="note">* Coverage details and terms are subject to the policy conditions of the insurance provider. For complete information, please consult a Citimotors sales representative or your insurer.</p>

<?php
// Include your existing footer here if needed:
// include('includes/footer.php');
?>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>