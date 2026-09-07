<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: reports.php");
    exit;
}

$report_type   = $_POST['report_type'] ?? "";
$department_id = $_POST['department_id'] ?? "";
$date_from     = $_POST['date_from'] ?? "";
$date_to       = $_POST['date_to'] ?? "";

if (empty($report_type)) {
    $_SESSION['report_error'] = "Please select a report type.";
    header("Location: reports.php");
    exit;
}

$report_title = "";
$columns      = [];
$data         = [];
$params       = [];


if ($report_type === "users") {

    $report_title = "Users Report";

    $columns = [
        "Full Name",
        "Email",
        "Role",
        "Status",
        "Last Login",
        "Date Created"
    ];

    $sql = "
        SELECT
            full_name,
            email,
            role,
            status,
            last_login,
            created_at
        FROM users
        WHERE 1=1
    ";

    if (!empty($date_from)) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY full_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

elseif ($report_type === "employees") {

    $report_title = "Employees Report";

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
            CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
            d.department_name,
            p.position_name,
            e.employment_status,
            e.hire_date
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN positions p ON e.position_id = p.position_id
        WHERE 1=1
    ";

    if (!empty($department_id)) {
        $sql .= " AND e.department_id = ?";
        $params[] = $department_id;
    }

    if (!empty($date_from)) {
        $sql .= " AND e.hire_date >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND e.hire_date <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY e.first_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

elseif ($report_type === "departments") {

    $report_title = "Departments Report";

    $columns = [
        "Department Name",
        "Description",
        "Total Employees",
        "Status",
        "Date Created"
    ];

    $sql = "
        SELECT
            d.department_name,
            d.description,
            (
                SELECT COUNT(*)
                FROM employees e
                WHERE e.department_id = d.department_id
            ) AS total_employees,
            d.status,
            d.created_at
        FROM departments d
        WHERE 1=1
    ";

    if (!empty($department_id)) {
        $sql .= " AND d.department_id = ?";
        $params[] = $department_id;
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(d.created_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(d.created_at) <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY d.department_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

elseif ($report_type === "positions") {

    $report_title = "Positions Report";

    $columns = [
        "Position Name",
        "Department",
        "Status",
        "Date Created"
    ];

    $sql = "
        SELECT
            p.position_name,
            d.department_name,
            p.status,
            p.created_at
        FROM positions p
        LEFT JOIN departments d ON p.department_id = d.department_id
        WHERE 1=1
    ";

    if (!empty($department_id)) {
        $sql .= " AND p.department_id = ?";
        $params[] = $department_id;
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(p.created_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(p.created_at) <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY p.position_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

elseif ($report_type === "audit") {

    $report_title = "Audit Log Report";

    $columns = [
        "Date",
        "User",
        "Activity",
        "Module",
        "Action Type"
    ];

    $sql = "
        SELECT
            al.date_created,
            u.full_name AS user_name,
            al.activity,
            al.module,
            al.action_type
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
        WHERE 1=1
    ";

    if (!empty($date_from)) {
        $sql .= " AND DATE(al.date_created) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(al.date_created) <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY al.date_created DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

elseif ($report_type === "login_attempts") {

    $report_title = "Login Attempts Report";

    $columns = [
        "Email",
        "Attempts",
        "Last Attempt",
        "Locked Until",
        "Status"
    ];

    $sql = "
        SELECT
            email,
            attempt_count,
            last_attempt,
            locked_until,
            CASE
                WHEN locked_until IS NOT NULL AND locked_until > NOW()
                THEN 'Locked'
                ELSE 'Not Locked'
            END AS lock_status
        FROM login_attempts
        WHERE 1=1
    ";

    if (!empty($date_from)) {
        $sql .= " AND DATE(last_attempt) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(last_attempt) <= ?";
        $params[] = $date_to;
    }

    $sql .= " ORDER BY last_attempt DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


try {

    $generated_by = $_SESSION['user_id'] ?? null;

    $log = $conn->prepare("
        INSERT INTO report_logs (generated_by, report_type)
        VALUES (?, ?)
    ");

    $log->execute([$generated_by, $report_title]);

} catch (PDOException $e) {
    // report_logs insert is best-effort only; report still generates
}


$_SESSION['report_title']   = $report_title;
$_SESSION['report_columns'] = $columns;
$_SESSION['report_data']    = $data;
$_SESSION['report_ready']   = true;

header("Location: reports.php");
exit;

?>