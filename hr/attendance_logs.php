<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Attendance History";


if (isset($_POST['employee_id'])) {
    $_SESSION['attendance_employee_id'] = (int) $_POST['employee_id'];
}

if (!isset($_SESSION['attendance_employee_id'])) {
    header("Location: attendance.php");
    exit;
}

$employee_id = (int) $_SESSION['attendance_employee_id'];


$success = $_SESSION['attendance_success'] ?? "";
$error   = $_SESSION['attendance_error'] ?? "";

unset($_SESSION['attendance_success']);
unset($_SESSION['attendance_error']);


$search = trim($_GET['search'] ?? "");


$employeeQuery = $conn->prepare("
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
    WHERE e.employee_id = ?
    LIMIT 1
");

$employeeQuery->execute([$employee_id]);

$employee = $employeeQuery->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    $_SESSION['attendance_error'] = "Employee not found.";
    header("Location: attendance.php");
    exit;
}


$totalLogsQuery = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
");

$totalLogsQuery->execute([$employee_id]);

$totalLogs = $totalLogsQuery->fetchColumn();


$presentQuery = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
    AND status = 'Present'
");

$presentQuery->execute([$employee_id]);

$totalPresent = $presentQuery->fetchColumn();


$lateQuery = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
    AND status = 'Late'
");

$lateQuery->execute([$employee_id]);

$totalLate = $lateQuery->fetchColumn();


$absentQuery = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance
    WHERE employee_id = ?
    AND status = 'Absent'
");

$absentQuery->execute([$employee_id]);

$totalAbsent = $absentQuery->fetchColumn();


$sql = "
    SELECT
        a.attendance_id,
        a.employee_id,
        a.attendance_date,
        a.time_in,
        a.time_out,
        a.status,
        a.remarks,
        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        d.department_name,
        p.position_name
    FROM attendance a
    LEFT JOIN employees e
        ON a.employee_id = e.employee_id
    LEFT JOIN departments d
        ON e.department_id = d.department_id
    LEFT JOIN positions p
        ON e.position_id = p.position_id
    WHERE a.employee_id = ?
";

$params = [$employee_id];


if (!empty($search)) {
    $sql .= "
        AND (
            a.attendance_date LIKE ?
            OR a.status LIKE ?
            OR a.remarks LIKE ?
            OR d.department_name LIKE ?
            OR p.position_name LIKE ?
        )
    ";

    $keyword = "%" . $search . "%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}


$sql .= "
    ORDER BY
        a.attendance_date DESC,
        a.time_in DESC
";

$stmt = $conn->prepare($sql);

$stmt->execute($params);

$attendanceLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    
    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-attlogs-page">

                <div class="hr-attlogs-summary-grid">

                    <div class="hr-attlogs-summary-card blue">

                        <div class="hr-attlogs-summary-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="hr-attlogs-summary-content">
                            <span>Total Attendance</span>
                            <h2><?= $totalLogs; ?></h2>
                            <p>Total attendance records</p>
                        </div>

                    </div>

                    <div class="hr-attlogs-summary-card green">

                        <div class="hr-attlogs-summary-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div class="hr-attlogs-summary-content">
                            <span>Present</span>
                            <h2><?= $totalPresent; ?></h2>
                            <p>Present records</p>
                        </div>

                    </div>

                    <div class="hr-attlogs-summary-card orange">

                        <div class="hr-attlogs-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="hr-attlogs-summary-content">
                            <span>Late</span>
                            <h2><?= $totalLate; ?></h2>
                            <p>Late records</p>
                        </div>

                    </div>

                    <div class="hr-attlogs-summary-card red">

                        <div class="hr-attlogs-summary-icon">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>

                        <div class="hr-attlogs-summary-content">
                            <span>Absent</span>
                            <h2><?= $totalAbsent; ?></h2>
                            <p>Absent records</p>
                        </div>

                    </div>

                </div>

                <div class="hr-attlogs-header">

                    <div class="hr-attlogs-title">

                        <h1>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Attendance History
                        </h1>

                        <p>Complete attendance history of the selected employee.</p>

                    </div>

                </div>

                <div class="hr-attlogs-table-card">

                    <div class="hr-attlogs-search">

                        <form onsubmit="return false;">

                            <div class="hr-attlogs-search-box">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                <input
                                    type="text"
                                    id="attendanceLogsSearch"
                                    name="search"
                                    placeholder="Search attendance history..."
                                    value="<?= htmlspecialchars($search); ?>"
                                >

                            </div>

                        </form>

                    </div>

                    <div class="hr-attlogs-table-wrapper">

                        <table class="hr-attlogs-table">

                            <thead>

                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody id="attendanceLogsTableBody">

                                <?php if (!empty($attendanceLogs)): ?>
                                    <?php foreach ($attendanceLogs as $record): ?>

                                        <tr class="attendance-logs-row">

            <td>

                <div class="hr-attlogs-name">

                    <div class="hr-attlogs-avatar-table">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars(
                                trim(
                                    $record['first_name'] . " " .
                                    (!empty($record['middle_name'])
                                        ? $record['middle_name'] . " "
                                        : "") .
                                    $record['last_name']
                                )
                            ); ?>
                        </strong>

                        <small>
                            <?= htmlspecialchars($record['employee_code']); ?>
                        </small>

                    </div>

                </div>

            </td>

            <td>
                <?= htmlspecialchars($record['department_name'] ?? "N/A"); ?>
            </td>

            <td>
                <?= htmlspecialchars($record['position_name'] ?? "N/A"); ?>
            </td>

            <td>
                <?= date(
                    "F d, Y",
                    strtotime($record['attendance_date'])
                ); ?>
            </td>

            <td>

                <?php if (!empty($record['time_in'])): ?>

                    <span class="hr-attlogs-time in">

                        <i class="fa-solid fa-right-to-bracket"></i>

                        <?= date(
                            "h:i A",
                            strtotime($record['time_in'])
                        ); ?>

                    </span>

                <?php else: ?>

                    <span class="hr-attlogs-no-time">
                        No Time In
                    </span>

                <?php endif; ?>

            </td>

            <td>

                <?php if (!empty($record['time_out'])): ?>

                    <span class="hr-attlogs-time out">

                        <i class="fa-solid fa-right-from-bracket"></i>

                        <?= date(
                            "h:i A",
                            strtotime($record['time_out'])
                        ); ?>

                    </span>

                <?php else: ?>

                    <span class="hr-attlogs-no-time">
                        No Time Out
                    </span>

                <?php endif; ?>

            </td>

            <td>

                <?php $status = $record['status'] ?? "Absent"; ?>

                <span class="hr-attlogs-status <?= strtolower($status); ?>">

                    <?php if ($status === "Present"): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php elseif ($status === "Late"): ?>

                        <i class="fa-solid fa-clock"></i>

                    <?php elseif ($status === "Leave"): ?>

                        <i class="fa-solid fa-calendar-days"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-xmark"></i>

                    <?php endif; ?>

                    <?= htmlspecialchars($status); ?>

                </span>

            </td>

            <td>

                <div class="hr-attlogs-actions">

                                        <form
                        action="attendance_view.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="attendance_id"
                            value="<?= $record['attendance_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-attlogs-action view"
                            title="View Attendance"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>

                    </form>

                                        <form
                        action="attendance_edit.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="attendance_id"
                            value="<?= $record['attendance_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-attlogs-action edit"
                            title="Edit Attendance"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>

                    </form>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

    <?php else: ?>

<tr>

    <td colspan="8">

        <div class="hr-attlogs-empty">

            <i class="fa-solid fa-calendar-xmark"></i>

            <h3>No Attendance History Found</h3>

            <p>This employee doesn't have any attendance records yet.</p>

        </div>

    </td>

</tr>

<?php endif; ?>

<tr
    id="attendanceLogsNoResult"
    style="display: none;"
>

    <td colspan="8">

        <div class="hr-attlogs-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Matching Attendance Found</h3>

            <p>Try searching another keyword.</p>

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

<?php if (!empty($success)): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({
        icon: "success",
        title: "Success!",
        text: <?= json_encode($success); ?>,
        confirmButtonColor: "#003DA5",
        confirmButtonText: "OK"
    });

});

</script>

<?php endif; ?>

<?php if (!empty($error)): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({
        icon: "error",
        title: "Unable to Continue",
        text: <?= json_encode($error); ?>,
        confirmButtonColor: "#003DA5",
        confirmButtonText: "OK"
    });

});

</script>

<?php endif; ?>

<script>

const attendanceLogsSearch = document.getElementById("attendanceLogsSearch");

if (attendanceLogsSearch) {

    attendanceLogsSearch.addEventListener("keyup", function () {

        const value = this.value.toLowerCase();

        const rows = document.querySelectorAll(
            "#attendanceLogsTableBody .attendance-logs-row"
        );

        let hasResult = false;

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();

            if (text.includes(value)) {

                row.style.display = "";
                hasResult = true;

            } else {

                row.style.display = "none";

            }

        });

        const noResult = document.getElementById("attendanceLogsNoResult");

        if (noResult) {
            noResult.style.display = hasResult ? "none" : "";
        }

    });

}

</script>


<script src="../assets/js/hr.js"></script>


</body>

</html>