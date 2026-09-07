<?php

$current_page = basename($_SERVER['PHP_SELF']);

function employeeActive($page)
{
    global $current_page;

    return $current_page === $page ? "active" : "";
}

?>

<aside class="emp-sidebar">

    <div class="emp-sidebar-brand-area">

        <a href="dashboard.php" class="emp-sidebar-brand">

            <div class="emp-logo-wrapper">

                <img
                    src="../assets/images/logo_2.png"
                    alt="Daily Cravings Foods Inc."
                    class="emp-sidebar-logo">

            </div>

            <div class="emp-brand-text">

                <h3>Daily Cravings</h3>

                <span>Foods Inc.</span>

                <small>Employee Portal</small>

            </div>

        </a>

    </div>

    <nav class="emp-sidebar-menu">

        <p class="emp-sidebar-title">
            MAIN MENU
        </p>

        <a href="dashboard.php"
        class="emp-sidebar-link <?= employeeActive('dashboard.php'); ?>">

            <i class="fa-solid fa-house"></i>

            <span>Dashboard</span>

        </a>

        <p class="emp-sidebar-title">
            MY ATTENDANCE
        </p>

        <a href="attendance.php"
        class="emp-sidebar-link <?= employeeActive('attendance.php'); ?>">

            <i class="fa-solid fa-clock"></i>

            <span>Time In / Time Out</span>

        </a>

        <a href="attendance_history.php"
        class="emp-sidebar-link <?= employeeActive('attendance_history.php'); ?>">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <span>Attendance History</span>

        </a>

        <p class="emp-sidebar-title">
            MY PAYROLL
        </p>

        <a href="my_payroll.php"
        class="emp-sidebar-link <?= employeeActive('my_payroll.php'); ?>">

            <i class="fa-solid fa-money-bill-wave"></i>

            <span>My Payslips</span>

        </a>

        <p class="emp-sidebar-title">
            MY LEAVES
        </p>

        <a href="leave_request.php"
        class="emp-sidebar-link <?= employeeActive('leave_request.php'); ?>">

            <i class="fa-solid fa-pen-to-square"></i>

            <span>Submit Leave Request</span>

        </a>

        <a href="leave_history.php"
        class="emp-sidebar-link <?= employeeActive('leave_history.php'); ?>">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <span>Leave History</span>

        </a>

        <p class="emp-sidebar-title">
            MY EMPLOYMENT
        </p>

        <a href="employment_contracts.php"
        class="emp-sidebar-link <?= employeeActive('employment_contracts.php'); ?>">

            <i class="fa-solid fa-file-contract"></i>

            <span>Employment Contract</span>

        </a>

        <p class="emp-sidebar-title">
            MY RESIGNATION
        </p>

        <a href="resignation_request.php"
        class="emp-sidebar-link <?= employeeActive('resignation_request.php'); ?>">

            <i class="fa-solid fa-pen-to-square"></i>

            <span>Submit Resignation Request</span>

        </a>

        <a href="resignation_history.php"
        class="emp-sidebar-link <?= employeeActive('resignation_history.php'); ?>">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <span>Resignation History</span>

        </a>

        <p class="emp-sidebar-title">
            ACCOUNT
        </p>

        <a href="profile.php"
        class="emp-sidebar-link <?= employeeActive('profile.php'); ?>">

            <i class="fa-solid fa-user"></i>

            <span>My Profile</span>

        </a>

    </nav>

    <div class="emp-sidebar-footer">

        <div class="emp-sidebar-user">

            <div class="emp-sidebar-user-icon">

                <?php if (!empty($_SESSION['profile_picture'])): ?>

                    <img
                        src="../uploads/profile/<?= htmlspecialchars($_SESSION['profile_picture']); ?>"
                        alt="Profile"
                        class="emp-sidebar-user-image">

                <?php else: ?>

                    <i class="fa-solid fa-user"></i>

                <?php endif; ?>

            </div>

            <div>

                <strong>

                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Employee'); ?>

                </strong>

                <small>

                    Employee Account

                </small>

            </div>

        </div>

        <a href="../auth/logout.php"
        class="emp-sidebar-logout">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>

<div class="emp-sidebar-overlay"></div>