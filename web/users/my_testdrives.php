<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* USER DATA */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* TEST DRIVES */
$stmt = $conn->prepare("
    SELECT td.*, v.model_name, v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE td.user_id = ?
    ORDER BY td.id DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Test Drives - CITI MOTORS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="user_dashboard.css">

<style>
/* ── Page background ── */
body { background: #f2f2f2; color: #111; }

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

/* ── Table wrapper ── */
.table-wrap {
    border: 1px solid #ebebeb;
    border-radius: 6px;
    overflow: hidden;
    margin-top: 4px;
}

/* ── Table ── */
.drives-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.drives-table thead tr {
    background: #000;
}

.drives-table thead th {
    padding: 10px 14px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #fff;
    text-align: left;
    white-space: nowrap;
    border: none;
}

.drives-table tbody tr {
    border-bottom: 1px solid #f5f5f5;
}

.drives-table tbody tr:last-child { border-bottom: none; }

.drives-table td {
    padding: 10px 14px;
    vertical-align: middle;
    color: #555;
}

/* Vehicle name */
.td-model   { font-weight: 700; color: #000; }
.td-variant { font-size: 11px; color: #bbb; margin-top: 2px; }

/* Date / time */
.td-date { color: #444; white-space: nowrap; }

/* Message preview */
.td-message {
    color: #888;
    font-style: italic;
    font-size: 11px;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Status badges ── */
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

.s-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.s-pending   { color: #92400e; background: #fef3c7; }
.s-approved  { color: #065f46; background: #d1fae5; }
.s-rejected  { color: #fff;    background: #e60012; }
.s-completed { color: #fff;    background: #000; }

/* ── Action buttons ── */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 26px;
    padding: 0 12px;
    font-size: 11px;
    font-weight: 700;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
}

.action-btn.view   { background: #065f46; color: #fff; }
.action-btn.reason { background: #e60012; color: #fff; }

.td-muted {
    font-size: 11px;
    color: #ccc;
    font-family: inherit;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #ccc;
}

.empty-state i {
    font-size: 36px;
    display: block;
    margin-bottom: 10px;
    color: #e8e8e8;
}

.empty-state p {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #ccc;
    margin: 0;
}

/* ── Back button ── */
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
}

/* ── Modals ── */
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

.modal-message-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 8px;
}

.modal-message-label.approved { color: #065f46; }
.modal-message-label.rejected { color: #e60012; }

.modal-message-box {
    background: #f8f8f8;
    border-left: 3px solid #e60012;
    border-radius: 0 4px 4px 0;
    padding: 12px 16px;
    font-size: 13px;
    color: #333;
    line-height: 1.7;
}

.modal-message-box.approved { border-left-color: #065f46; }

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

<div class="dashboard">

    <!-- SIDEBAR — untouched -->
    <?php include 'user_sidebar.php'; ?>

    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="box">

            <div class="section-head">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Test Drive History</h3>
                </div>
                <a href="user_dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left" style="font-size:11px;"></i> Back
                </a>
            </div>

            <?php if ($result->num_rows > 0): ?>

            <div class="table-wrap">
            <table class="drives-table">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()):
                $status = strtolower($row['status']);
            ?>
            <tr>
                <td>
                    <div class="td-model"><?= htmlspecialchars($row['model_name']) ?></div>
                    <div class="td-variant"><?= htmlspecialchars($row['model_variant']) ?></div>
                </td>

                <td class="td-date"><?= date("M d, Y", strtotime($row['date'])) ?></td>
                <td class="td-date"><?= date("h:i A", strtotime($row['time'])) ?></td>

                <td>
                    <div class="td-message" title="<?= htmlspecialchars($row['message']) ?>">
                        <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 30, '…')) ?>
                    </div>
                </td>

                <td>
                    <span class="s-badge s-<?= $status ?>">
                        <span class="s-dot"></span>
                        <?= ucfirst($status) ?>
                    </span>
                </td>

                <td>
                    <?php if ($status === 'rejected' && !empty($row['admin_notes'])): ?>
                        <button class="action-btn reason"
                                data-bs-toggle="modal"
                                data-bs-target="#adminModal<?= $row['id'] ?>">
                            <i class="fas fa-eye" style="font-size:10px;"></i> Reason
                        </button>

                    <?php elseif ($status === 'approved' && !empty($row['admin_message'])): ?>
                        <button class="action-btn view"
                                data-bs-toggle="modal"
                                data-bs-target="#adminModal<?= $row['id'] ?>">
                            <i class="fas fa-eye" style="font-size:10px;"></i> View
                        </button>

                    <?php else: ?>
                        <span class="td-muted">
                            <?= $status === 'pending' ? 'Waiting…' : 'No details' ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
            </table>
            </div>

            <?php else: ?>

            <div class="empty-state">
                <i class="fas fa-car"></i>
                <p>No test drive bookings yet</p>
            </div>

            <?php endif; ?>

        </div>

    </main>
</div>

<!-- MODALS -->
<?php
$result->data_seek(0);
while($row = $result->fetch_assoc()):
    $status = strtolower($row['status']);
?>
<div class="modal fade" id="adminModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            <?= $status === 'approved' ? 'Approval Message' : 'Rejection Reason' ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <?php if ($status === 'rejected'): ?>
            <div class="modal-message-label rejected">Reason for Rejection</div>
            <div class="modal-message-box">
                <?= nl2br(htmlspecialchars($row['admin_notes'] ?: 'No reason provided.')) ?>
            </div>
        <?php elseif ($status === 'approved'): ?>
            <div class="modal-message-label approved">Message from Admin</div>
            <div class="modal-message-box approved">
                <?= nl2br(htmlspecialchars($row['admin_message'] ?: 'Your test drive has been approved.')) ?>
            </div>
        <?php else: ?>
            <p style="font-size:13px;color:#888;">No additional details available.</p>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button class="btn-modal-close" data-bs-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>
<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>