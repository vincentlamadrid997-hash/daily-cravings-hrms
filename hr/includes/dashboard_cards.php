<?php

require_once __DIR__ . "/../../config/db.php";


$activeEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
    WHERE employment_status = 'Active'
")->fetchColumn();


$pendingApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
    WHERE status = 'Pending'
")->fetchColumn();


$openJobs = $conn->query("
    SELECT COUNT(*)
    FROM job_postings
    WHERE status = 'Open'
")->fetchColumn();


$todayInterviews = $conn->query("
    SELECT COUNT(*)
    FROM interviews
    WHERE DATE(interview_date) = CURDATE()
")->fetchColumn();


$activePositions = $conn->query("
    SELECT COUNT(*)
    FROM positions
    WHERE status = 'Active'
")->fetchColumn();


$activeContracts = $conn->query("
    SELECT COUNT(*)
    FROM contracts
    WHERE status = 'Active'
")->fetchColumn();


$pendingLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE status = 'Pending'
")->fetchColumn();


$presentToday = $conn->query("
    SELECT COUNT(*)
    FROM attendance a

    INNER JOIN employees e
        ON a.employee_id = e.employee_id

    WHERE
        a.attendance_date = CURDATE()
        AND a.status = 'Present'
        AND e.employment_status = 'Active'
")->fetchColumn();

?>

<section class="hr-dashboard-stats">

    <div class="hr-stat-card hr-primary">

        <div class="hr-stat-icon hr-primary">

            <i class="fa-solid fa-users"></i>

        </div>

        <div class="hr-stat-content">

            <span>Active Employees</span>

            <h2><?= number_format($activeEmployees); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-warning">

        <div class="hr-stat-icon hr-warning">

            <i class="fa-solid fa-user-clock"></i>

        </div>

        <div class="hr-stat-content">

            <span>Pending Applicants</span>

            <h2><?= number_format($pendingApplicants); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-success">

        <div class="hr-stat-icon hr-success">

            <i class="fa-solid fa-briefcase"></i>

        </div>

        <div class="hr-stat-content">

            <span>Open Job Postings</span>

            <h2><?= number_format($openJobs); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-danger">

        <div class="hr-stat-icon hr-danger">

            <i class="fa-solid fa-calendar-check"></i>

        </div>

        <div class="hr-stat-content">

            <span>Today's Interviews</span>

            <h2><?= number_format($todayInterviews); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-primary">

        <div class="hr-stat-icon hr-primary">

            <i class="fa-solid fa-briefcase-medical"></i>

        </div>

        <div class="hr-stat-content">

            <span>Active Positions</span>

            <h2><?= number_format($activePositions); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-success">

        <div class="hr-stat-icon hr-success">

            <i class="fa-solid fa-file-signature"></i>

        </div>

        <div class="hr-stat-content">

            <span>Active Contracts</span>

            <h2><?= number_format($activeContracts); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-warning">

        <div class="hr-stat-icon hr-warning">

            <i class="fa-solid fa-calendar-minus"></i>

        </div>

        <div class="hr-stat-content">

            <span>Pending Leave Requests</span>

            <h2><?= number_format($pendingLeaves); ?></h2>

        </div>

    </div>

    <div class="hr-stat-card hr-success">

        <div class="hr-stat-icon hr-success">

            <i class="fa-solid fa-user-check"></i>

        </div>

        <div class="hr-stat-content">

            <span>Present Today</span>

            <h2><?= number_format($presentToday); ?></h2>

        </div>

    </div>

</section>