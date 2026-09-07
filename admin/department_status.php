<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

requireCSRFToken("departments.php");

if (!isset($_POST['department_id'])) {
    header("Location: departments.php");
    exit;
}

$department_id = (int) $_POST['department_id'];

$stmt = $conn->prepare("
    SELECT department_name, status FROM departments WHERE department_id = ?
");

$stmt->execute([$department_id]);
$department = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$department) {
    $_SESSION['department_error'] = "Department not found.";
    header("Location: departments.php");
    exit;
}

$oldStatus = $department['status'];
$newStatus = ($oldStatus === "Active") ? "Inactive" : "Active";

try {
    $conn->beginTransaction();

    $update = $conn->prepare("
        UPDATE departments
        SET status = ?, updated_at = NOW()
        WHERE department_id = ?
    ");

    $update->execute([$newStatus, $department_id]);

    // ========== LOG THE STATUS CHANGE ==========
    logDepartmentStatusChange($conn, $department['department_name'], $oldStatus, $newStatus);
    // ==========================================

    $conn->commit();

    if ($newStatus === "Inactive") {
        $_SESSION['department_success'] = "Department has been deactivated successfully.";
    } else {
        $_SESSION['department_success'] = "Department has been activated successfully.";
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Department status update error: " . $e->getMessage());
    $_SESSION['department_error'] = "Failed to update department status.";
}

header("Location: departments.php");
exit;

?>