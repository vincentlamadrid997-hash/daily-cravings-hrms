<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Generate Payroll";


$alert = [
    "type" => "",
    "message" => ""
];


$selected_period = $_POST['pay_period'] ?? date("Y-m");


$employeeQuery = $conn->prepare("

    SELECT
        e.employee_id,
        e.employee_code,
        CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
        e.basic_salary,
        p.position_name

    FROM employees e

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    WHERE e.employment_status = 'Active'

    AND NOT EXISTS (

        SELECT 1
        FROM payroll pr
        WHERE pr.employee_id = e.employee_id
        AND pr.pay_period = ?

    )

    ORDER BY e.first_name ASC

");


$employeeQuery->execute([
    $selected_period
]);


$employee_list = $employeeQuery->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("payroll_generate.php");

    $employee_id = (int)($_POST['employee_id'] ?? 0);

    $pay_period = trim($_POST['pay_period'] ?? "");

    if ($employee_id <= 0 || empty($pay_period)) {

        $alert["type"] = "error";

        $alert["message"] =
            "Please select employee and payroll period.";

    } else {

        $checkPayroll = $conn->prepare("

            SELECT payroll_id
            FROM payroll
            WHERE employee_id = ?
            AND pay_period = ?
            LIMIT 1

        ");

        $checkPayroll->execute([

            $employee_id,
            $pay_period

        ]);


        if ($checkPayroll->fetch()) {

            $alert["type"] = "error";
            $alert["message"] =
                "Payroll already exists for this employee.";

        } else {

            $salaryQuery = $conn->prepare("

                SELECT basic_salary
                FROM employees
                WHERE employee_id = ?
                LIMIT 1

            ");

            $salaryQuery->execute([

                $employee_id

            ]);

            $employee = $salaryQuery->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {

                $alert["type"] = "error";
                $alert["message"] =
                    "Employee record not found.";

            } else {

                $basic_salary = (float)$employee['basic_salary'];

                $allowanceQuery = $conn->prepare("

                    SELECT COALESCE(SUM(amount),0)
                    FROM allowances
                    WHERE employee_id = ?

                ");

                $allowanceQuery->execute([

                    $employee_id

                ]);

                $allowances = (float)$allowanceQuery->fetchColumn();

                $deductionQuery = $conn->prepare("

                    SELECT COALESCE(SUM(amount),0)
                    FROM deductions
                    WHERE employee_id = ?

                ");

                $deductionQuery->execute([

                    $employee_id

                ]);

                $deductions = (float)$deductionQuery->fetchColumn();

                                // Pay period bounds, e.g. "2026-09" -> 2026-09-01 to 2026-09-30
                $period_start = date("Y-m-01", strtotime($pay_period . "-01"));
                $period_end   = date("Y-m-t", strtotime($pay_period . "-01"));

                $attendanceQuery = $conn->prepare("

                    SELECT
                        status,
                        COUNT(*) AS total
                    FROM attendance
                    WHERE employee_id = ?
                    AND attendance_date BETWEEN ? AND ?
                    GROUP BY status

                ");

                $attendanceQuery->execute([

                    $employee_id,
                    $period_start,
                    $period_end

                ]);

                $absent = 0;
                $late = 0;

                while ($row = $attendanceQuery->fetch(PDO::FETCH_ASSOC)) {


                    if ($row['status'] === "Absent") {
                        $absent = (int)$row['total'];

                    }

                    if ($row['status'] === "Late") {
                        $late = (int)$row['total'];

                    }

                }

                $hoursQuery = $conn->prepare("

                    SELECT
                        COALESCE(SUM(TIME_TO_SEC(undertime_hours)),0) AS undertime_seconds,
                        COALESCE(SUM(TIME_TO_SEC(overtime_hours)),0) AS overtime_seconds
                    FROM attendance
                    WHERE employee_id = ?
                    AND attendance_date BETWEEN ? AND ?

                ");

                $hoursQuery->execute([

                    $employee_id,
                    $period_start,
                    $period_end

                ]);

                $hoursRow = $hoursQuery->fetch(PDO::FETCH_ASSOC);

                $undertime_hours = ((float) $hoursRow['undertime_seconds']) / 3600;
                $overtime_hours  = ((float) $hoursRow['overtime_seconds']) / 3600;

                $requiredHoursStmt = $conn->query("
                    SELECT required_hours
                    FROM attendance_settings
                    LIMIT 1
                ");

                $requiredHoursRow = $requiredHoursStmt->fetch(PDO::FETCH_ASSOC);

                $required_hours_per_day = (!empty($requiredHoursRow['required_hours']))
                    ? (float) $requiredHoursRow['required_hours']
                    : 8;

                $daily_rate = $basic_salary / 26;
                $hourly_rate = $daily_rate / $required_hours_per_day;

                $undertime_deduction = $hourly_rate * $undertime_hours;
                $overtime_pay = $hourly_rate * $overtime_hours;

                $attendance_deduction =
                    ($daily_rate * $absent) +
                    (($daily_rate * 0.10) * $late) +
                    $undertime_deduction;

                $gross_pay =
                    $basic_salary +
                    $allowances +
                    $overtime_pay;

                $total_deductions =
                    $deductions +
                    $attendance_deduction;

                $net_pay =
                    $gross_pay -
                    $total_deductions;

                if ($net_pay < 0) {

                    $net_pay = 0;

                }

                $insertPayroll = $conn->prepare("

                    INSERT INTO payroll

                    (
                        employee_id,
                        pay_period,
                        basic_salary,
                        allowances,
                        deductions,
                        gross_pay,
                        net_pay,
                        status
                    )

                    VALUES

                    (
                        ?,?,?,?,?,?,?,?
                    )

                ");

                $success = $insertPayroll->execute([

                    $employee_id,
                    $pay_period,
                    $basic_salary,
                    $allowances,
                    $total_deductions,
                    $gross_pay,
                    $net_pay,
                    "Draft"

                ]);

                if ($success) {

                    $alert["type"] = "success";

                    $alert["message"] =
                        "Payroll generated successfully.";

                } else {

                    $alert["type"] = "error";

                    $alert["message"] =
                        "Failed to generate payroll.";

                }

            }

        }

    }

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

                            <i class="fa-solid fa-money-check-dollar"></i>
                            Generate Payroll

                        </h1>

                        <p>Generate monthly payroll for active employees.</p>

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

                            <i class="fa-solid fa-calculator"></i>

                        </div>

                        <div>

                            <h2>Payroll Information</h2>

                            <p>Select employee and payroll month.</p>

                        </div>

                    </div>

                                                            <form method="POST" id="payrollGenerateForm" novalidate>

                        <?php csrfField(); ?>
                        <div class="hr-pay-crud-form-grid">

                            <div class="hr-pay-crud-form-group">

                                <label>

                                    <i class="fa-solid fa-user"></i>
                                    Employee

                                </label>

                                <select
                                    name="employee_id"
                                    id="employeeSelect"
                                    required
                                >

                                    <option value="">
                                        Select Employee
                                    </option>

                                    <?php foreach ($employee_list as $emp): ?>

                                        <option
                                            value="<?= $emp['employee_id']; ?>"
                                        >

                                            <?= htmlspecialchars($emp['employee_code']); ?>

                                            -

                                            <?= htmlspecialchars($emp['employee_name']); ?>

                                            <?php if (!empty($emp['position_name'])): ?>

                                                (<?= htmlspecialchars($emp['position_name']); ?>)

                                            <?php endif; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="hr-pay-crud-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-days"></i>
                                    Payroll Period

                                </label>

                                <select
                                    name="pay_period"
                                    required
                                >

                                    <option value="">
                                        Select Payroll Month
                                    </option>

                                    <?php

                                    $currentYear = date("Y");

                                    $currentMonth = date("n");

                                    for ($month = $currentMonth; $month <= 12; $month++) {

                                        $value =
                                            $currentYear . "-" .
                                            str_pad($month, 2, "0", STR_PAD_LEFT);

                                        $monthName = date(
                                            "F Y",
                                            mktime(
                                                0,
                                                0,
                                                0,
                                                $month,
                                                1,
                                                $currentYear
                                            )
                                        );

                                    ?>

                                        <option
                                            value="<?= $value; ?>"
                                            <?= ($selected_period == $value) ? "selected" : ""; ?>
                                        >

                                            <?= $monthName; ?>

                                        </option>

                                    <?php } ?>

                                </select>

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
                                        id="basicSalary"
                                        value="0.00"
                                        readonly
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
                                        id="allowances"
                                        value="0.00"
                                        readonly
                                    >

                                </div>

                            </div>

                            <div class="hr-pay-crud-form-group">

                                <label>

                                    <i class="fa-solid fa-minus-circle"></i>
                                    Total Deductions

                                </label>

                                <div class="hr-pay-crud-input-icon">

                                    <span>₱</span>

                                    <input
                                        type="text"
                                        id="deductions"
                                        value="0.00"
                                        readonly
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
                                        id="attendanceDeduction"
                                        value="0.00"
                                        readonly
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
                                        id="grossPay"
                                        value="0.00"
                                        readonly
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
                                        id="netPay"
                                        value="0.00"
                                        readonly
                                    >

                                </div>

                            </div>

                            <div class="hr-pay-crud-actions">

                                <button
                                    type="submit"
                                    class="hr-pay-crud-submit-btn"
                                >

                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    Generate Payroll

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if (!empty($alert["message"])): ?>

<script>

Swal.fire({

    icon: "<?= $alert['type']; ?>",

    title:
        "<?= $alert['type'] == 'success'
            ? 'Payroll Generated!'
            : 'Unable to Generate Payroll'; ?>",

    text: <?= json_encode($alert["message"]); ?>,

    confirmButtonColor: "#003DA5",

    confirmButtonText: "OK",

    allowOutsideClick: false

}).then(() => {

    <?php if ($alert["type"] == "success"): ?>

        window.location.href = "payroll.php";

    <?php endif; ?>

});


</script>

<?php endif; ?>


<script>

const employeeSelect = document.getElementById("employeeSelect");

employeeSelect.addEventListener("change", function(){

    let employee_id = this.value;

    if (employee_id === "") {

        return;

    }

    let pay_period = document.querySelector('select[name="pay_period"]').value;

    fetch(
        "get_payroll_data.php?employee_id=" + employee_id + "&pay_period=" + pay_period
    )

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            document.getElementById("basicSalary").value =
                Number(data.basic_salary)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

            document.getElementById("allowances").value =
                Number(data.allowances)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

            document.getElementById("deductions").value =
                Number(data.deductions)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

            document.getElementById("attendanceDeduction").value =
                Number(data.attendance_deduction)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

            document.getElementById("grossPay").value =
                Number(data.gross_pay)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

            document.getElementById("netPay").value =
                Number(data.net_pay)
                .toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2
                    }
                );

        }


    })

    .catch(error => {

        console.error(error);

    });

});

</script>


<script>
document.getElementById("payrollGenerateForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Generate this payroll?",
        text: "This will create a new payroll record for the selected employee and pay period.",
        showCancelButton: true,
        confirmButtonText: "Yes, Generate",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#003DA5",
        reverseButtons: true
    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

});
</script>

<script src="../assets/js/hr.js"></script>


</body>

</html>