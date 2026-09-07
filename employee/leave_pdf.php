<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$employee_id = $_SESSION["employee_id"] ?? 0;


if (!$employee_id) {

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

    if (empty($date)) {
        return "-";

    }

    return date(
        "F d, Y",
        strtotime($date)
    );

}

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

function getStatusClass($status)
{

    switch ($status) {

        case "Approved":

            return "approved";

        case "Rejected":

            return "rejected";

        case "Cancelled":

            return "cancelled";

        default:

            return "pending";

    }

}


$leave_id = $_POST["leave_id"] ?? 0;

if (!$leave_id) {

    header("Location: leave_history.php");
    exit;

}


$stmt = $conn->prepare("

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

$stmt->execute([

    ":leave_id" => $leave_id,
    ":employee_id" => $employee_id

]);

$leave = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$leave) {

    $_SESSION["leave_error"] = "Leave request not found.";

    header("Location: leave_history.php");
    exit;

}


$employee_name = trim(

    $leave["first_name"]
    . " "
    .
    ($leave["middle_name"] ?? "")
    . " "
    .
    $leave["last_name"]

);

$leave_type = getLeaveType($leave);


$status_class = getStatusClass(

    $leave["status"]

);


$generated_date = date(

    "F d, Y h:i A"

);

$report_title = "Employee Leave Request Report";


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($report_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>


<style>

* {
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f5f7fb;
    padding: 40px;
    color: #37474F;
    margin: 0;
}

.report-container {
    max-width: 900px;
    margin: auto;
}

.report-page {
    background: #ffffff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    margin-bottom: 30px;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 3px solid #FFC72C;
    padding-bottom: 20px;
    margin-bottom: 25px;
}

.company-name {
    font-size: 24px;
    font-weight: 800;
    color: #003DA5;
    margin-bottom: 6px;
}

.company-subtitle {
    color: #607D8B;
    font-size: 13px;
}

.report-title {
    text-align: right;
}

.report-title h1 {
    margin: 0;
    color: #003DA5;
    font-size: 20px;
}

.report-title p {
    margin-top: 6px;
    color: #78909C;
    font-size: 12px;
}

.report-card {
    border: 1px solid #E3E8EF;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}

.report-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
}

.report-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #E3F2FD;
    color: #1565C0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.report-card-header h2 {
    margin: 0;
    font-size: 15px;
    color: #263238;
}

.report-card-header p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #90A4AE;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-group label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #607D8B;
    text-transform: uppercase;
}

.info-group span {
    font-size: 14px;
    color: #263238;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
}

.status-badge.pending {
    background: #FFF8E1;
    color: #F9A825;
}

.status-badge.approved {
    background: #E8F5E9;
    color: #2E7D32;
}

.status-badge.rejected {
    background: #FDECEC;
    color: #C62828;
}

.status-badge.cancelled {
    background: #ECEFF1;
    color: #546E7A;
}

@media print {

    body {
        background: white;
        padding: 0;
    }

    .report-page {
        box-shadow: none;
        border-radius: 0;
        page-break-after: always;
    }

    .no-print {
        display: none !important;
    }

}

</style>


</head>


<body>

<div class="report-container">

    <div class="report-page">

        <div class="report-header">

            <div>

                <div class="company-name">
                    Daily Cravings Foods Inc.
                </div>

                <div class="company-subtitle">
                    Employee Leave Management Report
                </div>

            </div>

            <div class="report-title">

                <h1>
                    <?= clean($report_title); ?>
                </h1>

                <p>
                    Generated:
                    <?= $generated_date; ?>
                </p>

            </div>

        </div>

        <div class="report-card">

            <div class="report-card-header">

                <div class="report-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>

                    <h2>Employee Information</h2>

                    <p>Employee profile details</p>

                </div>

            </div>

            <div class="info-grid">

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-id-card"></i>
                        Employee Code
                    </label>

                    <span>
                        <?= clean($leave["employee_code"]); ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-user"></i>
                        Full Name
                    </label>

                    <span>
                        <?= clean($employee_name); ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-building"></i>
                        Department
                    </label>

                    <span>
                        <?= clean($leave["department_name"]); ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-briefcase"></i>
                        Position
                    </label>

                    <span>
                        <?= clean($leave["position_name"]); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="report-card">

            <div class="report-card-header">

                <div class="report-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

                <div>

                    <h2>Leave Request Information</h2>

                    <p>Submitted leave application details</p>

                </div>

            </div>

            <div class="info-grid">

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-list"></i>
                        Leave Type
                    </label>

                    <span>
                        <?= clean($leave_type); ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-calendar"></i>
                        Inclusive Dates
                    </label>

                    <span>
                        <?= dateFormat($leave["start_date"]); ?>

                        <br>

                        to

                        <br>

                        <?= dateFormat($leave["end_date"]); ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-clock"></i>
                        Total Days
                    </label>

                    <span>
                        <?= number_format($leave["total_days"], 2); ?>
                        Day(s)
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-calendar-plus"></i>
                        Submitted Date
                    </label>

                    <span>
                        <?= dateFormat($leave["created_at"]); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="report-card">

            <div class="report-card-header">

                <div class="report-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div>

                    <h2>Approval Information</h2>

                    <p>Current leave request status</p>

                </div>

            </div>

            <div class="info-grid">

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-check"></i>
                        Status
                    </label>

                    <span>

                        <span class="status-badge <?= $status_class; ?>">

                            <i class="fa-solid fa-clock"></i>
                            <?= clean($leave["status"]); ?>

                        </span>

                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-message"></i>
                        Remarks
                    </label>

                    <span>
                        <?=
                            !empty($leave["remarks"])
                            ?
                            clean($leave["remarks"])
                            :
                            "No remarks"
                        ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

    <div class="report-page">

        <div class="report-card">

            <div class="report-card-header">

                <div class="report-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>

                <div>

                    <h2>Additional Information</h2>

                    <p>Leave explanation and uploaded document.</p>

                </div>

            </div>

            <div class="info-grid">

                <div class="info-group" style="grid-column:span 2;"

                >

                    <label>
                        <i class="fa-solid fa-comment"></i>
                        Leave Reason
                    </label>

                    <span>
                        <?=
                            !empty($leave["reason"])
                            ?
                            clean($leave["reason"])
                            :
                            "No reason provided"
                        ?>
                    </span>

                </div>

                <div class="info-group">

                    <label>
                        <i class="fa-solid fa-paperclip"></i>
                        Attachment
                    </label>

                    <span>

                        <?php if (!empty($leave["attachment"])): ?>

                            <a
                                href="../uploads/leaves/<?= clean($leave["attachment"]); ?>"
                                target="_blank"
                                style="
                                    color:#003DA5;
                                    font-weight:700;
                                    text-decoration:none;
                                "

                            >

                                <i class="fa-solid fa-eye"></i>
                                View Attachment
                            </a>

                        <?php else: ?>

                            No Attachment

                        <?php endif; ?>

                    </span>

                </div>

            </div>

        </div>

        <div class="report-card">

            <div class="report-card-header">

                <div class="report-icon">

                    <i class="fa-solid fa-ban"></i>

                </div>

                <div>

                    <h2>Cancellation Information</h2>

                    <p>Cancellation details if applicable.</p>

                </div>

            </div>

            <div class="info-grid">

                <div class="info-group" style="grid-column:span 2;"

                >

                    <label>
                        <i class="fa-solid fa-circle-xmark"></i>
                        Cancellation Reason
                    </label>

                    <span>
                        <?=
                            !empty($leave["cancelled_reason"])
                            ?
                            clean($leave["cancelled_reason"])
                            :
                            "N/A"
                        ?>
                    </span>

                </div>

            </div>

        </div>

        <div

            style="
                margin-top:40px;
                padding-top:20px;
                border-top:1px solid #E5E7EB;
                text-align:right;
                color:#78909C;
                font-size:13px;
            "
        >


            Generated:
            <?= $generated_date; ?>

            <br>

            Daily Cravings Foods Inc.

            <br>

            HR Management System

        </div>

    </div>

    <div

        class="no-print"

        style="
            display:flex;
            justify-content:flex-end;
            padding:20px 35px 35px;
        "
    >

        <button
            onclick="window.print();"

            style="
                background:#003DA5;
                color:white;
                border:none;
                padding:13px 25px;
                border-radius:14px;
                font-weight:700;
                cursor:pointer;
            "
        >

            <i class="fa-solid fa-print"></i>
            Print Leave Report
        </button>

    </div>

</div>


<script>

window.onload = function(){

    setTimeout(function(){

        window.print();

    }, 500);

};


window.onafterprint = function(){

    window.location.href = "leave_history.php";

};

</script>


</body>

</html>