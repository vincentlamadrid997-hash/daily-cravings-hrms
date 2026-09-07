<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Leave History";



$employee_id = $_SESSION["employee_id"] ?? 0;


if(!$employee_id){

    header("Location: ../login.php");
    exit;

}


function clean($value)
{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"

    );

}


$employeeStmt = $conn->prepare("

SELECT

    e.employee_id,
    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,
    d.department_name,
    p.position_name

FROM employees e

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE e.employee_id = :employee_id
LIMIT 1

");


$employeeStmt->execute([

    ":employee_id" => $employee_id

]);


$employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);


if(!$employee){

    header("Location: dashboard.php");
    exit;

}


function getLeaveStatus($status)

{

    switch($status){

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


function getLeaveType($leave)

{

    if(!empty($leave["custom_leave_type"])){
        return $leave["custom_leave_type"];
    }

    if(!empty($leave["leave_type_name"])){
        return $leave["leave_type_name"];
    }

    return "Others";

}


function dateFormat($date)

{

    if(empty($date)){
        return "-";
    }

    return date(
        "M d, Y",
        strtotime($date)
    );

}


function formatLeaveDays($days)

{

    $days = (float)$days;

    if($days == floor($days)){
        return number_format(
            $days,
            0
        );

    }

    return rtrim(

        rtrim(

            number_format(
                $days,
                1
            ),
            "0"
        ),
        "."
    );
}


function countLeave(

    $conn,
    $employee_id,
    $status = null

)

{

    if($status){

        $stmt = $conn->prepare("

            SELECT COUNT(*)
            FROM leave_requests
            WHERE employee_id = ?
            AND status = ?

        ");

        $stmt->execute([

            $employee_id,
            $status

        ]);

    }

    else{

        $stmt = $conn->prepare("

            SELECT COUNT(*)
            FROM leave_requests
            WHERE employee_id = ?

        ");

        $stmt->execute([

            $employee_id

        ]);

    }

    return (int)$stmt->fetchColumn();

}

$totalLeave = countLeave(

    $conn,
    $employee_id

);

$pendingLeave = countLeave(

    $conn,
    $employee_id,
    "Pending"

);

$approvedLeave = countLeave(

    $conn,
    $employee_id,
    "Approved"

);

$rejectedLeave = countLeave(

    $conn,
    $employee_id,
    "Rejected"

);


$leaveStmt = $conn->prepare("


SELECT


    lr.leave_id,
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
    lt.leave_type_name

FROM leave_requests lr

LEFT JOIN leave_types lt
ON lr.leave_type_id = lt.leave_type_id

WHERE lr.employee_id = :employee_id
ORDER BY lr.created_at DESC
");


$leaveStmt->execute([

    ":employee_id" => $employee_id

]);


$leaveHistory = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION["leave_success"] ?? "";
$error = $_SESSION["leave_error"] ?? "";

unset($_SESSION["leave_success"]);
unset($_SESSION["leave_error"]);


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-leave-history-page">

                <div class="employee-leave-history-header">

                    <div class="employee-leave-history-title">

                        <h1>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Leave History
                        </h1>

                        <p>Review your submitted leave requests and monitor approval status.</p>

                    </div>

                    <a href="leave_request.php"

                    class="employee-new-leave-btn">

                        <i class="fa-solid fa-plus"></i>
                        Submit Leave Request

                    </a>

                </div>

                <div class="employee-leave-summary-grid">

                    <div class="employee-leave-summary-card blue">

                        <div class="employee-summary-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>

                        <div class="employee-summary-content">

                            <span>
                                Total Requests
                            </span>

                            <h2>
                                <?= number_format($totalLeave); ?>
                            </h2>

                            <p>All submitted requests.</p>

                        </div>

                    </div>

                    <div class="employee-leave-summary-card orange">

                        <div class="employee-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="employee-summary-content">

                            <span>
                                Pending
                            </span>

                            <h2>
                                <?= number_format($pendingLeave); ?>
                            </h2>

                            <p>Waiting for approval.</p>

                        </div>

                    </div>

                    <div class="employee-leave-summary-card green">

                        <div class="employee-summary-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div class="employee-summary-content">

                            <span>
                                Approved
                            </span>

                            <h2>
                                <?= number_format($approvedLeave); ?>
                            </h2>

                            <p>Approved requests.</p>

                        </div>

                    </div>

                    <div class="employee-leave-summary-card red">

                        <div class="employee-summary-icon">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div class="employee-summary-content">

                            <span>
                                Rejected
                            </span>

                            <h2>
                                <?= number_format($rejectedLeave); ?>
                            </h2>

                            <p>Declined requests.</p>

                        </div>

                    </div>

                </div>

                <div class="employee-leave-table-card">

                    <div class="employee-leave-table-title">

                        <h2>
                            <i class="fa-solid fa-table-list"></i>
                            Leave Request History
                        </h2>

                        <p>Browse all submitted leave applications.</p>

                    </div>

                    <div class="employee-leave-search">

                        <div class="employee-leave-search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                            type="text"
                            id="leaveSearch"
                            placeholder="Search leave type or status..."
                            >

                        </div>

                        <div class="employee-leave-filter-box">

                            <select id="statusFilter">

                                <option value="">
                                    All Status
                                </option>

                                <option value="Pending">
                                    Pending
                                </option>

                                <option value="Approved">
                                    Approved
                                </option>

                                <option value="Rejected">
                                    Rejected
                                </option>

                                <option value="Cancelled">
                                    Cancelled
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="employee-leave-table-wrapper">

                        <table
                        class="employee-leave-table"
                        id="leaveHistoryTable"
                        >

                            <thead>

                                <tr>

                                    <th>#</th>
                                    <th>Leave Type</th>
                                    <th>Inclusive Dates</th>
                                    <th>Days</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if(!empty($leaveHistory)): ?>

                                    <?php foreach($leaveHistory as $index=>$leave): ?>

                                        <?php

                                        $status = getLeaveStatus(
                                            $leave["status"]
                                        );

                                        ?>

                                        <tr>

                                            <td>
                                                <?= $index + 1; ?>
                                            </td>

                                            <td>

                                                <div class="employee-leave-type">
                                                    <i class="fa-solid fa-calendar-days"></i>

                                                    <strong>
                                                        <?= clean(getLeaveType($leave)); ?>
                                                    </strong>

                                                </div>

                                            </td>

                                            <td>

                                                <div class="employee-date-range">

                                                    <span>
                                                        <?= dateFormat($leave["start_date"]); ?>
                                                    </span>

                                                    <small>
                                                        to
                                                    </small>

                                                    <span>
                                                        <?= dateFormat($leave["end_date"]); ?>
                                                    </span>

                                                </div>

                                            </td>

                                            <td>

                                                <strong>
                                                    <?= formatLeaveDays($leave["total_days"]); ?>
                                                </strong>

                                                day<?=

                                                (float)$leave["total_days"] > 1

                                                ?
                                                "s"
                                                :
                                                ""
                                                ?>

                                            </td>

                                            <td>
                                                <?= dateFormat($leave["created_at"]); ?>
                                            </td>

                                            <td>

                                                <span class="employee-status-badge <?= $status["class"]; ?>">

                                                    <i class="fa-solid <?= $status["icon"]; ?>"></i>
                                                    <?= clean($leave["status"]); ?>

                                                </span>

                                            </td>
                                            
                                            <td>

                                                <div class="employee-leave-actions">

                                                                                                        <form
                                                    method="POST"
                                                    action="leave_view.php"
                                                    >

                                                        <?php csrfField(); ?>

                                                        <input
                                                        type="hidden"
                                                        name="leave_id"
                                                        value="<?= htmlspecialchars($leave["leave_id"]); ?>"
                                                        >

                                                        <button
                                                        type="submit"
                                                        class="employee-leave-view-btn"
                                                        >

                                                            <i class="fa-solid fa-eye"></i>
                                                            View Details

                                                        </button>

                                                    </form>

                                                                                                        <form
                                                    method="POST"
                                                    action="leave_pdf.php"
                                                    target="_blank"
                                                    >

                                                        <?php csrfField(); ?>

                                                        <input
                                                        type="hidden"
                                                        name="leave_id"
                                                        value="<?= $leave["leave_id"]; ?>"
                                                        >

                                                        <button
                                                        type="submit"
                                                        class="employee-leave-pdf-btn"
                                                        title="Export Leave PDF"
                                                        >

                                                            <i class="fa-solid fa-file-pdf"></i>
                                                            PDF

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="7">

                                            <div class="employee-leave-empty">
                                                <i class="fa-solid fa-calendar-xmark"></i>

                                                <h3>No Leave Requests Found</h3>

                                                <p>You have not submitted any leave request yet.</p>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endif; ?>

                                <tr id="employeeSearchEmpty" style="display:none;">

                                    <td colspan="7">

                                        <div class="employee-leave-empty">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <h3>No Matching Leave Request</h3>

                                            <p>No leave request matched your search.</p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/employee.js"></script>


<?php if($success): ?>

<script>

Swal.fire({

    icon:"success",
    title:"Success",
    text:<?= json_encode($success); ?>,
    confirmButtonColor:"#0D47A1"

});

</script>

<?php endif; ?>


<?php if($error): ?>

<script>

Swal.fire({

    icon:"error",
    title:"Error",
    text:<?= json_encode($error); ?>,
    confirmButtonColor:"#C62828"

});

</script>

<?php endif; ?>


</body>


</html>