<?php

$current_page = basename($_SERVER['PHP_SELF']);

function adminActive($page)
{
    global $current_page;
    return ($current_page === $page) ? "active" : "";
}

?>

<aside class="admin-sidebar">

    <div class="admin-sidebar-brand-area">

        <a href="dashboard.php" class="admin-sidebar-brand">

            <div class="admin-logo-wrapper">

                <img
                    src="../assets/images/logo_2.png"
                    alt="Daily Cravings Foods Inc."
                    class="admin-sidebar-logo">

            </div>

            <div class="admin-brand-text">

                <h3>Daily Cravings</h3>

                <span>Foods Inc.</span>

                <small>Admin Management System</small>

            </div>

        </a>

    </div>

    <nav class="admin-sidebar-menu">

        <p class="admin-sidebar-title">
            Main Menu
        </p>

        <a href="dashboard.php"
            class="admin-sidebar-link <?= adminActive('dashboard.php'); ?>">

            <i class="fa-solid fa-house"></i>

            <span>Dashboard</span>

        </a>

        <p class="admin-sidebar-title">
            User Management
        </p>

        <a href="users.php"
            class="admin-sidebar-link <?= adminActive('users.php'); ?>">

            <i class="fa-solid fa-users"></i>

            <span>Users</span>

        </a>

        <a href="roles_permissions.php"
            class="admin-sidebar-link <?= adminActive('roles_permissions.php'); ?>">

            <i class="fa-solid fa-user-shield"></i>

            <span>Roles & Permissions</span>

        </a>

        <p class="admin-sidebar-title">
            HR Management
        </p>

            <a href="employees.php"
            class="admin-sidebar-link <?= adminActive('employees.php'); ?>">

            <i class="fa-solid fa-id-card"></i>

            <span>Employees</span>

                </a>

            <!-- Departments -->
        <a href="departments.php"
            class="admin-sidebar-link <?= adminActive('departments.php'); ?>">
            <i class="fa-solid fa-sitemap"></i>
            <span>Departments</span>
        </a>

        <!-- Positions -->
        <a href="positions.php"
            class="admin-sidebar-link <?= adminActive('positions.php'); ?>">
            <i class="fa-solid fa-briefcase"></i>
            <span>Positions</span>
        </a>

        <p class="admin-sidebar-title">
            System Management
        </p>

        <a href="audit_logs.php"
            class="admin-sidebar-link <?= adminActive('audit_logs.php'); ?>">

            <i class="fa-solid fa-file-lines"></i>

            <span>Audit Logs</span>

        </a>

        <a href="system_settings.php"
            class="admin-sidebar-link <?= adminActive('system_settings.php'); ?>">

            <i class="fa-solid fa-gears"></i>

            <span>System Settings</span>

        </a>

        <a href="login_attempts.php"
            class="admin-sidebar-link <?= adminActive('login_attempts.php'); ?>">

            <i class="fa-solid fa-lock"></i>

            <span>Login Attempts</span>

        </a>

        <p class="admin-sidebar-title">
            Reports
        </p>

        <a href="reports.php"
            class="admin-sidebar-link <?= adminActive('reports.php'); ?>">

            <i class="fa-solid fa-chart-column"></i>

            <span>Reports</span>

        </a>

    </nav>

    <div class="admin-sidebar-footer">

                <a href="profile.php" class="admin-sidebar-user" style="text-decoration:none; cursor:pointer;">

            <div class="admin-sidebar-user-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>

            <div>

                <strong>
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?>
                </strong>

                <small>
                    System Administrator
                </small>

            </div>

        </a>

        <a
            href="../auth/logout.php"
            class="admin-sidebar-logout">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>

<div class="admin-sidebar-overlay"></div>

<script>

    (function () {

        var activeLink = document.querySelector(".admin-sidebar-link.active");

        // Find whichever ancestor is actually the scrolling element,
        // instead of assuming a specific class — keeps this working
        // even if admin.css changes which element scrolls.
        function findScrollableParent(el) {

            while (el && el !== document.body) {

                var style = getComputedStyle(el);

                if (
                    el.scrollHeight > el.clientHeight
                    && /(auto|scroll)/.test(style.overflowY)
                ) {
                    return el;
                }

                el = el.parentElement;

            }

            return null;

        }

        var scrollContainer = activeLink
            ? findScrollableParent(activeLink)
            : null;

        if (!scrollContainer) {
            return;
        }

        var storageKey = "adminSidebarScrollTop";
        var savedScroll = sessionStorage.getItem(storageKey);

        if (savedScroll !== null) {

            // Restore exactly where the user left it.
            scrollContainer.scrollTop = parseInt(savedScroll, 10);

        } else if (activeLink) {

            // First visit this session: just bring the current
            // section into view instead of leaving it hidden below
            // the fold.
            activeLink.scrollIntoView({ block: "center" });

        }

        scrollContainer.addEventListener("scroll", function () {
            sessionStorage.setItem(storageKey, scrollContainer.scrollTop);
        });

    })();

</script>