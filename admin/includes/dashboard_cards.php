<?php

require_once __DIR__ . "/../../config/db.php";


$totalUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
")->fetchColumn();


$activeUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE status = 'active'
")->fetchColumn();



$hrAccounts = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'hr'
")->fetchColumn();


$employeeAccounts = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'employee'
")->fetchColumn();


$totalEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
")->fetchColumn();


$totalApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
")->fetchColumn();


$loginAttempts = $conn->query("
    SELECT COUNT(*)
    FROM login_attempts
    WHERE DATE(last_attempt) = CURDATE()
")->fetchColumn();


$auditLogs = $conn->query("
    SELECT COUNT(*)
    FROM audit_logs
    WHERE DATE(date_created) = CURDATE()
")->fetchColumn();

?>

<section class="admin-dashboard-stats">

    <div class="admin-stat-card admin-primary">

        <div class="admin-stat-icon admin-primary">

            <i class="fa-solid fa-users"></i>

        </div>

        <div class="admin-stat-content">

            <span>Total Users</span>

            <h2>
                <?= number_format($totalUsers); ?>
            </h2>

        </div>

    </div>

    <div class="admin-stat-card admin-success">

        <div class="admin-stat-icon admin-success">

            <i class="fa-solid fa-user-check"></i>

        </div>

        <div class="admin-stat-content">

            <span>Active Users</span>

            <h2>
                <?= number_format($activeUsers); ?>
            </h2>

        </div>

    </div>

    <div class="admin-stat-card admin-warning">

        <div class="admin-stat-icon admin-warning">

            <i class="fa-solid fa-user-tie"></i>

        </div>

        <div class="admin-stat-content">

            <span>HR Accounts</span>

            <h2>
                <?= number_format($hrAccounts); ?>
            </h2>

        </div>

    </div>

    <div class="admin-stat-card admin-danger">

        <div class="admin-stat-icon admin-danger">

            <i class="fa-solid fa-id-badge"></i>

        </div>

        <div class="admin-stat-content">

            <span>Employee Accounts</span>

            <h2>
                <?= number_format($employeeAccounts); ?>
            </h2>

        </div>


    </div>

    <div class="admin-stat-card admin-primary">

        <div class="admin-stat-icon admin-primary">

            <i class="fa-solid fa-user-group"></i>

        </div>

        <div class="admin-stat-content">

            <span>Total Employees</span>

            <h2>
                <?= number_format($totalEmployees); ?>
            </h2>

        </div>

    </div>

    <div class="admin-stat-card admin-success">

        <div class="admin-stat-icon admin-success">

            <i class="fa-solid fa-user-plus"></i>

        </div>

        <div class="admin-stat-content">

            <span>Total Applicants</span>

            <h2>
                <?= number_format($totalApplicants); ?>
            </h2>

        </div>


    </div>

    <div class="admin-stat-card admin-warning">

        <div class="admin-stat-icon admin-warning">

            <i class="fa-solid fa-right-to-bracket"></i>

        </div>

        <div class="admin-stat-content">

            <span>Login Attempts Today</span>

            <h2>
                <?= number_format($loginAttempts); ?>
            </h2>

        </div>


    </div>

    <div class="admin-stat-card admin-danger">

        <div class="admin-stat-icon admin-danger">

            <i class="fa-solid fa-clock-rotate-left"></i>

        </div>

        <div class="admin-stat-content">

            <span>Audit Logs Today</span>

            <h2>
                <?= number_format($auditLogs); ?>
            </h2>

        </div>

    </div>

</section>