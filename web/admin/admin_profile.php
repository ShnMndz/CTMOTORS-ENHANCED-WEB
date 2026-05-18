<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$currentPage = 'profile';
include 'sidebar.php';
$id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../login.php");
    exit();
}

$pending_test_drives = $conn->query("
    SELECT COUNT(*) as total 
    FROM test_drives 
    WHERE status = 'pending'
")->fetch_assoc()['total'];

/* FETCH STATS */
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'] ?? 0;

$total_posts = $conn->query("SELECT COUNT(*) as total FROM posts")->fetch_assoc()['total'] ?? 0;

/* Actions this month from activity log (adjust table name if needed) */
$actions_month = 0;
$act_check = $conn->query("SHOW TABLES LIKE 'activity_logs'");
if ($act_check && $act_check->num_rows > 0) {
    $act_res = $conn->query("SELECT COUNT(*) as total FROM activity_logs WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    if ($act_res) $actions_month = $act_res->fetch_assoc()['total'] ?? 0;
}

/* Store last login timestamp in session on login — here we just display if set */
$last_login = $_SESSION['last_login'] ?? null;

/* Member since */
$member_since = '';
if (!empty($user['created_at'])) {
    $member_since = date('M Y', strtotime($user['created_at']));
}

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['save_all'])) {

    $fullname    = trim($_POST['fullname']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone'] ?? '');
   

    $updated = false;

    if ($fullname !== $user['fullname'] || $email !== $user['email']) {
        if (!empty($fullname) && !empty($email)) {
            $update = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $update->bind_param("ssi", $fullname, $email, $id);
            $update->execute();
            $_SESSION['user'] = $fullname;
            $updated = true;
        }
    }

    /* Phone */
    $cols = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($cols && $cols->num_rows > 0 && $phone !== ($user['phone'] ?? '')) {
        $upd = $conn->prepare("UPDATE users SET phone=? WHERE id=?");
        $upd->bind_param("si", $phone, $id);
        $upd->execute();
        $updated = true;
    }

 
    /* PROFILE PICTURE */
    if (!empty($_FILES['profile_pic']['name'])) {
        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);                                                      
        $target   = "../uploads/" . $fileName;
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                if (!empty($user['profile_pic']) && file_exists("../uploads/" . $user['profile_pic'])) {
                    unlink("../uploads/" . $user['profile_pic']);
                }
                $upd3 = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $upd3->bind_param("si", $fileName, $id);
                $upd3->execute();
                $updated = true;
            }
        } else {
            $_SESSION['message'] = "Invalid image format.";
            $_SESSION['type']    = "danger";
            header("Location: admin_profile.php");
            exit();
        }
    }

    $_SESSION['message'] = $updated ? "Profile updated successfully!" : "No changes made.";
    $_SESSION['type']    = $updated ? "success" : "warning";

    if ($updated) {
        logActivity($conn, $_SESSION['user'], 'Updated Profile', "Updated profile information");
    }

    header("Location: admin_profile.php");
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
        header("Location: admin_profile.php");
        exit();
    }

    if ($new !== $confirm) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['type']    = "danger";
        header("Location: admin_profile.php");
        exit();
    }

    if (strlen($new) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters.";
        $_SESSION['type']    = "warning";
        header("Location: admin_profile.php");
        exit();
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $upd4   = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $upd4->bind_param("si", $hashed, $id);
    $upd4->execute();

    logActivity($conn, $_SESSION['user'], 'Changed Password', "Changed admin password");

    $_SESSION['message'] = "Password changed successfully!";
    $_SESSION['type']    = "success";

    header("Location: admin_profile.php");
    exit();
}

/* Initials fallback */
$initials = '';
foreach (explode(' ', $user['fullname']) as $w) {
    $initials .= strtoupper($w[0] ?? '');
}
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
/* ── Base ── */
body { background: #121212; color: #e0e0e0; }

/* ── Layout ── */
.profile-wrap {
    max-width: 780px;
    margin: 30px auto;
    padding: 0 16px 60px;
}

/* ── Card ── */
.p-card {
    background: #1a1a1a;
    border: 1px solid #2a2a2a;
    border-radius: 14px;
    padding: 28px 28px 24px;
    margin-bottom: 20px;
}
.p-card-title {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.p-card-title i { color: #e63b3b; font-size: 16px; }

/* ── Avatar ── */
.avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 14px;
}
.avatar-img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e63b3b;
    display: block;
}
.avatar-initials {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #e63b3b;
    background: #2a1010;
    color: #e63b3b;
    font-size: 28px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-edit-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e63b3b;
    border: 2px solid #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 11px;
    color: #fff;
    transition: background .2s;
}
.avatar-edit-btn:hover { background: #c92e2e; }

/* ── Badges ── */
.badge-role {
    background: #3b1414;
    color: #e67b7b;
    font-size: 11px;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.badge-active {
    background: #0d2e1a;
    color: #4caf7d;
    font-size: 11px;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.meta-info {
    font-size: 11px;
    color: #555;
    margin-top: 6px;
}

/* ── Form ── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }

.f-group label {
    font-size: 11px;
    color: #777;
    margin-bottom: 5px;
    display: block;
}
.form-control {
    background: #242424;
    border: 1px solid #303030;
    color: #ddd;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    transition: border .2s;
}
.form-control:focus {
    background: #242424;
    border-color: #e63b3b;
    color: #fff;
    box-shadow: none;
}
.form-control::placeholder { color: #444; }

/* ── Stats ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.stat-box {
    background: #1a1a1a;
    border: 1px solid #2a2a2a;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}
.stat-box .num {
    font-size: 26px;
    font-weight: 600;
    color: #e63b3b;
    line-height: 1;
}
.stat-box .lbl {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}

/* ── Session rows ── */
.session-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #222;
}
.session-row:last-child { border-bottom: none; }
.session-icon {
    width: 36px;
    height: 36px;
    background: #242424;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #888;
    font-size: 15px;
}
.session-name { font-size: 13px; color: #ccc; }
.session-meta { font-size: 11px; color: #555; margin-top: 2px; }
.session-badge {
    background: #1a2e1a;
    color: #4caf7d;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: 6px;
}
.session-revoke {
    margin-left: auto;
    font-size: 12px;
    color: #e63b3b;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    flex-shrink: 0;
}
.session-revoke:hover { color: #ff6b6b; }

/* ── Password strength ── */
.pw-strength-bar {
    height: 4px;
    background: #2a2a2a;
    border-radius: 4px;
    margin-top: 6px;
    overflow: hidden;
}
.pw-strength-fill {
    height: 100%;
    width: 0%;
    border-radius: 4px;
    transition: width .3s, background .3s;
}
.pw-strength-label {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
}

/* ── Buttons ── */
.btn-main {
    background: #e63b3b;
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
    margin-top: 14px;
    transition: background .2s;
}
.btn-main:hover { background: #c92e2e; color: #fff; }
</style>
</head>
<body>
>

<!-- CONTENT -->
<div class="content">
<div class="profile-wrap">

    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['type'] ?> mt-2 mb-3">
        <?= $_SESSION['message'] ?>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['type']); endif; ?>

    <!-- ── STATS ── -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="num"><?= number_format($actions_month) ?></div>
            <div class="lbl">Actions this month</div>
        </div>
        <div class="stat-box">
            <div class="num"><?= number_format($total_users) ?></div>
            <div class="lbl">Users managed</div>
        </div>
        <div class="stat-box">
            <div class="num"><?= number_format($total_posts) ?></div>
            <div class="lbl">Posts published</div>
        </div>
    </div>

    <!-- ── PROFILE UPDATE ── -->
    <div class="p-card">
        <div class="p-card-title"><i class="fas fa-user-circle"></i> Admin profile</div>

        <form method="POST" enctype="multipart/form-data">

            <!-- Avatar -->
            <div class="text-center mb-4">
                <div class="avatar-wrapper d-inline-block">
                    <?php if (!empty($user['profile_pic']) && file_exists("../uploads/" . $user['profile_pic'])): ?>
                        <img id="preview"
                             src="../uploads/<?= htmlspecialchars($user['profile_pic']) ?>"
                             class="avatar-img">
                    <?php else: ?>
                        <div id="initialsBox" class="avatar-initials"><?= htmlspecialchars($initials) ?></div>
                        <img id="preview" src="" class="avatar-img d-none">
                    <?php endif; ?>
                    <label for="fileInput" class="avatar-edit-btn" title="Change photo">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="profile_pic" id="fileInput" accept="image/*" hidden>
                </div>

                <!-- Badges & meta -->
                <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                    <span class="badge-role"><i class="fas fa-shield-alt me-1" style="font-size:9px;"></i>Super Admin</span>
                    <span class="badge-active"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Active</span>
                </div>
                <div class="meta-info">
                    <?php if ($member_since): ?>Member since <?= $member_since ?><?php endif; ?>
                    <?php if ($last_login): ?> &nbsp;·&nbsp; Last login <?= $last_login ?><?php endif; ?>
                </div>
            </div>

            <!-- Fields grid -->
            <div class="form-grid mb-2">
                <div class="f-group">
                    <label>Full name</label>
                    <input type="text" name="fullname" class="form-control"
                           value="<?= htmlspecialchars($user['fullname']) ?>" required>
                </div>
                <div class="f-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="f-group">
                    <label>Phone number</label>
                    <input type="text" name="phone" class="form-control"
                           placeholder="+63 9XX XXX XXXX"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" name="save_all" class="btn btn-main">
                <i class="fas fa-save me-1"></i> Save changes
            </button>
        </form>
    </div>

    <!-- ── ACTIVE SESSIONS ── -->
    <div class="p-card">
        <div class="p-card-title"><i class="fas fa-laptop"></i> Active sessions</div>

        <div class="session-row">
            <div class="session-icon"><i class="fas fa-desktop"></i></div>
            <div>
                <div class="session-name">
                    <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ? 'Web Browser' : 'Unknown Device') ?>
                    <span class="session-badge">current</span>
                </div>
                <div class="session-meta">
                    IP: <?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown') ?>
                    &nbsp;·&nbsp; Active now
                </div>
            </div>
        </div>

        <?php
        /* If you have a sessions table, fetch other sessions here.
           Example placeholder row below — remove if not needed. */
        ?>
        <!--
        <div class="session-row">
            <div class="session-icon"><i class="fas fa-mobile-alt"></i></div>
            <div>
                <div class="session-name">Mobile Chrome</div>
                <div class="session-meta">IP: 192.168.1.XX &nbsp;·&nbsp; 3 hrs ago</div>
            </div>
            <form method="POST" style="margin:0;">
                <input type="hidden" name="revoke_session" value="SESSION_ID">
                <button type="submit" class="session-revoke">Revoke</button>
            </form>
        </div>
        -->
    </div>

    <!-- ── CHANGE PASSWORD ── -->
    <div class="p-card">
        <div class="p-card-title"><i class="fas fa-lock"></i> Change password</div>

        <form method="POST">
            <div class="f-group mb-3">
                <label>Current password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="f-group mb-1">
                <label>New password</label>
                <input type="password" name="new_password" id="newPw" class="form-control"
                       required oninput="checkStrength(this.value)">
                <div class="pw-strength-bar">
                    <div class="pw-strength-fill" id="pwFill"></div>
                </div>
                <div class="pw-strength-label" id="pwLabel"></div>
            </div>
            <div class="f-group mb-2">
                <label>Confirm password</label>
                <input type="password" name="confirm_password" id="confirmPw" class="form-control" required>
                <div class="pw-strength-label" id="matchLabel" style="margin-top:4px;"></div>
            </div>

            <button type="submit" name="change_password" class="btn btn-main">
                <i class="fas fa-key me-1"></i> Update password
            </button>
        </form>
    </div>

</div><!-- /profile-wrap -->
</div><!-- /content -->

<script>
/* Avatar preview */
document.getElementById('fileInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const prev = document.getElementById('preview');
        const initBox = document.getElementById('initialsBox');
        prev.src = ev.target.result;
        prev.classList.remove('d-none');
        if (initBox) initBox.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

/* Password strength */
function checkStrength(pw) {
    const fill  = document.getElementById('pwFill');
    const label = document.getElementById('pwLabel');
    let score = 0;
    if (pw.length >= 6)  score++;
    if (pw.length >= 10) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { pct: '0%',   color: '#444',    text: '' },
        { pct: '25%',  color: '#e63b3b', text: 'Weak' },
        { pct: '50%',  color: '#e6a43b', text: 'Fair' },
        { pct: '75%',  color: '#3b9ae6', text: 'Good' },
        { pct: '100%', color: '#4caf7d', text: 'Strong' },
    ];
    const lvl = levels[Math.min(score, 4)];
    fill.style.width    = lvl.pct;
    fill.style.background = lvl.color;
    label.style.color   = lvl.color;
    label.textContent   = pw.length ? 'Strength: ' + lvl.text : '';
}

/* Password match hint */
document.getElementById('confirmPw').addEventListener('input', function () {
    const ml = document.getElementById('matchLabel');
    const match = this.value === document.getElementById('newPw').value;
    ml.textContent = this.value ? (match ? '✓ Passwords match' : '✗ Passwords do not match') : '';
    ml.style.color = match ? '#4caf7d' : '#e63b3b';
});
</script>

</body>
</html>