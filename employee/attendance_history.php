<?php

session_start();

date_default_timezone_set("Asia/Manila");


require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Attendance History";

$employee_id = $_SESSION["employee_id"] ?? 0;


if (!$employee_id) {

    header("Location: ../login.php");
    exit;

}


$today = date("Y-m-d");
$month = $_POST["month"] ?? date("Y-m");
$dateFrom = $_POST["date_from"] ?? "";
$dateTo = $_POST["date_to"] ?? "";
$status = $_POST["status"] ?? "";

$errorMessage = "";


if (!empty($dateFrom) && $dateFrom > $today) {

    $errorMessage = "Future dates are not allowed.";

} elseif (!empty($dateTo) && $dateTo > $today) {

    $errorMessage = "Future dates are not allowed.";

} elseif (
    !empty($dateFrom)
    &&
    !empty($dateTo)
    &&
    $dateFrom > $dateTo
) {

    $errorMessage =
        "Invalid date range. From date cannot be later than To date.";

}


$summaryStmt = $conn->prepare("

SELECT

    COALESCE(SUM(status='Present'),0) AS present,
    COALESCE(SUM(status='Late'),0) AS late,
    COALESCE(SUM(status='Leave'),0) AS leave_count,
    COALESCE(SUM(status='Absent'),0) AS absent

FROM attendance

WHERE employee_id = :employee_id

");

$summaryStmt->execute([

    ":employee_id" => $employee_id

]);


$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

$present = (int) $summary["present"];
$late = (int) $summary["late"];
$leave = (int) $summary["leave_count"];
$absent = (int) $summary["absent"];


$sql = "

SELECT

    attendance_date,
    work_type,
    time_in,
    break_out,
    break_in,
    time_out,
    total_working_hours,
    overtime_hours,
    undertime_hours,
    status,
    remarks

FROM attendance

WHERE employee_id = :employee_id

";


$params = [

    ":employee_id" => $employee_id

];


if (!empty($month)) {

    $sql .= "
    AND DATE_FORMAT(attendance_date,'%Y-%m') = :month
    ";
    $params[":month"] = $month;

}

if (!empty($dateFrom)) {

    $sql .= "
    AND attendance_date >= :date_from
    ";
    $params[":date_from"] = $dateFrom;

}

if (!empty($dateTo)) {

    $sql .= "
    AND attendance_date <= :date_to
    ";
    $params[":date_to"] = $dateTo;

}

if (!empty($status)) {

    $sql .= "
    AND status = :status
    ";
    $params[":status"] = $status;

}

$sql .= "

ORDER BY attendance_date DESC

";


if (empty($errorMessage)) {

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {

    $attendance = [];

}

function displayTime($time)
{

    if (empty($time)) {

        return "--:--";

    }

    return date(

        "h:i A",
        strtotime($time)

    );

}

function displayHours($time)
{

    if (empty($time) || $time == "00:00:00") {

        return "--";

    }

    return substr($time, 0, 5);

}

function statusClass($status)
{

    return match ($status) {

        "Present" => "present",
        "Late" => "late",
        "Leave" => "leave",
        "Absent" => "absent",
        "Holiday" => "holiday",
        "Half Day" => "halfday",
        default => ""

    };

}

function workTypeClass($type)
{

    return match ($type) {

        "Regular" => "regular",
        "Holiday" => "holiday",
        "Rest Day" => "restday",
        default => ""

    };

}

function overtimeDisplay($time)
{

    if (
        empty($time)
        ||
        $time == "00:00:00"
        ||
        $time == "00:00"
    ) {

        return "No OT";

    }

    $parts = explode(":", $time);

    $hours = (int) $parts[0];
    $minutes = (int) $parts[1];

    $display = [];

    if ($hours > 0) {

        $display[] = $hours . " hr";

    }

    if ($minutes > 0) {

        $display[] = $minutes . " mins";

    }

    return "+" . implode(" ", $display);

}

function undertimeDisplay($time)
{

    if (empty($time) || $time == "00:00:00") {

        return "None";

    }

    return "-" . substr($time, 0, 5);

}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

</head>


<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-attendance-history">

                <div class="employee-attendance-header">

                    <div>

                        <h1>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Attendance History
                        </h1>

                        <p>View and monitor your attendance records.</p>

                    </div>

                </div>

                <div class="employee-attendance-summary">

                    <div class="employee-attendance-card blue">

                        <div class="employee-attendance-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div class="employee-attendance-content">

                            <span>
                                Present
                            </span>

                            <h2>
                                <?= $present ?>
                            </h2>

                            <p>Total Present Days</p>

                        </div>

                    </div>

                    <div class="employee-attendance-card orange">

                        <div class="employee-attendance-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="employee-attendance-content">

                            <span>
                                Late
                            </span>

                            <h2>
                                <?= $late ?>
                            </h2>

                            <p>Total Late Days</p>

                        </div>

                    </div>

                    <div class="employee-attendance-card green">

                        <div class="employee-attendance-icon">
                            <i class="fa-solid fa-plane-departure"></i>
                        </div>

                        <div class="employee-attendance-content">

                            <span>
                                Leave
                            </span>


                            <h2>
                                <?= $leave ?>
                            </h2>

                            <p>Total Leave Days</p>

                        </div>

                    </div>

                    <div class="employee-attendance-card red">

                        <div class="employee-attendance-icon">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div class="employee-attendance-content">

                            <span>
                                Absent
                            </span>

                            <h2>
                                <?= $absent ?>
                            </h2>


                            <p>Total Absent Days</p>

                        </div>

                    </div>

                </div>

                <div class="employee-attendance-filter-card">

                                        <form
                        method="POST"
                        action="attendance_history.php"
                        id="attendanceFilterForm"
                    >

                        <?php csrfField(); ?>

                        <div class="employee-attendance-filter-grid">

                            <div class="employee-attendance-filter-group">

                                <label>
                                    Month
                                </label>

                                <input
                                    type="month"
                                    name="month"
                                    id="month"
                                    value="<?= htmlspecialchars($month) ?>"
                                    max="<?= date("Y-m") ?>"
                                />

                            </div>

                            <div class="employee-attendance-filter-group">

                                <label>
                                    From Date
                                </label>

                                <input
                                    type="date"
                                    id="date_from"
                                    name="date_from"
                                    value="<?= htmlspecialchars($dateFrom) ?>"
                                    max="<?= date("Y-m-d") ?>"
                                />

                            </div>

                            <div class="employee-attendance-filter-group">

                                <label>
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    id="date_to"
                                    name="date_to"
                                    value="<?= htmlspecialchars($dateTo) ?>"
                                    max="<?= date("Y-m-d") ?>"
                                />

                            </div>

                            <div class="employee-attendance-filter-group">

                                <label>
                                    Status
                                </label>

                                <select name="status">

                                    <option value="">
                                        All Status
                                    </option>

                                    <option value="Present"
                                        <?= $status == "Present" ? "selected" : "" ?>

                                    >

                                        Present
                                    </option>

                                    <option value="Late"
                                        <?= $status == "Late" ? "selected" : "" ?>

                                    >

                                        Late
                                    </option>

                                    <option value="Leave"
                                        <?= $status == "Leave" ? "selected" : "" ?>

                                    >

                                        Leave
                                    </option>

                                    <option value="Absent"
                                        <?= $status == "Absent" ? "selected" : "" ?>

                                    >

                                        Absent
                                    </option>

                                </select>

                            </div>

                            <div class="employee-attendance-filter-group">

                                <label>
                                    &nbsp;
                                </label>

                                <button
                                    type="submit"
                                    class="employee-attendance-search-btn"

                                >

                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    Search
                                </button>

                            </div>

                        </div>

                        <div class="employee-attendance-buttons">

                            <button
                                type="button"
                                id="exportPdfBtn"
                                class="employee-attendance-btn pdf"

                            >

                                <i class="fa-solid fa-file-pdf"></i>
                                Export PDF
                            </button>

                            <a
                                href="attendance_history.php"
                                class="employee-attendance-btn reset"

                            >

                                <i class="fa-solid fa-rotate-right"></i>
                                Reset Filters
                            </a>

                        </div>

                    </form>

                                        <form
                        method="POST"
                        action="attendance_pdf.php"
                        id="pdfExportForm"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="month"
                            id="pdf_month"
                        />

                        <input
                            type="hidden"
                            name="date_from"
                            id="pdf_date_from"
                        />

                        <input
                            type="hidden"
                            name="date_to"
                            id="pdf_date_to"
                        />

                        <input
                            type="hidden"
                            name="status"
                            id="pdf_status"
                        />

                    </form>

                </div>

                <div class="employee-attendance-table-card">

                    <div class="employee-attendance-table-wrapper">

                        <table class="employee-attendance-table">

                            <thead>

                                <tr>

                                    <th>Date</th>
                                    <th>Work Type</th>
                                    <th>Time In</th>
                                    <th>Break</th>
                                    <th>Time Out</th>
                                    <th>Working Hours</th>
                                    <th>Overtime</th>
                                    <th>Undertime</th>
                                    <th>Status</th>
                                    <th>Remarks</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (!empty($attendance)): ?>

                                    <?php foreach ($attendance as $row): ?>

                                        <tr>

                                            <td>

                                                <?= date(
                                                    "F d, Y",
                                                    strtotime($row["attendance_date"])
                                                ) ?>

                                            </td>

                                            <td>

                                                <span class="employee-worktype-badge <?= workTypeClass($row["work_type"]) ?>">

                                                    <?= htmlspecialchars($row["work_type"]) ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?= displayTime($row["time_in"]) ?>

                                            </td>

                                            <td>

                                                <div class="employee-break-wrapper">

                                                    <span class="employee-break-out">

                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                        <?= displayTime($row["break_out"]) ?>

                                                    </span>

                                                    <span class="employee-break-in">

                                                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                                        <?= displayTime($row["break_in"]) ?>

                                                    </span>

                                                </div>

                                            </td>

                                            <td>

                                                <?= displayTime($row["time_out"]) ?>

                                            </td>

                                            <td>

                                                <span class="employee-hours-badge">

                                                    <i class="fa-regular fa-clock"></i>
                                                    <?= displayHours($row["total_working_hours"]) ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?php if (
                                                    !empty($row["overtime_hours"])
                                                    &&
                                                    $row["overtime_hours"] != "00:00:00"
                                                ): ?>

                                                    <span class="employee-overtime-badge">

                                                        <i class="fa-solid fa-arrow-trend-up"></i>
                                                        <?= overtimeDisplay($row["overtime_hours"]) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span class="employee-no-overtime">

                                                        No OT

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?php if (
                                                    !empty($row["undertime_hours"])
                                                    &&
                                                    $row["undertime_hours"] != "00:00:00"
                                                ): ?>

                                                    <span class="employee-undertime-badge">

                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        <?= undertimeDisplay($row["undertime_hours"]) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span class="employee-no-undertime">

                                                        No UT

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <span class="employee-status-badge <?= statusClass($row["status"]) ?>">

                                                    <?= htmlspecialchars($row["status"]) ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?php if (!empty($row["remarks"])): ?>

                                                    <?= htmlspecialchars($row["remarks"]) ?>

                                                <?php else: ?>

                                                    <span class="employee-no-remarks">

                                                        No remarks

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="10">

                                            <div class="employee-attendance-empty">

                                                <i class="fa-solid fa-calendar-xmark"></i>

                                                <h3>No Attendance Records Found</h3>

                                                <p>We couldn't find any attendance records that match your selected filters.</p>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/employee.js"></script>


</body>

</html>