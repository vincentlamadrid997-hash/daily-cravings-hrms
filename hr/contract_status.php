<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

requireCSRFToken("contracts.php");

if (!isset($_POST['contract_id'])) {

    $_SESSION['contract_error'] = "Contract record not found.";

    header("Location: contracts.php");
    exit;

}


$contract_id = (int) $_POST['contract_id'];


$stmt = $conn->prepare("
    SELECT 
        contract_id,
        status
    FROM contracts
    WHERE contract_id = ?
");

$stmt->execute([$contract_id]);

$contract = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$contract) {

    $_SESSION['contract_error'] = "Contract not found.";

    header("Location: contracts.php");
    exit;

}


if ($contract['status'] === "Active") {

    $newStatus = "Expired";

    $_SESSION['contract_success'] = 
        "Contract has been marked as expired successfully.";

} else {

    $newStatus = "Active";

    $_SESSION['contract_success'] = 
        "Contract has been activated successfully.";

}


$update = $conn->prepare("
    UPDATE contracts
    SET status = ?
    WHERE contract_id = ?
");


$success = $update->execute([
    $newStatus,
    $contract_id
]);



if (!$success) {

    $_SESSION['contract_error'] = 
        "Unable to update contract status.";

}


header("Location: contracts.php");
exit;

?>