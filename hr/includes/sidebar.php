<?php

$current_page = basename($_SERVER['PHP_SELF']);


function hrActive($page)
{

    global $current_page;

    return $current_page === $page ? "active" : "";

}


?>


<aside class="hr-sidebar">

    <div class="hr-sidebar-brand-area">

        <a href="dashboard.php" class="hr-sidebar-brand">

            <div class="hr-logo-wrapper">

                <img 
                src="../assets/images/logo_2.png"
                alt="Daily Cravings Foods Inc."
                class="hr-sidebar-logo">

            </div>

            <div class="hr-brand-text">

                <h3>Daily Cravings</h3>

                <span>Foods Inc.</span>

                <small>HR Management System</small>

            </div>

        </a>

    </div>

    <nav class="hr-sidebar-menu">

        <p class="hr-sidebar-title">
            MAIN MENU
        </p>

        <a href="dashboard.php"
        class="hr-sidebar-link <?=hrActive('dashboard.php');?>">

            <i class="fa-solid fa-house"></i>

            <span>
                Dashboard
            </span>

        </a>

        <p class="hr-sidebar-title">
            RECRUITMENT MANAGEMENT
        </p>

        <a href="job_postings.php"
        class="hr-sidebar-link <?= hrActive('job_postings.php'); ?>">

            <i class="fa-solid fa-bullhorn"></i>

            <span>
                Job Postings
            </span>

        </a>

        <a href="applicants.php"
        class="hr-sidebar-link <?=hrActive('applicants.php');?>">

            <i class="fa-solid fa-users"></i>

            <span>
                Applicants
            </span>

        </a>

        <a href="interviews.php"
        class="hr-sidebar-link <?=hrActive('interviews.php');?>">

            <i class="fa-solid fa-calendar-check"></i>

            <span>
                Interviews
            </span>

        </a>

        <p class="hr-sidebar-title">
            EMPLOYEE MANAGEMENT
        </p>

        <a href="employees.php"
        class="hr-sidebar-link <?=hrActive('employees.php');?>">

            <i class="fa-solid fa-id-card"></i>

            <span>
                Employees
            </span>

        </a>

        <a href="departments.php"
        class="hr-sidebar-link <?=hrActive('departments.php');?>">

            <i class="fa-solid fa-building"></i>

            <span>
                Departments
            </span>

        </a>

        <a href="positions.php"
        class="hr-sidebar-link <?=hrActive('positions.php');?>">

            <i class="fa-solid fa-briefcase"></i>

            <span>
                Positions
            </span>

        </a>

        <p class="hr-sidebar-title">
            HR OPERATIONS
        </p>

        <a href="contracts.php"
        class="hr-sidebar-link <?=hrActive('contracts.php');?>">

            <i class="fa-solid fa-file-signature"></i>

            <span>
                Contracts
            </span>

        </a>

        <a href="leaves.php"
        class="hr-sidebar-link <?=hrActive('leaves.php');?>">

            <i class="fa-solid fa-calendar-minus"></i>

            <span>
                Leave Requests
            </span>

        </a>

        <a href="attendance.php"
        class="hr-sidebar-link <?=hrActive('attendance.php');?>">

            <i class="fa-solid fa-clock"></i>

            <span>
                Attendance
            </span>

        </a>

        <a href="payroll.php"
        class="hr-sidebar-link <?=hrActive('payroll.php');?>">

            <i class="fa-solid fa-money-check-dollar"></i>

            <span>
                Payroll
            </span>

        </a>

        <a href="resignations.php"
        class="hr-sidebar-link <?=hrActive('resignations.php');?>">

            <i class="fa-solid fa-user-minus"></i>

            <span>
                Resignations
            </span>

        </a>

        <a href="reports.php"
        class="hr-sidebar-link <?=hrActive('reports.php');?>">

            <i class="fa-solid fa-chart-line"></i>

            <span>
                Reports
            </span>

        </a>

    </nav>

    <div class="hr-sidebar-footer">

                <a href="profile.php" class="hr-sidebar-user" style="text-decoration:none; cursor:pointer;">

            <div class="hr-sidebar-user-icon">

                <i class="fa-solid fa-user-tie"></i>

            </div>

            <div>

                <strong>
                    HR Administrator
                </strong>

                <small>
                    Daily Cravings
                </small>

            </div>

        </a>

        <a href="../auth/logout.php"
        class="hr-sidebar-logout">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>
                Logout
            </span>

        </a>

    </div>

</aside>

<div class="hr-sidebar-overlay"></div>