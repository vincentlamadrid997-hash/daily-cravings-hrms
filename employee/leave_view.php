<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Leave Details";

$employee_id = $_SESSION["employee_id"] ?? 0;


if(!$employee_id){

    header("Location: ../login.php");
    exit;

}

requireCSRFToken("leave_history.php");


function clean($value)
{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

}

function dateFormat($date)
{

    if(empty($date)){
        return "-";
    }

    return date(
        "F d, Y",
        strtotime($date)
    );

}

$leave_id = $_POST["leave_id"] ?? 0;

if(!$leave_id){
    header("Location: leave_history.php");
    exit;

}


$leaveStmt = $conn->prepare("

SELECT

    lr.leave_id,
    lr.employee_id,
    lr.start_date,
    lr.end_date,
    lr.total_days,
    lr.reason,
    lr.attachment,
    lr.status,
    lr.created_at,
    lr.remarks,
    lr.cancelled_reason,
    lr.custom_leave_type,

    lt.leave_type_name,

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,

    d.department_name,
    p.position_name

FROM leave_requests lr

LEFT JOIN leave_types lt
ON lr.leave_type_id = lt.leave_type_id

LEFT JOIN employees e
ON lr.employee_id = e.employee_id

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE lr.leave_id = :leave_id
AND lr.employee_id = :employee_id

LIMIT 1

");


$leaveStmt->execute([

    ":leave_id" => $leave_id,
    ":employee_id" => $employee_id

]);


$leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);


if (!$leave) {

    $_SESSION["leave_error"] = "Leave request not found.";
    header("Location: leave_history.php");
    exit;

}


$employee = [

    "employee_code" => $leave["employee_code"],
    "first_name" => $leave["first_name"],
    "middle_name" => $leave["middle_name"],
    "last_name" => $leave["last_name"],
    "department_name" => $leave["department_name"],
    "position_name" => $leave["position_name"]

];


function getLeaveType($leave)
{

    if (!empty($leave["custom_leave_type"])) {
        return $leave["custom_leave_type"];
    }

    if (!empty($leave["leave_type_name"])) {
        return $leave["leave_type_name"];
    }

    return "Others";

}


function getLeaveStatus($status)
{

    switch ($status) {

        case "Approved":

            return [
                "class" => "approved",
                "icon" => "fa-circle-check"
            ];

        case "Rejected":

            return [
                "class" => "rejected",
                "icon" => "fa-circle-xmark"
            ];

        case "Cancelled":

            return [
                "class" => "cancelled",
                "icon" => "fa-ban"
            ];

        default:

            return [
                "class" => "pending",
                "icon" => "fa-clock"
            ];

    }

}

$status = getLeaveStatus(

    $leave["status"]

);


$employee_name = trim(

    $employee["first_name"]
    . " "
    . $employee["middle_name"]
    . " "
    . $employee["last_name"]

);


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="../assets/css/crud_employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

    <div class="employee-wrapper">

        <?php require_once "includes/sidebar.php"; ?>

        <div class="employee-content-wrapper">

            <?php require_once "includes/header.php"; ?>

            <main class="employee-main">

                <section class="employee-payroll-details-page">

                    <div class="employee-payroll-details-header">

                        <div class="employee-payroll-details-title">

                            <h1>
                                <i class="fa-solid fa-calendar-check"></i>
                                Leave Request Details
                            </h1>

                            <p>View complete information about your submitted leave request.</p>

                        </div>

                        <a
                            href="leave_history.php"
                            class="employee-payroll-details-back-btn"

                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to History
                        </a>

                    </div>

                    <div class="employee-payroll-details-card">

                        <div class="employee-payroll-details-card-header">

                            <div class="employee-payroll-details-icon">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div>

                                <h2>Employee Information</h2>

                                <p>Employee profile details</p>

                            </div>

                        </div>

                        <div class="employee-payroll-details-grid">

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-id-card"></i>
                                    Employee Code
                                </label>

                                <p>
                                    <?= clean($employee["employee_code"]); ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Full Name
                                </label>

                                <p>
                                    <?= clean($employee_name); ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department
                                </label>

                                <p>
                                    <?= clean($employee["department_name"]); ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-briefcase"></i>
                                    Position
                                </label>

                                <p>
                                    <?= clean($employee["position_name"]); ?>
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="employee-payroll-details-card">

                        <div class="employee-payroll-details-card-header">

                            <div class="employee-payroll-details-icon">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <div>

                                <h2>Leave Information</h2>

                                <p>Submitted leave request details</p>

                            </div>

                        </div>

                        <div class="employee-payroll-details-grid">

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-list"></i>
                                    Leave Type
                                </label>

                                <p>
                                    <?= clean(getLeaveType($leave)); ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-calendar"></i>
                                    Inclusive Dates
                                </label>

                                <p>

                                    <?= dateFormat($leave["start_date"]); ?>
                                    &nbsp; - &nbsp;
                                    <?= dateFormat($leave["end_date"]); ?>

                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-clock"></i>
                                    Total Days
                                </label>

                                <p>
                                    <?php

                                    $days = (float)$leave["total_days"];

                                    if ($days == floor($days)) {
                                        echo number_format($days, 0);

                                    } else {

                                        echo rtrim(

                                            rtrim(
                                                number_format($days, 1),
                                                "0"
                                            ),
                                            "."
                                        );
                                    }

                                    ?>

                                    day<?= $days > 1 ? "s" : ""; ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Submitted Date
                                </label>

                                <p>
                                    <?= dateFormat($leave["created_at"]); ?>
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="employee-payroll-details-card">

                        <div class="employee-payroll-details-card-header">

                            <div class="employee-payroll-details-icon">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>

                            <div>

                                <h2>Request Status</h2>

                                <p>Current approval status</p>

                            </div>

                        </div>

                        <div class="employee-payroll-details-summary">

                            <div class="employee-payroll-summary-box">

                                <span>
                                    Status
                                </span>

                                <h3>

                                    <span
                                        class="employee-status-badge <?= $status["class"]; ?>"

                                    >

                                        <i class="fa-solid <?= $status["icon"]; ?>"></i>
                                        <?= clean($leave["status"]); ?>
                                    </span>

                                </h3>

                            </div>

                            <div class="employee-payroll-summary-box">

                                <span>
                                    Remarks
                                </span>

                                <h3>
                                    <?=
                                        !empty($leave["remarks"])
                                        ?
                                        clean($leave["remarks"])
                                        :
                                        "No remarks"
                                    ?>
                                </h3>

                            </div>

                            <div class="employee-payroll-summary-box">

                                <span>
                                    Cancellation Reason
                                </span>

                                <h3>
                                    <?=
                                        !empty($leave["cancelled_reason"])
                                        ?
                                        clean($leave["cancelled_reason"])
                                        :
                                        "N/A"
                                    ?>

                                </h3>

                            </div>

                        </div>

                    </div>

                    <div class="employee-payroll-details-card">

                        <div class="employee-payroll-details-card-header">

                            <div class="employee-payroll-details-icon">
                                <i class="fa-solid fa-comment"></i>
                            </div>

                            <div>

                                <h2>Additional Information</h2>

                                <p>Leave reason and attachment</p>

                            </div>

                        </div>

                        <div class="employee-payroll-details-grid">

                            <div
                                class="employee-payroll-details-group"
                                style="grid-column:span 2;"

                            >

                                <label>
                                    <i class="fa-solid fa-message"></i>
                                    Reason
                                </label>

                                <p>
                                    <?= clean($leave["reason"]); ?>
                                </p>

                            </div>

                            <div class="employee-payroll-details-group">

                                <label>
                                    <i class="fa-solid fa-paperclip"></i>
                                    Attachment
                                </label>

                                <p>

                                    <?php if (!empty($leave["attachment"])): ?>

                                        <a
                                            href="../uploads/leaves/<?= clean($leave["attachment"]); ?>"
                                            target="_blank"

                                        >

                                            View File
                                        </a>

                                    <?php else: ?>

                                        No Attachment

                                    <?php endif; ?>

                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="employee-payroll-details-actions">

                        <form
                            action="leave_pdf.php"
                            method="POST"

                        >

                            <input
                                type="hidden" 
                                name="leave_id"
                                value="<?= $leave["leave_id"]; ?>"
                            >

                            <button
                                type="submit"
                                class="employee-payroll-details-btn download"

                            >

                                <i class="fa-solid fa-file-pdf"></i>
                                Download Leave
                            </button>

                        </form>

                    </div>

                </section>

            </main>

        </div>

    </div>

</body>

</html>