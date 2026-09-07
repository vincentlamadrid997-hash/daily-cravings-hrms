<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'toggle_position_status', 'positions.php');

requireCSRFToken("positions.php");

if (!isset($_POST['position_id'])) {

    header("Location: positions.php");
    exit;

}

$position_id = (int) $_POST['position_id'];


$stmt = $conn->prepare("

    SELECT

        status

    FROM positions

    WHERE position_id = ?

");

$stmt->execute([
    $position_id
]);

$position = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$position) {

    $_SESSION['position_error'] =
        "Position not found.";

    header("Location: positions.php");
    exit;

}


$newStatus = (

    $position['status'] === "Active"

)

? "Inactive"

: "Active";


$update = $conn->prepare("

    UPDATE positions

    SET

        status = ?

    WHERE position_id = ?

");

$update->execute([

    $newStatus,

    $position_id

]);


if ($newStatus === "Inactive") {

    $_SESSION['position_success'] =
        "Position has been deactivated successfully.";

} else {

    $_SESSION['position_success'] =
        "Position has been activated successfully.";

}


header("Location: positions.php");
exit;

?>