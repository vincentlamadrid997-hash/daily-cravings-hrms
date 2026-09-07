<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'toggle_department_status', 'departments.php');

requireCSRFToken("departments.php");

if (!isset($_POST['department_id'])) {

    header("Location: departments.php");
    exit;

}


$department_id = (int) $_POST['department_id'];


$stmt = $conn->prepare("
    SELECT status
    FROM departments
    WHERE department_id = ?
");


$stmt->execute([
    $department_id
]);


$department = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$department) {

    $_SESSION['department_error'] = 
        "Department not found.";

    header("Location: departments.php");
    exit;

}


$newStatus = (

    $department['status'] === "Active"

)

? "Inactive"

: "Active";


$update = $conn->prepare("
    UPDATE departments
    SET status = ?
    WHERE department_id = ?
");


$update->execute([

    $newStatus,

    $department_id

]);


if ($newStatus === "Inactive") {

    $_SESSION['department_success'] =
        "Department has been deactivated successfully.";

} else {

    $_SESSION['department_success'] =
        "Department has been activated successfully.";

}


header("Location: departments.php");

exit;

?>