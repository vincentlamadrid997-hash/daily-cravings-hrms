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

requireCSRFToken("attendance_history.php");

$month = $_POST["month"] ?? date("Y-m");
$dateFrom = $_POST["date_from"] ?? "";
$dateTo = $_POST["date_to"] ?? "";
$status = $_POST["status"] ?? "";


$employeeStmt = $conn->prepare("

    SELECT
        employee_code,
        first_name,
        middle_name,
        last_name

    FROM employees
    WHERE employee_id = :employee_id

");


$employeeStmt->execute([
    ":employee_id" => $employee_id
]);


$employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);


$employee_name = "";

if ($employee) {

    $employee_name =
        $employee["first_name"] . " " .
        ($employee["middle_name"] ?? "") . " " .
        $employee["last_name"];

}

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


$stmt = $conn->prepare($sql);
$stmt->execute($params);

$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

function overtimeDisplay($time)
{

    if (empty($time) || $time == "00:00:00") {

        return "--";

    }

    return "+" . substr($time, 0, 5);

}

function undertimeDisplay($time)
{

    if (empty($time) || $time == "00:00:00") {

        return "--";

    }

    return "-" . substr($time, 0, 5);

}


$report_title = "Employee Attendance Report";


$generated_date = date(
    "F d, Y h:i A"
);


?>

<style>

* {
    box-sizing: border-box;
}

body {

    font-family: "Segoe UI", Arial, sans-serif;
    background: #f5f7fb;
    padding: 40px;
    color: #37474F;

}

.report-container {

    max-width: 1200px;
    margin: auto;
    background: #ffffff;
    padding: 35px;
    border-radius: 20px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .08);

}

.header {

    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #FFC72C;
    padding-bottom: 20px;
    margin-bottom: 25px;

}

.company {

    font-size: 26px;
    font-weight: 800;
    color: #003DA5;

}

.subtitle {

    margin-top: 8px;
    color: #607D8B;
    font-size: 14px;

}

.report-info {

    text-align: right;

}

.report-info h2 {

    margin: 0;
    color: #003DA5;
    font-size: 24px;
    font-weight: 800;

}

.report-info p {

    margin-top: 8px;
    color: #78909C;
    font-size: 13px;

}

.employee-info {

    background: #F8FAFC;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 5px solid #003DA5;

}

.employee-info h3 {

    margin: 0 0 12px;
    color: #003DA5;
    font-size: 18px;

}

.employee-info p {

    margin: 5px 0;
    font-size: 14px;
    color: #455A64;

}

.table-title {

    margin-top: 30px;
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: 800;
    color: #003DA5;

}

.table-wrapper {

    overflow: auto;

}

table {

    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;

}

thead th {

    background: #003DA5;
    color: white;
    padding: 14px;
    font-size: 13px;
    text-align: left;

}

tbody tr:nth-child(even) {

    background: #F8FAFC;

}

tbody tr:hover {

    background: #EEF5FF;

}

td {

    padding: 12px;
    border-bottom: 1px solid #E3E8EF;
    font-size: 13px;
    color: #455A64;

}

.status {

    display: inline-flex;
    padding: 7px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;

}

.status.present {

    background: #E8F5E9;
    color: #2E7D32;

}

.status.late {

    background: #FFF8E1;
    color: #F9A825;

}

.status.absent {

    background: #FDECEC;
    color: #C62828;

}

.status.leave {

    background: #E3F2FD;
    color: #1565C0;

}

.work-type {

    display: inline-flex;
    padding: 7px 14px;
    border-radius: 30px;
    background: #E3F2FD;
    color: #1565C0;
    font-weight: 700;
    font-size: 12px;

}

.footer {

    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #E5E7EB;
    text-align: right;
    font-size: 12px;
    color: #78909C;

}

.print-btn {

    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;

}

button {

    background: #003DA5;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .3s;

}

button:hover {

    background: #0D47A1;
    transform: translateY(-2px);

}

@media print {

    body {

        background: white;
        padding: 0;

    }

    .report-container {

        box-shadow: none;
        border-radius: 0;
        padding: 20px;

    }

    .no-print {

        display: none !important;

    }

    table {

        page-break-inside: auto;

    }

    tr {

        page-break-inside: avoid;

    }

}


</style>

<body>

<div class="report-container">

    <div class="header">

        <div>

            <div class="company">
                Daily Cravings Foods Inc.
            </div>

            <div class="subtitle">
                Employee Attendance Report
            </div>

        </div>

        <div class="report-info">

            <h2>
                <?= htmlspecialchars($report_title); ?>
            </h2>

            <p>Generated HR Attendance Report</p>

        </div>

    </div>

    <div class="employee-info">

        <h3>
            <i class="fa-solid fa-user"></i>
            Employee Information
        </h3>

        <p>
            <strong>Employee:</strong>
            <?= htmlspecialchars($employee_name ?? "N/A"); ?>
        </p>

        <p>
            <strong>Report Period:</strong>
            <?= !empty($month)
                ? date("F Y", strtotime($month . "-01"))
                : "All Records"; ?>
        </p>

        <p>
            <strong>Generated:</strong>
            <?= date("F d, Y h:i A"); ?>
        </p>

    </div>

    <div class="print-btn no-print">

        <button onclick="window.print();">

            <i class="fa-solid fa-print"></i>
            Print Attendance Report

        </button>

    </div>

    <div class="table-title">
        <i class="fa-solid fa-calendar-check"></i>
        Attendance Summary
    </div>

    <table>

        <thead>

            <tr>

                <th>Date</th>
                <th>Work Type</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Working Hours</th>
                <th>Status</th>

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
                            ); ?>

                        </td>

                        <td>

                            <span class="work-type">
                                <?= htmlspecialchars($row["work_type"]); ?>
                            </span>

                        </td>

                        <td>

                            <?= displayTime($row["time_in"]); ?>

                        </td>

                        <td>

                            <?= displayTime($row["time_out"]); ?>

                        </td>

                        <td>

                            <?= displayHours($row["total_working_hours"]); ?>

                        </td>

                        <td>

                            <span class="status <?= strtolower($row["status"]); ?>">
                                <?= htmlspecialchars($row["status"]); ?>
                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">
                        No Attendance Records Available
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <div class="table-title">
        <i class="fa-solid fa-clock"></i>
        Attendance Details
    </div>

    <table>

        <thead>

            <tr>

                <th>Date</th>
                <th>Break Out</th>
                <th>Break In</th>
                <th>Overtime</th>
                <th>Undertime</th>
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
                            ); ?>

                        </td>

                        <td>

                            <?= displayTime($row["break_out"]); ?>

                        </td>

                        <td>

                            <?= displayTime($row["break_in"]); ?>

                        </td>

                        <td>

                            <?php

                            if (
                                !empty($row["overtime_hours"]) &&
                                $row["overtime_hours"] != "00:00:00"
                            ) {

                                echo "+" . substr(
                                    $row["overtime_hours"],
                                    0,
                                    5
                                );

                            } else {

                                echo "No OT";

                            }

                            ?>

                        </td>

                        <td>

                            <?php

                            if (
                                !empty($row["undertime_hours"]) &&
                                $row["undertime_hours"] != "00:00:00"
                            ) {

                                echo "-" . substr(
                                    $row["undertime_hours"],
                                    0,
                                    5
                                );

                            } else {

                                echo "No UT";

                            }

                            ?>

                        </td>

                        <td>

                            <?= !empty($row["remarks"])
                                ? htmlspecialchars($row["remarks"])
                                : "No remarks"; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">
                        No Attendance Details Available
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <div class="footer">

        Generated:
        <?= date("F d, Y h:i A"); ?>

        <br>

        Daily Cravings Foods Inc.

        <br>

        HR Management System

    </div>

</div>


<script>

window.onload = function () {

    window.print();

};

window.onafterprint = function () {

    window.location.href = "attendance_history.php";

};

</script>


</body>

</html>