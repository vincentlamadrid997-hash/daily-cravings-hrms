<?php

require_once "../config/db.php";

header("Content-Type: application/json");


if (!isset($_GET['employee_id']) || !isset($_GET['pay_period'])) {

    echo json_encode([
        "success" => false,
        "message" => "Employee ID or pay period missing."
    ]);

    exit;
}


$employee_id = (int) $_GET['employee_id'];
$pay_period  = trim($_GET['pay_period']);


$stmt = $conn->prepare("
    SELECT basic_salary
    FROM employees
    WHERE employee_id = ?
    LIMIT 1
");

$stmt->execute([$employee_id]);

$employee = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$employee) {

    echo json_encode([
        "success" => false,
        "message" => "Employee not found."
    ]);

    exit;
}


$basic_salary = (float) $employee['basic_salary'];


$stmt = $conn->prepare("
    SELECT COALESCE(SUM(amount),0)
    FROM allowances
    WHERE employee_id = ?
");

$stmt->execute([$employee_id]);

$allowances = (float) $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COALESCE(SUM(amount),0)
    FROM deductions
    WHERE employee_id = ?
");

$stmt->execute([$employee_id]);

$deductions = (float) $stmt->fetchColumn();


// Pay period bounds, e.g. "2026-09" -> 2026-09-01 to 2026-09-30
$period_start = date("Y-m-01", strtotime($pay_period . "-01"));
$period_end   = date("Y-m-t", strtotime($pay_period . "-01"));


$stmt = $conn->prepare("
    SELECT
        status,
        COUNT(*) AS total
    FROM attendance
    WHERE employee_id = ?
    AND attendance_date BETWEEN ? AND ?
    GROUP BY status
");

$stmt->execute([$employee_id, $period_start, $period_end]);


$absent = 0;
$late   = 0;


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    if ($row['status'] == "Absent") {
        $absent = (int) $row['total'];
    }

    if ($row['status'] == "Late") {
        $late = (int) $row['total'];
    }

}


// Undertime / overtime totals, regardless of status, within the pay period
$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(TIME_TO_SEC(undertime_hours)),0) AS undertime_seconds,
        COALESCE(SUM(TIME_TO_SEC(overtime_hours)),0) AS overtime_seconds
    FROM attendance
    WHERE employee_id = ?
    AND attendance_date BETWEEN ? AND ?
");

$stmt->execute([$employee_id, $period_start, $period_end]);

$hoursRow = $stmt->fetch(PDO::FETCH_ASSOC);

$undertime_hours = ((float) $hoursRow['undertime_seconds']) / 3600;
$overtime_hours  = ((float) $hoursRow['overtime_seconds']) / 3600;


// Required hours per day, from Attendance Settings (fallback to 8 if not configured)
$requiredHoursStmt = $conn->query("
    SELECT required_hours
    FROM attendance_settings
    LIMIT 1
");

$requiredHoursRow = $requiredHoursStmt->fetch(PDO::FETCH_ASSOC);

$required_hours_per_day = (!empty($requiredHoursRow['required_hours']))
    ? (float) $requiredHoursRow['required_hours']
    : 8;


$daily_rate  = $basic_salary / 26;
$hourly_rate = $daily_rate / $required_hours_per_day;


$undertime_deduction = $hourly_rate * $undertime_hours;
$overtime_pay        = $hourly_rate * $overtime_hours;


$attendance_deduction =
    ($daily_rate * $absent)
    +
    (($daily_rate * 0.10) * $late)
    +
    $undertime_deduction;


$gross_pay =
    $basic_salary
    +
    $allowances
    +
    $overtime_pay;


$total_deductions =
    $deductions
    +
    $attendance_deduction;


$net_pay =
    $gross_pay
    -
    $total_deductions;


if ($net_pay < 0) {

    $net_pay = 0;

}


echo json_encode([
    "success" => true,
    "basic_salary" => $basic_salary,
    "allowances" => $allowances,
    "deductions" => $deductions,
    "attendance_deduction" => $attendance_deduction,
    "overtime_pay" => $overtime_pay,
    "gross_pay" => $gross_pay,
    "net_pay" => $net_pay
]);