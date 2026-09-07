<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Generate Report";


if ($_SERVER['REQUEST_METHOD'] !== "POST") {

  header("Location: reports.php");
  exit;

}

requireCSRFToken("reports.php");


$report_type = $_POST['report_type'] ?? "";

$department_id = $_POST['department_id'] ?? "";

$employee_id = $_POST['employee_id'] ?? "";

$date_from = $_POST['date_from'] ?? "";

$date_to = $_POST['date_to'] ?? "";


if (empty($report_type)) {

  $_SESSION['report_error'] = "Please select a report type.";

  header("Location: reports.php");
  exit;

}


$report_title = "";

$columns = [];

$data = [];

$params = [];


if ($report_type === "employee") {

  $report_title = "Employee Report";

  $columns = [

    "Employee Code",
    "Employee Name",
    "Department",
    "Position",
    "Employment Status",
    "Hire Date"

  ];


  $sql = "

    SELECT

      e.employee_code,

      CONCAT(
        e.first_name,
        ' ',
        e.last_name
      ) AS employee_name,

      d.department_name,

      p.position_name,

      e.employment_status,

      e.hire_date

    FROM employees e

    LEFT JOIN departments d
      ON e.department_id = d.department_id

    LEFT JOIN positions p
      ON e.position_id = p.position_id

    WHERE 1=1

  ";


  if (!empty($department_id)) {

    $sql .= "

      AND e.department_id = ?

    ";

    $params[] = $department_id;

  }


  if (!empty($employee_id)) {

    $sql .= "

      AND e.employee_id = ?

    ";

    $params[] = $employee_id;

  }


  $sql .= "

    ORDER BY e.first_name ASC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


elseif ($report_type === "attendance") {

  $report_title = "Attendance Report";


  $columns = [

    "Employee",
    "Attendance Date",
    "Time In",
    "Time Out",
    "Status"

  ];


  $sql = "

    SELECT

      CONCAT(
        e.first_name,
        ' ',
        e.last_name
      ) AS employee_name,

      a.attendance_date,

      a.time_in,

      a.time_out,

      a.status


    FROM attendance a


    INNER JOIN employees e

      ON a.employee_id = e.employee_id


    WHERE 1=1

  ";


  if (!empty($employee_id)) {

    $sql .= "

      AND a.employee_id = ?

    ";

    $params[] = $employee_id;

  }


  if (!empty($date_from)) {

    $sql .= "

      AND a.attendance_date >= ?

    ";

    $params[] = $date_from;

  }


  if (!empty($date_to)) {

    $sql .= "

      AND a.attendance_date <= ?

    ";

    $params[] = $date_to;

  }


  $sql .= "

    ORDER BY a.attendance_date DESC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


elseif ($report_type === "payroll") {

  $report_title = "Payroll Report";


  $columns = [

    "Employee",
    "Pay Period",
    "Basic Salary",
    "Allowances",
    "Deductions",
    "Gross Pay",
    "Net Pay",
    "Status",
    "Generated Date"

  ];


  $sql = "

    SELECT

      CONCAT(
        e.first_name,
        ' ',
        e.last_name
      ) AS employee_name,

      p.pay_period,

      p.basic_salary,

      p.allowances,

      p.deductions,

      p.gross_pay,

      p.net_pay,

      p.status,

      p.created_at


    FROM payroll p


    INNER JOIN employees e

      ON p.employee_id = e.employee_id


    WHERE 1=1

  ";


  if (!empty($employee_id)) {

    $sql .= "

      AND p.employee_id = ?

    ";

    $params[] = $employee_id;

  }


  if (!empty($date_from)) {

    $sql .= "

      AND DATE(p.created_at) >= ?

    ";

    $params[] = $date_from;

  }


  if (!empty($date_to)) {

    $sql .= "

      AND DATE(p.created_at) <= ?

    ";

    $params[] = $date_to;

  }


  $sql .= "

    ORDER BY p.created_at DESC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


elseif ($report_type === "leave") {

  $report_title = "Leave Report";


  $columns = [

    "Employee",
    "Leave Type ID",
    "Start Date",
    "End Date",
    "Status",
    "Reason"

  ];


  $sql = "

    SELECT

      CONCAT(
        e.first_name,
        ' ',
        e.last_name
      ) AS employee_name,

      l.leave_type_id,

      l.start_date,

      l.end_date,

      l.status,

      l.reason


    FROM leave_requests l


    INNER JOIN employees e

      ON l.employee_id = e.employee_id


    WHERE 1=1

  ";


  if (!empty($employee_id)) {

    $sql .= "

      AND l.employee_id = ?

    ";

    $params[] = $employee_id;

  }


  if (!empty($date_from)) {

    $sql .= "

      AND l.start_date >= ?

    ";

    $params[] = $date_from;

  }


  if (!empty($date_to)) {

    $sql .= "

      AND l.end_date <= ?

    ";

    $params[] = $date_to;

  }


  $sql .= "

    ORDER BY l.created_at DESC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


elseif ($report_type === "resignation") {

  $report_title = "Resignation Report";


  $columns = [

    "Employee",
    "Reason",
    "Resignation Date",
    "Status"

  ];


  $sql = "

    SELECT

      CONCAT(
        e.first_name,
        ' ',
        e.last_name
      ) AS employee_name,

      r.reason,

      r.resignation_date,

      r.status


    FROM resignations r


    INNER JOIN employees e

      ON r.employee_id = e.employee_id


    WHERE 1=1

  ";


  if (!empty($employee_id)) {

    $sql .= "

      AND r.employee_id = ?

    ";

    $params[] = $employee_id;

  }


  if (!empty($date_from)) {

    $sql .= "

      AND r.resignation_date >= ?

    ";

    $params[] = $date_from;

  }


  if (!empty($date_to)) {

    $sql .= "

      AND r.resignation_date <= ?

    ";

    $params[] = $date_to;

  }


  $sql .= "

    ORDER BY r.resignation_date DESC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


elseif ($report_type === "applicant") {

  $report_title = "Applicant Report";


  $columns = [

    "Applicant Name",
    "Email",
    "Phone",
    "Status",
    "Date Applied"

  ];


  $sql = "

    SELECT

      CONCAT(
        a.first_name,
        ' ',
        a.last_name
      ) AS applicant_name,

      a.email,

      a.phone,

      a.status,

      a.created_at


    FROM applications a


    WHERE 1=1

  ";


  if (!empty($date_from)) {

    $sql .= "

      AND DATE(a.created_at) >= ?

    ";

    $params[] = $date_from;

  }


  if (!empty($date_to)) {

    $sql .= "

      AND DATE(a.created_at) <= ?

    ";

    $params[] = $date_to;

  }


  $sql .= "

    ORDER BY a.created_at DESC

  ";


  $stmt = $conn->prepare($sql);

  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


try {

  $generated_by = $_SESSION['user_id'] ?? null;


  $log = $conn->prepare("

    INSERT INTO report_logs

    (

      generated_by,

      report_type

    )


    VALUES

    (

      ?,

      ?

    )

  ");


  $log->execute([

    $generated_by,

    $report_title

  ]);


}

catch (PDOException $e) {

}


$_SESSION['report_title'] = $report_title;

$_SESSION['report_columns'] = $columns;

$_SESSION['report_data'] = $data;


$_SESSION['report_ready'] = true;

?>