<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset($_POST['resignation_id']) ||
    !isset($_POST['status'])
) {
    header("Location: resignations.php");
    exit;
}

requireCSRFToken("resignations.php");

$resignation_id = (int) $_POST['resignation_id'];
$status = trim($_POST['status']);


if (
    $status !== "Approved" &&
    $status !== "Rejected"
) {
    $_SESSION['resignation_error'] = "Invalid resignation status.";

    header("Location: resignations.php");
    exit;
}


if (!isset($_SESSION['user_id'])) {
    $_SESSION['resignation_error'] = "Unable to verify your account.";

    header("Location: resignations.php");
    exit;
}

$approved_by = (int) $_SESSION['user_id'];


$stmt = $conn->prepare("
    SELECT
        resignation_id,
        employee_id,
        status
    FROM resignations
    WHERE resignation_id = ?
    LIMIT 1
");

$stmt->execute([$resignation_id]);
$resignation = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$resignation) {
    $_SESSION['resignation_error'] = "Resignation request not found.";

    header("Location: resignations.php");
    exit;
}


if (
    $resignation['status'] === "Approved" ||
    $resignation['status'] === "Rejected"
) {
    $_SESSION['resignation_error'] = "This resignation request has already been finalized.";

    header("Location: resignations.php");
    exit;
}


$approved_date = date("Y-m-d H:i:s");


try {

    $conn->beginTransaction();


    $updateResignation = $conn->prepare("
        UPDATE resignations
        SET
            status = ?,
            approved_by = ?,
            approved_date = ?
        WHERE resignation_id = ?
    ");

    $updateResignation->execute([
        $status,
        $approved_by,
        $approved_date,
        $resignation_id
    ]);


        if ($status === "Approved") {

        $updateEmployee = $conn->prepare("
            UPDATE employees
            SET
                employment_status = 'Resigned'
            WHERE employee_id = ?
        ");

        $updateEmployee->execute([
            $resignation['employee_id']
        ]);
    }

    $userStmt = $conn->prepare("
        SELECT user_id FROM users WHERE employee_id = ?
    ");
    $userStmt->execute([$resignation['employee_id']]);
    $employeeUserId = $userStmt->fetchColumn();

    if ($employeeUserId) {

        $notifyStmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, redirect_url, status)
            VALUES (?, ?, 'resignation_history.php', 'Unread')
        ");

        $notifyStmt->execute([
            $employeeUserId,
            "Your resignation request has been " . strtolower($status) . "."
        ]);

    }


    $conn->commit();


    if ($status === "Approved") {
        $_SESSION['resignation_success'] = "Resignation request approved successfully.";
    } else {
        $_SESSION['resignation_success'] = "Resignation request rejected successfully.";
    }

} catch (PDOException $e) {


    if ($conn->inTransaction()) {
        $conn->rollBack();
    }


    $_SESSION['resignation_error'] = "Unable to update resignation status.";
}


header("Location: resignations.php");
exit;