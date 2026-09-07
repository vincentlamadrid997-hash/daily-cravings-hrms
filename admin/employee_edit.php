<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Edit Employee";

if (isset($_POST['employee_id'])) {
    $_SESSION['selected_employee'] = (int) $_POST['employee_id'];
}

if (!isset($_SESSION['selected_employee'])) {
    header("Location: employees.php");
    exit;
}

$employee_id = (int) $_SESSION['selected_employee'];

$stmt = $conn->prepare("
    SELECT * FROM employees WHERE employee_id = ?
");

$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    unset($_SESSION['selected_employee']);
    header("Location: employees.php");
    exit;
}

$departments = $conn->query("
    SELECT department_id, department_name
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$positions = $conn->query("
    SELECT position_id, position_name
    FROM positions
    WHERE status = 'Active'
    ORDER BY position_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$alert = ["type" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_employee'])) {

    requireCSRFToken("employee_edit.php");

    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department_id = $_POST['department_id'];
    $position_id = $_POST['position_id'];
    $employment_status = $_POST['employment_status'];
    $address = trim($_POST['address']);

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $alert["type"] = "error";
        $alert["message"] = "Please complete all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert["type"] = "error";
        $alert["message"] = "Invalid email address.";
    } else {
        try {
            $conn->beginTransaction();

            // Track changes
            $changes = [];
            $oldFullName = $employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name'];
            $newFullName = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;
            
            if ($oldFullName !== $newFullName) {
                $changes[] = "name: {$oldFullName} -> {$newFullName}";
            }
            if ($employee['email'] !== $email) {
                $changes[] = "email: {$employee['email']} -> {$email}";
            }
            if (($employee['phone'] ?? '') !== $phone) {
                $changes[] = "phone: " . ($employee['phone'] ?? 'N/A') . " -> " . ($phone ?: 'N/A');
            }
            if ($employee['department_id'] != $department_id) {
                $changes[] = "department changed";
            }
            if ($employee['position_id'] != $position_id) {
                $changes[] = "position changed";
            }
            if ($employee['employment_status'] !== $employment_status) {
                $changes[] = "status: {$employee['employment_status']} -> {$employment_status}";
            }

            $stmt = $conn->prepare("
                UPDATE employees
                SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, 
                    department_id = ?, position_id = ?, employment_status = ?, address = ?, updated_at = NOW()
                WHERE employee_id = ?
            ");

            $stmt->execute([
                $first_name,
                !empty($middle_name) ? $middle_name : null,
                $last_name,
                $email,
                !empty($phone) ? $phone : null,
                $department_id,
                $position_id,
                $employment_status,
                !empty($address) ? $address : null,
                $employee_id
            ]);

            // ========== LOG THE EMPLOYEE EDIT ==========
            if (!empty($changes)) {
                logEmployeeEdit($conn, $newFullName, $email, $changes);
            }
            // ==========================================

            $conn->commit();

            unset($_SESSION['selected_employee']);
            $_SESSION['employee_success'] = "Employee updated successfully.";
            header("Location: employees.php");
            exit;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Employee edit error: " . $e->getMessage());
            $alert["type"] = "error";
            $alert["message"] = "Failed to update employee.";
        }
    }
}

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
                        <i class="fa-solid fa-edit"></i>
                        Edit Employee
                    </h1>

                    <p>Update employee details.</p>

                </div>

                <div class="crud-header-actions">

                    <a href="employees.php" class="crud-back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Employees
                    </a>

                </div>

            </div>

            <?php if ($alert['type']): ?>
                <div class="crud-alert <?= $alert['type'] === 'error' ? 'error' : 'success'; ?>">
                    <i class="fa-solid fa-<?= $alert['type'] === 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                    <?= htmlspecialchars($alert['message']); ?>
                </div>
            <?php endif; ?>

            <div class="crud-card">

                <div class="crud-card-header">

                    <div class="crud-icon">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>

                    <div>
                        <h2>Employee Information</h2>
                        <p>Update this employee's details.</p>
                    </div>

                </div>

                    <form method="POST" action="employee_edit.php">

                    <?php csrfField(); ?>

                    <input type="hidden" name="update_employee" value="1">

                    <div class="crud-section-title">
                        <h3>
                            <i class="fa-solid fa-user"></i>
                            Personal Information
                        </h3>
                    </div>

                    <div class="crud-form-grid">

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>
                            <input type="text" value="<?= htmlspecialchars($employee['employee_code']); ?>" disabled style="background: #f5f5f5;">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-user"></i>
                                First Name
                            </label>
                            <input type="text" name="first_name" required value="<?= htmlspecialchars($employee['first_name']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-user"></i>
                                Middle Name
                            </label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($employee['middle_name'] ?? ''); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-user"></i>
                                Last Name
                            </label>
                            <input type="text" name="last_name" required value="<?= htmlspecialchars($employee['last_name']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($employee['email']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($employee['phone'] ?? ''); ?>">
                        </div>

                    </div>

                    <div class="crud-section-title">
                        <h3>
                            <i class="fa-solid fa-briefcase"></i>
                            Employment Information
                        </h3>
                    </div>

                    <div class="crud-form-grid">

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>
                            <select name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id']; ?>" <?= $employee['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dept['department_name']); ?>
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
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?= $pos['position_id']; ?>" <?= $employee['position_id'] == $pos['position_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($pos['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-user-check"></i>
                                Employment Status
                            </label>
                            <select name="employment_status">
                                <option value="Active" <?= $employee['employment_status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?= $employee['employment_status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="Resigned" <?= $employee['employment_status'] === 'Resigned' ? 'selected' : ''; ?>>Resigned</option>
                            </select>
                        </div>

                    </div>

                    <div class="crud-section-title">
                        <h3>
                            <i class="fa-solid fa-location-dot"></i>
                            Additional Information
                        </h3>
                    </div>

                    <div class="crud-form-grid">

                        <div class="crud-form-group crud-full">
                            <label>
                                <i class="fa-solid fa-location-dot"></i>
                                Address
                            </label>
                            <textarea name="address" rows="3" style="resize: vertical;"><?= htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                        </div>

                    </div>

                    <div class="crud-actions">

                        <a href="employees.php" class="crud-btn crud-btn-secondary">
                            <i class="fa-solid fa-xmark"></i>
                            Cancel
                        </a>

                                                <button type="submit" id="empEditSaveBtn" disabled class="crud-btn crud-btn-primary">
                            <i class="fa-solid fa-save"></i>
                            Update Employee
                        </button>

                    </div>

                </form>

            </div>

        </section>

        </main>

    </div>

</div>

<script>
(function () {

    const form = document.querySelector('form[action="employee_edit.php"]');
    const saveBtn = document.getElementById("empEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

    form.addEventListener("submit", function (e) {

        e.preventDefault();

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

})();
</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>