<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";


$page_title = "Download Payslip";


if (isset($_POST['payroll_id'])) {

    $_SESSION['selected_payroll'] = (int) $_POST['payroll_id'];

}


if (!isset($_SESSION['selected_payroll'])) {

    header("Location: payroll.php");
    exit;

}


$payroll_id = (int) $_SESSION['selected_payroll'];


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
        e.email,
        e.phone,

        d.department_name,

        pos.position_name

    FROM payroll p

    INNER JOIN employees e
        ON p.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions pos
        ON e.position_id = pos.position_id

    WHERE p.payroll_id = ?

    LIMIT 1

");


$stmt->execute([
    $payroll_id
]);


$payroll = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$payroll) {

    unset($_SESSION['selected_payroll']);

    header("Location: payroll.php");
    exit;

}


$employee_name = trim(

    $payroll['first_name'] . " " .

    (
        !empty($payroll['middle_name'])
        ? $payroll['middle_name'] . " "
        : ""
    )

    . $payroll['last_name']

);


$attendance_deduction =
    $payroll['gross_pay']
    -
    $payroll['net_pay']
    -
    $payroll['deductions'];


if ($attendance_deduction < 0) {

    $attendance_deduction = 0;

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

body {

    font-family: Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 30px;
    color: #263238;

}

.payslip-container {

    max-width: 850px;
    margin: auto;
    background: white;
    padding: 40px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);

}

.company-header {

    text-align: center;
    border-bottom: 2px solid #003DA5;
    padding-bottom: 20px;
    margin-bottom: 30px;

}

.company-header h1 {

    color: #003DA5;
    margin: 0;
    font-size: 30px;
    font-weight: 800;

}

.company-header p {

    margin: 8px 0 0;
    color: #607D8B;

}

.payslip-title {

    text-align: center;
    margin-bottom: 30px;

}

.payslip-title h2 {

    margin: 0;
    color: #263238;

}

.info-section {

    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 20px;
    margin-bottom: 30px;

}

.info-box {

    background: #F8FAFD;
    border: 1px solid #E5EBF3;
    padding: 15px;
    border-radius: 12px;

}

.info-box span {

    display: block;
    color: #78909C;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 5px;

}

.info-box strong {

    font-size: 15px;

}

.salary-table {

    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;

}

.salary-table th {

    background: #003DA5;
    color: white;
    padding: 14px;
    text-align: left;

}

.salary-table td {

    padding: 14px;
    border-bottom: 1px solid #E5EBF3;

}

.amount {

    text-align: right;
    font-weight: 700;

}

.net-pay {

    margin-top: 30px;
    background: #003DA5;
    color: white;
    padding: 20px;
    border-radius: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;

}

.net-pay span {

    font-size: 18px;
    font-weight: 700;

}

.net-pay strong {

    font-size: 25px;

}

.actions {

    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;

}

.btn {

    padding: 13px 25px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;

}

.btn-print {

    background: #003DA5;
    color: white;

}

.btn-back {

    background: #F4A900;
    color: white;

}

@media print {

    body {

        background: white;
        padding: 0;

    }

    .actions {

        display: none;

    }

    .payslip-container {

        box-shadow: none;

    }

}

</style>

</head>

<body>


<div class="payslip-container">

    <div class="company-header">

        <h1>Daily Cravings Foods Inc.</h1>

        <p>Employee Payroll Payslip</p>

    </div>

    <div class="payslip-title">

        <h2>PAYSLIP</h2>

        <p>
            Payroll Period:
            <?= htmlspecialchars($payroll['pay_period']); ?>
        </p>

    </div>

    <div class="info-section">

        <div class="info-box">

            <span>
                Employee Code
            </span>

            <strong>
                <?= htmlspecialchars($payroll['employee_code']); ?>
            </strong>

        </div>



        <div class="info-box">

            <span>
                Employee Name
            </span>

            <strong>
                <?= htmlspecialchars($employee_name); ?>
            </strong>

        </div>



        <div class="info-box">

            <span>
                Department
            </span>

            <strong>
                <?= htmlspecialchars($payroll['department_name'] ?? "N/A"); ?>
            </strong>

        </div>



        <div class="info-box">

            <span>
                Position
            </span>

            <strong>
                <?= htmlspecialchars($payroll['position_name'] ?? "N/A"); ?>
            </strong>

        </div>


    </div>

    <h3>Salary Breakdown</h3>

    <table class="salary-table">

        <tr>

            <th>Description</th>
            <th>Amount</th>

        </tr>

        <tr>

            <td>Basic Salary</td>
            <td class="amount">
                ₱ <?= number_format($payroll['basic_salary'], 2); ?>

            </td>

        </tr>

        <tr>

            <td>Allowance</td>
            <td class="amount">
                ₱ <?= number_format($payroll['allowances'], 2); ?>
            </td>

        </tr>

        <tr>

            <td>Deductions</td>
            <td class="amount">
                ₱ <?= number_format($payroll['deductions'], 2); ?>
            </td>

        </tr>

        <tr>

            <td>Attendance Deduction</td>
            <td class="amount">
                ₱ <?= number_format($attendance_deduction, 2); ?>
            </td>

        </tr>

        <tr>

            <td>Gross Pay</td>
            <td class="amount">
                ₱ <?= number_format($payroll['gross_pay'], 2); ?>
            </td>

        </tr>

    </table>

    <div class="net-pay">

        <span>
            NET PAY
        </span>

        <strong>

            ₱ <?= number_format($payroll['net_pay'], 2); ?>

        </strong>

    </div>

    <div class="actions">

        <a 
            href="payroll.php"
            class="btn btn-back"
        >

            <i class="fa-solid fa-arrow-left"></i>
            Back to Payroll

        </a>

        <button
            onclick="printPayslip()"
            class="btn btn-print"
        >

            <i class="fa-solid fa-print"></i>
            Print / Save PDF

        </button>

    </div>

</div>

<script>

function printPayslip() {

    window.print();

}

window.onafterprint = function () {

    window.location.href = "payroll.php";

};

</script>


</body>

</html>