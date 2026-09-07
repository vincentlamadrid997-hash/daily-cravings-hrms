<?php

require_once __DIR__ . "/../../config/db.php";

$employee_id = $_SESSION['employee_id'] ?? 0;


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
    AND attendance_date = CURDATE()
");

$stmt->execute([$employee_id]);

$todayAttendance = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
    AND MONTH(attendance_date)=MONTH(CURDATE())
    AND YEAR(attendance_date)=YEAR(CURDATE())
");

$stmt->execute([$employee_id]);

$monthlyAttendance = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM payroll
    WHERE employee_id = ?
");

$stmt->execute([$employee_id]);

$totalPayslips = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE employee_id = ?
    AND status='Pending'
");

$stmt->execute([$employee_id]);

$pendingLeaves = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE employee_id = ?
    AND status='Approved'
");

$stmt->execute([$employee_id]);

$approvedLeaves = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM contracts
    WHERE employee_id = ?
    AND status='Active'
");

$stmt->execute([$employee_id]);

$activeContract = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM employees
    WHERE employee_id=?
");

$stmt->execute([$employee_id]);

$profileExists = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id=?
");

$stmt->execute([$employee_id]);

$totalAttendanceHistory = $stmt->fetchColumn();

?>

<section class="employee-dashboard-stats">

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-green">

            <i class="fa-solid fa-calendar-check"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Today's Attendance</span>

            <h2><?= number_format($todayAttendance); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-blue">

            <i class="fa-solid fa-calendar-days"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Monthly Attendance</span>

            <h2><?= number_format($monthlyAttendance); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-orange">

            <i class="fa-solid fa-file-invoice-dollar"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>My Payslips</span>

            <h2><?= number_format($totalPayslips); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-red">

            <i class="fa-solid fa-hourglass-half"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Pending Leaves</span>

            <h2><?= number_format($pendingLeaves); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-purple">

            <i class="fa-solid fa-circle-check"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Approved Leaves</span>

            <h2><?= number_format($approvedLeaves); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-cyan">

            <i class="fa-solid fa-file-signature"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Active Contract</span>

            <h2><?= number_format($activeContract); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-indigo">

            <i class="fa-solid fa-user"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Profile</span>

            <h2><?= number_format($profileExists); ?></h2>

        </div>

    </div>

    <div class="employee-dashboard-stat-card">

        <div class="employee-dashboard-stat-icon employee-dashboard-teal">

            <i class="fa-solid fa-clock-rotate-left"></i>

        </div>

        <div class="employee-dashboard-stat-content">

            <span>Attendance History</span>

            <h2><?= number_format($totalAttendanceHistory); ?></h2>

        </div>

    </div>

</section>