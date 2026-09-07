<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: leaves.php");
    exit;

}

requireCSRFToken("leaves.php", "leave_error");


if (!isset($_POST['leave_id']) || !isset($_POST['status'])) {

    $_SESSION['leave_error'] = "Invalid leave request.";

    header("Location: leaves.php");
    exit;

}


$leave_id = (int) $_POST['leave_id'];
$status   = $_POST['status'];


$allowed_status = [
    "Approved",
    "Rejected"
];


if (!in_array($status, $allowed_status)) {

    $_SESSION['leave_error'] = "Invalid status update.";

    header("Location: leaves.php");
    exit;

}


$stmt = $conn->prepare("
    SELECT status, employee_id
    FROM leave_requests
    WHERE leave_id = ?
");

$stmt->execute([$leave_id]);
$leave = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$leave) {

    $_SESSION['leave_error'] = "Leave request not found.";

    header("Location: leaves.php");
    exit;

}


if ($leave['status'] !== "Pending") {

    $_SESSION['leave_error'] = "This leave request has already been " . strtolower($leave['status']) . " and cannot be changed again.";

    header("Location: leaves.php");
    exit;

}


try {

    $stmt = $conn->prepare("
        UPDATE leave_requests
        SET
            status = ?,
            approved_by = ?,
            approved_at = NOW()
        WHERE leave_id = ?
    ");


        $stmt->execute([
        $status,
        $_SESSION['user_id'],
        $leave_id
    ]);

    $userStmt = $conn->prepare("
        SELECT user_id FROM users WHERE employee_id = ?
    ");
    $userStmt->execute([$leave['employee_id']]);
    $employeeUserId = $userStmt->fetchColumn();

    if ($employeeUserId) {

        $notifyStmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, redirect_url, status)
            VALUES (?, ?, 'leave_history.php', 'Unread')
        ");

        $notifyStmt->execute([
            $employeeUserId,
            "Your leave request has been " . strtolower($status) . "."
        ]);

    }


    $_SESSION['leave_success'] =
        "Leave request has been " . strtolower($status) . ".";


} catch (PDOException $e) {

    $_SESSION['leave_error'] =
        "Failed to update leave status.";

}


header("Location: leaves.php");
exit;

?>