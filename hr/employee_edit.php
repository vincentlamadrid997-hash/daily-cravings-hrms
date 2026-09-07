<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

// Server-side permission enforcement (Admin bypasses this automatically).
// This MUST run before the $_SESSION['selected_employee'] fallback below,
// otherwise someone without edit_employees could still reach this page
// by revisiting it without a fresh POST.
requirePermission($conn, 'edit_employees', 'employees.php');

$page_title = "Edit Employee";


if (isset($_POST['employee_id'])) {
    $_SESSION['selected_employee'] = (int) $_POST['employee_id'];
}

if (!isset($_SESSION['selected_employee'])) {
    header("Location: employees.php");
    exit;
}

$employee_id = (int) $_SESSION['selected_employee'];


$departments = $conn->query("
    SELECT
        department_id,
        department_name
    FROM departments
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$positions = $conn->query("
    SELECT
        position_id,
        position_name
    FROM positions
    ORDER BY position_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("
    SELECT *
    FROM employees
    WHERE employee_id = ?
");

$stmt->execute([$employee_id]);

$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    unset($_SESSION['selected_employee']);

    header("Location: employees.php");
    exit;
}


$alert = [
    "type" => "",
    "message" => ""
];


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_employee'])) {

    requireCSRFToken("employee_edit.php");

    $employee_code = trim($_POST['employee_code']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

    $department_id = (int) $_POST['department_id'];
    $position_id = (int) $_POST['position_id'];

    $employment_status = $_POST['employment_status'];

    $birthdate = !empty($_POST['birthdate'])
        ? $_POST['birthdate']
        : null;

    $gender = !empty($_POST['gender'])
        ? $_POST['gender']
        : null;

    $civil_status = !empty($_POST['civil_status'])
        ? $_POST['civil_status']
        : null;

    $address = trim($_POST['address']);
    $hire_date = $_POST['hire_date'];


    if (
        empty($first_name) ||
        empty($last_name) ||
        empty($email) ||
        empty($phone) ||
        empty($department_id) ||
        empty($position_id) ||
        empty($employment_status) ||
        empty($hire_date)
    ) {

        $alert["type"] = "error";
        $alert["message"] = "Please complete all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $alert["type"] = "error";
        $alert["message"] = "Invalid email address.";

    } elseif ($hire_date > date("Y-m-d")) {

        $alert["type"] = "error";
        $alert["message"] = "Hire date cannot be in the future.";

    } else {


        $check = $conn->prepare("
            SELECT employee_id
            FROM employees
            WHERE email = ?
            AND employee_id != ?
        ");

        $check->execute([
            $email,
            $employee_id
        ]);

        if ($check->fetch()) {
            $alert["type"] = "error";
            $alert["message"] = "Email address already exists.";
        } else {


            $update = $conn->prepare("
                UPDATE employees
                SET
                    first_name = ?,
                    middle_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    birthdate = ?,
                    gender = ?,
                    civil_status = ?,
                    address = ?,
                    department_id = ?,
                    position_id = ?,
                    employment_status = ?,
                    hire_date = ?
                WHERE employee_id = ?
            ");

            $success = $update->execute([
                $first_name,
                $middle_name,
                $last_name,
                $email,
                $phone,
                $birthdate,
                $gender,
                $civil_status,
                $address,
                $department_id,
                $position_id,
                $employment_status,
                $hire_date,
                $employee_id
            ]);

            if ($success) {

                $alert["type"] = "success";
                $alert["message"] = "Employee updated successfully.";

                $stmt->execute([$employee_id]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {

                $alert["type"] = "error";
                $alert["message"] = "Unable to update employee.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> |
        Daily Cravings Foods Inc.
    </title>

    <link
        rel="stylesheet" href="../assets/css/global.css">

    <link
        rel="stylesheet" href="../assets/css/hr.css">

    <link
        rel="stylesheet" href="../assets/css/crud_hr.css">

    <link
        rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="crud-page">

                <div class="crud-header">

                    <div class="crud-title">

                        <h1>

                            <i class="fa-solid fa-user-pen"></i>
                            Edit Employee

                        </h1>

                        <p>Update employee information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a
                            href="employees.php"
                            class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Employees

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-user-pen"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars($employee['first_name'] . " " . $employee['last_name']); ?>

                            </h2>

                            <p>Edit Employee Information</p>

                        </div>

                    </div>

                                            <form method="POST" id="employeeEditForm" novalidate>

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="update_employee"
                            value="1">

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-id-card"></i>
                                    Employee Code
                                </label>

                                <input
                                    type="text"
                                    name="employee_code"
                                    value="<?= htmlspecialchars($employee['employee_code']); ?>"
                                    readonly>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    value="<?= htmlspecialchars($employee['first_name']); ?>"
                                    required>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Middle Name
                                </label>

                                <input
                                    type="text"
                                    name="middle_name"
                                    value="<?= htmlspecialchars($employee['middle_name']); ?>">

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    value="<?= htmlspecialchars($employee['last_name']); ?>"
                                    required>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-envelope"></i>
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="<?= htmlspecialchars($employee['email']); ?>"
                                    required>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-phone"></i>
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    maxlength="11"
                                    pattern="[0-9]{11}"
                                    value="<?= htmlspecialchars($employee['phone']); ?>"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    required>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department
                                </label>

                                <select
                                    name="department_id"
                                    required>

                                    <option value="">
                                        Select Department
                                    </option>

                                    <?php foreach ($departments as $department): ?>

                                        <option
                                            value="<?= $department['department_id']; ?>"
                                            <?= $department['department_id'] == $employee['department_id'] ? "selected" : ""; ?>>

                                            <?= htmlspecialchars($department['department_name']); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-briefcase"></i>
                                    Position
                                </label>

                                <select
                                    name="position_id"
                                    required>

                                    <option value="">
                                        Select Position
                                    </option>

                                    <?php foreach ($positions as $position): ?>

                                        <option
                                            value="<?= $position['position_id']; ?>"
                                            <?= $position['position_id'] == $employee['position_id'] ? "selected" : ""; ?>>

                                            <?= htmlspecialchars($position['position_name']); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user-check"></i>
                                    Employment Status
                                </label>

                                <select
                                    name="employment_status"
                                    required>

                                    <?php
                                    $statusList = [
                                        "Active",
                                        "Inactive",
                                        "Resigned"
                                    ];

                                    foreach ($statusList as $status):
                                    ?>

                                        <option
                                            value="<?= $status; ?>"
                                            <?= $employee['employment_status'] == $status ? "selected" : ""; ?>>

                                            <?= $status; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-cake-candles"></i>
                                    Birthdate
                                </label>

                                <input
                                    type="date"
                                    name="birthdate"
                                    value="<?= $employee['birthdate']; ?>">

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-venus-mars"></i>
                                    Gender
                                </label>

                                <select
                                    name="gender">

                                    <option value="">
                                        Select Gender
                                    </option>

                                    <option
                                        value="Male"
                                        <?= $employee['gender'] == "Male" ? "selected" : ""; ?>>

                                        Male

                                    </option>

                                    <option
                                        value="Female"
                                        <?= $employee['gender'] == "Female" ? "selected" : ""; ?>>

                                        Female

                                    </option>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-ring"></i>
                                    Civil Status
                                </label>

                                <select
                                    name="civil_status">

                                    <option value="">
                                        Select Civil Status
                                    </option>

                                    <option
                                        value="Single"
                                        <?= $employee['civil_status'] == "Single" ? "selected" : ""; ?>>

                                        Single

                                    </option>

                                    <option
                                        value="Married"
                                        <?= $employee['civil_status'] == "Married" ? "selected" : ""; ?>>

                                        Married

                                    </option>

                                    <option
                                        value="Widowed"
                                        <?= $employee['civil_status'] == "Widowed" ? "selected" : ""; ?>>

                                        Widowed

                                    </option>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-calendar"></i>
                                    Hire Date
                                </label>

                                <input
                                    type="date"
                                    name="hire_date"
                                    max="<?= date('Y-m-d'); ?>"
                                    value="<?= $employee['hire_date']; ?>"
                                    required>

                            </div>

                        </div>

                        <div class="crud-form-group">

                            <label>
                                <i class="fa-solid fa-location-dot"></i>
                                Address
                            </label>

                            <textarea
                                name="address"
                                rows="4"><?= htmlspecialchars($employee['address']); ?></textarea>

                        </div>

                        <div class="crud-actions">

                                                        <button
                                type="submit"
                                id="employeeEditSaveBtn"
                                disabled
                                class="crud-btn crud-btn-primary">

                                <i class="fa-solid fa-floppy-disk"></i>

                                Save Changes

                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if (!empty($alert['message'])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "<?= $alert['type'] == "success" ? "Success!" : "Oops!"; ?>",

        text: "<?= htmlspecialchars($alert['message'], ENT_QUOTES); ?>",

        confirmButtonColor: "#003DA5"

    }).then(() => {

        <?php if ($alert['type'] == "success"): ?>

            window.location = "employees.php";

        <?php endif; ?>

    });

});

</script>

<?php endif; ?>


<script>
(function () {

    const form = document.getElementById("employeeEditForm");
    const saveBtn = document.getElementById("employeeEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("employeeEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this employee's record.",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#003DA5",
        reverseButtons: true
    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

});
</script>

<script src="../assets/js/hr.js"></script>

</body>

</html>