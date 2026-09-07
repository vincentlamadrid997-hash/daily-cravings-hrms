<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

requireCSRFToken("employees.php");

if (!isset($_POST['employee_id'])) {
    header("Location: employees.php");
    exit;
}

$employee_id = (int) $_POST['employee_id'];

// Get current employee info
$stmt = $conn->prepare("
    SELECT first_name, middle_name, last_name, email, employment_status
    FROM employees
    WHERE employee_id = ?
");

$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    $_SESSION['employee_error'] = "Employee not found.";
    header("Location: employees.php");
    exit;
}

$oldStatus = $employee['employment_status'];

// Determine new status
if ($oldStatus === "Active") {
    $newStatus = "Inactive";
    $successMsg = "Employee has been deactivated successfully.";
} else {
    $newStatus = "Active";
    $successMsg = "Employee has been activated successfully.";
}

try {
    $conn->beginTransaction();

    // Update employee status
    $update = $conn->prepare("
        UPDATE employees
        SET employment_status = ?, updated_at = NOW()
        WHERE employee_id = ?
    ");

    $update->execute([$newStatus, $employee_id]);

    // ========== LOG THE STATUS CHANGE ==========
    $fullName = $employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name'];
    logEmployeeStatusChange($conn, $fullName, $employee['email'], $oldStatus, $newStatus);
    // ==========================================

    $conn->commit();

    $_SESSION['employee_success'] = $successMsg;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Employee status update error: " . $e->getMessage());
    $_SESSION['employee_error'] = "Failed to update employee status.";
}

header("Location: employees.php");
exit;

?>