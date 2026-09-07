<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

$page_title = "Users";

// Server-side permission enforcement: even though admin_auth.php
// already restricts this page to the "admin" role, the Roles &
// Permissions panel can still revoke individual permissions
// (including from Admin), so this check is enforced independently.
requirePermission($conn, 'view_users', 'dashboard.php');

$currentRole = $_SESSION['role'] ?? '';
$canAddUsers          = userHasPermission($conn, $currentRole, 'add_users');
$canEditUsers         = userHasPermission($conn, $currentRole, 'edit_users');
$canViewUserDetails   = userHasPermission($conn, $currentRole, 'view_user_details');
$canToggleUserStatus  = userHasPermission($conn, $currentRole, 'toggle_user_status');


$success = $_SESSION['user_success'] ?? "";
$error   = $_SESSION['user_error'] ?? "";

unset($_SESSION['user_success']);
unset($_SESSION['user_error']);


$totalUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
")->fetchColumn();


$activeUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE status = 'active'
")->fetchColumn();


$inactiveUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE status = 'inactive'
")->fetchColumn();


$adminUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'admin'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

SELECT

    u.user_id,
    u.employee_id,
    u.full_name,
    u.email,
    u.role,
    u.gender,
    u.status,
    u.last_login,
    u.created_at,
    u.profile_picture,

    e.employee_code

FROM users u

LEFT JOIN employees e

    ON u.employee_id = e.employee_id

WHERE 1

";


$params = [];


if ($search !== "") {

    $sql .= "

        AND (

            u.full_name LIKE ?
            OR u.email LIKE ?
            OR u.role LIKE ?
            OR u.status LIKE ?
            OR e.employee_code LIKE ?

        )

    ";

    $keyword = "%{$search}%";

    $params = [

        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword

    ];

}


$sql .= "

ORDER BY

    u.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>

        #usersSummaryGrid {
            grid-template-columns: repeat(2, 1fr);
        }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

                <div class="admin-user-summary-grid" id="usersSummaryGrid">

                <div class="admin-user-summary-card blue">

                    <div class="admin-user-summary-icon">

                        <i class="fa-solid fa-users"></i>

                    </div>

                    <div class="admin-user-summary-content">

                        <span>Total Users</span>

                        <h2>
                            <?= number_format($totalUsers); ?>
                        </h2>

                        <p>All registered system users</p>

                    </div>

                </div>

                <div class="admin-user-summary-card green">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>

                    <div class="admin-user-summary-content">

                        <span>Active Users</span>

                        <h2>
                            <?= number_format($activeUsers); ?>
                        </h2>

                        <p>Currently active accounts</p>

                    </div>

                </div>

                <div class="admin-user-summary-card red">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>

                    <div class="admin-user-summary-content">

                        <span>Inactive Users</span>

                        <h2>
                            <?= number_format($inactiveUsers); ?>
                        </h2>

                        <p>Disabled user accounts</p>

                    </div>

                </div>

                <div class="admin-user-summary-card purple">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>

                    <div class="admin-user-summary-content">

                        <span>Administrators</span>

                        <h2>
                            <?= number_format($adminUsers); ?>
                        </h2>

                        <p>Full system access</p>

                    </div>

                </div>

            </div>

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-users"></i>
                        Users
                    </h1>

                    <p>Manage administrator, HR, and employee accounts.</p>

                </div>

                <?php if ($canAddUsers): ?>

                    <a
                        href="user_add.php"
                        class="admin-user-add-btn"
                    >

                        <i class="fa-solid fa-plus"></i>
                        Add User
                    </a>

                <?php endif; ?>

            </div>

            <div class="admin-user-table-card">

                <div class="admin-user-search">

                    <div class="admin-user-search-box">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            id="userSearch"
                            placeholder="Search users..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <div class="admin-user-table-wrapper">

                    <table class="admin-user-table">

                        <thead>

                            <tr>

                                <th>User</th>
                                <th>Employee Code</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th width="170">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody id="userTableBody">

                            <?php if (!empty($users)): ?>

                                <?php foreach ($users as $user): ?>

                                    <tr class="user-row">

                                        <td>

                                            <div class="admin-user-name">

                                                <div class="admin-user-avatar">

                                                    <?php if (!empty($user["profile_picture"])): ?>

                                                        <img
                                                            src="../uploads/profile/<?= htmlspecialchars($user["profile_picture"]); ?>"
                                                            alt="Profile"
                                                        >

                                                    <?php else: ?>

                                                        <i class="fa-solid fa-user"></i>

                                                    <?php endif; ?>

                                                </div>

                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars($user["full_name"]); ?>

                                                    </strong>

                                                    <br>

                                                    <small>

                                                        <?= ucfirst($user["gender"] ?? "N/A"); ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars($user["employee_code"] ?? "N/A"); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($user["email"]); ?>
                                        </td>

                                        <td>

                                            <span class="admin-user-role">
                                                <?= ucfirst(htmlspecialchars($user["role"])); ?>
                                            </span>

                                        </td>

                                        <td>

                                            <span class="admin-user-status <?= strtolower($user["status"]); ?>">

                                                <?php if ($user["status"] == "active"): ?>

                                                    <i class="fa-solid fa-circle-check"></i>

                                                <?php elseif ($user["status"] == "inactive"): ?>

                                                    <i class="fa-solid fa-circle-xmark"></i>

                                                <?php else: ?>

                                                    <i class="fa-solid fa-lock"></i>

                                                <?php endif; ?>

                                                <?= ucfirst(htmlspecialchars($user["status"])); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php

                                                if (!empty($user["last_login"])) {

                                                    echo date(

                                                        "M d, Y h:i A",

                                                        strtotime($user["last_login"])

                                                    );

                                                } else {

                                                    echo "Never";

                                                }

                                            ?>

                                        </td>

                                        <td>

                                            <div class="admin-user-actions">

                                                <?php if ($canViewUserDetails): ?>

                                                    <form
                                                        action="user_view.php"
                                                        method="POST"
                                                    >

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="<?= $user["user_id"]; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="admin-user-action view"
                                                            title="View User"
                                                        >

                                                            <i class="fa-solid fa-eye"></i>

                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                                <?php if ($canEditUsers): ?>

                                                    <form
                                                        action="user_edit.php"
                                                        method="POST"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="<?= $user["user_id"]; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="admin-user-action edit"
                                                            title="Edit User"
                                                        >

                                                            <i class="fa-solid fa-pen"></i>

                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                                <?php if ($canToggleUserStatus): ?>

                                                    <form
                                                        action="user_status.php"
                                                        method="POST"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="<?= $user["user_id"]; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="admin-user-action status <?= strtolower($user["status"]); ?>"
                                                            title="<?= $user["status"] == "active"
                                                                ? "Deactivate User"
                                                                : "Activate User"; ?>"

                                                        >

                                                            <?php if ($user["status"] == "active"): ?>

                                                                <i class="fa-solid fa-toggle-on"></i>

                                                            <?php else: ?>

                                                                <i class="fa-solid fa-toggle-off"></i>

                                                            <?php endif; ?>
                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            <tr
                                id="noUserResult"
                                style="display:none;"

                            >

                                <td colspan="7">

                                    <div class="admin-user-empty">

                                        <i class="fa-solid fa-magnifying-glass"></i>

                                        <h3>No User Found</h3>

                                        <p>No records matched your search.</p>

                                    </div>

                                </td>

                            </tr>

                            <?php if (empty($users)): ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="admin-user-empty">

                                            <i class="fa-solid fa-users"></i>

                                            <h3>No Users Found</h3>

                                            <p>There are currently no registered users.</p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

        </main>

    </div>

</div>

<div

    id="userAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error="<?= htmlspecialchars($error); ?>"

></div>


<script src="../assets/js/admin.js"></script>


</body>

</html>