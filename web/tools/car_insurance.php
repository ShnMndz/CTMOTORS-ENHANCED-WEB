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
  <title>Car Insurance – Citimotors</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red:     #e00000;
      --red-dark:#b30000;
      --black:   #0a0a0a;
      --gray-dk: #1c1c1c;
      --gray-md: #2e2e2e;
      --gray-lt: #f2f2f2;
      --white:   #ffffff;
      --font-display: 'Barlow Condensed', sans-serif;
      --font-body:    'Barlow', sans-serif;
    }

    body {
      background: var(--black);
      color: var(--white);
      font-family: var(--font-body);
      font-size: 16px;
      line-height: 1.6;
    }

    /* ── HERO ── */
    .hero {
      position: relative;
      background: var(--black);
      padding: 80px 40px 60px;
      overflow: hidden;
      border-bottom: 3px solid var(--red);
    }
    .hero::before {
      content: '';
      position: absolute;
      top: 0; right: -60px;
      width: 55%;
      height: 100%;
      background: var(--gray-dk);
      clip-path: polygon(12% 0, 100% 0, 100% 100%, 0% 100%);
    }
    .hero-inner {
      position: relative;
      max-width: 960px;
      margin: 0 auto;
    }
    .hero-eyebrow {
      font-family: var(--font-display);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--red);
      margin-bottom: 10px;
    }
    .hero h1 {
      font-family: var(--font-display);
      font-size: clamp(42px, 6vw, 72px);
      font-weight: 800;
      line-height: 1;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: 18px;
    }
    .hero h1 span { color: var(--red); }
    .hero-sub {
      font-size: 15px;
      font-weight: 300;
      color: #aaa;
      max-width: 480px;
      line-height: 1.7;
    }

    /* ── SECTION ── */
    .section {
      max-width: 960px;
      margin: 0 auto;
      padding: 60px 40px;
    }
    .section-label {
      font-family: var(--font-display);
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--red);
      margin-bottom: 8px;
    }
    .section-title {
      font-family: var(--font-display);
      font-size: clamp(28px, 4vw, 42px);
      font-weight: 800;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: 40px;
      line-height: 1.1;
    }

    /* ── COVERAGE GRID ── */
    .coverage-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2px;
      background: #1a1a1a;
      border: 1px solid #1a1a1a;
    }
    .coverage-card {
      background: var(--gray-dk);
      padding: 28px 24px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
      transition: background 0.2s;
      position: relative;
      overflow: hidden;
    }
    .coverage-card::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0;
      width: 0; height: 3px;
      background: var(--red);
      transition: width 0.3s ease;
    }
    .coverage-card:hover { background: var(--gray-md); }
    .coverage-card:hover::after { width: 100%; }

    .coverage-icon {
      font-size: 34px;
      flex-shrink: 0;
      line-height: 1;
      margin-top: 2px;
    }
    .coverage-info h3 {
      font-family: var(--font-display);
      font-size: 17px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: 6px;
      letter-spacing: 0.03em;
    }
    .coverage-info p {
      font-size: 13px;
      font-weight: 300;
      color: #999;
      line-height: 1.6;
    }

    /* ── TIPS STRIP ── */
    .tips-strip {
      background: var(--gray-dk);
      border-top: 3px solid var(--red);
      border-bottom: 1px solid #2a2a2a;
    }
    .tips-inner {
      max-width: 960px;
      margin: 0 auto;
      padding: 50px 40px;
    }
    .tips-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 30px;
      margin-top: 36px;
    }
    .tip-item {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .tip-number {
      font-family: var(--font-display);
      font-size: 42px;
      font-weight: 800;
      color: var(--red);
      line-height: 1;
      opacity: 0.5;
    }
    .tip-title {
      font-family: var(--font-display);
      font-size: 16px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--white);
      letter-spacing: 0.04em;
    }
    .tip-desc {
      font-size: 13px;
      font-weight: 300;
      color: #999;
      line-height: 1.6;
    }

    /* ── CTA ── */
    .cta-strip {
      background: var(--red);
      padding: 48px 40px;
      text-align: center;
    }
    .cta-strip h2 {
      font-family: var(--font-display);
      font-size: clamp(24px, 3vw, 36px);
      font-weight: 800;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: 8px;
    }
    .cta-strip p {
      font-size: 14px;
      font-weight: 300;
      color: rgba(255,255,255,0.8);
      margin-bottom: 28px;
    }
    .cta-btn {
      display: inline-block;
      background: var(--white);
      color: var(--red);
      font-family: var(--font-display);
      font-size: 15px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      padding: 14px 40px;
      text-decoration: none;
      transition: background 0.2s, color 0.2s;
    }
    .cta-btn:hover {
      background: var(--black);
      color: var(--white);
    }

    /* ── NOTE ── */
    .note {
      max-width: 960px;
      margin: 0 auto;
      padding: 24px 40px 48px;
      font-size: 12px;
      font-weight: 300;
      color: #555;
      line-height: 1.6;
    }

    @media (max-width: 600px) {
      .hero, .section, .tips-inner, .note { padding-left: 20px; padding-right: 20px; }
      .hero::before { display: none; }
      .cta-strip { padding: 36px 20px; }
    }
  </style>
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