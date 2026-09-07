<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Payroll Details";

$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {
    header("Location: ../login.php");
    exit;
}

requireCSRFToken("my_payroll.php");

if (!isset($_POST["payroll_id"])) {
    header("Location: my_payroll.php");
    exit;
}

$payroll_id = $_POST["payroll_id"];

$stmt = $conn->prepare("

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

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,
    e.basic_salary AS employee_salary,
    d.department_name,
    po.position_name

FROM payroll p

INNER JOIN employees e
    ON p.employee_id = e.employee_id

LEFT JOIN departments d
    ON e.department_id = d.department_id

LEFT JOIN positions po
    ON e.position_id = po.position_id

WHERE
    p.payroll_id = :payroll_id
AND
    p.employee_id = :employee_id

");

$stmt->execute([
    ":payroll_id"  => $payroll_id,
    ":employee_id" => $employee_id
]);

$payroll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payroll) {
    header("Location: my_payroll.php");
    exit;
}

$employeeName = trim(
    $payroll["first_name"]
    . " "
    . $payroll["middle_name"]
    . " "
    . $payroll["last_name"]
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

    <link rel="stylesheet" href="../assets/css/crud_employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-payroll-details-page">

                <div class="employee-payroll-details-header">

                    <div class="employee-payroll-details-title">

                        <h1>
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            Payroll Details
                        </h1>

                        <p>View your complete payroll information and salary breakdown.</p>

                    </div>

                    <form method="POST" action="my_payroll.php">

                        <button class="employee-payroll-details-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Payroll

                        </button>

                    </form>

                </div>

                <div class="employee-payroll-details-card">

                    <div class="employee-payroll-details-card-header">

                        <div class="employee-payroll-details-icon">

                            <i class="fa-solid fa-user"></i>

                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Personal employment details</p>

                        </div>

                    </div>

                    <div class="employee-payroll-details-grid">

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>

                            <p>
                                <?= htmlspecialchars($payroll["employee_code"]) ?>
                            </p>

                        </div>

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Employee Name
                            </label>

                            <p>
                                <?= htmlspecialchars($employeeName) ?>
                            </p>

                        </div>

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <p>
                                <?= htmlspecialchars(
                                    $payroll["department_name"] ?? "N/A"
                                ) ?>
                            </p>

                        </div>

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <p>
                                <?= htmlspecialchars(
                                    $payroll["position_name"] ?? "N/A"
                                ) ?>
                            </p>

                        </div>

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-calendar"></i>
                                Pay Period
                            </label>

                            <p>
                                <?= htmlspecialchars($payroll["pay_period"]) ?>
                            </p>

                        </div>

                        <div class="employee-payroll-details-group">

                            <label>
                                <i class="fa-solid fa-clock"></i>
                                Generated Date
                            </label>

                            <p>
                                <?= date(
                                    "F d, Y",
                                    strtotime($payroll["created_at"])
                                ) ?>
                            </p>

                        </div>

                    </div>

                </div>

                <div class="employee-payroll-details-card">

                    <div class="employee-payroll-details-card-header">

                        <div class="employee-payroll-details-icon">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>

                        <div>

                            <h2>Payroll Summary</h2>

                            <p>Salary computation overview</p>

                        </div>

                    </div>

                    <div class="employee-payroll-details-summary">

                        <div class="employee-payroll-summary-box">

                            <span>
                                Basic Salary
                            </span>

                            <h3>
                                <?= moneyFormat($payroll["basic_salary"]) ?>
                            </h3>

                        </div>

                        <div class="employee-payroll-summary-box">

                            <span>
                                Allowances
                            </span>

                            <h3>
                                <?= moneyFormat($payroll["allowances"]) ?>
                            </h3>

                        </div>

                        <div class="employee-payroll-summary-box">

                            <span>
                                Deductions
                            </span>

                            <h3>
                                <?= moneyFormat($payroll["deductions"]) ?>
                            </h3>

                        </div>

                    </div>

                </div>

                <div class="employee-payroll-details-card">

                    <div class="employee-payroll-details-card-header">

                        <div class="employee-payroll-details-icon">
                            <i class="fa-solid fa-wallet"></i>
                        </div>

                        <div>

                            <h2>Final Payroll Result</h2>

                            <p>Total salary received</p>

                        </div>

                    </div>

                    <div class="employee-payroll-details-summary">

                        <div class="employee-payroll-summary-box">

                            <span>
                                Gross Pay
                            </span>

                            <h3>
                                <?= moneyFormat($payroll["gross_pay"]) ?>
                            </h3>

                        </div>

                        <div class="employee-payroll-summary-box">

                            <span>
                                Net Pay
                            </span>

                            <h3 class="employee-net-highlight">

                                <?= moneyFormat($payroll["net_pay"]) ?>

                            </h3>

                        </div>

                        <div class="employee-payroll-summary-box">

                            <span>
                                Payroll Status
                            </span>

                            <h3>

                                <span class="employee-payroll-status <?= statusClass($payroll["status"]) ?>">

                                    <?= htmlspecialchars($payroll["status"]) ?>

                                </span>

                            </h3>

                        </div>

                    </div>

                    <div class="employee-payroll-details-actions">

                        <form method="POST" action="payslip_pdf.php">

                            <input
                                type="hidden"
                                name="payroll_id"
                                value="<?= $payroll["payroll_id"] ?>"
                            >

                            <button
                                type="submit"
                                class="employee-payroll-details-btn download"
                            >

                                <i class="fa-solid fa-file-pdf"></i>
                                Download Payslip

                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/employee.js"></script>


</body>

</html>