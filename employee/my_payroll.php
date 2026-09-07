<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "My Payroll";

$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {
    header("Location: ../login.php");
    exit;
}


$year = $_POST["year"] ?? date("Y");
$month = $_POST["month"] ?? "";


$employeeStmt = $conn->prepare("
    SELECT
        employee_id,
        employee_code,
        first_name,
        middle_name,
        last_name,
        basic_salary
    FROM employees
    WHERE employee_id = :employee_id
");

$employeeStmt->execute([
    ":employee_id" => $employee_id
]);

$employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header("Location: dashboard.php");
    exit;
}


$sql = "
SELECT
    p.payroll_id,
    p.pay_period,
    p.basic_salary,
    p.allowances,
    p.deductions,
    p.gross_pay,
    p.net_pay,
    p.status,
    p.created_at,
    s.payslip_id,
    s.file,
    s.generated_date
FROM payroll p
LEFT JOIN payslips s
    ON p.payroll_id = s.payroll_id
WHERE p.employee_id = :employee_id
";

$params = [
    ":employee_id" => $employee_id
];


if (!empty($year)) {
    $sql .= "
    AND YEAR(p.created_at) = :year
    ";

    $params[":year"] = $year;
}


if (!empty($month)) {
    $sql .= "
    AND MONTH(p.created_at) = :month
    ";

    $params[":month"] = $month;
}

$sql .= "
ORDER BY p.created_at DESC
";

$payrollStmt = $conn->prepare($sql);

$payrollStmt->execute($params);

$payrolls = $payrollStmt->fetchAll(PDO::FETCH_ASSOC);


$latestPayroll = $payrolls[0] ?? null;

if ($latestPayroll) {
    $basicSalary = $latestPayroll["basic_salary"];
    $allowances = $latestPayroll["allowances"];
    $deductions = $latestPayroll["deductions"];
    $grossPay = $latestPayroll["gross_pay"];
    $netPay = $latestPayroll["net_pay"];
    $payStatus = $latestPayroll["status"];
} else {
    $basicSalary = 0;
    $allowances = 0;
    $deductions = 0;
    $grossPay = 0;
    $netPay = 0;
    $payStatus = "No Payroll";
}


$employeeName = trim(
    $employee["first_name"]
    . " "
    . $employee["middle_name"]
    . " "
    . $employee["last_name"]
);


function moneyFormat($amount)
{
    return "₱" . number_format(
        $amount,
        2
    );
}

function statusClass($status)
{
    return match ($status) {
        "Paid" => "paid",
        "Generated" => "generated",
        "Draft" => "draft",
        default => ""
    };
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

                <section class="employee-payroll-page">

                    <div class="employee-payroll-header">

                        <div>

                            <h1>
                                <i class="fa-solid fa-money-check-dollar"></i>
                                My Payroll
                            </h1>

                            <p>View your salary details, payroll history, and payslips.</p>

                        </div>

                    </div>

                    <div class="employee-payroll-summary">

                        <div class="employee-payroll-card blue">

                            <div class="employee-payroll-icon">
                                <i class="fa-solid fa-wallet"></i>
                            </div>

                            <div class="employee-payroll-content">

                                <span>
                                    Basic Salary
                                </span>

                                <h2>
                                    <?= moneyFormat($basicSalary) ?>
                                </h2>

                                <p>Monthly Salary</p>

                            </div>

                        </div>

                        <div class="employee-payroll-card green">

                            <div class="employee-payroll-icon">
                                <i class="fa-solid fa-circle-plus"></i>
                            </div>

                            <div class="employee-payroll-content">

                                <span>
                                    Allowances
                                </span>

                                <h2>
                                    <?= moneyFormat($allowances) ?>
                                </h2>

                                <p>Additional Benefits</p>

                            </div>

                        </div>

                        <div class="employee-payroll-card orange">

                            <div class="employee-payroll-icon">
                                <i class="fa-solid fa-circle-minus"></i>
                            </div>

                            <div class="employee-payroll-content">

                                <span>
                                    Deductions
                                </span>

                                <h2>
                                    <?= moneyFormat($deductions) ?>
                                </h2>

                                <p>Total Deductions</p>

                            </div>

                        </div>

                        <div class="employee-payroll-card gold">

                            <div class="employee-payroll-icon">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>

                            <div class="employee-payroll-content">

                                <span>
                                    Net Pay
                                </span>

                                <h2>
                                    <?= moneyFormat($netPay) ?>
                                </h2>

                                <p>Final Salary</p>

                            </div>

                        </div>

                    </div>

                    <div class="employee-payroll-filter-card">

                                                <form method="POST" action="my_payroll.php">

                            <?php csrfField(); ?>

                            <div class="employee-payroll-filter-grid">

                                <div class="employee-payroll-filter-group">

                                    <label>
                                        <i class="fa-solid fa-calendar"></i>
                                        Year
                                    </label>

                                    <select name="year">

                                        <?php for ($y = date("Y"); $y >= 2020; $y--): ?>

                                            <option
                                                value="<?= $y ?>"
                                                <?= $year == $y ? "selected" : "" ?>
                                            >

                                                <?= $y ?>
                                            </option>

                                        <?php endfor; ?>

                                    </select>

                                </div>

                                <div class="employee-payroll-filter-group">

                                    <label>
                                        <i class="fa-solid fa-calendar-days"></i>
                                        Month
                                    </label>

                                    <select name="month">

                                        <option value="">
                                            All Months
                                        </option>

                                        <option value="1" <?= $month == "1" ? "selected" : "" ?>>
                                            January
                                        </option>

                                        <option value="2" <?= $month == "2" ? "selected" : "" ?>>
                                            February
                                        </option>

                                        <option value="3" <?= $month == "3" ? "selected" : "" ?>>
                                            March
                                        </option>

                                        <option value="4" <?= $month == "4" ? "selected" : "" ?>>
                                            April
                                        </option>

                                        <option value="5" <?= $month == "5" ? "selected" : "" ?>>
                                            May
                                        </option>

                                        <option value="6" <?= $month == "6" ? "selected" : "" ?>>
                                            June
                                        </option>

                                        <option value="7" <?= $month == "7" ? "selected" : "" ?>>
                                            July
                                        </option>

                                        <option value="8" <?= $month == "8" ? "selected" : "" ?>>
                                            August
                                        </option>

                                        <option value="9" <?= $month == "9" ? "selected" : "" ?>>
                                            September
                                        </option>

                                        <option value="10" <?= $month == "10" ? "selected" : "" ?>>
                                            October
                                        </option>

                                        <option value="11" <?= $month == "11" ? "selected" : "" ?>>
                                            November
                                        </option>

                                        <option value="12" <?= $month == "12" ? "selected" : "" ?>>
                                            December
                                        </option>

                                    </select>

                                </div>

                                <div class="employee-payroll-filter-group">

                                    <label>
                                        &nbsp;
                                    </label>

                                    <button
                                        type="submit"
                                        class="employee-payroll-search-btn"
                                    >

                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        Search
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                    <div class="employee-payroll-info-card">

                        <div class="employee-payroll-info-header">

                            <h2>
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                Latest Payroll
                            </h2>

                            <span class="employee-payroll-status <?= statusClass($payStatus) ?>">

                                <?= htmlspecialchars($payStatus) ?>

                            </span>

                        </div>

                        <?php if ($latestPayroll): ?>

                            <div class="employee-payroll-details">

                                <div>

                                    <label>
                                        Employee
                                    </label>

                                    <p>
                                        <?= htmlspecialchars($employeeName) ?>
                                    </p>

                                </div>

                                <div>

                                    <label>
                                        Pay Period
                                    </label>

                                    <p>
                                        <?= htmlspecialchars($latestPayroll["pay_period"]) ?>
                                    </p>

                                </div>

                                <div>

                                    <label>
                                        Gross Pay
                                    </label>

                                    <p>
                                        <?= moneyFormat($grossPay) ?>
                                    </p>

                                </div>

                                <div>

                                    <label>
                                        Net Pay
                                    </label>

                                    <p>
                                        <?= moneyFormat($netPay) ?>
                                    </p>

                                </div>

                                <div>

                                    <label>
                                        Allowances
                                    </label>

                                    <p>
                                        <?= moneyFormat($allowances) ?>
                                    </p>

                                </div>

                                <div>

                                    <label>
                                        Deductions
                                    </label>

                                    <p>
                                        <?= moneyFormat($deductions) ?>
                                    </p>

                                </div>

                            </div>

                        <?php else: ?>

                            <div class="employee-payroll-empty">

                                <i class="fa-solid fa-file-circle-xmark"></i>

                                <h3>No Payroll Available</h3>

                                <p>Your payroll record is not yet generated.</p>

                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="employee-payroll-table-card">

                        <div class="employee-payroll-table-header">

                            <h2>
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Payroll History
                            </h2>

                            <p>Previous payroll records</p>

                        </div>

                        <div class="employee-payroll-table-wrapper">

                            <table class="employee-payroll-table">

                                <thead>

                                    <tr>

                                        <th>Pay Period</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Gross Pay</th>
                                        <th>Net Pay</th>
                                        <th>Status</th>
                                        <th>Actions</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if (!empty($payrolls)): ?>

                                        <?php foreach ($payrolls as $row): ?>

                                            <tr>

                                                <td>

                                                    <div class="employee-payroll-period">
                                                        <i class="fa-solid fa-calendar-days"></i>
                                                        <?= htmlspecialchars($row["pay_period"]) ?>
                                                    </div>

                                                </td>

                                                <td>
                                                    <?= moneyFormat($row["basic_salary"]) ?>
                                                </td>

                                                <td>

                                                    <span class="employee-allowance-badge">
                                                        <i class="fa-solid fa-circle-plus"></i>
                                                        <?= moneyFormat($row["allowances"]) ?>
                                                    </span>

                                                </td>

                                                <td>

                                                    <span class="employee-deduction-badge">
                                                        <i class="fa-solid fa-circle-minus"></i>
                                                        <?= moneyFormat($row["deductions"]) ?>
                                                    </span>

                                                </td>

                                                <td>
                                                    <?= moneyFormat($row["gross_pay"]) ?>
                                                </td>

                                                <td>

                                                    <strong class="employee-net-pay">
                                                        <?= moneyFormat($row["net_pay"]) ?>
                                                    </strong>

                                                </td>

                                                <td>

                                                    <span class="employee-payroll-status <?= statusClass($row["status"]) ?>">
                                                        <?= htmlspecialchars($row["status"]) ?>
                                                    </span>

                                                </td>

                                                <td>

                                                    <div class="employee-payroll-actions">

                                                                                                                <form method="POST" action="payroll_details.php">

                                                            <?php csrfField(); ?>

                                                            <input
                                                                type="hidden"
                                                                name="payroll_id"
                                                                value="<?= htmlspecialchars($row["payroll_id"]) ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="employee-payroll-view-btn"
                                                            >

                                                                <i class="fa-solid fa-eye"></i>
                                                                View Details
                                                            </button>

                                                        </form>

                                                                                                                <form method="POST" action="payslip_pdf.php">

                                                            <?php csrfField(); ?>

                                                            <input
                                                                type="hidden"
                                                                name="payroll_id"
                                                                value="<?= htmlspecialchars($row["payroll_id"]) ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="employee-payroll-pdf-btn"
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

                                            <td colspan="8">

                                                <div class="employee-payroll-empty">

                                                    <i class="fa-solid fa-file-circle-xmark"></i>

                                                    <h3>No Payroll Records Found</h3>

                                                    <p>There are no payroll records available for your account.</p>

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