<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

$page_title = "Roles & Permissions";

/*
 * Only HR has permissions that are actually enforceable right now:
 *
 * - Admin is a hardcoded superuser (see config/permissions.php) and
 *   always passes every permission check, so nothing here can ever
 *   change what Admin can do.
 *
 * - Users management (users.php) is gated by admin_auth.php, which
 *   only lets the "admin" role through. HR and Employee sessions are
 *   redirected before any permission check runs, so a Users toggle
 *   for HR/Employee could never take effect. It is intentionally not
 *   shown here.
 *
 * - The regular "employee" role can never reach users.php (admin-only)
 *   or the Employees module (hr-only, gated by hr_auth.php), so it has
 *   no permissions that could ever apply either.
 *
 * That leaves HR's access to the Employees module (employees.php,
 * employee_add.php, employee_edit.php, employee_view.php,
 * employee_status.php) as the only permission set this panel can
 * meaningfully control.
 */

$success = $_SESSION['rp_success'] ?? "";
$error   = $_SESSION['rp_error'] ?? "";

unset($_SESSION['rp_success']);
unset($_SESSION['rp_error']);


// Employees-module permissions.
$employeePermissionKeys = [
    'view_employees',
    'add_employees',
    'edit_employees',
    'view_employee_details',
    'toggle_employee_status',
];

// Departments-module permissions.
$departmentPermissionKeys = [
    'view_departments',
    'add_departments',
    'edit_departments',
    'view_department_details',
    'toggle_department_status',
];

// Positions-module permissions.
$positionPermissionKeys = [
    'view_positions',
    'add_positions',
    'edit_positions',
    'view_position_details',
    'toggle_position_status',
];

// Every key this panel is allowed to touch. IMPORTANT: the Save
// logic below deletes all of HR's permissions and only re-inserts
// keys found in this combined list — any permission key NOT in
// here would be silently wiped out on every save. When adding a
// new module's permissions to this page, its keys must be added
// to this merge too.
$manageablePermissionKeys = array_merge(
    $employeePermissionKeys,
    $departmentPermissionKeys,
    $positionPermissionKeys
);


// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    requireCSRFToken("roles_permissions.php");

    $allPermissions = getAllPermissions($conn);
    $checkedKeys    = $_POST['permissions'] ?? [];

    try {

        $conn->beginTransaction();

        $delStmt = $conn->prepare("
            DELETE FROM role_permissions
            WHERE role = 'hr'
        ");
        $delStmt->execute();

        $insStmt = $conn->prepare("
            INSERT INTO role_permissions (role, permission_id)
            VALUES ('hr', ?)
            ON DUPLICATE KEY UPDATE role = role
        ");

        foreach ($allPermissions as $perm) {

            if (
                in_array($perm['permission_key'], $manageablePermissionKeys, true)
                && in_array($perm['permission_key'], $checkedKeys, true)
            ) {
                $insStmt->execute([$perm['permission_id']]);
            }

        }

        $conn->commit();

        $_SESSION['rp_success'] = "HR permissions updated.";

    } catch (Exception $e) {

        $conn->rollBack();
        $_SESSION['rp_error'] = "Failed to update permissions. Please try again.";

    }

    header("Location: roles_permissions.php");
    exit();

}


$allPermissions    = getAllPermissions($conn);
$rolePermissionMap = getRolePermissionMap($conn);

$employeePermissions = array_filter(
    $allPermissions,
    fn($perm) => in_array($perm['permission_key'], $employeePermissionKeys, true)
);

$departmentPermissions = array_filter(
    $allPermissions,
    fn($perm) => in_array($perm['permission_key'], $departmentPermissionKeys, true)
);

$positionPermissions = array_filter(
    $allPermissions,
    fn($perm) => in_array($perm['permission_key'], $positionPermissionKeys, true)
);

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>

        .rp-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .rp-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            background: #F5F8FF;
            border-bottom: 1px solid #E6ECF5;
        }

        .rp-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #4a6cf7;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .rp-card-header h2 {
            margin: 0;
            font-size: 16px;
            color: #003DA5;
            font-weight: 700;
        }

        .rp-card-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #78909C;
        }

        .rp-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #4a6cf7;
            color: #E8ECFF;
            border-left: 4px solid #3457D5;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 13px;
            margin: 0 0 20px;
        }

        .rp-note i {
            color: #fff;
        }

        .rp-permission-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 24px;
            border-bottom: 1px solid #f0f0f0;
        }

        .rp-permission-row:last-child {
            border-bottom: none;
        }

        .rp-permission-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #EEF2FF;
            color: #4a6cf7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .rp-permission-info {
            flex: 1;
        }

        .rp-permission-info strong {
            display: block;
            font-size: 15px;
            color: #333;
        }

        .rp-permission-info small {
            color: #888;
            font-size: 13px;
        }

        .rp-switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
            flex-shrink: 0;
        }

        .rp-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .rp-switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 24px;
            transition: .15s;
        }

        .rp-switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            border-radius: 50%;
            transition: .15s;
        }

        .rp-switch input:checked + .rp-switch-slider {
            background-color: #4a6cf7;
        }

        .rp-switch input:checked + .rp-switch-slider:before {
            transform: translateX(22px);
        }

        .rp-save-bar {
            display: flex;
            justify-content: flex-end;
            padding: 16px 20px;
        }

        #rpSaveBtn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-user-shield"></i>
                        Roles &amp; Permissions
                    </h1>

                                        <p>Control what HR accounts can do on the Employees, Departments, and Positions pages.</p>

                </div>

            </div>

                        <p class="rp-note">
                <i class="fa-solid fa-circle-info"></i>
                Admin always has full access and is not shown here.
                Users management is Admin-only and has no configurable permissions.
                The Employee role has no access to any permission-gated page.
            </p>

                        <?php
                $permissionMeta = [
                    'view_employees'         => ['fa-eye',           'View the employee list.'],
                    'add_employees'          => ['fa-user-plus',     'Add new employee records.'],
                    'edit_employees'         => ['fa-user-pen',      'Edit existing employee records.'],
                    'view_employee_details'  => ['fa-address-card',  "Open a single employee's full profile."],
                    'toggle_employee_status' => ['fa-toggle-on',     'Activate or deactivate an employee.'],

                    'view_departments'          => ['fa-eye',          'View the department list.'],
                    'add_departments'           => ['fa-square-plus',  'Add new department records.'],
                    'edit_departments'          => ['fa-pen',          'Edit existing department records.'],
                    'view_department_details'   => ['fa-address-card', "Open a single department's full details."],
                    'toggle_department_status'  => ['fa-toggle-on',    'Activate or deactivate a department.'],

                    'view_positions'          => ['fa-eye',          'View the position list.'],
                    'add_positions'           => ['fa-square-plus',  'Add new position records.'],
                    'edit_positions'          => ['fa-pen',          'Edit existing position records.'],
                    'view_position_details'   => ['fa-address-card', "Open a single position's full details."],
                    'toggle_position_status'  => ['fa-toggle-on',    'Activate or deactivate a position.'],
                ];
            ?>

            <form method="POST" action="roles_permissions.php" id="rpForm">
                <?php csrfField(); ?>

            <div class="rp-card" style="margin-bottom: 20px;">

                <div class="rp-card-header">

                    <div class="rp-card-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h2>Employees</h2>
                        <p>Permissions for the Employees module.</p>
                    </div>

                </div>

                    <?php foreach ($employeePermissions as $perm): ?>

                        <?php
                            $isChecked = isset($rolePermissionMap['hr'][$perm['permission_key']]);
                            $meta = $permissionMeta[$perm['permission_key']] ?? ['fa-key', ''];
                        ?>

                        <div class="rp-permission-row">

                            <div class="rp-permission-icon">
                                <i class="fa-solid <?= $meta[0]; ?>"></i>
                            </div>

                            <div class="rp-permission-info">
                                <strong><?= htmlspecialchars($perm['permission_label']); ?></strong>
                                <?php if ($meta[1]): ?>
                                    <small><?= htmlspecialchars($meta[1]); ?></small>
                                <?php endif; ?>
                            </div>

                            <label class="rp-switch">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    class="rp-permission-toggle"
                                    value="<?= htmlspecialchars($perm['permission_key']); ?>"
                                    data-initial="<?= $isChecked ? '1' : '0'; ?>"
                                    <?= $isChecked ? 'checked' : ''; ?>
                                >
                                <span class="rp-switch-slider"></span>
                            </label>

                        </div>

                    <?php endforeach; ?>

            </div>

            <div class="rp-card" style="margin-bottom: 20px;">

                <div class="rp-card-header">

                    <div class="rp-card-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <div>
                        <h2>Departments</h2>
                        <p>Permissions for the Departments module.</p>
                    </div>

                </div>

                    <?php foreach ($departmentPermissions as $perm): ?>

                        <?php
                            $isChecked = isset($rolePermissionMap['hr'][$perm['permission_key']]);
                            $meta = $permissionMeta[$perm['permission_key']] ?? ['fa-key', ''];
                        ?>

                        <div class="rp-permission-row">

                            <div class="rp-permission-icon">
                                <i class="fa-solid <?= $meta[0]; ?>"></i>
                            </div>

                            <div class="rp-permission-info">
                                <strong><?= htmlspecialchars($perm['permission_label']); ?></strong>
                                <?php if ($meta[1]): ?>
                                    <small><?= htmlspecialchars($meta[1]); ?></small>
                                <?php endif; ?>
                            </div>

                            <label class="rp-switch">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    class="rp-permission-toggle"
                                    value="<?= htmlspecialchars($perm['permission_key']); ?>"
                                    data-initial="<?= $isChecked ? '1' : '0'; ?>"
                                    <?= $isChecked ? 'checked' : ''; ?>
                                >
                                <span class="rp-switch-slider"></span>
                            </label>

                        </div>

                    <?php endforeach; ?>

            </div>

            <div class="rp-card">

                <div class="rp-card-header">

                    <div class="rp-card-icon">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>

                    <div>
                        <h2>Positions</h2>
                        <p>Permissions for the Positions module.</p>
                    </div>

                </div>

                    <?php foreach ($positionPermissions as $perm): ?>

                        <?php
                            $isChecked = isset($rolePermissionMap['hr'][$perm['permission_key']]);
                            $meta = $permissionMeta[$perm['permission_key']] ?? ['fa-key', ''];
                        ?>

                        <div class="rp-permission-row">

                            <div class="rp-permission-icon">
                                <i class="fa-solid <?= $meta[0]; ?>"></i>
                            </div>

                            <div class="rp-permission-info">
                                <strong><?= htmlspecialchars($perm['permission_label']); ?></strong>
                                <?php if ($meta[1]): ?>
                                    <small><?= htmlspecialchars($meta[1]); ?></small>
                                <?php endif; ?>
                            </div>

                            <label class="rp-switch">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    class="rp-permission-toggle"
                                    value="<?= htmlspecialchars($perm['permission_key']); ?>"
                                    data-initial="<?= $isChecked ? '1' : '0'; ?>"
                                    <?= $isChecked ? 'checked' : ''; ?>
                                >
                                <span class="rp-switch-slider"></span>
                            </label>

                        </div>

                    <?php endforeach; ?>

                    <div class="rp-save-bar">

                        <button type="submit" class="admin-user-add-btn" id="rpSaveBtn" disabled>
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save HR Permissions
                        </button>

                    </div>

            </div>

            </form>

        </section>

        </main>

    </div>

</div>

<div
    id="userAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error="<?= htmlspecialchars($error); ?>"
></div>

<script>

    (function () {

        var form    = document.getElementById("rpForm");
        var saveBtn = document.getElementById("rpSaveBtn");
        var toggles = form
            ? form.querySelectorAll(".rp-permission-toggle")
            : [];

        if (!form || !saveBtn || toggles.length === 0) {
            return;
        }

        function hasChanges() {

            for (var i = 0; i < toggles.length; i++) {

                var toggle       = toggles[i];
                var initialState = toggle.getAttribute("data-initial") === "1";

                if (toggle.checked !== initialState) {
                    return true;
                }

            }

            return false;

        }

        function refreshSaveState() {
            saveBtn.disabled = !hasChanges();
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener("change", refreshSaveState);
        });

        // Guard against a stray/duplicate submit if the button is
        // ever re-enabled programmatically without a real change,
        // and confirm with the user before actually submitting.
        var isConfirmed = false;

        form.addEventListener("submit", function (event) {

            if (!hasChanges()) {
                event.preventDefault();
                return;
            }

            if (isConfirmed) {
                // Already confirmed via the SweetAlert dialog; let it through.
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: "Are you sure want to save?",
                text: "This updates what HR can do on the Employees page.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Confirm",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#4a6cf7",
                reverseButtons: true
            }).then(function (result) {

                if (result.isConfirmed) {
                    isConfirmed = true;
                    form.submit();
                }

            });

        });

    })();

</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>