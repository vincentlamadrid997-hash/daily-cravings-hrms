<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Add User";


$success = $_SESSION['user_success'] ?? "";
$error   = $_SESSION['user_error'] ?? "";

unset($_SESSION['user_success']);
unset($_SESSION['user_error']);


$stmt = $conn->query("

    SELECT

        employee_id,
        employee_code,
        email,

        CONCAT(
            first_name,
            ' ',
            IFNULL(middle_name,''),
            ' ',
            last_name
        ) AS employee_name

        FROM employees

    WHERE employment_status = 'Active'

    AND employee_id NOT IN (

        SELECT employee_id
        FROM users
        WHERE employee_id IS NOT NULL

    )

    ORDER BY first_name ASC

");


$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet" href="../assets/css/crud_admin.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                            <i class="fa-solid fa-user-plus"></i>
                            Add User
                        </h1>

                        <p>Create a new system account for an employee or administrator.</p>

                    </div>

                    <a
                        href="users.php"
                        class="admin-user-back-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Users
                    </a>

                </div>

                <form
                    id="userForm"
                    action="user_store.php"
                    method="POST"
                    enctype="multipart/form-data"
                    autocomplete="off"
                    novalidate
                >

                    <?php csrfField(); ?>

                    <div class="admin-user-card">

                        <div class="admin-user-card-header">

                            <div class="admin-user-card-icon">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>

                            <div>

                                <h2>User Account Information</h2>

                                <p>Select an employee to automatically generate account details.</p>

                            </div>

                        </div>

                        <div class="admin-user-grid">

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Full Name *
                                </label>

                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    maxlength="150"
                                    required
                                    placeholder="Employee full name, or type manually"
                                >

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-envelope"></i>
                                    Email Address *
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    maxlength="150"
                                    required
                                    placeholder="Employee email address, or type manually"
                                >

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-lock"></i>
                                    Password *
                                </label>

                             <div class="password-box">

                                <input
                                        type="text"
                                        id="password"
                                        name="password"
                                        minlength="8"
                                        required
                                        placeholder="Password"
                                    >

                                </div>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-shield-halved"></i>
                                    Confirm Password *
                                </label>

                                <div class="password-box">
                                <input
                                        type="text"
                                        id="confirm_password"
                                        name="confirm_password"
                                        minlength="8"
                                        disabled
                                        placeholder="Confirm password"
                                    >

                                </div>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-user-gear"></i>
                                    User Role *
                                </label>

                                <select
                                    name="role"
                                    required
                                >

                                    <option value="">
                                        Select Role
                                    </option>

                                    <option value="admin">
                                        Administrator
                                    </option>

                                    <option value="hr">
                                        HR Staff
                                    </option>

                                    <option value="employee">
                                        Employee
                                    </option>

                                </select>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-venus-mars"></i>
                                    Gender *
                                </label>

                                <select
                                    name="gender"
                                    required
                                >

                                    <option value="">
                                        Select Gender
                                    </option>

                                    <option value="Male">
                                        Male
                                    </option>

                                    <option value="Female">
                                        Female
                                    </option>

                                </select>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-circle-check"></i>
                                    Account Status *
                                </label>

                                <select
                                    name="status"
                                    required
                                >

                                    <option value="active">
                                        Active
                                    </option>

                                    <option value="inactive">
                                        Inactive
                                    </option>

                                    <option value="locked">
                                        Locked
                                    </option>

                                </select>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group">

                                <label id="linkedEmployeeLabel">
                                    <i class="fa-solid fa-id-card"></i>
                                    Linked Employee (optional)
                                </label>

                                <select
                                    name="employee_id"
                                    id="employee_id"
                                >

                                    <option value="">
                                        Select Employee
                                    </option>

                                    <?php foreach($employees as $employee): ?>

                                        <option
                                            value="<?= $employee['employee_id']; ?>"
                                            data-name="<?= htmlspecialchars($employee['employee_name']); ?>"
                                            data-email="<?= htmlspecialchars($employee['email']); ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $employee['employee_code']
                                                . " - "
                                                . $employee['employee_name']
                                            ); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <small class="admin-error-message"></small>

                            </div>

                            <div class="admin-user-group admin-user-full">

                                <label>
                                    <i class="fa-solid fa-image"></i>
                                    Profile Picture
                                </label>

                                <input
                                    type="file"
                                    name="profile_picture"
                                    id="profile_picture"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small class="admin-user-help">
                                    Accepted formats:
                                    JPG, JPEG, PNG, WEBP
                                    (Maximum 5MB)
                                </small>

                            </div>

                            <div class="admin-user-group admin-user-full">

                                <label>
                                    <i class="fa-solid fa-eye"></i>
                                    Profile Preview
                                </label>

                                <div class="admin-user-image-preview">

                                    <img
                                        src="../assets/images/default-profile.png"
                                        id="previewImage"
                                        alt="Profile Preview"
                                    >

                                </div>

                            </div>

                        </div>

                        <div class="admin-user-actions">

                            <button
                                type="submit"
                                class="admin-user-btn save"
                            >

                                <i class="fa-solid fa-floppy-disk"></i>
                                Save User
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

    const roleSelect = document.getElementById("role") || document.querySelector('select[name="role"]');
    const employeeSelect = document.getElementById("employee_id");
    const linkedEmployeeLabel = document.getElementById("linkedEmployeeLabel");

    function updateEmployeeRequirement() {

        const role = roleSelect.value;

        if (role === "hr" || role === "employee") {

            employeeSelect.required = true;

            linkedEmployeeLabel.innerHTML =
                '<i class="fa-solid fa-id-card"></i> Linked Employee <span style="color:#e74c3c;">*</span>';

        } else {

            employeeSelect.required = false;

            linkedEmployeeLabel.innerHTML =
                '<i class="fa-solid fa-id-card"></i> Linked Employee (optional)';

        }

    }

    if (roleSelect) {
        roleSelect.addEventListener("change", updateEmployeeRequirement);
        updateEmployeeRequirement();
    }

    const fullNameInput = document.getElementById("full_name");
    const passwordInput = document.getElementById("password");

    // Only auto-fill if the Admin hasn't started typing their own
    // password yet — never overwrite something they've already entered.
    let passwordManuallyEdited = false;

    passwordInput.addEventListener("input", function () {
        passwordManuallyEdited = true;
    });

    function generateDefaultPassword(fullName) {

        const firstName = fullName.trim().split(/\s+/)[0] || "";

        const cleanFirstName = firstName
            .toLowerCase()
            .replace(/[^a-z0-9]/g, "");

        if (!cleanFirstName) {
            return "";
        }

        const year = new Date().getFullYear();

        return cleanFirstName + "@" + year;

    }

    fullNameInput.addEventListener("input", function () {

        if (passwordManuallyEdited) {
            return;
        }

        const generated = generateDefaultPassword(fullNameInput.value);

        passwordInput.value = generated;

    });

})();

</script>

<script src="../assets/js/admin.js"></script>


</body>

</html>