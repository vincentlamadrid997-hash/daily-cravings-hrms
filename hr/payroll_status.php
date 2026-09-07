<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

requireCSRFToken("payroll.php");

if (!isset($_POST['payroll_id'])) {

    $_SESSION['payroll_error'] = "Invalid payroll record selected.";

    header("Location: payroll.php");
    exit;

}


$payroll_id = (int) $_POST['payroll_id'];


$stmt = $conn->prepare("
    SELECT 
        payroll_id,
        status
    FROM payroll
    WHERE payroll_id = ?
    LIMIT 1
");


$stmt->execute([
    $payroll_id
]);


$payroll = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$payroll) {

    $_SESSION['payroll_error'] = "Payroll record not found.";

    header("Location: payroll.php");
    exit;

}


if ($payroll['status'] === "Paid") {

    $_SESSION['payroll_error'] = "This payroll is already marked as Paid.";

    header("Location: payroll.php");
    exit;

}


$update = $conn->prepare("
    UPDATE payroll
    SET status = 'Paid'
    WHERE payroll_id = ?
");


$update->execute([
    $payroll_id
]);


$_SESSION['payroll_success'] = "Payroll successfully marked as Paid.";


header("Location: payroll.php");
exit;