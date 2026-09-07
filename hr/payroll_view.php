<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "View Payroll";


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
        p.employee_id,
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


$stmt->execute([$payroll_id]);


$payroll = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$payroll) {

    unset($_SESSION['selected_payroll']);

    header("Location: payroll.php");
    exit;

}


$employee_name = trim(

    $payroll['first_name']
    . " "
    .
    (
        !empty($payroll['middle_name'])
            ? $payroll['middle_name'] . " "
            : ""
    )
    .
    $payroll['last_name']

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

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/payroll_crud.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"=>

</head>


<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-pay-crud-page">

                <div class="hr-pay-crud-header">

                    <div class="hr-pay-crud-title">

                        <h1>

                            <i class="fa-solid fa-eye"></i>
                            Payroll Details

                        </h1>

                        <p>View complete employee payroll information.</p>

                    </div>

                    <div class="hr-pay-crud-header-actions">

                        <a 
                            href="payroll.php"
                            class="hr-pay-crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Payroll
                        </a>

                    </div>

                </div>

                <div class="hr-pay-crud-card">

                    <div class="hr-pay-crud-card-header">

                        <div class="hr-pay-crud-icon">

                            <i class="fa-solid fa-money-check-dollar"></i>

                        </div>

                        <div>

                            <h2>Employee Payroll</h2>

                            <p>Payroll summary and salary breakdown.</p>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-user"></i>
                            Employee Information

                        </h3>

                    </div>

                    <div class="hr-pay-crud-form-grid">

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-id-card"></i>
                                Employee Code

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['employee_code']
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-user"></i>
                                Employee Name

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $employee_name
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-building"></i>
                                Department

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['department_name'] ?? 'N/A'
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-briefcase"></i>
                                Position

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['position_name'] ?? 'N/A'
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-envelope"></i>
                                Email

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['email'] ?? 'N/A'
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-phone"></i>
                                Phone

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['phone'] ?? 'N/A'
                                ); ?>"
                            >

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            Payroll Information

                        </h3>

                    </div>

                    <div class="hr-pay-crud-form-grid">

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-calendar-days"></i>
                                Payroll Period

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['pay_period']
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-file-circle-check"></i>
                                Status

                            </label>

                            <input

                                type="text"
                                readonly

                                value="<?= htmlspecialchars(
                                    $payroll['status']
                                ); ?>"
                            >

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-peso-sign"></i>
                                Basic Salary

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $payroll['basic_salary'],
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                Allowances

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $payroll['allowances'],
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-minus-circle"></i>
                                Deductions

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $payroll['deductions'],
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-calendar-xmark"></i>
                                Attendance Deduction

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $attendance_deduction,
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-arrow-trend-up"></i>
                                Gross Pay

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $payroll['gross_pay'],
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                        <div class="hr-pay-crud-form-group">

                            <label>

                                <i class="fa-solid fa-wallet"></i>
                                Net Pay

                            </label>

                            <div class="hr-pay-crud-input-icon">

                                <span>₱</span>

                                <input

                                    type="text"
                                    readonly

                                    value="<?= number_format(
                                        $payroll['net_pay'],
                                        2
                                    ); ?>"
                                >

                            </div>

                        </div>

                    </div>

                    <div class="payroll-view-actions">

                                                <form 
                            action="payroll_edit.php"
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input

                                type="hidden"
                                name="payroll_id"
                                value="<?= $payroll['payroll_id']; ?>"

                            >

                            <button

                                type="submit"
                                class="crud-btn crud-btn-primary"

                            >

                                <i class="fa-solid fa-pen"></i>
                                Edit Payroll

                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/hr.js"></script>


</body>

</html>