<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

requireCSRFToken("positions.php");

if (!isset($_POST['position_id'])) {
    header("Location: positions.php");
    exit;
}

$position_id = (int) $_POST['position_id'];

$stmt = $conn->prepare("
    SELECT position_name, status FROM positions WHERE position_id = ?
");

$stmt->execute([$position_id]);
$position = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$position) {
    $_SESSION['position_error'] = "Position not found.";
    header("Location: positions.php");
    exit;
}

$oldStatus = $position['status'];
$newStatus = ($oldStatus === "Active") ? "Inactive" : "Active";

try {
    $conn->beginTransaction();

    $update = $conn->prepare("
        UPDATE positions
        SET status = ?, updated_at = NOW()
        WHERE position_id = ?
    ");

    $update->execute([$newStatus, $position_id]);

    // ========== LOG THE STATUS CHANGE ==========
    logPositionStatusChange($conn, $position['position_name'], $oldStatus, $newStatus);
    // ==========================================

    $conn->commit();

    if ($newStatus === "Inactive") {
        $_SESSION['position_success'] = "Position has been deactivated successfully.";
    } else {
        $_SESSION['position_success'] = "Position has been activated successfully.";
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Position status update error: " . $e->getMessage());
    $_SESSION['position_error'] = "Failed to update position status.";
}

header("Location: positions.php");
exit;

?>