<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   ENSURE NEW COLUMNS EXIST
   Run once — safe to keep
========================= */
$conn->query("ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS address VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS date_of_birth DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS preferred_branch VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS preferred_language VARCHAR(50) DEFAULT 'English',
    ADD COLUMN IF NOT EXISTS interested_models TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS fuel_preference VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS budget_range VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS notif_promos TINYINT(1) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS notif_testdrive TINYINT(1) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS notif_launches TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS notif_dealer TINYINT(1) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS notif_sms TINYINT(1) DEFAULT 0
");

/* Re-fetch user after potential column additions */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* FETCH RECENT ACTIVITY (test drives + saved vehicles) */
$activity = [];

/* Test drives */
$tdStmt = $conn->prepare("
    SELECT 'testdrive' AS type, v.model AS label, td.preferred_date AS date, td.status
    FROM test_drive_requests td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE td.user_id = ?
    ORDER BY td.preferred_date DESC
    LIMIT 3
");
if ($tdStmt) {
    $tdStmt->bind_param("i", $id);
    $tdStmt->execute();
    $tdResult = $tdStmt->get_result();
    while ($row = $tdResult->fetch_assoc()) $activity[] = $row;
}

/* Saved vehicles */
$svStmt = $conn->prepare("
    SELECT 'saved' AS type, v.model AS label, sv.created_at AS date, NULL AS status
    FROM saved_vehicles sv
    JOIN vehicles v ON sv.vehicle_id = v.id
    WHERE sv.user_id = ?
    ORDER BY sv.created_at DESC
    LIMIT 3
");
if ($svStmt) {
    $svStmt->bind_param("i", $id);
    $svStmt->execute();
    $svResult = $svStmt->get_result();
    while ($row = $svResult->fetch_assoc()) $activity[] = $row;
}

/* Sort combined activity by date desc */
usort($activity, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
$activity = array_slice($activity, 0, 5);

/* =========================
   UPDATE PERSONAL INFO
========================= */
if (isset($_POST['save_personal'])) {

    $fullname  = trim($_POST['fullname']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);
    $dob       = $_POST['date_of_birth'] ?: null;
    $branch    = trim($_POST['preferred_branch']);
    $language  = trim($_POST['preferred_language']);

    if (!empty($fullname) && !empty($email)) {
        $update = $conn->prepare("UPDATE users SET fullname=?, email=?, phone=?, address=?, date_of_birth=?, preferred_branch=?, preferred_language=? WHERE id=?");
        $update->bind_param("sssssssi", $fullname, $email, $phone, $address, $dob, $branch, $language, $id);
        $update->execute();
        $_SESSION['message'] = "Personal info updated!";
        $_SESSION['type']    = "success";
    } else {
        $_SESSION['message'] = "Name and email are required.";
        $_SESSION['type']    = "danger";
    }

    if (!empty($_FILES['profile_pic']['name'])) {
        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target   = "../uploads/" . $fileName;
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png'])) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                if (!empty($user['profile_pic']) && file_exists("../uploads/" . $user['profile_pic'])) {
                    unlink("../uploads/" . $user['profile_pic']);
                }
                $up2 = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $up2->bind_param("si", $fileName, $id);
                $up2->execute();
            }
        } else {
            $_SESSION['message'] = "Invalid image format.";
            $_SESSION['type']    = "danger";
        }
    }

    header("Location: profile.php?tab=personal");
    exit();
}

/* =========================
   UPDATE VEHICLE PREFERENCES
========================= */
if (isset($_POST['save_preferences'])) {
    $models    = isset($_POST['models']) ? implode(',', $_POST['models']) : '';
    $fuel      = trim($_POST['fuel_preference']);
    $budget    = trim($_POST['budget_range']);

    $update = $conn->prepare("UPDATE users SET interested_models=?, fuel_preference=?, budget_range=? WHERE id=?");
    $update->bind_param("sssi", $models, $fuel, $budget, $id);
    $update->execute();

    $_SESSION['message'] = "Vehicle preferences saved!";
    $_SESSION['type']    = "success";

    header("Location: profile.php?tab=preferences");
    exit();
}

/* =========================
   UPDATE NOTIFICATIONS
========================= */
if (isset($_POST['save_notifications'])) {
    $p = isset($_POST['notif_promos'])    ? 1 : 0;
    $t = isset($_POST['notif_testdrive']) ? 1 : 0;
    $l = isset($_POST['notif_launches'])  ? 1 : 0;
    $d = isset($_POST['notif_dealer'])    ? 1 : 0;
    $s = isset($_POST['notif_sms'])       ? 1 : 0;

    $update = $conn->prepare("UPDATE users SET notif_promos=?, notif_testdrive=?, notif_launches=?, notif_dealer=?, notif_sms=? WHERE id=?");
    $update->bind_param("iiiiii", $p, $t, $l, $d, $s, $id);
    $update->execute();

    $_SESSION['message'] = "Notification preferences saved!";
    $_SESSION['type']    = "success";

    header("Location: profile.php?tab=notifications");
    exit();
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $_SESSION['message'] = "Current password is incorrect.";
        $_SESSION['type']    = "danger";
    } elseif ($new !== $confirm) {
        $_SESSION['message'] = "New passwords do not match.";
        $_SESSION['type']    = "danger";
    } elseif (strlen($new) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters.";
        $_SESSION['type']    = "warning";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hashed, $id);
        $update->execute();
        $_SESSION['message'] = "Password changed successfully!";
        $_SESSION['type']    = "success";
    }

    header("Location: profile.php?tab=personal");
    exit();
}

/* Active tab */
$activeTab = $_GET['tab'] ?? 'personal';

/* Saved models as array */
$savedModels = !empty($user['interested_models']) ? explode(',', $user['interested_models']) : [];

$allModels  = ['Destinator', 'Montero', 'Mirage', 'Mirage G4', 'Triton', 'XForce'];
$fuelTypes  = ['Gasoline', 'Diesel', 'PHEV', 'Electric'];
$budgets    = ['Under ₱1M', '₱1M – ₱1.5M', '₱1.5M – ₱2.5M', '₱2.5M+'];
$branches   = ['Makati', 'BGC', 'Alabang', 'Mandaluyong', 'Las Piñas'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<link rel="stylesheet" href="user_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background: #f5f7fb; color: #222; }

.panel { padding: 30px; overflow-y: auto; }

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

/* ---- Tabs ---- */
.profile-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.profile-tabs .tab-btn {
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: color .15s;
    text-decoration: none;
}
.profile-tabs .tab-btn:hover { color: #ff4d4d; }
.profile-tabs .tab-btn.active { color: #ff4d4d; border-bottom-color: #ff4d4d; }

/* ---- Cards ---- */
.profile-card {
    max-width: 560px;
    background: #fff;
    padding: 28px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    margin-bottom: 20px;
}
.section-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: #999;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ---- Form controls ---- */
.form-control, .form-select {
    background: #f1f3f6;
    border: none;
    color: #222;
}
.form-control:focus, .form-select:focus {
    background: #e8eaf0;
    color: #222;
    box-shadow: 0 0 0 2px rgba(255,77,77,.15);
    border-color: transparent;
}
.form-label { font-size: 13px; color: #555; margin-bottom: 4px; }

/* ---- Pill selectors ---- */
.pill-group { display: flex; flex-wrap: wrap; gap: 8px; }
.pill-check { display: none; }
.pill-label {
    padding: 6px 16px;
    border: 1.5px solid #ddd;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    color: #555;
    background: #f8f8f8;
    transition: all .15s;
    user-select: none;
}
.pill-check:checked + .pill-label {
    background: #ff4d4d;
    border-color: #c0392b;
    color: #fff;
    font-weight: 500;
}
.pill-label:hover { border-color: #ff4d4d; color: #ff4d4d; }
.pill-check:checked + .pill-label:hover { color: #fff; }

/* ---- Activity ---- */
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8f9fb;
    border-radius: 10px;
    margin-bottom: 8px;
}
.activity-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,77,77,.12);
    color: #ff4d4d;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 15px;
}
.activity-label { font-size: 14px; font-weight: 500; }
.activity-meta  { font-size: 12px; color: #888; }

/* ---- Notification toggles ---- */
.notif-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}
.notif-row:last-child { border-bottom: none; }
.notif-info small { font-size: 12px; color: #999; display: block; }
.form-check-input[type="checkbox"].toggle-switch {
    width: 40px; height: 22px;
    border-radius: 11px;
    appearance: none;
    background: #ccc;
    cursor: pointer;
    position: relative;
    transition: background .2s;
    border: none;
    outline: none;
    flex-shrink: 0;
}
.form-check-input[type="checkbox"].toggle-switch:checked { background: #ff4d4d; }
.form-check-input[type="checkbox"].toggle-switch::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    transition: left .2s;
}
.form-check-input[type="checkbox"].toggle-switch:checked::after { left: 21px; }

/* ---- Buttons ---- */
.btn-main { background: #ff4d4d; border: none; color: #fff; }
.btn-main:hover { background: #e03e3e; color: #fff; }

/* ---- Profile pic ---- */
.profile-pic {
    width: 110px; height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff4d4d;
    cursor: pointer;
    transition: opacity .2s;
}
.profile-pic:hover { opacity: .85; }

/* ---- Empty state ---- */
.empty-state { text-align: center; padding: 30px 0; color: #bbb; font-size: 14px; }
.empty-state i { font-size: 36px; display: block; margin-bottom: 8px; }
</style>
</head>
<body>
<div class="dashboard">

 <?php include 'user_sidebar.php'; ?>
 
    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="top-bar">
            <div>
                <h3>Edit Profile &nbsp;<i class="fa-solid fa-user fa-sm text-muted"></i></h3>
                <p class="text-muted mb-0">Manage your account details and preferences</p>
            </div>
            <a href="../home.php" class="btn btn-outline-dark btn-sm">Return to Homepage</a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['type'] ?> mt-0 mb-3" style="max-width:560px;">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['type']); endif; ?>

        <!-- TABS -->
        <div class="profile-tabs">
            <a href="?tab=personal"       class="tab-btn <?= $activeTab === 'personal'       ? 'active' : '' ?>"><i class="fa-solid fa-id-card"></i>      Personal Info</a>
            <a href="?tab=preferences"    class="tab-btn <?= $activeTab === 'preferences'    ? 'active' : '' ?>"><i class="fa-solid fa-car"></i>           Vehicle Preferences</a>
            <a href="?tab=activity"       class="tab-btn <?= $activeTab === 'activity'       ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity</a>
            <a href="?tab=notifications"  class="tab-btn <?= $activeTab === 'notifications'  ? 'active' : '' ?>"><i class="fa-solid fa-bell"></i>           Notifications</a>
        </div>

        <!-- ===========================
             TAB: PERSONAL INFO
        =========================== -->
        <?php if ($activeTab === 'personal'): ?>

        <div class="profile-card">
            <div class="section-label"><i class="fa-solid fa-circle-info"></i> Basic information</div>
            <form method="POST" enctype="multipart/form-data">

                <div class="text-center mb-4">
                    <label for="fileInput">
                        <img id="preview" src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>" class="profile-pic">
                    </label>
                    <input type="file" name="profile_pic" id="fileInput" hidden accept="image/jpeg,image/png">
                    <div class="small text-muted mt-1">Click photo to change</div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullname" class="form-control"
                               value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control"
                               placeholder="+63 9XX XXX XXXX"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preferred Language</label>
                        <select name="preferred_language" class="form-select">
                            <?php foreach (['English','Filipino'] as $lang): ?>
                            <option value="<?= $lang ?>" <?= ($user['preferred_language'] ?? '') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control"
                               placeholder="City, Province, Philippines"
                               value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Preferred Branch</label>
                        <select name="preferred_branch" class="form-select">
                            <option value="">— Select branch —</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= $b ?>" <?= ($user['preferred_branch'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="save_personal" class="btn btn-main w-100 mt-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <div class="section-label"><i class="fa-solid fa-lock"></i> Change password</div>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-main w-100">
                    <i class="fa-solid fa-key me-1"></i> Update Password
                </button>
            </form>
        </div>

        <!-- ===========================
             TAB: VEHICLE PREFERENCES
        =========================== -->
        <?php elseif ($activeTab === 'preferences'): ?>

        <div class="profile-card">
            <div class="section-label"><i class="fa-solid fa-car"></i> Vehicle preferences</div>
            <form method="POST">

                <div class="mb-4">
                    <label class="form-label fw-500">Interested Models</label>
                    <div class="pill-group mt-2">
                        <?php foreach ($allModels as $model): ?>
                        <input type="checkbox" class="pill-check" name="models[]"
                               id="model_<?= $model ?>"
                               value="<?= $model ?>"
                               <?= in_array($model, $savedModels) ? 'checked' : '' ?>>
                        <label class="pill-label" for="model_<?= $model ?>"><?= $model ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Fuel Preference</label>
                    <div class="pill-group mt-2">
                        <?php foreach ($fuelTypes as $fuel): ?>
                        <input type="radio" class="pill-check" name="fuel_preference"
                               id="fuel_<?= $fuel ?>"
                               value="<?= $fuel ?>"
                               <?= ($user['fuel_preference'] ?? '') === $fuel ? 'checked' : '' ?>>
                        <label class="pill-label" for="fuel_<?= $fuel ?>"><?= $fuel ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Budget Range</label>
                    <select name="budget_range" class="form-select">
                        <option value="">— Select budget —</option>
                        <?php foreach ($budgets as $b): ?>
                        <option value="<?= $b ?>" <?= ($user['budget_range'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="save_preferences" class="btn btn-main w-100">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Preferences
                </button>
            </form>
        </div>

        <!-- ===========================
             TAB: ACTIVITY
        =========================== -->
        <?php elseif ($activeTab === 'activity'): ?>

        <div class="profile-card" style="max-width:560px;">
            <div class="section-label"><i class="fa-solid fa-clock-rotate-left"></i> Recent activity</div>

            <?php if (empty($activity)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                No recent activity yet.
            </div>
            <?php else: ?>

            <?php foreach ($activity as $item):
                $isTestDrive = $item['type'] === 'testdrive';
                $icon   = $isTestDrive ? 'fa-car' : 'fa-heart';
                $status = $item['status'] ?? null;
                $date   = date("M j, Y", strtotime($item['date']));
                $statusBadge = '';
                if ($status === 'approved' || $status === 'completed') {
                    $statusBadge = '<span class="badge" style="background:#d4edda;color:#155724;font-weight:500;">' . ucfirst($status) . '</span>';
                } elseif ($status === 'pending') {
                    $statusBadge = '<span class="badge" style="background:#fff3cd;color:#856404;font-weight:500;">Pending</span>';
                } elseif ($status === 'cancelled') {
                    $statusBadge = '<span class="badge" style="background:#f8d7da;color:#721c24;font-weight:500;">Cancelled</span>';
                }
            ?>
            <div class="activity-item">
                <div class="activity-icon"><i class="fa-solid <?= $icon ?>"></i></div>
                <div class="flex-grow-1">
                    <div class="activity-label">
                        <?= $isTestDrive ? 'Test Drive' : 'Saved' ?> — <?= htmlspecialchars($item['label']) ?>
                    </div>
                    <div class="activity-meta"><?= $date ?></div>
                </div>
                <?= $statusBadge ?>
            </div>
            <?php endforeach; ?>

            <div class="mt-3 d-flex gap-2">
                <a href="my_testdrives.php"  class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-car me-1"></i>All Test Drives</a>
                <a href="saved_vehicles.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-heart me-1"></i>All Saved</a>
            </div>

            <?php endif; ?>
        </div>

        <!-- ===========================
             TAB: NOTIFICATIONS
        =========================== -->
        <?php elseif ($activeTab === 'notifications'): ?>

        <div class="profile-card" style="max-width:560px;">
            <div class="section-label"><i class="fa-solid fa-bell"></i> Notification preferences</div>
            <form method="POST">
                <?php
                $notifs = [
                    ['notif_promos',    'Promo alerts & deals',   'Get notified about the latest offers'],
                    ['notif_testdrive', 'Test drive reminders',   'Reminders before your scheduled test drive'],
                    ['notif_launches',  'New model launches',     'Be the first to know about new vehicles'],
                    ['notif_dealer',    'Dealer replies',         'Get notified when a dealer responds to you'],
                    ['notif_sms',       'SMS notifications',      'Receive alerts via text message'],
                ];
                foreach ($notifs as [$name, $label, $desc]):
                ?>
                <div class="notif-row">
                    <div class="notif-info">
                        <div><?= $label ?></div>
                        <small><?= $desc ?></small>
                    </div>
                    <input type="checkbox" class="toggle-switch"
                           name="<?= $name ?>"
                           id="<?= $name ?>"
                           <?= !empty($user[$name]) ? 'checked' : '' ?>>
                </div>
                <?php endforeach; ?>

                <button type="submit" name="save_notifications" class="btn btn-main w-100 mt-3">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Preferences
                </button>
            </form>
        </div>

        <?php endif; ?>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Profile pic preview */
document.getElementById("fileInput")?.addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => document.getElementById("preview").src = ev.target.result;
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>