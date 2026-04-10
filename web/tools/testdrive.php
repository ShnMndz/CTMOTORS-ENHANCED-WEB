<?php
include '../db.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname  = $_POST['fullname'];
    $email     = $_POST['email'];
    $contact   = $_POST['contact'];
    $car_model = $_POST['car_model'];
    $date      = $_POST['date'];
    $time      = $_POST['time'];
    $message   = $_POST['message'];

    $stmt = $conn->prepare("
        INSERT INTO test_drives 
        (fullname, email, contact, car_model, date, time, message)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssss", $fullname, $email, $contact, $car_model, $date, $time, $message);

    if ($stmt->execute()) {
        $success = "Test drive request submitted successfully!";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Test Drive Request</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">



<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">
<style>
    body {
        background: #0b0b0b;
        font-family: 'Poppins', sans-serif;
        color: #fff;
    }

    .top-bar {
        height: 6px;
        background: linear-gradient(90deg, #e60012, #111, #fff);
    }

    .form-box {
        max-width: 750px;
        margin: 50px auto;
        background: #111;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #222;
    }

    .header {
        background: linear-gradient(135deg, #e60012, #8b0000);
        text-align: center;
        padding: 25px;
    }

    .header h2 {
        margin: 0;
        font-weight: 700;
    }

    .body {
        padding: 30px;
    }

    label {
        font-size: 13px;
        color: #ccc;
    }

    .form-control {
        background: #1a1a1a;
        border: 1px solid #333;
        color: #fff;
    }

    .form-control:focus {
        border-color: #e60012;
        box-shadow: none;
        background: #1a1a1a;
        color: #fff;
    }

    .btn-submit {
        background: #e60012;
        width: 100%;
        padding: 12px;
        font-weight: bold;
        border: none;
    }

    .btn-submit:hover {
        background: #b3000f;
    }

    .alert-success {
        background: #0f5132;
        color: #fff;
        border: none;
    }

    .alert-danger {
        background: #842029;
        color: #fff;
        border: none;
    }
</style>
</head>
<body>
<!-- Navbar -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/citimotorsweb/web/includes/navbar.php'; ?>

<div class="top-bar"></div>

<div class="form-box">

    <div class="header">
        <h2>TEST DRIVE REQUEST</h2>
        <p>Mitsubishi Inspired Booking Form</p>
    </div>

    <div class="body">

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Car Model</label>
                <input type="text" name="car_model" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-submit">SUBMIT REQUEST</button>

        </form>
    </div>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>