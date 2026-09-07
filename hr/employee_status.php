<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

// Server-side permission enforcement (Admin bypasses this automatically).
requirePermission($conn, 'toggle_employee_status', 'employees.php');

requireCSRFToken("employees.php");

if (!isset($_POST['employee_id'])) {

    header("Location: employees.php");
    exit;

}


$employee_id = (int) $_POST['employee_id'];


$stmt = $conn->prepare("
    SELECT employment_status
    FROM employees
    WHERE employee_id = ?
");


$stmt->execute([
    $employee_id
]);


$employee = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$employee) {

    $_SESSION['employee_error'] =
        "Employee not found.";

    header("Location: employees.php");
    exit;

}


if ($employee['employment_status'] === "Active") {

    $newStatus = "Inactive";

    $_SESSION['employee_success'] =
        "Employee has been deactivated successfully.";

} else {

    $newStatus = "Active";

    $_SESSION['employee_success'] =
        "Employee has been activated successfully.";

}


$update = $conn->prepare("
    UPDATE employees
    SET employment_status = ?
    WHERE employee_id = ?
");


$success = $update->execute([

    $newStatus,

    $employee_id

]);


if (!$success) {

    $_SESSION['employee_error'] =
        "Unable to update employee status.";

}


header("Location: employees.php");

exit;

?>