<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

// Server-side permission enforcement (Admin bypasses this automatically).
requirePermission($conn, 'add_employees', 'employees.php');

$page_title = "Add Employee";


$lastEmployee = $conn->query("
    SELECT employee_code
    FROM employees
    ORDER BY employee_id DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if ($lastEmployee && !empty($lastEmployee['employee_code'])) {

    preg_match(
        '/(\d+)$/',
        $lastEmployee['employee_code'],
        $matches
    );

    $number = isset($matches[1])
        ? (int) $matches[1] + 1
        : 5;

} else {

    $number = 5;
}

$employee_code = "DCI-EMP-" . str_pad(
    $number,
    4,
    "0",
    STR_PAD_LEFT
);


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


$alert = [
    "type" => "",
    "message" => ""
];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("employee_add.php");

    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department_id = $_POST['department_id'] ?? '';
    $position_id = $_POST['position_id'] ?? '';
    $employment_status = $_POST['employment_status'] ?? 'Active';
    $hire_date = $_POST['hire_date'] ?? '';


    if (
        empty($first_name) ||
        empty($last_name) ||
        empty($email) ||
        empty($phone) ||
        empty($department_id) ||
        empty($position_id) ||
        empty($hire_date)
    ) {

        $alert["type"] = "error";
        $alert["message"] = "Please complete all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $alert["type"] = "error";
        $alert["message"] = "Please enter a valid email address.";

    } elseif (!ctype_digit($phone)) {

        $alert["type"] = "error";
        $alert["message"] = "Phone number must contain numbers only.";

    } elseif ($hire_date < date("Y-m-d")) {

        $alert["type"] = "error";
        $alert["message"] = "Hire date cannot be earlier than today.";

    } else {

        
        $check = $conn->prepare("
            SELECT employee_id
            FROM employees
            WHERE email = ?
        ");

        $check->execute([$email]);

        if ($check->fetch()) {

            $alert["type"] = "error";
            $alert["message"] = "Email address already exists.";

        } else {

          
            $stmt = $conn->prepare("
                INSERT INTO employees (
                    employee_code,
                    first_name,
                    middle_name,
                    last_name,
                    email,
                    phone,
                    department_id,
                    position_id,
                    employment_status,
                    hire_date
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $employee_code,
                $first_name,
                $middle_name,
                $last_name,
                $email,
                $phone,
                $department_id,
                $position_id,
                $employment_status,
                $hire_date
            ]);

            $alert["type"] = "success";
            $alert["message"] = "Employee added successfully.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.
    </title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/hr.css">
    <link rel="stylesheet" href="../assets/css/crud_hr.css">
    <link rel="stylesheet" href="../assets/css/employee.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

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
                            <i class="fa-solid fa-user-plus"></i>
                            Add Employee
                        </h1>

                        <p>Create new employee record.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="employees.php" class="crud-back-btn">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Employees
                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>

                        <div>
                            <h2>Employee Information</h2>
                            <p>Fill in employee details.</p>
                        </div>

                    </div>

                                        <form method="POST" id="employeeAddForm" novalidate>

                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-id-card"></i>
                                    Employee Code
                                </label>

                                <input
                                    type="text"
                                    value="<?= htmlspecialchars($employee_code); ?>"
                                    readonly
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    placeholder="Enter first name"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Middle Name
                                </label>

                                <input
                                    type="text"
                                    name="middle_name"
                                    placeholder="Enter middle name"
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    placeholder="Enter last name"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-envelope"></i>
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    placeholder="example@email.com"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-phone"></i>
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    placeholder="Enter phone number"
                                    maxlength="20"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department
                                </label>

                                <select name="department_id" required>

                                    <option value="">Select Department</option>

                                    <?php foreach ($departments as $department): ?>

                                        <option value="<?= $department['department_id']; ?>">
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

                                <select name="position_id" required>

                                    <option value="">Select Position</option>

                                    <?php foreach ($positions as $position): ?>

                                        <option value="<?= $position['position_id']; ?>">
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

                                <select name="employment_status" required>

                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Resigned">Resigned</option>

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
                                    min="<?= date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                        </div>

                        <div class="crud-actions">

                            <a href="employees.php" class="crud-btn crud-btn-secondary">
                                <i class="fa-solid fa-xmark"></i>
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="crud-btn crud-btn-primary"
                            >
                                <i class="fa-solid fa-save"></i>
                                Save Employee
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if (!empty($alert["message"])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "<?= $alert['type'] == "success"
            ? "Employee Added!"
            : "Unable to Add Employee"; ?>",

        text: "<?= htmlspecialchars(
            $alert['message'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>",

        confirmButtonColor: "#003DA5",
        confirmButtonText: "OK",
        allowOutsideClick: false,
        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert["type"] == "success"): ?>

        if (result.isConfirmed) {
            window.location.href = "employees.php";
        }

        <?php endif; ?>

    });

});

</script>

<?php endif; ?>

<script>
document.getElementById("employeeAddForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Add this employee?",
        text: "This will create a new employee record.",
        showCancelButton: true,
        confirmButtonText: "Yes, Add",
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