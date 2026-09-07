<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Reports Management";

$error = $_SESSION['report_error'] ?? "";

unset($_SESSION['report_error']);


$totalEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
")->fetchColumn();


$totalApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
")->fetchColumn();


$totalPayroll = $conn->query("
    SELECT COUNT(*)
    FROM payroll
")->fetchColumn();


$pendingLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE status = 'Pending'
")->fetchColumn();


$departments = $conn->query("
    SELECT
        department_id,
        department_name
    FROM departments
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$employees = $conn->query("
    SELECT
        employee_id,
        employee_code,
        first_name,
        middle_name,
        last_name
    FROM employees
    WHERE employment_status = 'Active'
    ORDER BY first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$reportLogs = $conn->query("
    SELECT
        rl.report_id,
        rl.report_type,
        rl.generated_at,
        u.full_name AS generated_by
    FROM report_logs rl
    INNER JOIN users u
        ON rl.generated_by = u.user_id
    WHERE u.role = 'hr'
    ORDER BY rl.generated_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?>
        | Daily Cravings Foods Inc.
    </title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/hr.css">
    <link rel="stylesheet" href="../assets/css/reports_hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-rep-page">

<div class="hr-rep-summary-grid">

    <div class="hr-rep-summary-card blue">

        <div class="hr-rep-summary-icon">
            <i class="fa-solid fa-users"></i>
        </div>

        <div class="hr-rep-summary-content">

            <span>
                Total Employees
            </span>

            <h2>
                <?= $totalEmployees; ?>
            </h2>

            <p>Active employee records</p>

        </div>

    </div>

    <div class="hr-rep-summary-card green">

        <div class="hr-rep-summary-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>

        <div class="hr-rep-summary-content">

            <span>
                Total Applicants
            </span>

            <h2>
                <?= $totalApplicants; ?>
            </h2>

            <p>Submitted applications</p>

        </div>

    </div>

    <div class="hr-rep-summary-card orange">

        <div class="hr-rep-summary-icon">
            <i class="fa-solid fa-money-check-dollar"></i>
        </div>

        <div class="hr-rep-summary-content">

            <span>
                Payroll Records
            </span>

            <h2>
                <?= $totalPayroll; ?>
            </h2>

            <p>Generated payroll records</p>

        </div>

    </div>

    <div class="hr-rep-summary-card red">

        <div class="hr-rep-summary-icon">
            <i class="fa-solid fa-calendar-xmark"></i>
        </div>

        <div class="hr-rep-summary-content">

            <span>
                Pending Leaves
            </span>

            <h2>
                <?= $pendingLeaves; ?>
            </h2>

            <p>Waiting for approval</p>

        </div>

    </div>

</div>

<div class="hr-rep-header">

    <div class="hr-rep-title">

        <h1>
            <i class="fa-solid fa-chart-column"></i>
            Reports Management
        </h1>

        <p>Generate, export and manage HR reports.</p>

    </div>

</div>

<div class="hr-rep-filter-card">

        <form action="report_generate.php" method="POST">

        <?php csrfField(); ?>

        <div class="hr-rep-filter-grid">

            <div class="hr-rep-filter-group">

                <label>
                    Report Type
                </label>

                <select name="report_type" required>

                    <option value="">
                        Select Report
                    </option>

                    <option value="employee">
                        Employee Report
                    </option>

                    <option value="attendance">
                        Attendance Report
                    </option>

                    <option value="payroll">
                        Payroll Report
                    </option>

                    <option value="leave">
                        Leave Report
                    </option>

                    <option value="resignation">
                        Resignation Report
                    </option>

                    <option value="applicant">
                        Applicant Report
                    </option>

                </select>

            </div>

            <div class="hr-rep-filter-group">

                <label>
                    Department
                </label>

                <select name="department_id">

                    <option value="">
                        All Departments
                    </option>

                    <?php foreach($departments as $department): ?>

                        <option value="<?= $department['department_id']; ?>">

                            <?= htmlspecialchars($department['department_name']); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="hr-rep-filter-group">

                <label>
                    Employee
                </label>

                <select name="employee_id">

                    <option value="">
                        All Employees
                    </option>

                    <?php foreach($employees as $employee): ?>

                        <option value="<?= $employee['employee_id']; ?>">

                            <?= htmlspecialchars(
                                $employee['employee_code']
                                ." - "
                                .$employee['first_name']
                                ." "
                                .(!empty($employee['middle_name'])
                                    ? $employee['middle_name']." "
                                    : ""
                                )
                                .$employee['last_name']
                            ); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="hr-rep-filter-group">

                <label>
                    Date From
                </label>

                <input type="date" name="date_from">

            </div>

            <div class="hr-rep-filter-group">

                <label>
                    Date To
                </label>

                <input type="date" name="date_to">

            </div>

        </div>

        <div class="hr-rep-buttons">

            <button 
                type="submit" 
                class="hr-rep-btn generate"
            >

                <i class="fa-solid fa-file-circle-plus"></i>
                Generate Report
            </button>


            <a 
                href="report_pdf.php" 
                class="hr-rep-btn pdf"
            >

                <i class="fa-solid fa-file-pdf"></i>
                Export PDF
            </a>


            <a 
                href="report_excel.php" 
                class="hr-rep-btn excel"
            >

                <i class="fa-solid fa-file-excel"></i>
                Export Excel
            </a>

        </div>

    </form>

</div>

<div class="hr-rep-table-card">

    <div class="hr-rep-header">

        <div class="hr-rep-title">

            <h1>

                <i class="fa-solid fa-clock-rotate-left"></i>
                Report History

            </h1>

            <p>Recently generated HR reports.</p>

        </div>

    </div>

    <div class="hr-rep-search">

        <div class="hr-rep-search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="reportHistorySearch"
                placeholder="Search report ID, generated by, report type..."
                autocomplete="off"
            >

        </div>

    </div>

    <div class="hr-rep-table-wrapper">

        <table class="hr-rep-table">

            <thead>

                <tr>

                    <th>Report ID</th>
                    <th>Generated By</th>
                    <th>Report Type</th>
                    <th>Generated Date</th>

                </tr>

            </thead>

            <tbody id="reportHistoryBody">

                <?php if(!empty($reportLogs)): ?>

                    <?php foreach($reportLogs as $log): ?>

                        <tr class="report-history-row">

                            <td>
                                <?= $log['report_id']; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['generated_by']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['report_type']); ?>
                            </td>

                            <td>

                                <?= date(
                                    "F d, Y h:i A",
                                    strtotime($log['generated_at'])
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <tr 
                        id="noReportHistoryResult" 
                        style="display:none;"
                    >

                        <td colspan="4">

                            <div class="hr-rep-empty">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                <h3>No Result Found</h3>

                                <p>No report history matched your search.</p>

                            </div>

                        </td>

                    </tr>

                <?php else: ?>

                    <tr>

                        <td colspan="4">

                            <div class="hr-rep-empty">

                                <i class="fa-solid fa-clock-rotate-left"></i>

                                <h3>No Report History</h3>

                                <p>Generated reports will appear here.</p>

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

<?php if(!empty($error)): ?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

        Swal.fire({

            icon: "error",

            title: "Unable to Continue",

            text: <?= json_encode($error); ?>,

            confirmButtonColor: "#003DA5"

        });

    }
);

</script>

<?php endif; ?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const searchInput = document.getElementById(
            "reportHistorySearch"
        );


        const rows = document.querySelectorAll(
            ".report-history-row"
        );


        const noResult = document.getElementById(
            "noReportHistoryResult"
        );


        if(!searchInput || rows.length === 0){

            return;

        }


        searchInput.addEventListener(
            "input",
            function(){

                const keyword = this.value
                    .toLowerCase()
                    .trim();


                let visibleCount = 0;


                rows.forEach(function(row){

                    const text = row.textContent
                        .toLowerCase();


                    if(keyword === "" || text.includes(keyword)){


                        row.style.display = "";

                        visibleCount++;


                    } else {


                        row.style.display = "none";


                    }


                });


                if(noResult){

                    noResult.style.display =

                        visibleCount === 0

                        ? ""

                        : "none";

                }


            }
        );


    }   
);

</script>


<script src="../assets/js/hr.js"></script>


</body>

</html>