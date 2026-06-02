<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* USER DATA FOR SIDEBAR */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT 
        td.id,
        td.vehicle_id,
        td.date,
        td.time,
        td.status,
        td.message,
        td.admin_message,
        td.admin_notes,
        td.created_at,
        v.model_name,
        v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE td.user_id = ?
    ORDER BY td.date ASC, td.time ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$testdrives = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$counts = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0];
foreach ($testdrives as $td) {
    $counts['total']++;
    $s = strtolower($td['status']);
    if (isset($counts[$s])) $counts[$s]++;
}

$events = [];
foreach ($testdrives as $td) {
    $statusColor = match(strtolower($td['status'])) {
        'pending'   => '#d97706',
        'approved'  => '#065f46',
        'rejected'  => '#e60012',
        'completed' => '#1e40af',
        default     => '#888'
    };

    $events[] = [
        'id'              => $td['id'],
        'title'           => $td['model_name'] . ' ' . $td['model_variant'],
        'start'           => $td['date'] . 'T' . $td['time'],
        'backgroundColor' => $statusColor,
        'borderColor'     => $statusColor,
        'extendedProps'   => [
            'vehicle'      => $td['model_name'] . ' ' . $td['model_variant'],
            'status'       => $td['status'],
            'message'      => $td['message'],
            'adminMessage' => $td['admin_message'],
            'adminNotes'   => $td['admin_notes'],
            'createdAt'    => $td['created_at'],
        ]
    ];
}

$eventsJSON = json_encode($events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Schedule - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="user_dashboard.css">
<style>
/* ── Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #f2f2f2; color: #111; font-family: 'Segoe UI', sans-serif; }

.main-wrap {
    max-width: 1100px;
    margin: 32px 24px 32px 300px;
    padding: 0 24px;
}

@media (max-width: 991px) {
    .main-wrap {
        margin: 32px auto;
        padding-top: 340px;
    }
    .sidebar {
        position: relative;
        width: 100%;
        min-width: auto;
        height: auto;
        border-right: none;
    }
}

/* ── Section box ── */
.box {
    background: #fff;
    border-left: 4px solid #e60012;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}

/* ── Section heading ── */
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e60012;
    flex-wrap: wrap;
    gap: 10px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-icon {
    width: 32px;
    height: 32px;
    background: #e60012;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    flex-shrink: 0;
}

.section-title h3 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #000;
    margin: 0;
}

/* ── Stats bar ── */
.stats-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.stat-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f8f8;
    border: 1px solid #ebebeb;
    border-radius: 6px;
    padding: 8px 14px;
    flex: 1;
    min-width: 90px;
}

.stat-pill .sp-num {
    font-size: 20px;
    font-weight: 800;
    line-height: 1;
    color: #000;
}

.stat-pill .sp-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #aaa;
}

.stat-pill.pending   { border-left: 3px solid #d97706; }
.stat-pill.approved  { border-left: 3px solid #065f46; }
.stat-pill.rejected  { border-left: 3px solid #e60012; }
.stat-pill.completed { border-left: 3px solid #1e40af; }
.stat-pill.total     { border-left: 3px solid #000; }

.stat-pill.pending   .sp-num { color: #d97706; }
.stat-pill.approved  .sp-num { color: #065f46; }
.stat-pill.rejected  .sp-num { color: #e60012; }
.stat-pill.completed .sp-num { color: #1e40af; }

/* ── Toolbar row ── */
.toolbar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

/* ── Legend ── */
.legend {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #888;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── Filter select ── */
.filter-select {
    height: 32px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #333;
    background: #fff;
    cursor: pointer;
    outline: none;
    font-family: inherit;
}

/* ── Book button ── */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 32px;
    padding: 0 16px;
    background: #000;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: .06em;
    cursor: pointer;
    white-space: nowrap;
}

/* ── Calendar wrapper ── */
.calendar-wrap {
    border: 1px solid #ebebeb;
    border-radius: 6px;
    overflow: hidden;
    padding: 16px;
    background: #fff;
}

/* ── FullCalendar overrides ── */
.fc {
    --fc-border-color: #ebebeb;
    --fc-button-bg-color: #000;
    --fc-button-border-color: #000;
    --fc-button-hover-bg-color: #222;
    --fc-button-hover-border-color: #222;
    --fc-button-active-bg-color: #e60012;
    --fc-button-active-border-color: #e60012;
    --fc-button-text-color: #fff;
    --fc-today-bg-color: rgba(230,0,18,0.05);
    --fc-page-bg-color: #fff;
    color: #111;
    font-family: inherit;
    font-size: 13px;
}

.fc .fc-toolbar-title {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #000;
}

.fc .fc-button {
    border-radius: 4px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .05em !important;
    padding: 5px 12px !important;
    font-family: inherit !important;
}

.fc .fc-button-primary:not(:disabled):active,
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background: #e60012 !important;
    border-color: #e60012 !important;
}

.fc .fc-col-header-cell-cushion {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #888;
    text-decoration: none;
}

.fc .fc-daygrid-day-number {
    font-size: 12px;
    font-weight: 600;
    color: #444;
    text-decoration: none;
}

.fc .fc-day-today .fc-daygrid-day-number {
    background: #e60012;
    color: #fff;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 2px;
}

.fc-event {
    border-radius: 3px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    padding: 2px 6px !important;
    cursor: pointer !important;
    text-transform: uppercase !important;
    letter-spacing: .03em !important;
}

.fc-event:hover { opacity: 0.85 !important; }

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #ccc;
}

.empty-state i { font-size: 36px; display: block; margin-bottom: 10px; color: #e8e8e8; }
.empty-state p {
    font-size: 13px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .08em; color: #ccc; margin: 0;
}

/* ── Modal — identical to test_drives.php ── */
.modal-content {
    border: none;
    border-radius: 8px;
    border-top: 4px solid #e60012;
    overflow: hidden;
}

.modal-header {
    background: #000;
    border-bottom: none;
    padding: 14px 20px;
}

.modal-title {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.modal-body { background: #fff; padding: 20px; }

/* Detail rows */
.detail-section { margin-bottom: 16px; }

.detail-section-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #e60012;
    margin-bottom: 8px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 12px;
}

.detail-row:last-child { border-bottom: none; }
.detail-row span { color: #888; }
.detail-row strong { color: #111; font-weight: 600; text-align: right; }

/* Status badges — same as test_drives.php */
.s-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
}

.s-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

.s-pending   { color: #92400e; background: #fef3c7; }
.s-approved  { color: #065f46; background: #d1fae5; }
.s-rejected  { color: #fff;    background: #e60012; }
.s-completed { color: #fff;    background: #000; }

/* Message boxes */
.modal-message-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 8px;
}

.modal-message-label.user-lbl    { color: #555; }
.modal-message-label.approved    { color: #065f46; }
.modal-message-label.rejected    { color: #e60012; }

.modal-message-box {
    background: #f8f8f8;
    border-left: 3px solid #e60012;
    border-radius: 0 4px 4px 0;
    padding: 12px 16px;
    font-size: 13px;
    color: #333;
    line-height: 1.7;
    margin-bottom: 12px;
}

.modal-message-box.approved { border-left-color: #065f46; }
.modal-message-box.user-msg { border-left-color: #aaa; }

.modal-footer {
    background: #f8f8f8;
    border-top: 1px solid #eee;
    padding: 12px 20px;
}

.btn-modal-close {
    height: 32px;
    padding: 0 16px;
    background: #000;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: .05em;
}
</style>
</head>
<body>

<div class="main-wrap">

 <?php include 'user_sidebar.php'; ?>

        <div class="box">

            <!-- Header -->
            <div class="section-head">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>My Test Drive Schedule</h3>
                </div>
                <a href="../tools/testdrive.php" class="btn-back">
                    <i class="fas fa-plus" style="font-size:11px;"></i> Book New Test Drive
                </a>
            </div>

            <!-- Stats pills -->
            <div class="stats-bar">
                <div class="stat-pill total">
                    <div>
                        <div class="sp-num"><?= $counts['total'] ?></div>
                        <div class="sp-label">Total</div>
                    </div>
                </div>
                <div class="stat-pill pending">
                    <div>
                        <div class="sp-num"><?= $counts['pending'] ?></div>
                        <div class="sp-label">Pending</div>
                    </div>
                </div>
                <div class="stat-pill approved">
                    <div>
                        <div class="sp-num"><?= $counts['approved'] ?></div>
                        <div class="sp-label">Approved</div>
                    </div>
                </div>
                <div class="stat-pill completed">
                    <div>
                        <div class="sp-num"><?= $counts['completed'] ?></div>
                        <div class="sp-label">Completed</div>
                    </div>
                </div>
                <div class="stat-pill rejected">
                    <div>
                        <div class="sp-num"><?= $counts['rejected'] ?></div>
                        <div class="sp-label">Rejected</div>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="toolbar-row">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
                <div class="legend">
                    <div class="legend-item"><div class="legend-dot" style="background:#d97706"></div>Pending</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#065f46"></div>Approved</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#1e40af"></div>Completed</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#e60012"></div>Rejected</div>
                </div>
            </div>

            <!-- Calendar -->
            <?php if (empty($testdrives)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No test drive bookings yet</p>
            </div>
            <?php else: ?>
            <div class="calendar-wrap">
                <div id="calendar"></div>
            </div>
            <?php endif; ?>

        </div>

</div>

<!-- EVENT MODAL -->
<div class="modal fade" id="eventModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">

        <div class="detail-section">
            <div class="detail-section-label">Vehicle</div>
            <div class="detail-row">
                <span>Model</span>
                <strong id="mVehicle"></strong>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-label">Appointment</div>
            <div class="detail-row">
                <span>Date &amp; Time</span>
                <strong id="mDateTime"></strong>
            </div>
            <div class="detail-row">
                <span>Status</span>
                <strong id="mStatus"></strong>
            </div>
            <div class="detail-row">
                <span>Booked on</span>
                <strong id="mCreated"></strong>
            </div>
        </div>

        <div class="detail-section" id="mMessagesSection">
            <div class="detail-section-label">Messages</div>

            <div id="mUserMsgWrap" style="display:none;">
                <div class="modal-message-label user-lbl">Your Message</div>
                <div class="modal-message-box user-msg" id="mUserMsg"></div>
            </div>

            <div id="mAdminMsgWrap" style="display:none;">
                <div class="modal-message-label approved">Message from Admin</div>
                <div class="modal-message-box approved" id="mAdminMsg"></div>
            </div>

            <div id="mAdminNotesWrap" style="display:none;">
                <div class="modal-message-label rejected">Rejection Reason</div>
                <div class="modal-message-box" id="mAdminNotes"></div>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button class="btn-modal-close" data-bs-dismiss="modal">Close</button>
    </div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const events = <?php echo $eventsJSON; ?>;
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function (info) { showModal(info.event); },
        height: 'auto',
        contentHeight: 'auto',
        eventDisplay: 'block',
        nowIndicator: true,
    });

    calendar.render();

    document.getElementById('statusFilter').addEventListener('change', function () {
        const s = this.value.toLowerCase();
        calendar.getEvents().forEach(ev => {
            ev.setProp('display',
                !s || ev.extendedProps.status.toLowerCase() === s ? 'auto' : 'none'
            );
        });
    });
});

function showModal(event) {
    const p = event.extendedProps;
    const s = p.status.toLowerCase();

    document.getElementById('modalTitle').textContent = event.title;
    document.getElementById('mVehicle').textContent   = p.vehicle;

    document.getElementById('mDateTime').textContent = new Date(event.start).toLocaleString('en-US', {
        weekday: 'short', year: 'numeric', month: 'short',
        day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    document.getElementById('mStatus').innerHTML =
        `<span class="s-badge s-${s}"><span class="s-dot"></span>${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span>`;

    document.getElementById('mCreated').textContent = p.createdAt
        ? new Date(p.createdAt).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' })
        : '—';

    setMsg('mUserMsgWrap',   'mUserMsg',    p.message);
    setMsg('mAdminMsgWrap',  'mAdminMsg',   p.adminMessage);
    setMsg('mAdminNotesWrap','mAdminNotes', p.adminNotes);

    // hide messages section header if nothing to show
    const hasMsg = p.message || p.adminMessage || p.adminNotes;
    document.getElementById('mMessagesSection').style.display = hasMsg ? '' : 'none';

    new bootstrap.Modal(document.getElementById('eventModal')).show();
}

function setMsg(wrapId, textId, val) {
    const wrap = document.getElementById(wrapId);
    if (val && val.trim()) {
        wrap.style.display = 'block';
        document.getElementById(textId).textContent = val;
    } else {
        wrap.style.display = 'none';
    }
}
</script>
</body>
</html>