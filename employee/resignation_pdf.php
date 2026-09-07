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

requireCSRFToken("resignation_history.php");

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

    if (
        empty($date) ||
        $date === "0000-00-00" ||
        $date === "0000-00-00 00:00:00"
    ) {

        return "-";

    }

    return date(
        "F d, Y",
        strtotime($date)
    );

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


$resignation_id = $_POST["resignation_id"] ?? 0;

if (!$resignation_id) {

    header("Location: resignation_history.php");
    exit;

}


$stmt = $conn->prepare("

SELECT

    r.resignation_id,
    r.employee_id,
    r.reason,
    r.resignation_date,
    r.last_working_day,
    r.status,
    r.remarks,
    r.approved_by,
    r.approved_date,
    r.created_at,
    r.updated_at,

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,

    d.department_name,

    p.position_name

FROM resignations r

LEFT JOIN employees e
ON r.employee_id = e.employee_id

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE
    r.resignation_id = :resignation_id
AND
    r.employee_id = :employee_id

LIMIT 1

");

$stmt->execute([

    ":resignation_id" => $resignation_id,
    ":employee_id"    => $employee_id

]);

$resignation = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$resignation) {

    $_SESSION["resignation_error"] = "Resignation request not found.";

    header("Location: resignation_history.php");
    exit;

}


$employee_name = trim(

    $resignation["first_name"] .
    " " .
    ($resignation["middle_name"] ? $resignation["middle_name"] . " " : "") .
    $resignation["last_name"]

);

$status_class = getStatusClass(

    $resignation["status"]

);

$generated_date = date(

    "F d, Y h:i A"

);

$report_title = "Employee Resignation Report";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> <?= clean($report_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

*{

    margin:0;
    padding:0;
    box-sizing:border-box;

}


body{

    background:#F5F7FB;
    color:#263238;
    padding:40px;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    line-height:1.6;

}

.employee-resignation-pdf-container{

    max-width:1000px;
    margin:0 auto;
    background:#FFFFFF;
    border-radius:28px;
    overflow:hidden;

    box-shadow:
        0 15px 40px rgba(0,0,0,.10);

}

.employee-resignation-pdf-page{

    padding:45px;
    min-height:100vh;

}

.employee-resignation-pdf-header{

    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:30px;
    padding-bottom:30px;
    margin-bottom:35px;
    border-bottom:4px solid #FFC107;

}

.employee-resignation-pdf-company{

    display:flex;
    flex-direction:column;

}

.employee-resignation-pdf-company-name{

    font-size:32px;
    font-weight:900;
    color:#003DA5;
    letter-spacing:.5px;

}

.employee-resignation-pdf-company-subtitle{

    margin-top:10px;
    font-size:15px;
    color:#607D8B;
    font-weight:600;

}

.employee-resignation-pdf-title{

    text-align:right;

}

.employee-resignation-pdf-title h1{

    margin:0;
    color:#003DA5;
    font-size:26px;
    font-weight:900;
    text-transform:uppercase;

}

.employee-resignation-pdf-title p{

    margin-top:10px;
    font-size:13px;
    color:#78909C;

}

.employee-resignation-pdf-card{

    background:#FFFFFF;
    border:1px solid #E8EDF5;
    border-radius:22px;
    padding:30px;
    margin-bottom:28px;

    box-shadow:
        0 8px 20px rgba(0,0,0,.05);

}

.employee-resignation-pdf-card-header{

    display:flex;
    align-items:center;
    gap:16px;
    margin-bottom:25px;
    padding-bottom:20px;
    border-bottom:1px solid #EDF1F7;

}

.employee-resignation-pdf-icon{

    width:58px;
    height:58px;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-shrink:0;
    border-radius:18px;
    background:#E3F2FD;
    color:#003DA5;
    font-size:22px;

}

.employee-resignation-pdf-card-header h2{

    margin:0;
    color:#263238;
    font-size:22px;
    font-weight:800;

}

.employee-resignation-pdf-card-header p{

    margin-top:6px;
    color:#78909C;
    font-size:14px;

}

.employee-resignation-pdf-grid{

    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:24px;

}

.employee-resignation-pdf-group{

    display:flex;
    flex-direction:column;
    gap:8px;

}

.employee-resignation-pdf-group.full-width{

    grid-column:span 2;

}

.employee-resignation-pdf-group label{

    display:flex;
    align-items:center;
    gap:8px;
    color:#607D8B;
    font-size:13px;
    font-weight:700;

}

.employee-resignation-pdf-group label i{

    color:#003DA5;

}

.employee-resignation-pdf-group span{

    color:#263238;
    font-size:15px;
    font-weight:600;
    line-height:1.8;
    word-break:break-word;

}

.employee-resignation-pdf-group a{

    display:inline-flex;
    align-items:center;
    gap:8px;
    color:#003DA5;
    text-decoration:none;
    font-weight:700;

}

.employee-resignation-pdf-group a:hover{

    text-decoration:underline;

}

.employee-resignation-pdf-status{

    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:9px 18px;
    border-radius:30px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;

}

.employee-resignation-pdf-status.pending{

    background:#FFF8E1;
    color:#F9A825;

}

.employee-resignation-pdf-status.approved{

    background:#E8F5E9;
    color:#2E7D32;

}

.employee-resignation-pdf-status.rejected{

    background:#FDECEC;
    color:#C62828;

}

.employee-resignation-pdf-status.cancelled{

    background:#ECEFF1;
    color:#607D8B;

}

.employee-resignation-pdf-footer{

    margin-top:40px;
    padding-top:20px;
    border-top:1px solid #E5E7EB;
    text-align:right;
    color:#78909C;
    font-size:13px;
    line-height:1.8;

}

.employee-resignation-pdf-print-wrapper{

    display:flex;
    justify-content:flex-end;
    padding:20px 35px 35px;

}

.employee-resignation-pdf-print-btn{

    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding:13px 26px;
    border:none;
    border-radius:14px;
    background:#003DA5;
    color:#FFFFFF;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    transition:.30s ease;

}

.employee-resignation-pdf-print-btn:hover{

    background:#002B75;

}

.employee-resignation-pdf-print-btn i{

    font-size:15px;

}

@media print{

    @page{

        size:A4;
        margin:12mm;

    }

    body{

        background:#FFFFFF;
        margin:0;
        padding:0;

    }

    .employee-resignation-pdf-container{

        box-shadow:none;
        border-radius:0;

    }

    .employee-resignation-pdf-page{

        min-height:auto;
        padding:30px;
        page-break-after:always;
        break-after:page;

    }

    .employee-resignation-pdf-page:last-child{

        page-break-after:auto;
        break-after:auto;

    }

    .employee-resignation-pdf-card{

        page-break-inside:avoid;
        break-inside:avoid;

    }

    .employee-resignation-pdf-footer{

        page-break-inside:avoid;
        break-inside:avoid;

    }

    .no-print{

        display:none !important;

    }

}

@media(max-width:900px){

    body{

        padding:18px;

    }

    .employee-resignation-pdf-header{

        flex-direction:column;
        align-items:flex-start;
        gap:20px;

    }

    .employee-resignation-pdf-title{

        text-align:left;

    }

    .employee-resignation-pdf-page{

        padding:28px;

    }

    .employee-resignation-pdf-grid{

        grid-template-columns:1fr;

    }

    .employee-resignation-pdf-group.full-width{

        grid-column:span 1;

    }

}

</style>

</head>

<body>

<div class="employee-resignation-pdf-container">

    <div class="employee-resignation-pdf-page">

        <div class="employee-resignation-pdf-header">

            <div class="employee-resignation-pdf-company">

                <div class="employee-resignation-pdf-company-name">
                    Daily Cravings Foods Inc.
                </div>

                <div class="employee-resignation-pdf-company-subtitle">
                    Employee Resignation Management Report
                </div>

            </div>

            <div class="employee-resignation-pdf-title">

                <h1>
                    <?= clean($report_title); ?>
                </h1>

                <p>
                    Generated:
                    <?= clean($generated_date); ?>
                </p>

            </div>

        </div>

        <div class="employee-resignation-pdf-card">

            <div class="employee-resignation-pdf-card-header">

                <div class="employee-resignation-pdf-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>

                    <h2>Employee Information</h2>

                    <p>Employee profile details</p>

                </div>

            </div>

            <div class="employee-resignation-pdf-grid">

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-id-card"></i>
                        Employee Code
                    </label>

                    <span>
                        <?= clean($resignation["employee_code"]); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-user"></i>
                        Full Name
                    </label>

                    <span>
                        <?= clean($employee_name); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-building"></i>
                        Department
                    </label>

                    <span>
                        <?= clean($resignation["department_name"]); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-briefcase"></i>
                        Position
                    </label>

                    <span>
                        <?= clean($resignation["position_name"]); ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

    <div class="employee-resignation-pdf-page">
        
        <div class="employee-resignation-pdf-card">

            <div class="employee-resignation-pdf-card-header">

                <div class="employee-resignation-pdf-icon">
                    <i class="fa-solid fa-file-signature"></i>
                </div>

                <div>

                    <h2>Resignation Information</h2>

                    <p>Submitted resignation request details</p>

                </div>

            </div>

            <div class="employee-resignation-pdf-grid">

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-calendar-day"></i>
                        Resignation Date
                    </label>

                    <span>
                        <?= dateFormat($resignation["resignation_date"]); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-calendar-plus"></i>
                        Submitted Date
                    </label>

                    <span>
                        <?= dateFormat($resignation["created_at"]); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-hourglass-half"></i>
                        Last Working Day
                    </label>

                    <span>
                        <?= dateFormat($resignation["last_working_day"]); ?>
                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-circle-check"></i>
                        Status
                    </label>

                    <span>

                        <span class="employee-resignation-pdf-status <?= $status_class; ?>">

                            <?= clean($resignation["status"]); ?>

                        </span>

                    </span>

                </div>

            </div>

        </div>

        <div class="employee-resignation-pdf-card">

            <div class="employee-resignation-pdf-card-header">

                <div class="employee-resignation-pdf-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div>

                    <h2>Approval Information</h2>

                    <p>Current resignation request status</p>

                </div>

            </div>

            <div class="employee-resignation-pdf-grid">

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-check"></i>
                        Status
                    </label>

                    <span>

                        <span class="employee-resignation-pdf-status <?= $status_class; ?>">

                            <?= clean($resignation["status"]); ?>

                        </span>

                    </span>

                </div>

                <div class="employee-resignation-pdf-group">

                    <label>
                        <i class="fa-solid fa-message"></i>
                        HR Remarks
                    </label>

                    <span>
                        <?=
                            !empty($resignation["remarks"])
                            ?
                            clean($resignation["remarks"])
                            :
                            "No remarks"
                        ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

    <div class="employee-resignation-pdf-page">

        <div class="employee-resignation-pdf-card">

            <div class="employee-resignation-pdf-card-header">

                <div class="employee-resignation-pdf-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>

                <div>

                    <h2>Additional Information</h2>

                    <p>Reason provided by the employee.</p>

                </div>

            </div>

            <div class="employee-resignation-pdf-grid">

                <div class="employee-resignation-pdf-group full-width">

                    <label>
                        <i class="fa-solid fa-comment"></i>
                        Reason for Resignation
                    </label>

                    <span>
                        <?=
                            !empty($resignation["reason"])
                            ?
                            clean($resignation["reason"])
                            :
                            "No reason provided"
                        ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="employee-resignation-pdf-footer">

            Generated:
            <?= clean($generated_date); ?>

            <br>

            Daily Cravings Foods Inc.

            <br>

            HR Management System

        </div>

    </div>

    <div class="employee-resignation-pdf-print-wrapper no-print">

        <button
            type="button"
            class="employee-resignation-pdf-print-btn"
            onclick="window.print();"
        >

            <i class="fa-solid fa-print"></i>

            Print Resignation Report

        </button>

    </div>

</div>


<script>

window.onload = function () {

    setTimeout(function () {

        window.print();

    }, 500);

};

window.onafterprint = function () {

    window.location.href = "resignation_history.php";

};

</script>


</body>

</html>