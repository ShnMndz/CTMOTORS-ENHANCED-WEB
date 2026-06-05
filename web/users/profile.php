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

/* ENSURE NEW COLUMNS EXIST */
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

/* FETCH RECENT ACTIVITY */
$activity = [];

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

usort($activity, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
$activity = array_slice($activity, 0, 5);

/* UPDATE PERSONAL INFO */
if (isset($_POST['save_personal'])) {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $dob      = $_POST['date_of_birth'] ?: null;
    $branch   = trim($_POST['preferred_branch']);
    $language = trim($_POST['preferred_language']);

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

/* UPDATE VEHICLE PREFERENCES */
if (isset($_POST['save_preferences'])) {
    $models = isset($_POST['models']) ? implode(',', $_POST['models']) : '';
    $fuel   = trim($_POST['fuel_preference']);
    $budget = trim($_POST['budget_range']);

    $update = $conn->prepare("UPDATE users SET interested_models=?, fuel_preference=?, budget_range=? WHERE id=?");
    $update->bind_param("sssi", $models, $fuel, $budget, $id);
    $update->execute();

    $_SESSION['message'] = "Vehicle preferences saved!";
    $_SESSION['type']    = "success";

    header("Location: profile.php?tab=preferences");
    exit();
}

/* UPDATE NOTIFICATIONS */
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

/* CHANGE PASSWORD */
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

$activeTab   = $_GET['tab'] ?? 'personal';
$savedModels = !empty($user['interested_models']) ? explode(',', $user['interested_models']) : [];
$allModels   = ['Destinator', 'Montero', 'Mirage', 'Mirage G4', 'Triton', 'XForce'];
$fuelTypes   = ['Gasoline', 'Diesel', 'PHEV', 'Electric'];
$budgets     = ['Under ₱1M', '₱1M – ₱1.5M', '₱1.5M – ₱2.5M', '₱2.5M+'];
$branches    = ['Makati', 'BGC', 'Alabang', 'Mandaluyong', 'Las Piñas'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CITI MOTORS - User Profile</title>
<link rel="stylesheet" href="user_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="profile.css">
</head>
<body>
<div class="dashboard">

    <?php include 'user_sidebar.php'; ?>

    <!-- MAIN PANEL -->
    <main class="panel">

        <!-- TOP BAR -->
        <div class="topbar">
            <div>
                <h3>My Profile</h3>
                <p>Manage your account details and preferences</p>
            </div>
            <a href="../home.php" class="btn-home">
                <i class="fa-solid fa-house fa-sm"></i> Homepage
            </a>
        </div>

        <div class="profile-content">

            <!-- ALERT -->
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert-mitsu <?= $_SESSION['type'] ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['type']); endif; ?>

            <!-- TABS -->
            <div class="profile-tabs">
                <a href="?tab=personal"      class="tab-btn <?= $activeTab === 'personal'      ? 'active' : '' ?>"><i class="fa-solid fa-id-card"></i>      Personal Info</a>
                <a href="?tab=preferences"   class="tab-btn <?= $activeTab === 'preferences'   ? 'active' : '' ?>"><i class="fa-solid fa-car"></i>           Preferences</a>
                <a href="?tab=activity"      class="tab-btn <?= $activeTab === 'activity'      ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity</a>
                <a href="?tab=notifications" class="tab-btn <?= $activeTab === 'notifications' ? 'active' : '' ?>"><i class="fa-solid fa-bell"></i>           Notifications</a>
            </div>

            <!-- ========================
                 TAB: PERSONAL INFO
            ======================== -->
            <?php if ($activeTab === 'personal'): ?>

            <div class="profile-card">
                <div class="section-label"><i class="fa-solid fa-circle-info"></i> Basic information</div>
                <form method="POST" enctype="multipart/form-data">

                    <div class="avatar-wrap">
                        <label for="fileInput">
                            <img id="preview"
                                 src="../uploads/<?= htmlspecialchars($user['profile_pic'] ?: 'default.png') ?>"
                                 class="profile-pic">
                        </label>
                        <input type="file" name="profile_pic" id="fileInput" hidden accept="image/jpeg,image/png">
                        <div class="avatar-hint">Click photo to change</div>
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
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
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
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- ========================
                 TAB: VEHICLE PREFERENCES
            ======================== -->
            <?php elseif ($activeTab === 'preferences'): ?>

            <div class="profile-card">
                <div class="section-label"><i class="fa-solid fa-car"></i> Vehicle preferences</div>
                <form method="POST">

                    <div class="mb-4">
                        <label class="form-label">Interested Models</label>
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
                        <i class="fa-solid fa-floppy-disk"></i> Save Preferences
                    </button>
                </form>
            </div>

            <!-- ========================
                 TAB: ACTIVITY
            ======================== -->
            <?php elseif ($activeTab === 'activity'): ?>

            <div class="profile-card">
                <div class="section-label"><i class="fa-solid fa-clock-rotate-left"></i> Recent activity</div>

                <?php if (empty($activity)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    No recent activity yet.
                </div>
                <?php else: ?>

                <?php foreach ($activity as $item):
                    $isTestDrive = $item['type'] === 'testdrive';
                    $date        = date("M j, Y", strtotime($item['date']));
                    $status      = $item['status'] ?? null;
                    $badgeClass  = in_array($status, ['approved','completed']) ? 'approved' : ($status === 'pending' ? 'pending' : ($status === 'cancelled' ? 'cancelled' : ''));
                ?>
                <div class="activity-item <?= $isTestDrive ? 'td' : 'sv' ?>">
                    <div class="activity-icon <?= $isTestDrive ? '' : 'sv' ?>">
                        <i class="fa-solid <?= $isTestDrive ? 'fa-car' : 'fa-heart' ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="activity-label">
                            <?= $isTestDrive ? 'Test Drive' : 'Saved' ?> — <?= htmlspecialchars($item['label']) ?>
                        </div>
                        <div class="activity-meta"><?= $date ?></div>
                    </div>
                    <?php if ($badgeClass): ?>
                    <span class="badge-status <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="act-links">
                    <a href="my_testdrives.php"  class="act-link"><i class="fa-solid fa-car"></i> All Test Drives</a>
                    <a href="saved_vehicles.php" class="act-link"><i class="fa-solid fa-heart"></i> All Saved</a>
                </div>

                <?php endif; ?>
            </div>

            <!-- ========================
                 TAB: NOTIFICATIONS
            ======================== -->
            <?php elseif ($activeTab === 'notifications'): ?>

            <div class="profile-card">
                <div class="section-label"><i class="fa-solid fa-bell"></i> Notification preferences</div>
                <form method="POST">
                    <?php
                    $notifs = [
                        ['notif_promos',    'Promo alerts & deals',  'Get notified about the latest offers'],
                        ['notif_testdrive', 'Test drive reminders',  'Reminders before your scheduled test drive'],
                        ['notif_launches',  'New model launches',    'Be the first to know about new vehicles'],
                        ['notif_dealer',    'Dealer replies',        'Get notified when a dealer responds to you'],
                        ['notif_sms',       'SMS notifications',     'Receive alerts via text message'],
                    ];
                    foreach ($notifs as [$name, $label, $desc]):
                    ?>
                    <div class="notif-row">
                        <div class="notif-info">
                            <div class="notif-title"><?= $label ?></div>
                            <small><?= $desc ?></small>
                        </div>
                        <label class="toggle-wrap">
                            <input type="checkbox" name="<?= $name ?>" id="<?= $name ?>"
                                   <?= !empty($user[$name]) ? 'checked' : '' ?>>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" name="save_notifications" class="btn btn-main w-100 mt-3">
                        <i class="fa-solid fa-floppy-disk"></i> Save Preferences
                    </button>
                </form>
            </div>

            <?php endif; ?>

        </div><!-- /.profile-content -->
    </main>
</div><!-- /.dashboard -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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