<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

if (!isset($_SESSION['user_id'])) return;

$user_id = $_SESSION['user_id'];

// ==========================
// UNREAD COUNT
// ==========================
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread = $stmt->get_result()->fetch_assoc()['total'];

// ==========================
// LATEST 5 NOTIFICATIONS
// ==========================
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!-- 🔔 BELL -->
<div class="position-relative notif-wrapper">

    <div onclick="toggleNotif()" style="cursor:pointer;font-size:20px;">
        🔔
        <?php if($unread > 0): ?>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                <?= $unread ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- DROPDOWN -->
    <div id="notifDropdown" class="card shadow"
         style="display:none; position:absolute; right:0; top:35px; width:300px; z-index:999;">

        <div class="card-body p-2">

            <?php if($notifications->num_rows > 0): ?>
                <?php while($n = $notifications->fetch_assoc()): ?>

                    <div class="position-relative border-bottom p-2 <?= $n['is_read'] ? '' : 'bg-light' ?>">

                        <!-- ❌ DELETE BUTTON -->
                        <a href="/citimotorsweb/web/delete_notif.php?id=<?= $n['id'] ?>"
                           onclick="event.stopPropagation();"
                           style="position:absolute; right:8px; top:5px; color:red; text-decoration:none; font-weight:bold;">
                            ✕
                        </a>

                        <!-- NOTIFICATION LINK -->
                        <a href="<?php
                            if($n['type'] == 'vehicle'){
                                echo '/citimotorsweb/web/products/view.php?id='.$n['reference_id'];
                            } else {
                                echo '/citimotorsweb/web/users/my_testdrives.php';
                            }
                        ?>"
                        class="text-decoration-none text-dark d-block">

                            <strong><?= htmlspecialchars($n['title']) ?></strong><br>
                            <small><?= htmlspecialchars($n['message']) ?></small>

                        </a>

                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-muted p-2">No notifications</div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ========================== -->
<!-- JS -->
<!-- ========================== -->
<script>
function toggleNotif() {
    const dropdown = document.getElementById("notifDropdown");

    dropdown.style.display =
        dropdown.style.display === "block" ? "none" : "block";

    if (dropdown.style.display === "block") {
        fetch("/citimotorsweb/web/mark_read.php");
    }
}

document.addEventListener("click", function(e) {
    const wrapper = document.querySelector(".notif-wrapper");
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById("notifDropdown").style.display = "none";
    }
});
</script>