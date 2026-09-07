<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Employee Details";

if (isset($_POST['employee_id'])) {
    $_SESSION['selected_employee'] = (int) $_POST['employee_id'];
}

if (!isset($_SESSION['selected_employee'])) {
    header("Location: employees.php");
    exit;
}

$employee_id = (int) $_SESSION['selected_employee'];

$stmt = $conn->prepare("
    SELECT
        e.*,
        d.department_name,
        p.position_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN positions p ON e.position_id = p.position_id
    WHERE e.employee_id = ?
");

$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    unset($_SESSION['selected_employee']);
    header("Location: employees.php");
    exit;
}

$fullName = $employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name'];

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/crud_hr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

            <section class="crud-page">

                <div class="crud-header">

                    <div class="crud-title">

                        <h1>
                            <i class="fa-solid fa-id-card"></i>
                            Employee Details
                        </h1>

                        <p>View complete employee information.</p>

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
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>
                            <h2><?= htmlspecialchars($fullName); ?></h2>
                            <p>Employee Information</p>
                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">
                            <span>Employee Code</span>
                            <strong><?= htmlspecialchars($employee['employee_code']); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Email Address</span>
                            <strong><?= htmlspecialchars($employee['email']); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Phone Number</span>
                            <strong><?= htmlspecialchars($employee['phone'] ?: "N/A"); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Department</span>
                            <strong><?= htmlspecialchars($employee['department_name'] ?? "N/A"); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Position</span>
                            <strong><?= htmlspecialchars($employee['position_name'] ?? "N/A"); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Employment Status</span>
                            <strong>
                                <span class="hr-emp-status <?= strtolower($employee['employment_status']); ?>">
                                    <?= htmlspecialchars($employee['employment_status']); ?>
                                </span>
                            </strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Hire Date</span>
                            <strong>
                                <?= !empty($employee['hire_date'])
                                    ? date("F d, Y", strtotime($employee['hire_date']))
                                    : "N/A"; ?>
                            </strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Record Created</span>
                            <strong><?= date("F d, Y h:i A", strtotime($employee['created_at'])); ?></strong>
                        </div>

                    </div>

                    <div class="crud-section-title">
                        <h3>
                            <i class="fa-solid fa-user"></i>
                            Personal Information
                        </h3>
                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">
                            <span>First Name</span>
                            <strong><?= htmlspecialchars($employee['first_name']); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Middle Name</span>
                            <strong>
                                <?= !empty($employee['middle_name'])
                                    ? htmlspecialchars($employee['middle_name'])
                                    : "N/A"; ?>
                            </strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Last Name</span>
                            <strong><?= htmlspecialchars($employee['last_name']); ?></strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Birthdate</span>
                            <strong>
                                <?= !empty($employee['birthdate'])
                                    ? date("F d, Y", strtotime($employee['birthdate']))
                                    : "N/A"; ?>
                            </strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Gender</span>
                            <strong>
                                <?= !empty($employee['gender'])
                                    ? htmlspecialchars($employee['gender'])
                                    : "N/A"; ?>
                            </strong>
                        </div>

                        <div class="crud-info-box">
                            <span>Civil Status</span>
                            <strong>
                                <?= !empty($employee['civil_status'])
                                    ? htmlspecialchars($employee['civil_status'])
                                    : "N/A"; ?>
                            </strong>
                        </div>

                        <div class="crud-info-box" style="grid-column: 1 / -1;">
                            <span>Address</span>
                            <strong>
                                <?= !empty($employee['address'])
                                    ? nl2br(htmlspecialchars($employee['address']))
                                    : "N/A"; ?>
                            </strong>
                        </div>

                    </div>

                    <div class="crud-actions">

                        <form action="employee_edit.php" method="POST">
                            <?php csrfField(); ?>

                            <input type="hidden" name="employee_id" value="<?= (int) $employee['employee_id']; ?>">

                            <button type="submit" class="crud-btn crud-btn-primary">
                                <i class="fa-solid fa-pen"></i>
                                Edit Employee
                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="../assets/js/admin.js"></script>

</body>

</html>