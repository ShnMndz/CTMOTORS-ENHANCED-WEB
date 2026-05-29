<?php
session_start();
include '../db.php';

// Check admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all test drives with vehicle and user info
$testdrives = $conn->query("
    SELECT 
        td.id,
        td.user_id,
        td.vehicle_id,
        td.date,
        td.time,
        td.status,
        td.message,
        td.admin_message,
        td.admin_notes,
        td.created_at,
        v.model_name,
        v.model_variant,
        u.fullname,
        u.email,
        u.phone
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    JOIN users u ON td.user_id = u.id
    ORDER BY td.date ASC, td.time ASC
")->fetch_all(MYSQLI_ASSOC);

// Convert to JSON for calendar
$events = [];
foreach ($testdrives as $td) {
    $statusColor = match(strtolower($td['status'])) {
        'pending' => '#ffa000',
        'approved' => '#00c853',
        'rejected' => '#e8001c',
        'completed' => '#40c4ff',
        default => '#888'
    };

    $events[] = [
        'id' => $td['id'],
        'title' => $td['model_name'] . ' - ' . $td['fullname'],
        'start' => $td['date'] . 'T' . $td['time'],
        'backgroundColor' => $statusColor,
        'borderColor' => $statusColor,
        'extendedProps' => [
            'vehicle' => $td['model_name'] . ' ' . $td['model_variant'],
            'user' => $td['fullname'],
            'email' => $td['email'],
            'phone' => $td['phone'],
            'status' => $td['status'],
            'message' => $td['message'],
            'adminMessage' => $td['admin_message'],
            'adminNotes' => $td['admin_notes']
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
<title>Admin Calendar - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin_calendar.css">
<link rel="stylesheet" href="admin_dashboard.css">
</head>
<style></style>
<body>

<div class="admin-dashboard">

    <!-- SIDEBAR -->
    <?php 
    $currentPage = 'calendar';
    include '../admin_sidebar/sidebar.php';
    ?>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <div class="calendar-container">

            <div class="calendar-header">
                <div class="calendar-title">
                    <div class="fas fa-calendar"></div>
                    <h1>Test Drive Calendar</h1>
                </div>
                <div class="calendar-filters">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="calendar-wrapper">
                <div id="calendar"></div>
            </div>

        </div>

    </main>

</div>

<!-- EVENT DETAILS MODAL -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content event-modal">

            <div class="modal-header event-modal-header">
                <h5 class="modal-title" id="eventTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="event-details">

                    <div class="detail-section">
                        <h6>Vehicle Information</h6>
                        <div class="detail-row">
                            <span>Model:</span>
                            <strong id="eventVehicle"></strong>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h6>User Information</h6>
                        <div class="detail-row">
                            <span>Name:</span>
                            <strong id="eventUser"></strong>
                        </div>
                        <div class="detail-row">
                            <span>Email:</span>
                            <strong id="eventEmail"></strong>
                        </div>
                        <div class="detail-row">
                            <span>Phone:</span>
                            <strong id="eventPhone"></strong>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h6>Appointment Details</h6>
                        <div class="detail-row">
                            <span>Date & Time:</span>
                            <strong id="eventDateTime"></strong>
                        </div>
                        <div class="detail-row">
                            <span>Status:</span>
                            <strong id="eventStatus"></strong>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h6>Messages</h6>
                        <div class="message-box" id="userMessage" style="display:none;">
                            <p class="message-label">User Message:</p>
                            <p id="userMessageText"></p>
                        </div>
                        <div class="message-box" id="adminMessage" style="display:none;">
                            <p class="message-label">Admin Message:</p>
                            <p id="adminMessageText"></p>
                        </div>
                        <div class="message-box" id="adminNotes" style="display:none;">
                            <p class="message-label">Rejection Reason:</p>
                            <p id="adminNotesText"></p>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer event-modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="editBtn">Edit Appointment</button>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const events = <?php echo $eventsJSON; ?>;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function(info) {
            showEventModal(info.event);
        },
        height: 'auto',
        contentHeight: 'auto',
        eventDisplay: 'block'
    });

    calendar.render();

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function(e) {
        const status = e.target.value.toLowerCase();
        const allEvents = calendar.getEvents();
        
        allEvents.forEach(event => {
            if (!status) {
                event.setProp('display', 'auto');
            } else {
                const eventStatus = event.extendedProps.status.toLowerCase();
                event.setProp('display', eventStatus === status ? 'auto' : 'none');
            }
        });
    });
});

function showEventModal(event) {
    const props = event.extendedProps;
    
    document.getElementById('eventTitle').textContent = event.title;
    document.getElementById('eventVehicle').textContent = props.vehicle;
    document.getElementById('eventUser').textContent = props.user;
    document.getElementById('eventEmail').textContent = props.email;
    document.getElementById('eventPhone').textContent = props.phone || 'N/A';
    
    const dateObj = new Date(event.start);
    const dateString = dateObj.toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('eventDateTime').textContent = dateString;
    
    const statusBadge = document.getElementById('eventStatus');
    statusBadge.textContent = props.status.charAt(0).toUpperCase() + props.status.slice(1);
    statusBadge.className = 'status-badge status-' + props.status.toLowerCase();
    
    // Show/hide messages
    const userMsgEl = document.getElementById('userMessage');
    const adminMsgEl = document.getElementById('adminMessage');
    const adminNotesEl = document.getElementById('adminNotes');
    
    if (props.message) {
        userMsgEl.style.display = 'block';
        document.getElementById('userMessageText').textContent = props.message;
    } else {
        userMsgEl.style.display = 'none';
    }
    
    if (props.adminMessage) {
        adminMsgEl.style.display = 'block';
        document.getElementById('adminMessageText').textContent = props.adminMessage;
    } else {
        adminMsgEl.style.display = 'none';
    }
    
    if (props.adminNotes) {
        adminNotesEl.style.display = 'block';
        document.getElementById('adminNotesText').textContent = props.adminNotes;
    } else {
        adminNotesEl.style.display = 'none';
    }
    
    // Edit button
    document.getElementById('editBtn').onclick = function() {
        window.location.href = 'admin_test_drives.php?id=' + event.id;
    };
    
    new bootstrap.Modal(document.getElementById('eventModal')).show();
}
</script>

</body>
</html>