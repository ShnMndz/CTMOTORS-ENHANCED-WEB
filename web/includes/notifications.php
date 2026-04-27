<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

if (!isset($_SESSION['user_id'])) return;

$user_id = $_SESSION['user_id'];

// UNREAD COUNT
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread = $stmt->get_result()->fetch_assoc()['total'];
?>

<!-- 🔔 BELL -->
<div class="position-relative notif-wrapper" id="notifWrapper">

    <div onclick="toggleNotif()" style="cursor:pointer;font-size:20px;">
        🔔

        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle notif-badge"
              style="<?= $unread > 0 ? '' : 'display:none;' ?>">
            <?= $unread ?>
        </span>
    </div>

    <!-- DROPDOWN -->
    <div id="notifDropdown" class="card shadow"
         style="display:none; position:absolute; right:0; top:35px; width:300px; z-index:999;">

        <div class="card-body p-2 notif-body">
            <div class="text-center text-muted p-2">Loading...</div>
        </div>

    </div>
</div>

<!-- 🔊 SOUND -->
<audio id="notifSound" src="/citimotorsweb/web/sounds/notif.mp3" preload="auto"></audio>

<!-- 🎨 HIGHLIGHT -->
<style>
@keyframes highlightFade {
    0% { background-color: #ffe066; }
    100% { background-color: transparent; }
}
.new-notif {
    animation: highlightFade 2s ease;
}
</style>

<script>
if (!window.notifInit) {
    window.notifInit = true;

    let lastSeenId = 0;
    let firstLoad = true;

    // 🔓 SOUND UNLOCK (FIX FOR CHROME BLOCKING AUDIO)
    document.addEventListener("click", function unlockSound() {
        const sound = document.getElementById("notifSound");
        if (sound) {
            sound.play().then(() => {
                sound.pause();
                sound.currentTime = 0;
            }).catch(() => {});
        }
        document.removeEventListener("click", unlockSound);
    });

    function toggleNotif() {
        const dropdown = document.getElementById("notifDropdown");

        const isOpen = dropdown.style.display === "block";
        dropdown.style.display = isOpen ? "none" : "block";

        if (!isOpen) {
            loadNotifications();
            fetch("/citimotorsweb/web/mark_read.php");
        }
    }

    document.addEventListener("click", function(e) {
        const wrapper = document.getElementById("notifWrapper");
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById("notifDropdown").style.display = "none";
        }
    });

    // 🔥 LOAD NOTIFICATIONS (WITH SOUND FIX)
    function loadNotifications(){
        fetch("/citimotorsweb/web/get_notifications.php")
            .then(res => res.json())
            .then(data => {

                let html = "";

                if (data.length === 0) {
                    html = `<div class="text-center text-muted p-2">No notifications</div>`;
                } else {

                    data.forEach(n => {

                        let link = (n.type === "vehicle")
                            ? `/citimotorsweb/web/products/view.php?id=${n.reference_id}`
                            : `/citimotorsweb/web/users/my_testdrives.php`;

                        let isNew = n.id > lastSeenId;

                        // 🔊 PLAY SOUND ONLY FOR NEW NOTIFS
                        if (!firstLoad && isNew) {
                            const sound = document.getElementById("notifSound");
                            if (sound) {
                                sound.currentTime = 0;
                                sound.play().catch(() => {});
                            }
                        }

                        html += `
                            <div class="position-relative border-bottom p-2 ${n.is_read == 0 ? 'bg-light' : ''} ${isNew ? 'new-notif' : ''}">

                                <a href="/citimotorsweb/web/delete_notif.php?id=${n.id}"
                                   onclick="event.preventDefault(); deleteNotif(${n.id});"
                                   style="position:absolute; right:8px; top:5px; color:red; text-decoration:none; font-weight:bold;">
                                    ✕
                                </a>

                                <a href="${link}" class="text-decoration-none text-dark d-block">
                                    <strong>${n.title}</strong><br>
                                    <small>${n.message}</small>
                                </a>
                            </div>
                        `;

                        // update latest ID
                        if (n.id > lastSeenId) {
                            lastSeenId = n.id;
                        }
                    });
                }

                document.querySelector(".notif-body").innerHTML = html;
                firstLoad = false;
            });
    }

    // 🔥 DELETE NOTIF
    function deleteNotif(id){
        fetch(`/citimotorsweb/web/delete_notif.php?id=${id}`)
            .then(res => res.json())
            .then(() => {
                loadNotifications();
                updateBadge();
            });
    }

    // 🔥 BADGE UPDATE
    function updateBadge(){
        fetch("/citimotorsweb/web/user_notif_count.php")
            .then(res => res.json())
            .then(data => {

                const badge = document.querySelector(".notif-badge");
                if(!badge) return;

                if(data.count > 0){
                    badge.innerText = data.count;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            });
    }

    // 🚀 INITIAL LOAD
    updateBadge();
    loadNotifications();

    // 🔁 REAL-TIME LOOP (FIXED)
    setInterval(() => {
        loadNotifications();
        updateBadge();
    }, 5000);
}
</script>