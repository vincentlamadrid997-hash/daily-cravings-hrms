<?php

session_start();

date_default_timezone_set("Asia/Manila");


require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: resignation_request.php");
    exit;

}

requireCSRFToken("resignation_request.php", "resignation_error");

$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    $_SESSION["resignation_error"] =
        "Employee session expired. Please login again.";

    header("Location: ../login.php");
    exit;

}


$reason = trim(
    $_POST["reason"] ?? ""
);

$resignation_date =
    $_POST["resignation_date"] ?? "";

$last_working_day =
    $_POST["last_working_day"] ?? "";


if (
    empty($reason)
    ||
    empty($resignation_date)
    ||
    empty($last_working_day)
) {

    $_SESSION["resignation_error"] =
        "Please complete all required fields.";

    header("Location: resignation_request.php");
    exit;

}


if (strlen($reason) < 10) {

    $_SESSION["resignation_error"] =
        "Reason must contain at least 10 characters.";

    header("Location: resignation_request.php");
    exit;

}


$today = date("Y-m-d");

if ($resignation_date < $today) {

    $_SESSION["resignation_error"] =
        "Resignation date cannot be earlier than today.";

    header("Location: resignation_request.php");
    exit;

}


if ($last_working_day < $resignation_date) {

    $_SESSION["resignation_error"] =
        "Last working day cannot be earlier than resignation date.";

    header("Location: resignation_request.php");
    exit;

}


try {

    $checkStmt = $conn->prepare("

        SELECT
            resignation_id
        FROM resignations
        WHERE employee_id = :employee_id
        AND status = 'Pending'
        LIMIT 1

    ");

    $checkStmt->execute([

        ":employee_id" => $employee_id

    ]);

    if ($checkStmt->fetch()) {

        $_SESSION["resignation_error"] =
            "You already have a pending resignation request.";

        header("Location: resignation_request.php");
        exit;

    }


    $insertStmt = $conn->prepare("

        INSERT INTO resignations

        (

            employee_id,
            reason,
            resignation_date,
            last_working_day,
            status


        )

        VALUES

        (

            :employee_id,
            :reason,
            :resignation_date,
            :last_working_day,
            'Pending'

        )

    ");


    $insertStmt->execute([

        ":employee_id" =>
            $employee_id,

        ":reason" =>
            $reason,

        ":resignation_date" =>
            $resignation_date,

        ":last_working_day" =>
                        $last_working_day

    ]);

    $notifyStmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, redirect_url, status)
        VALUES (NULL, ?, 'resignations.php', 'Unread')
    ");

    $notifyStmt->execute([
        htmlspecialchars($_SESSION['full_name'] ?? 'An employee') . " submitted a resignation request."
    ]);


    $_SESSION["resignation_success"] =

        "Your resignation request has been submitted successfully and is waiting for HR approval.";


}

catch (PDOException $e) {

    $_SESSION["resignation_error"] =

        "Something went wrong while submitting your resignation request.";

}


header("Location: resignation_request.php");

exit;

?>