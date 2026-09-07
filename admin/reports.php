<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

$page_title = "Reports";

$error = $_SESSION['report_error'] ?? "";
unset($_SESSION['report_error']);


// ---- Summary cards ---------------------------------------------

$totalUsers = $conn->query("
    SELECT COUNT(*) FROM users
")->fetchColumn();

$totalEmployees = $conn->query("
    SELECT COUNT(*) FROM employees
")->fetchColumn();

$totalDepartments = $conn->query("
    SELECT COUNT(*) FROM departments
")->fetchColumn();

$reportsToday = $conn->query("
    SELECT COUNT(*)
    FROM report_logs
    WHERE DATE(generated_at) = CURDATE()
")->fetchColumn();


// ---- Filter dropdown data ---------------------------------------

$departments = $conn->query("
    SELECT department_id, department_name
    FROM departments
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


// ---- Report history ----------------------------------------------

$reportLogs = $conn->query("
    SELECT
        rl.report_id,
        rl.report_type,
        rl.generated_at,
        u.full_name AS generated_by
    FROM report_logs rl
    INNER JOIN users u
        ON rl.generated_by = u.user_id
    ORDER BY rl.generated_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>

        .rep-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px 0;
        }

        .rep-header h1 {
            font-size: 22px;
            color: #003DA5;
            margin: 0 0 4px;
        }

        .rep-header p {
            margin: 0;
            color: #78909C;
            font-size: 14px;
        }

        .rep-filter-card,
        .rep-table-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            margin: 20px 24px 0;
            padding: 22px;
        }

        .rep-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .rep-filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #607D8B;
            margin-bottom: 6px;
        }

        .rep-filter-group select,
        .rep-filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #E0E4EA;
            border-radius: 10px;
            font-size: 14px;
        }

        .rep-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .rep-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: .25s;
        }

        .rep-btn.generate {
            background: #003DA5;
            color: #fff;
        }

        .rep-btn.generate:hover {
            background: #0D47A1;
        }

        .rep-btn.pdf {
            background: #FDECEA;
            color: #C62828;
        }

        .rep-btn.pdf:hover {
            background: #F9CFCB;
        }

        .rep-btn.excel {
            background: #E6F4EA;
            color: #2E7D32;
        }

        .rep-btn.excel:hover {
            background: #C8E9D0;
        }

        .rep-search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #F5F7FA;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 16px;
            max-width: 340px;
        }

        .rep-search-box i {
            color: #90A4AE;
        }

        .rep-search-box input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .rep-table-wrapper {
            overflow-x: auto;
        }

        .rep-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rep-table th {
            text-align: left;
            font-size: 13px;
            color: #607D8B;
            padding: 12px;
            border-bottom: 2px solid #EEF1F5;
        }

        .rep-table td {
            padding: 12px;
            font-size: 14px;
            border-bottom: 1px solid #F2F4F7;
        }

        .rep-empty {
            text-align: center;
            padding: 40px 20px;
            color: #90A4AE;
        }

        .rep-empty i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

    </style>

</head>
<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

            <div class="admin-user-summary-grid">

                <div class="admin-user-summary-card blue">
                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="admin-user-summary-content">
                        <span>Total Users</span>
                        <h2><?= number_format($totalUsers); ?></h2>
                        <p>System accounts</p>
                    </div>
                </div>

                <div class="admin-user-summary-card green">
                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                    <div class="admin-user-summary-content">
                        <span>Total Employees</span>
                        <h2><?= number_format($totalEmployees); ?></h2>
                        <p>All employee records</p>
                    </div>
                </div>

                <div class="admin-user-summary-card purple">
                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div class="admin-user-summary-content">
                        <span>Departments</span>
                        <h2><?= number_format($totalDepartments); ?></h2>
                        <p>Active and inactive</p>
                    </div>
                </div>

                <div class="admin-user-summary-card orange">
                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div class="admin-user-summary-content">
                        <span>Reports Today</span>
                        <h2><?= number_format($reportsToday); ?></h2>
                        <p>Generated so far today</p>
                    </div>
                </div>

            </div>

            <div class="rep-header">
                <div>
                    <h1><i class="fa-solid fa-chart-column"></i> Reports</h1>
                    <p>Generate and export system-wide reports.</p>
                </div>
            </div>

            <div class="rep-filter-card">

                <form action="report_generate.php" method="POST">

                    <div class="rep-filter-grid">

                        <div class="rep-filter-group">
                            <label>Report Type</label>
                            <select name="report_type" required>
                                <option value="">Select Report</option>
                                <option value="users">Users Report</option>
                                <option value="employees">Employees Report</option>
                                <option value="departments">Departments Report</option>
                                <option value="positions">Positions Report</option>
                                <option value="audit">Audit Log Report</option>
                                <option value="login_attempts">Login Attempts Report</option>
                            </select>
                        </div>

                        <div class="rep-filter-group">
                            <label>Department</label>
                            <select name="department_id">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= $department['department_id']; ?>">
                                        <?= htmlspecialchars($department['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:#90A4AE;">Applies to Employees / Positions</small>
                        </div>

                        <div class="rep-filter-group">
                            <label>Date From</label>
                            <input type="date" name="date_from">
                        </div>

                        <div class="rep-filter-group">
                            <label>Date To</label>
                            <input type="date" name="date_to">
                        </div>

                    </div>

                    <div class="rep-buttons">

                        <button type="submit" class="rep-btn generate">
                            <i class="fa-solid fa-file-circle-plus"></i>
                            Generate Report
                        </button>

                        <a href="report_pdf.php" class="rep-btn pdf">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export PDF
                        </a>

                        <a href="report_excel.php" class="rep-btn excel">
                            <i class="fa-solid fa-file-excel"></i>
                            Export Excel
                        </a>

                    </div>

                </form>

            </div>

            <div class="rep-table-card">

                <div class="rep-header" style="padding:0 0 16px;">
                    <div>
                        <h1 style="font-size:18px;"><i class="fa-solid fa-clock-rotate-left"></i> Report History</h1>
                        <p>Recently generated reports.</p>
                    </div>
                </div>

                <div class="rep-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="reportHistorySearch" placeholder="Search report ID, generated by, report type...">
                </div>

                <div class="rep-table-wrapper">

                    <table class="rep-table">

                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Generated By</th>
                                <th>Report Type</th>
                                <th>Generated Date</th>
                            </tr>
                        </thead>

                        <tbody id="reportHistoryBody">

                            <?php if (!empty($reportLogs)): ?>

                                <?php foreach ($reportLogs as $log): ?>
                                    <tr class="report-history-row">
                                        <td><?= $log['report_id']; ?></td>
                                        <td><?= htmlspecialchars($log['generated_by']); ?></td>
                                        <td><?= htmlspecialchars($log['report_type']); ?></td>
                                        <td><?= date("F d, Y h:i A", strtotime($log['generated_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr id="noReportHistoryResult" style="display:none;">
                                    <td colspan="4">
                                        <div class="rep-empty">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <h3>No Result Found</h3>
                                            <p>No report history matched your search.</p>
                                        </div>
                                    </td>
                                </tr>

                            <?php else: ?>

                                <tr>
                                    <td colspan="4">
                                        <div class="rep-empty">
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

<?php if (!empty($error)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        icon: "error",
        title: "Unable to Continue",
        text: <?= json_encode($error); ?>,
        confirmButtonColor: "#003DA5"
    });
});
</script>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("reportHistorySearch");
    const rows = document.querySelectorAll(".report-history-row");
    const noResult = document.getElementById("noReportHistoryResult");

    if (!searchInput || rows.length === 0) {
        return;
    }

    searchInput.addEventListener("input", function () {

        const keyword = this.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            if (keyword === "" || text.includes(keyword)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        if (noResult) {
            noResult.style.display = visibleCount === 0 ? "" : "none";
        }

    });

});
</script>

<script src="../assets/js/admin.js"></script>

</body>
</html>