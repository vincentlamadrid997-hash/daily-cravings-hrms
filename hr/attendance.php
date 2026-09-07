<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Attendance";


$success = $_SESSION['attendance_success'] ?? "";
$error   = $_SESSION['attendance_error'] ?? "";

unset($_SESSION['attendance_success']);
unset($_SESSION['attendance_error']);


$search = trim($_GET['search'] ?? "");


$totalEmployees = $conn->query("

    SELECT COUNT(*)

    FROM employees

    WHERE employment_status = 'Active'

")->fetchColumn();


$totalAttendance = $conn->query("

    SELECT COUNT(*)

    FROM attendance

")->fetchColumn();


$presentToday = $conn->query("

    SELECT COUNT(*)

    FROM attendance

    WHERE attendance_date = CURDATE()

    AND status = 'Present'

")->fetchColumn();


$lateToday = $conn->query("

    SELECT COUNT(*)

    FROM attendance

    WHERE attendance_date = CURDATE()

    AND status = 'Late'

")->fetchColumn();


$sql = "

SELECT

    e.employee_id,

    e.employee_code,

    e.first_name,

    e.middle_name,

    e.last_name,

    e.employment_status,

    d.department_name,

    p.position_name,

    COUNT(a.attendance_id) AS total_attendance


FROM employees e


LEFT JOIN departments d

ON e.department_id = d.department_id


LEFT JOIN positions p

ON e.position_id = p.position_id


LEFT JOIN attendance a

ON

    e.employee_id = a.employee_id


WHERE

    e.employment_status = 'Active'

";


$params = [];



if (!empty($search)) {

    $sql .= "

    AND (

        e.employee_code LIKE ?

        OR e.first_name LIKE ?

        OR e.middle_name LIKE ?

        OR e.last_name LIKE ?

        OR d.department_name LIKE ?

        OR p.position_name LIKE ?

    )

    ";

    $keyword = "%" . $search . "%";

    $params = [

        $keyword,

        $keyword,

        $keyword,

        $keyword,

        $keyword,

        $keyword

    ];

}


$sql .= "

GROUP BY

    e.employee_id,

    e.employee_code,

    e.first_name,

    e.middle_name,

    e.last_name,

    d.department_name,

    p.position_name,

    e.employment_status


ORDER BY

    e.first_name ASC,

    e.last_name ASC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            <section class="hr-attendance-summary-page">

                <div class="hr-attendance-summary-grid">

                    <div class="hr-attendance-summary-card blue">

                        <div class="hr-attendance-summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div class="hr-attendance-summary-content">

                            <span>Employees</span>

                            <h2><?= $totalEmployees; ?></h2>

                            <p>Active employees</p>

                        </div>

                    </div>

                    <div class="hr-attendance-summary-card green">

                        <div class="hr-attendance-summary-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="hr-attendance-summary-content">

                            <span>Total Records</span>

                            <h2><?= $totalAttendance; ?></h2>

                            <p>Attendance records</p>

                        </div>

                    </div>

                    <div class="hr-attendance-summary-card orange">

                        <div class="hr-attendance-summary-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div class="hr-attendance-summary-content">

                            <span>Present Today</span>

                            <h2><?= $presentToday; ?></h2>

                            <p>Employees present</p>

                        </div>

                    </div>

                    <div class="hr-attendance-summary-card red">

                        <div class="hr-attendance-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="hr-attendance-summary-content">

                            <span>Late Today</span>

                            <h2><?= $lateToday; ?></h2>

                            <p>Late employees</p>

                        </div>

                    </div>

                </div>

                <div class="hr-attendance-summary-header">

                    <div class="hr-attendance-summary-title">

                        <h1>

                            <i class="fa-solid fa-clock"></i>
                            Attendance Management

                        </h1>

                        <p>Monitor employee attendance summary and history.</p>

                    </div>

                </div>

                <div class="hr-attendance-summary-table-card">

                    <div class="hr-attendance-summary-search">

                        <form method="GET">

                            <div class="hr-attendance-summary-search-box">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                <input

                                    type="text"
                                    id="attendanceSummarySearch"
                                    name="search"
                                    placeholder="Search employee..."
                                    value="<?= htmlspecialchars($search); ?>"
                                >

                            </div>

                        </form>

                    </div>

                    <div class="hr-attendance-summary-table-wrapper">

                        <table class="hr-attendance-summary-table">

                            <thead>

                                <tr>

                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Total Attendance</th>
                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody id="attendanceSummaryBody">

                                <?php if (!empty($employees)): ?>

    <?php foreach ($employees as $employee): ?>

        <tr class="attendance-summary-row">

            <td>

                <div class="hr-attendance-summary-name">

                    <div class="hr-attendance-summary-avatar">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <div>

                        <strong>

                            <?= htmlspecialchars(

                                trim(

                                    $employee['first_name']

                                    . " "
                                    .
                                    (
                                        !empty($employee['middle_name'])
                                        ? $employee['middle_name'] . " "
                                        : ""
                                    )
                                    .
                                    $employee['last_name']

                                )

                            ); ?>

                        </strong>

                        <small>

                            <?= htmlspecialchars(

                                $employee['employee_code']

                            ); ?>

                        </small>

                    </div>

                </div>

            </td>

            <td>

                <?= htmlspecialchars(

                    $employee['department_name'] ?? "N/A"

                ); ?>

            </td>

            <td>

                <?= htmlspecialchars(

                    $employee['position_name'] ?? "N/A"

                ); ?>

            </td>

            <td>

                <span class="hr-attendance-summary-badge">

                    <i class="fa-solid fa-calendar-days"></i>

                    <?= (int)$employee['total_attendance']; ?>

                    Day<?= $employee['total_attendance'] != 1 ? "s" : ""; ?>

                </span>

            </td>

            <td>

                <div class="hr-attendance-summary-actions">

                                        <form
                        action="attendance_logs.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input

                            type="hidden"
                            name="employee_id"
                            value="<?= $employee['employee_id']; ?>"
                        >

                        <button

                            type="submit"
                            class="hr-attendance-summary-action history"
                            title="View Attendance History"
                        >

                            <i class="fa-solid fa-clock-rotate-left"></i>
                            View History

                        </button>

                    </form>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>

        <td colspan="5">

            <div class="hr-attendance-summary-empty">

                <i class="fa-solid fa-calendar-xmark"></i>

                <h3>No Employees Found</h3>

                <p>There are currently no active employees available.</p>

            </div>

        </td>

    </tr>

<?php endif; ?>

<tr
    id="attendanceSummaryNoResult"
    style="display:none;"
>

    <td colspan="5">

        <div class="hr-attendance-summary-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Matching Employee Found</h3>

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

const attendanceSummarySearch = document.getElementById(
    "attendanceSummarySearch"
);

if (attendanceSummarySearch) {

    attendanceSummarySearch.addEventListener("keyup", function () {

        const value = this.value.toLowerCase();

        const rows = document.querySelectorAll(
            "#attendanceSummaryBody .attendance-summary-row"
        );

        let hasResult = false;

        rows.forEach(function(row){

            const text = row.textContent.toLowerCase();

            if(text.includes(value)){

                row.style.display = "";

                hasResult = true;

            }else{

                row.style.display = "none";

            }

        });

        const noResult = document.getElementById(
            "attendanceSummaryNoResult"
        );

        if(noResult){

            noResult.style.display = hasResult
                ? "none"
                : "";

        }

    });

}

</script>


<script src="../assets/js/hr.js"></script>


</body>

</html>